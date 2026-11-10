<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'service_id' => Service::factory(),
            'appointment_date' => now()->addDays(1)->toDateString(),
            'appointment_time' => '10:00:00',
            'service_duration_minutes' => 30,
            'service_price' => 50,
            'status' => Appointment::STATUS_PENDING,
        ];
    }
}
