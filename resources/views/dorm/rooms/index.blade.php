@extends('admin.layout')

@section('title', 'Rooms - Fanous Admin')

@section('content')
    @php
        $freeBeds = max(0, $totalCapacity - $occupiedBeds);
        $occupancyRate = $totalCapacity > 0 ? min(100, round(($occupiedBeds / $totalCapacity) * 100)) : 0;
        $activeRooms = $rooms->where('status', 'active')->count();
        $maintenanceRooms = $rooms->where('status', 'maintenance')->count();
        $closedRooms = $rooms->where('status', 'closed')->count();
        $roomStatusNames = [
            'active' => 'Active',
            'maintenance' => 'Maintenance',
            'closed' => 'Closed',
        ];
    @endphp

    <div class="student-command-shell">
        <div class="student-command-copy">
            <span class="student-command-kicker">Dorm control</span>
            <h1>Rooms & Capacity</h1>
            <p>Manage room readiness, bed usage, and student allocation from one calm overview.</p>
        </div>
        <div class="student-command-actions">
            <a href="{{ route('dorm.students.index') }}" class="btn btn-outline-secondary">Students</a>
            <a href="{{ route('dorm.rooms.create') }}" class="btn btn-primary">Create room</a>
        </div>
    </div>

    <div class="student-insight-grid">
        <div class="student-insight-card is-primary">
            <span>Total capacity</span>
            <strong>{{ $totalCapacity }}</strong>
            <p>{{ $occupiedBeds }} occupied beds</p>
        </div>
        <div class="student-insight-card">
            <span>Free beds</span>
            <strong>{{ $freeBeds }}</strong>
            <p>{{ $occupancyRate }}% dorm occupancy</p>
        </div>
        <div class="student-insight-card">
            <span>Active rooms</span>
            <strong>{{ $activeRooms }}</strong>
            <p>{{ $maintenanceRooms }} maintenance</p>
        </div>
        <div class="student-insight-card">
            <span>Registered rooms</span>
            <strong>{{ $rooms->count() }}</strong>
            <p>{{ $closedRooms }} closed rooms</p>
        </div>
    </div>

    <div class="room-board-layout">
        <section class="student-workspace-panel room-occupancy-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Occupancy</span>
                    <h2>Dorm usage</h2>
                    <p>Current bed usage across all registered rooms.</p>
                </div>
            </div>
            <div class="room-ring" style="--value: {{ $occupancyRate }}">
                <span>{{ $occupancyRate }}%</span>
            </div>
            <div class="room-stats-row">
                <div><span>Used</span><strong>{{ $occupiedBeds }}</strong></div>
                <div><span>Free</span><strong>{{ $freeBeds }}</strong></div>
                <div><span>Total</span><strong>{{ $totalCapacity }}</strong></div>
            </div>
        </section>

        <section class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Directory</span>
                    <h2>Room cards</h2>
                    <p>Open a room to allocate, move, or remove students.</p>
                </div>
                <a href="{{ route('dorm.rooms.create') }}" class="btn btn-primary">Create room</a>
            </div>

            <div class="room-directory-grid">
                @forelse ($rooms as $room)
                    @php
                        $roomFreeBeds = max(0, $room->capacity - $room->occupied_beds);
                        $usedPercent = $room->capacity > 0 ? min(100, round(($room->occupied_beds / $room->capacity) * 100)) : 0;
                        $badgeClass = match ($room->status) {
                            'active' => 'badge-outline-success',
                            'maintenance' => 'badge-outline-warning',
                            'closed' => 'badge-outline-danger',
                            default => 'badge-outline-secondary',
                        };
                    @endphp
                    <article class="room-command-card">
                        <div class="room-command-head">
                            <div>
                                <span class="student-command-kicker">{{ $room->floor ?: 'Floor not recorded' }}</span>
                                <h3>Room {{ $room->room_number }}</h3>
                            </div>
                            <span class="badge {{ $badgeClass }}">{{ $roomStatusNames[$room->status] ?? $room->status }}</span>
                        </div>

                        <div class="room-usage-line">
                            <span style="width: {{ $usedPercent }}%"></span>
                        </div>

                        <div class="room-stats-row">
                            <div><span>Capacity</span><strong>{{ $room->capacity }}</strong></div>
                            <div><span>Occupied</span><strong>{{ $room->occupied_beds }}</strong></div>
                            <div><span>Free</span><strong>{{ $roomFreeBeds }}</strong></div>
                        </div>

                        <p>{{ $room->notes ?: 'No notes recorded for this room.' }}</p>

                        <div class="student-profile-actions">
                            <a href="{{ route('dorm.rooms.show', $room) }}" class="btn btn-primary btn-sm">Manage</a>
                            <a href="{{ route('dorm.rooms.edit', $room) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                        </div>
                    </article>
                @empty
                    <div class="student-directory-empty">
                        <strong>No rooms yet</strong>
                        <p>Create the first room to start assigning students.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
