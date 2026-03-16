<?php

declare(strict_types=1);

namespace App\Validators;

use App\Models\Booking;
use App\Models\User;
use App\Models\Client;

class BookingValidator
{
    /**
     * @param array<string, mixed> $data
     * @param int|null $excludeBookingId
     * @return array<string, string>
     */
    public function validate(array $data, ?int $excludeBookingId = null): array
    {
        $errors = [];

        if (empty(trim($data['title'] ?? ''))) {
            $errors['title'] = 'Title is required.';
        }

        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User is required.';
        } elseif (!User::find((int) $data['user_id'])) {
            $errors['user_id'] = 'Selected user does not exist.';
        }

        if (empty($data['client_id'])) {
            $errors['client_id'] = 'Client is required.';
        } elseif (!Client::find((int) $data['client_id'])) {
            $errors['client_id'] = 'Selected client does not exist.';
        }

        $startTime = $this->parseDateTime($data['start_time'] ?? '');
        $endTime = $this->parseDateTime($data['end_time'] ?? '');

        if (!$startTime) {
            $errors['start_time'] = 'A valid start time is required.';
        }

        if (!$endTime) {
            $errors['end_time'] = 'A valid end time is required.';
        }

        if ($startTime && $endTime) {
            if ($startTime >= $endTime) {
                $errors['end_time'] = 'End time must be after start time.';
            } elseif (!empty($data['user_id'])) {
                if ($this->hasOverlap((int) $data['user_id'], $startTime->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s'), $excludeBookingId)) {
                    $errors['overlap'] = 'This booking overlaps with an existing booking for this user.';
                }
            }
        }

        return $errors;
    }

    private function hasOverlap(int $userId, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = Booking::where('user_id', $userId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function parseDateTime(string $value): ?\DateTimeImmutable
    {
        if (empty($value)) {
            return null;
        }

        $formats = ['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];

        foreach ($formats as $format) {
            $dt = \DateTimeImmutable::createFromFormat($format, $value);
            if ($dt !== false) {
                return $dt;
            }
        }

        return null;
    }
}
