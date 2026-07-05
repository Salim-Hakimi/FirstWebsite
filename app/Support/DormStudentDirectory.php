<?php

namespace App\Support;

use App\Models\DormStudent;

class DormStudentDirectory
{
    public static function visibleQuery(array $filters = [])
    {
        return DormStudent::query()
            ->with(['room', 'membershipCards' => fn ($query) => $query->where('scope', 'dorm')->latest('expires_at')])
            ->whereNotIn('status', ['waiting', 'on_hold', 'rejected'])
            ->when(
                ($filters['status'] ?? null) && ! in_array($filters['status'], ['waiting', 'on_hold', 'rejected'], true),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when($filters['room'] ?? null, function ($query, string $room): void {
                $query->where(function ($query) use ($room): void {
                    $query
                        ->where('room_number', $room)
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('room_number', $room));
                });
            })
            ->when($filters['date'] ?? null, function ($query, string $date): void {
                $query->where(function ($query) use ($date): void {
                    $query
                        ->whereDate('created_at', $date)
                        ->orWhereDate('application_date', $date);
                });
            })
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tazkira_number', 'like', "%{$search}%")
                        ->orWhere('room_number', 'like', "%{$search}%")
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('room_number', 'like', "%{$search}%"));
                });
            });
    }
}
