<?php

namespace App\Http\Controllers;

use App\Models\DormRoom;
use App\Models\DormStudent;
use App\Services\DormRoomService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DormRoomController extends Controller
{
    public function __construct(private readonly DormRoomService $roomService)
    {
    }

    public function index(): View
    {
        $rooms = DormRoom::query()
            ->withCount(['activeStudents as occupied_beds'])
            ->orderBy('room_number')
            ->get();

        return view('dorm.rooms.index', [
            'rooms' => $rooms,
            'totalCapacity' => (int) $rooms->sum('capacity'),
            'occupiedBeds' => (int) $rooms->sum('occupied_beds'),
            'statusLabels' => $this->roomService->statusLabels(),
        ]);
    }

    public function create(): View
    {
        return view('dorm.rooms.form', [
            'room' => new DormRoom(['capacity' => 4, 'status' => 'active', 'building' => 'اصلی']),
            'statusLabels' => $this->roomService->statusLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->roomService->create($request);

        return redirect()
            ->route('dorm.rooms.index')
            ->with('status', 'اتاق جدید ساخته شد.');
    }

    public function edit(DormRoom $room): View
    {
        return view('dorm.rooms.form', [
            'room' => $room,
            'statusLabels' => $this->roomService->statusLabels(),
        ]);
    }

    public function show(DormRoom $room): View
    {
        $room->load(['activeStudents' => fn ($query) => $query->orderBy('full_name')])
            ->loadCount(['activeStudents as occupied_beds']);

        return view('dorm.rooms.show', [
            'room' => $room,
            'statusLabels' => $this->roomService->statusLabels(),
            'unassignedStudents' => $this->unassignedStudents(),
            'roomsForMove' => $this->roomsForMove($room),
        ]);
    }

    public function update(Request $request, DormRoom $room): RedirectResponse
    {
        $this->roomService->update($request, $room);

        return redirect()
            ->route('dorm.rooms.index')
            ->with('status', 'اتاق به‌روزرسانی شد.');
    }

    public function destroy(DormRoom $room): RedirectResponse
    {
        if ($room->activeStudents()->exists()) {
            throw ValidationException::withMessages([
                'room' => 'برای حذف اتاق، اول همه محصلین فعال را تخلیه یا انتقال کنید.',
            ]);
        }

        $oldValues = $room->only(['room_number', 'building', 'floor', 'capacity', 'status']);
        $room->delete();
        Audit::record('dorm_room_deleted', $room, $oldValues, [], request());

        return redirect()
            ->route('dorm.rooms.index')
            ->with('status', 'اتاق حذف شد.');
    }

    public function storeAllocation(Request $request, DormRoom $room): RedirectResponse
    {
        $validated = $request->validate([
            'dorm_student_id' => ['required', 'exists:dorm_students,id'],
            'bed_number' => ['required', 'integer', 'min:1'],
        ]);

        $student = DormStudent::findOrFail($validated['dorm_student_id']);

        if ($student->dorm_room_id) {
            throw ValidationException::withMessages([
                'dorm_student_id' => 'این محصل قبلاً اتاق دارد. برای تغییر اتاق از بخش انتقال استفاده کنید.',
            ]);
        }

        $this->ensureRoomCanHost($room, null, $validated['bed_number'] ?? null, 'dorm_student_id');
        $this->assignStudentToRoom($student, $room, $validated['bed_number'] ?? null);

        return redirect()
            ->route('dorm.rooms.show', $room)
            ->with('status', 'محصل به اتاق اضافه شد.');
    }

    public function moveStudent(Request $request, DormRoom $room, DormStudent $student): RedirectResponse
    {
        if ((int) $student->dorm_room_id !== (int) $room->id) {
            abort(404);
        }

        $validated = $request->validate([
            'target_room_id' => ['required', 'exists:dorm_rooms,id', Rule::notIn([$room->id])],
            'bed_number' => ['required', 'integer', 'min:1'],
        ]);

        $targetRoom = DormRoom::findOrFail($validated['target_room_id']);
        $this->ensureRoomCanHost($targetRoom, $student, $validated['bed_number'] ?? null);
        $this->assignStudentToRoom($student, $targetRoom, $validated['bed_number'] ?? null);

        return redirect()
            ->route('dorm.rooms.show', $targetRoom)
            ->with('status', 'محصل به اتاق جدید انتقال شد.');
    }

    public function removeStudent(Request $request, DormRoom $room, DormStudent $student): RedirectResponse
    {
        if ((int) $student->dorm_room_id !== (int) $room->id) {
            abort(404);
        }

        $validated = $request->validate([
            'left_at' => ['required', 'date'],
        ]);

        if ($student->joined_at && $student->joined_at->gt($validated['left_at'])) {
            throw ValidationException::withMessages([
                'left_at' => 'تاریخ خروج نمی‌تواند قبل از تاریخ ورود باشد.',
            ]);
        }

        $student->update([
            'dorm_room_id' => null,
            'room_number' => null,
            'bed_number' => null,
            'active_bed_key' => null,
            'left_at' => $validated['left_at'],
            'status' => 'left',
        ]);

        return redirect()
            ->route('dorm.rooms.show', $room)
            ->with('status', 'محصل از اتاق خارج شد.');
    }

    private function unassignedStudents()
    {
        return DormStudent::query()
            ->where('status', 'active')
            ->whereNull('dorm_room_id')
            ->orderBy('full_name')
            ->get();
    }

    private function roomsForMove(DormRoom $currentRoom)
    {
        return DormRoom::query()
            ->withCount(['activeStudents as occupied_beds'])
            ->whereKeyNot($currentRoom->id)
            ->where('status', 'active')
            ->orderBy('room_number')
            ->get();
    }

    private function ensureRoomCanHost(DormRoom $room, ?DormStudent $student = null, ?string $bedNumber = null, string $roomField = 'target_room_id'): void
    {
        if ($room->status !== 'active') {
            throw ValidationException::withMessages([$roomField => 'اتاق انتخاب‌شده فعلاً فعال نیست.']);
        }

        $occupiedBeds = $room->activeStudents()
            ->when($student, fn ($query) => $query->whereKeyNot($student->id))
            ->count();

        if ($occupiedBeds >= $room->capacity) {
            throw ValidationException::withMessages([$roomField => 'ظرفیت این اتاق تکمیل است.']);
        }

        if ($bedNumber === null || $bedNumber === '') {
            throw ValidationException::withMessages(['bed_number' => 'نمبر تخت ضروری است.']);
        }

        if (! ctype_digit((string) $bedNumber) || (int) $bedNumber < 1 || (int) $bedNumber > (int) $room->capacity) {
            throw ValidationException::withMessages([
                'bed_number' => 'نمبر تخت باید بین ۱ و ظرفیت اتاق باشد.',
            ]);
        }

        if ($bedNumber) {
            $bedIsTaken = DormStudent::query()
                ->where('dorm_room_id', $room->id)
                ->where('bed_number', $bedNumber)
                ->where('status', 'active')
                ->when($student, fn ($query) => $query->whereKeyNot($student->id))
                ->exists();

            if ($bedIsTaken) {
                throw ValidationException::withMessages(['bed_number' => 'این تخت در اتاق انتخاب‌شده قبلاً گرفته شده است.']);
            }
        }
    }

    private function assignStudentToRoom(DormStudent $student, DormRoom $room, ?string $bedNumber = null): void
    {
        $student->update([
            'dorm_room_id' => $room->id,
            'room_number' => $room->room_number,
            'bed_number' => $bedNumber,
            'active_bed_key' => $room->id.':'.$bedNumber,
            'left_at' => null,
            'status' => 'active',
        ]);

        Audit::record('dorm_student_room_assigned', $student, [], $student->only(['dorm_room_id', 'room_number', 'bed_number', 'active_bed_key']), request());
    }
}
