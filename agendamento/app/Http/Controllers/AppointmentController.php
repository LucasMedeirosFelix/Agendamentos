<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\CreateAppointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function create(): View
    {
        return view('booking', [
            'services' => Service::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', Rule::in([
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
            ])],
        ]);

        $appointments = Appointment::query()
            ->with('service')
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('appointment_date', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(12)
            ->withQueryString();

        return view('dashboard', [
            'appointments' => $appointments,
            'todayCount' => Appointment::query()->whereDate('appointment_date', today())->count(),
            'pendingCount' => Appointment::query()->where('status', Appointment::STATUS_PENDING)->count(),
            'confirmedCount' => Appointment::query()->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'revenueToday' => Appointment::query()
                ->whereDate('appointment_date', today())
                ->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_COMPLETED])
                ->sum('service_price'),
            'statuses' => [
                Appointment::STATUS_PENDING => 'Pendente',
                Appointment::STATUS_CONFIRMED => 'Confirmado',
                Appointment::STATUS_COMPLETED => 'Concluído',
                Appointment::STATUS_CANCELLED => 'Cancelado',
            ],
        ]);
    }

    public function store(Request $request, CreateAppointment $createAppointment): RedirectResponse
    {
        $attributes = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $createAppointment->create($attributes);

        return redirect()->route('booking')->with('success', 'Solicitação enviada. Aguarde a confirmação do atendimento.');
    }

    public function update(Request $request, Appointment $appointment, CreateAppointment $createAppointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
            ])],
        ]);

        $createAppointment->changeStatus($appointment, $validated['status']);

        return redirect()->route('dashboard')->with('success', 'Status do agendamento atualizado.');
    }
}
