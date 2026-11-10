@extends('layouts.app')

@section('title', 'Agende seu atendimento')

@section('content')
<div class="booking-layout">
    <section class="booking-intro">
        <p class="eyebrow"><span class="eyebrow-dot"></span> ATENDIMENTO COM HORA MARCADA</p>
        <h1>Um tempo só<br>para <em>você.</em></h1>
        <p class="intro-copy">Escolha o serviço, encontre o melhor horário e deixe o resto com a gente.</p>

        <div class="intro-rule"></div>
        <div class="intro-facts">
            <div class="fact-number">01</div>
            <div><strong>Escolha seu serviço</strong><span>Veja duração e valor antes de agendar.</span></div>
        </div>
        <div class="intro-facts">
            <div class="fact-number">02</div>
            <div><strong>Envie sua solicitação</strong><span>Retornaremos para confirmar o horário.</span></div>
        </div>

        @if ($services->isNotEmpty())
            <div class="featured-services">
                <p class="eyebrow">SERVIÇOS DISPONÍVEIS</p>
                <div class="featured-list">
                    @foreach ($services->take(3) as $service)
                        <div class="featured-service">
                            <span>{{ $service->name }}</span>
                            <span>{{ $service->duration_minutes }} min</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="booking-card" aria-labelledby="booking-title">
        <div class="card-kicker"><span>SEU AGENDAMENTO</span><span>01 — 03</span></div>
        <h2 id="booking-title">Vamos marcar?</h2>
        <p class="card-intro">Preencha seus dados para solicitar um horário.</p>

        @if ($services->isEmpty())
            <div class="empty-services">
                <strong>Nenhum serviço disponível ainda.</strong>
                <span>Cadastre um serviço para começar a receber solicitações.</span>
                <a class="text-link" href="{{ route('services.index') }}">Ir para serviços <span aria-hidden="true">→</span></a>
            </div>
        @else
            <form method="POST" action="{{ route('appointments.store') }}" class="form-stack">
                @csrf
                <div class="field">
                    <label for="customer_name">Seu nome</label>
                    <input id="customer_name" name="customer_name" type="text" maxlength="120" value="{{ old('customer_name') }}" placeholder="Como podemos chamar você?" autocomplete="name" required>
                    @error('customer_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="phone">Telefone / WhatsApp</label>
                    <input id="phone" name="phone" type="tel" maxlength="30" value="{{ old('phone') }}" placeholder="(11) 99999-9999" autocomplete="tel" required>
                    @error('phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="service_id">Serviço</label>
                    <select id="service_id" name="service_id" required>
                        <option value="">Selecione um serviço</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" data-price="{{ number_format((float) $service->price, 2, ',', '.') }}" data-duration="{{ $service->duration_minutes }}" @selected((string) old('service_id') === (string) $service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="field-hint" id="service-details">Valor e duração aparecem aqui ao selecionar.</span>
                    @error('service_id') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="appointment_date">Data</label>
                        <input id="appointment_date" name="appointment_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('appointment_date') }}" required>
                        @error('appointment_date') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="appointment_time">Horário</label>
                        <input id="appointment_time" name="appointment_time" type="time" value="{{ old('appointment_time') }}" required>
                        @error('appointment_time') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <p class="form-note"><span aria-hidden="true">↗</span> O horário fica sujeito à confirmação da equipe.</p>
                <button class="button button-primary button-wide" type="submit">Solicitar horário <span aria-hidden="true">→</span></button>
            </form>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    const serviceSelect = document.querySelector('#service_id');
    const serviceDetails = document.querySelector('#service-details');

    if (serviceSelect && serviceDetails) {
        const updateServiceDetails = () => {
            const option = serviceSelect.selectedOptions[0];

            serviceDetails.textContent = option?.dataset.duration
                ? `${option.dataset.duration} min · R$ ${option.dataset.price}`
                : 'Valor e duração aparecem aqui ao selecionar.';
        };

        serviceSelect.addEventListener('change', updateServiceDetails);
        updateServiceDetails();
    }
</script>
@endpush