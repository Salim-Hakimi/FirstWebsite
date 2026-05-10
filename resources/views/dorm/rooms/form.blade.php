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

    <div class="student-form-hero">
        <div>
            <span class="student-command-kicker">Room setup</span>
            <h1>{{ $room->exists ? 'Edit Room' : 'Create Room' }}</h1>
            <p>Define the room number, capacity, floor, and availability before students are assigned.</p>
        </div>
        <div class="student-command-actions">
            <a href="{{ route('dorm.rooms.index') }}" class="btn btn-outline-secondary">Back to rooms</a>
        </div>
    </div>

    <form method="POST" action="{{ $room->exists ? route('dorm.rooms.update', $room) : route('dorm.rooms.store') }}">
        @csrf
        @if ($room->exists)
            @method('PUT')
        @endif

        <div class="student-form-layout">
            <div class="student-form-main">
                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span>01</span>
                        <div>
                            <h2>Room profile</h2>
                            <p>Capacity accepts the dormitory standards: 4, 6, or 8 beds.</p>
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group">
                            <label for="room_number">Room number</label>
                            <input id="room_number" class="form-control @error('room_number') is-invalid @enderror" name="room_number" value="{{ old('room_number', $room->room_number) }}" required>
                            @error('room_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="capacity">Capacity</label>
                            <select id="capacity" class="form-control @error('capacity') is-invalid @enderror" name="capacity" required>
                                @foreach ([4, 6, 8] as $capacity)
                                    <option value="{{ $capacity }}" @selected((int) old('capacity', $room->capacity) === $capacity)>{{ $capacity }} beds</option>
                                @endforeach
                            </select>
                            @error('capacity') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="floor">Floor</label>
                            <input id="floor" class="form-control @error('floor') is-invalid @enderror" name="floor" value="{{ old('floor', $room->floor) }}" placeholder="Example: 2nd floor">
                            @error('floor') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                @foreach ($roomStatusNames as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $room->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span>02</span>
                        <div>
                            <h2>Management note</h2>
                            <p>Keep condition, facilities, or temporary restrictions visible for the dorm team.</p>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="notes">Notes</label>
                        <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="6" placeholder="Room condition, facilities, or management notes">{{ old('notes', $room->notes) }}</textarea>
                        @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </section>
            </div>

            <aside class="student-form-section is-sticky">
                <h3 class="student-side-title">Capacity rules</h3>
                <div class="student-status-preview">
                    <strong>Allowed sizes</strong>
                    <p>Only 4, 6, and 8 bed rooms are accepted.</p>
                </div>
                <div class="student-status-preview">
                    <strong>Active rooms</strong>
                    <p>Students can be assigned only to active rooms.</p>
                </div>
                <div class="student-status-preview">
                    <strong>Safe update</strong>
                    <p>Capacity cannot be lower than current occupancy.</p>
                </div>

                <div class="student-save-panel">
                    <button class="btn btn-primary" type="submit">{{ $room->exists ? 'Save room' : 'Create room' }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('dorm.rooms.index') }}">Cancel</a>
                </div>
            </aside>
        </div>
    </form>
@endsection
