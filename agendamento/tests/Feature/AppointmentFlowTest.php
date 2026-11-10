<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AppointmentFlowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customer_can_request_an_appointment(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 45, 'price' => 85]);
        $date = now()->addDays(2)->toDateString();

        $response = $this->post(route('appointments.store'), [
            'customer_name' => 'Marina Costa',
            'phone' => '11987654321',
            'service_id' => $service->id,
            'appointment_date' => $date,
            'appointment_time' => '09:30',
        ]);

        $response->assertRedirect(route('booking'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('appointments', [
            'customer_name' => 'Marina Costa',
            'service_id' => $service->id,
            'appointment_date' => $date,
            'appointment_time' => '09:30',
            'service_duration_minutes' => 45,
            'service_price' => 85,
            'status' => Appointment::STATUS_PENDING,
        ]);
    }

    public function test_overlapping_appointments_are_rejected_but_adjacent_times_are_allowed(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $date = now()->addDays(3)->toDateString();
        $payload = [
            'customer_name' => 'Rafael Lima',
            'phone' => '11999990000',
            'service_id' => $service->id,
            'appointment_date' => $date,
        ];

        $this->post(route('appointments.store'), [...$payload, 'appointment_time' => '09:00'])
            ->assertRedirect(route('booking'));

        $this->from(route('booking'))
            ->post(route('appointments.store'), [...$payload, 'appointment_time' => '09:45'])
            ->assertSessionHasErrors(['appointment_time']);

        $this->post(route('appointments.store'), [...$payload, 'appointment_time' => '10:00'])
            ->assertRedirect(route('booking'));

        $this->assertDatabaseCount('appointments', 2);
    }

    public function test_staff_can_change_an_appointment_status(): void
    {
        $appointment = Appointment::factory()->create();

        $this->patch(route('appointments.update', $appointment), ['status' => Appointment::STATUS_CONFIRMED])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);
    }

    public function test_a_cancelled_appointment_cannot_be_reactivated_into_a_conflict(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $date = now()->addDays(4)->toDateString();
        $cancelled = Appointment::factory()->for($service)->create([
            'appointment_date' => $date,
            'appointment_time' => '09:00:00',
            'service_duration_minutes' => 60,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
        Appointment::factory()->for($service)->create([
            'appointment_date' => $date,
            'appointment_time' => '09:30:00',
            'service_duration_minutes' => 60,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->from(route('dashboard'))
            ->patch(route('appointments.update', $cancelled), ['status' => Appointment::STATUS_CONFIRMED])
            ->assertSessionHasErrors(['appointment_time']);

        $this->assertDatabaseHas('appointments', [
            'id' => $cancelled->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }

    public function test_staff_can_create_a_service(): void
    {
        $this->post(route('services.store'), [
            'name' => 'Massagem relaxante',
            'description' => 'Atendimento individual.',
            'price' => '120.00',
            'duration_minutes' => 50,
            'is_active' => '1',
        ])->assertRedirect(route('services.index'));

        $this->assertDatabaseHas('services', [
            'name' => 'Massagem relaxante',
            'price' => '120.00',
            'duration_minutes' => 50,
            'is_active' => true,
        ]);
    }

    public function test_booking_page_only_displays_active_services(): void
    {
        Service::factory()->create(['name' => 'Consulta disponível', 'is_active' => true]);
        Service::factory()->create(['name' => 'Serviço pausado', 'is_active' => false]);

        $this->get(route('booking'))
            ->assertOk()
            ->assertSee('Consulta disponível')
            ->assertDontSee('Serviço pausado');
    }

    public function test_dashboard_displays_scheduled_appointments(): void
    {
        $service = Service::factory()->create(['name' => 'Atendimento especial']);
        Appointment::factory()->for($service)->create(['customer_name' => 'Joana Almeida']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Joana Almeida')
            ->assertSee('Atendimento especial');
    }

    public function test_service_page_renders_the_catalog_and_registration_form(): void
    {
        Service::factory()->create(['name' => 'Manicure clássica']);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Manicure clássica')
            ->assertSee('Adicionar serviço');
    }

    public function test_staff_can_update_a_service(): void
    {
        $service = Service::factory()->create(['name' => 'Consulta antiga']);

        $this->put(route('services.update', $service), [
            'name' => 'Consulta renovada',
            'description' => 'Descrição atualizada.',
            'price' => '95.00',
            'duration_minutes' => 40,
            'is_active' => '1',
        ])->assertRedirect(route('services.index'));

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Consulta renovada',
            'price' => 95,
            'duration_minutes' => 40,
            'is_active' => true,
        ]);
    }
}
