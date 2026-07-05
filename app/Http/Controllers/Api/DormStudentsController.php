<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DormStudent;
use App\Support\DormStudentDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DormStudentsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'room' => ['nullable', 'string', 'max:40'],
            'date' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:24'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 8);
        $canManage = $request->user()?->canAccessAdmin() ?? false;

        $students = DormStudentDirectory::visibleQuery($validated)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $students->getCollection()->map(fn (DormStudent $student): array => $this->studentPayload($student, $canManage))->values(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
            'filters' => [
                'q' => $validated['q'] ?? '',
                'status' => $validated['status'] ?? '',
                'room' => $validated['room'] ?? '',
                'date' => $validated['date'] ?? '',
            ],
        ]);
    }

    private function studentPayload(DormStudent $student, bool $canManage): array
    {
        $card = $student->membershipCards->first();
        $roomNumber = $student->room?->room_number ?: $student->room_number;

        return [
            'id' => $student->id,
            'student_code' => 'STD-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT),
            'full_name' => $student->full_name,
            'father_name' => $student->father_name,
            'phone' => $student->whatsapp ?: $student->phone,
            'status' => $student->status,
            'room_number' => $student->status === 'active' ? ($roomNumber ?: null) : null,
            'bed_number' => $student->bed_number,
            'document_count' => count($student->document_names ?? []),
            'joined_at' => $student->joined_at?->toDateString(),
            'created_at' => $student->created_at?->toDateString(),
            'card_expires_at' => $card?->expires_at?->toDateString(),
            'profile_photo_url' => $student->profile_photo_path ? asset('storage/'.$student->profile_photo_path) : null,
            'links' => [
                'show' => route('dorm.students.show', $student),
                'edit' => $canManage ? route('dorm.students.edit', $student) : null,
            ],
        ];
    }

    private function statusLabels(): array
    {
        return [
            'active' => 'فعال',
            'waiting' => 'در انتظار',
            'on_hold' => 'ناقص',
            'rejected' => 'رد شده',
            'suspended' => 'مسدود',
            'graduated' => 'فارغ شده',
            'left' => 'خارج شده',
        ];
    }
}
