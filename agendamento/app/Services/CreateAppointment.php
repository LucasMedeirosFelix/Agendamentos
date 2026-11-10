<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateAppointment
{
    /**
     * @param  array{customer_name: string, phone: string, service_id: int, appointment_date: string, appointment_time: string}  $attributes
     */
    public function create(array $attributes): Appointment
    {
        return DB::transaction(function () use ($attributes): Appointment {
            $service = Service::query()
                ->whereKey($attributes['service_id'])
                ->where('is_active', true)
                ->firstOrFail();

            $this->lockDate($attributes['appointment_date']);
            $this->ensureAvailable(
                $attributes['appointment_date'],
                $attributes['appointment_time'],
                $service->duration_minutes,
            );

            return Appointment::query()->create([
                ...$attributes,
                'service_duration_minutes' => $service->duration_minutes,
                'service_price' => $service->price,
                'status' => Appointment::STATUS_PENDING,
            ]);
        });
    }

    public function changeStatus(Appointment $appointment, string $status): void
    {
        DB::transaction(function () use ($appointment, $status): void {
            $this->lockDate($appointment->appointment_date);

            if ($status !== Appointment::STATUS_CANCELLED) {
                $this->ensureAvailable(
                    $appointment->appointment_date,
                    $appointment->appointment_time,
                    $appointment->service_duration_minutes,
                    $appointment->id,
                );
            }

            $appointment->update(['status' => $status]);
        });
    }

    private function lockDate(string $date): void
    {
        DB::table('appointment_day_locks')->insertOrIgnore([
            'appointment_date' => $date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('appointment_day_locks')
            ->where('appointment_date', $date)
            ->lockForUpdate()
            ->first();
    }

    private function ensureAvailable(string $date, string $time, int $duration, ?int $ignoreId = null): void
    {
        $requestedStart = Carbon::parse("{$date} {$time}");
        $requestedEnd = $requestedStart->copy()->addMinutes($duration);

        $appointments = Appointment::query()
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->get(['id', 'appointment_time', 'service_duration_minutes']);

        foreach ($appointments as $existingAppointment) {
            $existingStart = Carbon::parse("{$date} {$existingAppointment->appointment_time}");
            $existingEnd = $existingStart->copy()->addMinutes($existingAppointment->service_duration_minutes);

            if ($existingStart->lt($requestedEnd) && $existingEnd->gt($requestedStart)) {
                throw ValidationException::withMessages([
                    'appointment_time' => 'Este horário se sobrepõe a outro atendimento. Escolha um novo horário.',
                ]);
            }
        }
    }
}
