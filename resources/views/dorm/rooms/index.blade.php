@extends('admin.layout')

@section('title', 'Rooms - Fanous Admin')

@section('content')
    @php
        $freeBeds = max(0, $totalCapacity - $occupiedBeds);
        $occupancyRate = $totalCapacity > 0 ? min(100, round(($occupiedBeds / $totalCapacity) * 100)) : 0;
        $roomStatusNames = [
            'active' => 'Active',
            'maintenance' => 'Maintenance',
            'closed' => 'Closed',
        ];
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Rooms & Capacity</h3>
                            <p class="mb-0 text-white-50">Track room status, capacity, occupied beds, and available beds across the dormitory.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('dorm.rooms.create') }}" class="btn btn-outline-light btn-rounded">+ Create room</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9"><h3 class="mb-0">{{ $totalCapacity }}</h3></div>
                        <div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">C</span></div></div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Total capacity</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9"><h3 class="mb-0">{{ $occupiedBeds }}</h3></div>
                        <div class="col-3"><div class="icon icon-box-warning"><span class="metric-icon">O</span></div></div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Occupied beds</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9"><h3 class="mb-0">{{ $freeBeds }}</h3></div>
                        <div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">F</span></div></div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Free beds</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9"><h3 class="mb-0">{{ $rooms->count() }}</h3></div>
                        <div class="col-3"><div class="icon icon-box-danger"><span class="metric-icon">R</span></div></div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Registered rooms</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Dorm occupancy</h4>
                    <p class="text-muted">Current usage across all registered rooms.</p>
                    <div class="room-ring" style="--value: {{ $occupancyRate }}">
                        <span>{{ $occupancyRate }}%</span>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <span class="text-muted">Used</span>
                        <strong>{{ $occupiedBeds }} / {{ $totalCapacity }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted">Available</span>
                        <strong>{{ $freeBeds }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Room directory</h4>
                            <p class="text-muted mb-0">Open a room to allocate, move, or remove students.</p>
                        </div>
                        <a href="{{ route('dorm.rooms.create') }}" class="btn btn-primary mt-3 mt-md-0">Create room</a>
                    </div>

                    <div class="row">
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
                            <div class="col-md-6 grid-margin stretch-card">
                                <div class="card room-mini-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h4 class="mb-1">Room {{ $room->room_number }}</h4>
                                                <p class="text-muted mb-0">Floor: {{ $room->floor ?: 'Not recorded' }}</p>
                                            </div>
                                            <span class="badge {{ $badgeClass }}">{{ $roomStatusNames[$room->status] ?? $room->status }}</span>
                                        </div>

                                        <div class="progress progress-md mt-4">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $usedPercent }}%" aria-valuenow="{{ $usedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                        <div class="room-stats-row">
                                            <div><span>Capacity</span><strong>{{ $room->capacity }}</strong></div>
                                            <div><span>Occupied</span><strong>{{ $room->occupied_beds }}</strong></div>
                                            <div><span>Free</span><strong>{{ $roomFreeBeds }}</strong></div>
                                        </div>

                                        <p class="text-muted mt-3 mb-4">{{ $room->notes ?: 'No notes recorded.' }}</p>

                                        <div class="d-flex flex-wrap">
                                            <a href="{{ route('dorm.rooms.show', $room) }}" class="btn btn-primary btn-sm mr-2">Manage</a>
                                            <a href="{{ route('dorm.rooms.edit', $room) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center text-muted py-5">No rooms have been created yet.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
