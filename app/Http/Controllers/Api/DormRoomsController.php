<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DormRoom;
use App\Services\DormRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DormRoomsController extends Controller
{
    public function __construct(private readonly DormRoomService $rooms)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', Rule::in(array_keys($this->rooms->statusLabels()))],
            'floor' => ['nullable', 'string', 'max:40'],
        ]);

        $allRooms = DormRoom::query()
            ->withCount(['activeStudents as occupied_beds'])
            ->orderBy('room_number')
            ->get();

        $rooms = $allRooms
            ->when($validated['q'] ?? null, function ($items, string $search) {
                $needle = mb_strtolower($search);

                return $items->filter(fn (DormRoom $room): bool => str_contains(mb_strtolower((string) $room->room_number), $needle)
                    || str_contains(mb_strtolower((string) $room->floor), $needle));
            })
            ->when($validated['status'] ?? null, fn ($items, string $status) => $items->where('status', $status))
            ->when($validated['floor'] ?? null, fn ($items, string $floor) => $items->where('floor', $floor))
            ->values();

        return response()->json([
            'data' => $rooms->map(fn (DormRoom $room): array => $this->roomPayload($room))->values(),
            'summary' => $this->summaryPayload($allRooms),
            'filters' => [
                'q' => $validated['q'] ?? '',
                'status' => $validated['status'] ?? '',
                'floor' => $validated['floor'] ?? '',
                'floors' => $allRooms->pluck('floor')->filter()->unique()->values(),
                'statuses' => $this->rooms->statusLabels(),
            ],
        ]);
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'statuses' => $this->rooms->statusLabels(),
            'capacities' => [4, 6, 8],
            'defaults' => [
                'capacity' => 4,
                'status' => 'active',
            ],
        ]);
    }

    public function show(DormRoom $room): JsonResponse
    {
        $room->loadCount(['activeStudents as occupied_beds']);

        return response()->json([
            'data' => $this->roomPayload($room),
            'form' => [
                'room_number' => $room->room_number,
                'capacity' => (int) $room->capacity,
                'floor' => $room->floor,
                'status' => $room->status,
                'notes' => $room->notes,
            ],
            'options' => [
                'statuses' => $this->rooms->statusLabels(),
                'capacities' => [4, 6, 8],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $room = $this->rooms->create($request)->loadCount(['activeStudents as occupied_beds']);

        return response()->json([
            'message' => 'اتاق جدید ساخته شد.',
            'data' => $this->roomPayload($room),
        ], 201);
    }

    public function update(Request $request, DormRoom $room): JsonResponse
    {
        $room = $this->rooms->update($request, $room)->loadCount(['activeStudents as occupied_beds']);

        return response()->json([
            'message' => 'اتاق به‌روزرسانی شد.',
            'data' => $this->roomPayload($room),
        ]);
    }

    private function roomPayload(DormRoom $room): array
    {
        $occupiedBeds = (int) $room->occupied_beds;
        $capacity = (int) $room->capacity;
        $freeBeds = max(0, $capacity - $occupiedBeds);

        return [
            'id' => $room->id,
            'room_number' => $room->room_number,
            'floor' => $room->floor,
            'status' => $room->status,
            'status_label' => $this->rooms->statusLabels()[$room->status] ?? $room->status,
            'capacity' => $capacity,
            'occupied_beds' => $occupiedBeds,
            'free_beds' => $freeBeds,
            'usage_percent' => $capacity > 0 ? min(100, round(($occupiedBeds / $capacity) * 100)) : 0,
            'notes' => $room->notes,
            'links' => [
                'show' => route('dorm.rooms.show', $room),
                'edit' => route('dorm.rooms.edit', $room),
                'api_show' => route('api.dorm.rooms.show', $room),
                'api_update' => route('api.dorm.rooms.update', $room),
            ],
        ];
    }

    private function summaryPayload($rooms): array
    {
        $totalCapacity = (int) $rooms->sum('capacity');
        $occupiedBeds = (int) $rooms->sum('occupied_beds');

        return [
            'total_rooms' => $rooms->count(),
            'active_rooms' => $rooms->where('status', 'active')->count(),
            'maintenance_rooms' => $rooms->where('status', 'maintenance')->count(),
            'closed_rooms' => $rooms->where('status', 'closed')->count(),
            'total_capacity' => $totalCapacity,
            'occupied_beds' => $occupiedBeds,
            'free_beds' => max(0, $totalCapacity - $occupiedBeds),
            'occupancy_rate' => $totalCapacity > 0 ? min(100, round(($occupiedBeds / $totalCapacity) * 100)) : 0,
        ];
    }
}
