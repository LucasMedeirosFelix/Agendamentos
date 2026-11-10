@extends('layouts.app')

@section('title', 'Agenda')

@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow"><span class="eyebrow-dot"></span> VISÃO GERAL</p>
        <h1>Agenda <em>de atendimentos</em></h1>
        <p class="heading-copy">Acompanhe solicitações e atualize cada atendimento.</p>
    </div>
    <a class="button button-primary" href="{{ route('booking') }}"><span aria-hidden="true">+</span> Novo agendamento</a>
</section>

<section class="metrics-grid" aria-label="Resumo da agenda">
    <article class="metric metric-coral"><span>Hoje</span><strong>{{ $todayCount }}</strong><small>atendimentos marcados</small></article>
    <article class="metric metric-green"><span>Aguardando</span><strong>{{ $pendingCount }}</strong><small>solicitações pendentes</small></article>
    <article class="metric metric-yellow"><span>Confirmados</span><strong>{{ $confirmedCount }}</strong><small>prontos para atender</small></article>
    <article class="metric metric-white"><span>Previsão de hoje</span><strong>R$ {{ number_format((float) $revenueToday, 2, ',', '.') }}</strong><small>confirmados e concluídos</small></article>
</section>

<section class="schedule-section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">CONSULTA</p>
            <h2>Todos os horários</h2>
        </div>
        <form class="filter-bar" method="GET" action="{{ route('dashboard') }}">
            <label class="filter-field"><span class="sr-only">Filtrar por data</span><input type="date" name="date" value="{{ request('date') }}"></label>
            <label class="filter-field"><span class="sr-only">Filtrar por status</span>
                <select name="status">
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="button button-filter" type="submit">Filtrar</button>
            @if (request()->hasAny(['date', 'status']))
                <a class="clear-filter" href="{{ route('dashboard') }}">Limpar</a>
            @endif
        </form>
    </div>

    @if ($appointments->isEmpty())
        <div class="empty-state">
            <span class="empty-mark" aria-hidden="true">◷</span>
            <h3>Nenhum horário por aqui</h3>
            <p>Quando alguém solicitar um atendimento, ele aparecerá nesta lista.</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="appointment-table">
                <thead>
                    <tr><th>Horário</th><th>Cliente</th><th>Serviço</th><th>Status</th><th>Atualizar</th></tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td class="appointment-date-cell"><strong>{{ substr($appointment->appointment_time, 0, 5) }}</strong><span>{{ \Illuminate\Support\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</span></td>
                            <td><strong>{{ $appointment->customer_name }}</strong><a class="phone-link" href="tel:{{ $appointment->phone }}">{{ $appointment->phone }}</a></td>
                            <td><strong>{{ $appointment->service->name }}</strong><span class="table-meta">{{ $appointment->service_duration_minutes }} min · R$ {{ number_format((float) $appointment->service_price, 2, ',', '.') }}</span></td>
                            <td><span class="status-pill status-{{ $appointment->status }}">{{ $statuses[$appointment->status] }}</span></td>
                            <td>
                                <form class="status-form" method="POST" action="{{ route('appointments.update', $appointment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="status-{{ $appointment->id }}">Status de {{ $appointment->customer_name }}</label>
                                    <select id="status-{{ $appointment->id }}" name="status">
                                        @foreach ($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected($appointment->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="icon-button" type="submit" aria-label="Salvar status de {{ $appointment->customer_name }}" title="Salvar status">↗</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($appointments->hasPages())
            <nav class="pagination" aria-label="Paginação">
                <span>Página {{ $appointments->currentPage() }} de {{ $appointments->lastPage() }}</span>
                <div class="pagination-actions">
                    @if ($appointments->previousPageUrl()) <a href="{{ $appointments->previousPageUrl() }}">← Anterior</a> @endif
                    @if ($appointments->nextPageUrl()) <a href="{{ $appointments->nextPageUrl() }}">Próxima →</a> @endif
                </div>
            </nav>
        @endif
    @endif
</section>
@endsection