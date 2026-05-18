<?php

namespace App\Http\Controllers;

use App\Models\DormRoom;
use App\Models\DormStudent;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DormRoomController extends Controller
{
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
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function create(): View
    {
        return view('dorm.rooms.form', [
            'room' => new DormRoom(['capacity' => 4, 'status' => 'active']),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $room = DormRoom::create($this->validateRoom($request));
        Audit::record('dorm_room_created', $room, [], $room->only(['room_number', 'capacity', 'floor', 'status']), $request);

        return redirect()
            ->route('dorm.rooms.index')
            ->with('status', 'اتاق جدید ساخته شد.');
    }

    public function edit(DormRoom $room): View
    {
        return view('dorm.rooms.form', [
            'room' => $room,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function show(DormRoom $room): View
    {
        $room->load(['activeStudents' => fn ($query) => $query->orderBy('full_name')])
            ->loadCount(['activeStudents as occupied_beds']);

        return view('dorm.rooms.show', [
            'room' => $room,
            'statusLabels' => $this->statusLabels(),
            'unassignedStudents' => $this->unassignedStudents(),
            'roomsForMove' => $this->roomsForMove($room),
        ]);
    }

    public function storeAllocation(Request $request, DormRoom $room): RedirectResponse
    {
        $validated = $request->validate([
            'dorm_student_id' => ['required', 'exists:dorm_students,id'],
            'bed_number' => ['nullable', 'string', 'max:40'],
        ]);

        $student = DormStudent::findOrFail($validated['dorm_student_id']);

        if ($student->dorm_room_id) {
            throw ValidationException::withMessages([
                'dorm_student_id' => 'این محصل قبلا اتاق دارد. برای تغییر اتاق از بخش انتقال استفاده کنید.',
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
            'bed_number' => ['nullable', 'string', 'max:40'],
        ]);

        $targetRoom = DormRoom::findOrFail($validated['target_room_id']);
        $this->ensureRoomCanHost($targetRoom, $student, $validated['bed_number'] ?? null);
        $this->assignStudentToRoom($student, $targetRoom, $validated['bed_number'] ?? null);

        return redirect()
            ->route('dorm.rooms.show', $targetRoom)
            ->with('status', 'محصل به اتاق جدید انتقال شد.');
    }

    public function removeStudent(DormRoom $room, DormStudent $student): RedirectResponse
    {
        if ((int) $student->dorm_room_id !== (int) $room->id) {
            abort(404);
        }

        $student->update([
            'dorm_room_id' => null,
            'room_number' => null,
            'bed_number' => null,
        ]);

        return redirect()
            ->route('dorm.rooms.show', $room)
            ->with('status', 'محصل از اتاق خارج شد.');
    }

    public function update(Request $request, DormRoom $room): RedirectResponse
    {
        $validated = $this->validateRoom($request, $room);
        $occupiedBeds = $room->activeStudents()->count();

        if ($validated['capacity'] < $occupiedBeds) {
            return back()
                ->withInput()
                ->withErrors(['capacity' => "ظرفیت نمی‌تواند کمتر از تعداد محصلین فعلی اتاق ({$occupiedBeds}) باشد."]);
        }

        $oldValues = $room->only(['room_number', 'capacity', 'floor', 'status', 'notes']);
        $room->update($validated);
        Audit::record('dorm_room_updated', $room, $oldValues, $room->fresh()->only(['room_number', 'capacity', 'floor', 'status', 'notes']), $request);

        return redirect()
            ->route('dorm.rooms.index')
            ->with('status', 'اتاق به‌روزرسانی شد.');
    }

    private function validateRoom(Request $request, ?DormRoom $room = null): array
    {
        return $request->validate([
            'room_number' => ['required', 'string', 'max:40', Rule::unique('dorm_rooms', 'room_number')->ignore($room)],
            'capacity' => ['required', 'integer', Rule::in([4, 6, 8])],
            'floor' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'notes' => ['nullable', 'string', 'max:700'],
        ]);
    }

    private function statusLabels(): array
    {
        return [
            'active' => 'فعال',
            'maintenance' => 'در تعمیر',
            'closed' => 'بسته',
        ];
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
            throw ValidationException::withMessages([$roomField => 'اتاق انتخاب‌شده فعلا فعال نیست.']);
        }

        $occupiedBeds = $room->activeStudents()
            ->when($student, fn ($query) => $query->whereKeyNot($student->id))
            ->count();

        if ($occupiedBeds >= $room->capacity) {
            throw ValidationException::withMessages([$roomField => 'ظرفیت این اتاق تکمیل است.']);
        }

        if ($bedNumber) {
            $bedIsTaken = DormStudent::query()
                ->where('dorm_room_id', $room->id)
                ->where('bed_number', $bedNumber)
                ->where('status', 'active')
                ->when($student, fn ($query) => $query->whereKeyNot($student->id))
                ->exists();

            if ($bedIsTaken) {
                throw ValidationException::withMessages(['bed_number' => 'این تخت در اتاق انتخاب‌شده قبلا گرفته شده است.']);
            }
        }
    }

    private function assignStudentToRoom(DormStudent $student, DormRoom $room, ?string $bedNumber = null): void
    {
        $student->update([
            'dorm_room_id' => $room->id,
            'room_number' => $room->room_number,
            'bed_number' => $bedNumber,
        ]);
        Audit::record('dorm_student_room_assigned', $student, [], $student->only(['dorm_room_id', 'room_number', 'bed_number']), request());
    }
}
