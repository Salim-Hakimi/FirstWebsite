@extends('admin.layout')

@section('title', $room->exists ? 'Edit Room - Fanous Admin' : 'Create Room - Fanous Admin')

@section('content')
    @php
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
                            <h3 class="mb-1">{{ $room->exists ? 'Edit Room' : 'Create Room' }}</h3>
                            <p class="mb-0 text-white-50">Define room number, capacity, floor, and availability so allocation rules stay accurate.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('dorm.rooms.index') }}" class="btn btn-outline-light btn-rounded">Back to rooms</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $room->exists ? route('dorm.rooms.update', $room) : route('dorm.rooms.store') }}">
        @csrf
        @if ($room->exists)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Room profile</h4>
                        <p class="card-description">Rooms can only host students when status is active and capacity is available.</p>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="room_number">Room number</label>
                                <input id="room_number" class="form-control @error('room_number') is-invalid @enderror" name="room_number" value="{{ old('room_number', $room->room_number) }}" required>
                                @error('room_number') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="capacity">Capacity</label>
                                <select id="capacity" class="form-control @error('capacity') is-invalid @enderror" name="capacity" required>
                                    @foreach ([4, 6, 8] as $capacity)
                                        <option value="{{ $capacity }}" @selected((int) old('capacity', $room->capacity) === $capacity)>{{ $capacity }} beds</option>
                                    @endforeach
                                </select>
                                @error('capacity') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="floor">Floor</label>
                                <input id="floor" class="form-control @error('floor') is-invalid @enderror" name="floor" value="{{ old('floor', $room->floor) }}" placeholder="Example: 2nd floor">
                                @error('floor') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="status">Status</label>
                                <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                    @foreach ($roomStatusNames as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $room->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12 form-group">
                                <label for="notes">Notes</label>
                                <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="6" placeholder="Room condition, facilities, or management notes">{{ old('notes', $room->notes) }}</textarea>
                                @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Capacity rules</h4>
                        <div class="preview-list">
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>1</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">Allowed sizes</p>
                                    <p class="text-muted mb-0">Only 4, 6, and 8 bed rooms are accepted.</p>
                                </div>
                            </div>
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>2</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">Active rooms</p>
                                    <p class="text-muted mb-0">Students can be assigned only to active rooms.</p>
                                </div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-thumbnail"><div class="preview-icon bg-danger"><span>3</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">Safe update</p>
                                    <p class="text-muted mb-0">Capacity cannot be lower than current occupancy.</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap mt-4">
                            <button class="btn btn-primary mr-2" type="submit">{{ $room->exists ? 'Save room' : 'Create room' }}</button>
                            <a class="btn btn-dark" href="{{ route('dorm.rooms.index') }}">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
