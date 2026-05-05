@extends('admin.layout')

@section('title', 'Room '.$room->room_number.' - Fanous Admin')

@section('content')
    @php
        $freeBeds = max(0, $room->capacity - $room->occupied_beds);
        $usedPercent = $room->capacity > 0 ? min(100, round(($room->occupied_beds / $room->capacity) * 100)) : 0;
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
                            <h3 class="mb-1">Room {{ $room->room_number }}</h3>
                            <p class="mb-0 text-white-50">Assign students, move residents to another room, and keep bed usage clean.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('dorm.rooms.edit', $room) }}" class="btn btn-outline-light btn-rounded">Edit room</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9"><h3 class="mb-0">{{ $room->capacity }}</h3></div>
                        <div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">C</span></div></div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Capacity</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9"><h3 class="mb-0">{{ $room->occupied_beds }}</h3></div>
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
                        <div class="col-9">
                            <h3 class="mb-0 room-status-heading">{{ $roomStatusNames[$room->status] ?? $room->status }}</h3>
                        </div>
                        <div class="col-3"><div class="icon icon-box-danger"><span class="metric-icon">S</span></div></div>
                    </div>
                    <h6 class="text-muted font-weight-normal">{{ $room->floor ?: 'Floor not recorded' }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Students in this room</h4>
                            <p class="text-muted mb-0">Capacity used: {{ $usedPercent }}%</p>
                        </div>
                        <a class="btn btn-outline-secondary mt-3 mt-md-0" href="{{ route('dorm.rooms.index') }}">Back to rooms</a>
                    </div>

                    <div class="progress progress-md mb-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $usedPercent }}%" aria-valuenow="{{ $usedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Bed</th>
                                    <th>Phone</th>
                                    <th>Move</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($room->activeStudents as $student)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-table-avatar">{{ strtoupper(substr($student->full_name, 0, 1)) }}</div>
                                                <div>
                                                    <span class="font-weight-bold">{{ $student->full_name }}</span>
                                                    <div class="text-muted small">Father: {{ $student->father_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $student->bed_number ?: 'Not recorded' }}</td>
                                        <td>{{ $student->phone }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('dorm.rooms.students.move', [$room, $student]) }}" class="room-inline-form">
                                                @csrf
                                                @method('PUT')
                                                <select class="form-control form-control-sm" name="target_room_id" required>
                                                    <option value="">Target room</option>
                                                    @foreach ($roomsForMove as $targetRoom)
                                                        <option value="{{ $targetRoom->id }}">
                                                            Room {{ $targetRoom->room_number }} · free {{ max(0, $targetRoom->capacity - $targetRoom->occupied_beds) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input class="form-control form-control-sm" name="bed_number" placeholder="Bed">
                                                <button class="btn btn-outline-primary btn-sm" type="submit">Move</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('dorm.rooms.students.remove', [$room, $student]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No active students are assigned to this room.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Assign student</h4>
                    <p class="card-description">Only active students without a room are listed here.</p>

                    @if ($freeBeds > 0 && $room->status === 'active')
                        <form method="POST" action="{{ route('dorm.rooms.allocations.store', $room) }}">
                            @csrf
                            <div class="form-group">
                                <label for="dorm_student_id">Student</label>
                                <select id="dorm_student_id" class="form-control @error('dorm_student_id') is-invalid @enderror" name="dorm_student_id" required>
                                    <option value="">Select student</option>
                                    @foreach ($unassignedStudents as $student)
                                        <option value="{{ $student->id }}" @selected(old('dorm_student_id') == $student->id)>
                                            {{ $student->full_name }} · {{ $student->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('dorm_student_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="bed_number">Bed number</label>
                                <input id="bed_number" class="form-control @error('bed_number') is-invalid @enderror" name="bed_number" value="{{ old('bed_number') }}" placeholder="Optional">
                                @error('bed_number') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <button class="btn btn-primary btn-block" type="submit">Add to room</button>
                        </form>
                    @else
                        <div class="user-access-note">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-danger"><span>!</span></div>
                            </div>
                            <p class="text-muted mb-0">This room is not active or has no available beds.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
