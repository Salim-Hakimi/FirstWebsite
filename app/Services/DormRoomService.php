<?php

namespace App\Services;

use App\Models\DormRoom;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DormRoomService
{
    /**
     * @return array<string, string>
     */
    public function statusLabels(): array
    {
        return [
            'active' => 'فعال',
            'maintenance' => 'در تعمیر',
            'closed' => 'بسته',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validate(Request $request, ?DormRoom $room = null): array
    {
        $building = trim((string) $request->input('building'));
        $floor = trim((string) $request->input('floor'));

        return $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('dorm_rooms', 'room_number')
                    ->where(fn ($query) => $query->where('building', $building)->where('floor', $floor))
                    ->ignore($room),
            ],
            'building' => ['required', 'string', 'max:80'],
            'capacity' => ['required', 'integer', Rule::in([4, 6, 8])],
            'floor' => ['required', 'string', 'max:40'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'notes' => ['nullable', 'string', 'max:700'],
        ]);
    }

    public function create(Request $request): DormRoom
    {
        $room = DormRoom::create($this->validate($request));
        Audit::record('dorm_room_created', $room, [], $room->only(['room_number', 'building', 'capacity', 'floor', 'status']), $request);

        return $room;
    }

    public function update(Request $request, DormRoom $room): DormRoom
    {
        $validated = $this->validate($request, $room);
        $occupiedBeds = $room->activeStudents()->count();

        if ((int) $validated['capacity'] < $occupiedBeds) {
            throw ValidationException::withMessages([
                'capacity' => "ظرفیت نمی‌تواند کمتر از تعداد محصلین فعلی اتاق ({$occupiedBeds}) باشد.",
            ]);
        }

        $oldValues = $room->only(['room_number', 'building', 'capacity', 'floor', 'status', 'notes']);
        $room->update($validated);

        Audit::record('dorm_room_updated', $room, $oldValues, $room->fresh()->only(['room_number', 'building', 'capacity', 'floor', 'status', 'notes']), $request);

        return $room->fresh();
    }
}
