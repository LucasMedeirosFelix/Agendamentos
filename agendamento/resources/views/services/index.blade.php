@extends('layouts.app')

@section('title', 'Serviços')

@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow"><span class="eyebrow-dot"></span> CATÁLOGO</p>
        <h1>Seus <em>serviços</em></h1>
        <p class="heading-copy">Defina o que você oferece, quanto custa e quanto tempo leva.</p>
    </div>
    <a class="button button-quiet" href="{{ route('booking') }}">Ver página de agendamento <span aria-hidden="true">↗</span></a>
</section>

<div class="service-layout">
    <section class="service-list-section">
        <div class="section-heading compact-heading">
            <div><p class="eyebrow">{{ $services->count() }} {{ $services->count() === 1 ? 'SERVIÇO' : 'SERVIÇOS' }}</p><h2>Catálogo atual</h2></div>
        </div>

        @if ($services->isEmpty())
            <div class="empty-state empty-state-small">
                <h3>Seu catálogo começa aqui</h3>
                <p>Cadastre o primeiro serviço para que os clientes possam solicitar um horário.</p>
            </div>
        @else
            <div class="service-list">
                @foreach ($services as $service)
                    <article class="service-item">
                        <div class="service-item-top">
                            <div><span class="service-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span class="service-state {{ $service->is_active ? 'is-active' : 'is-inactive' }}">{{ $service->is_active ? 'Ativo' : 'Pausado' }}</span></div>
                            <span class="service-bookings">{{ $service->appointments_count }} {{ $service->appointments_count === 1 ? 'agendamento' : 'agendamentos' }}</span>
                        </div>
                        <form method="POST" action="{{ route('services.update', $service) }}" class="service-edit-form">
                            @csrf
                            @method('PUT')
                            <div class="field">
                                <label for="name-{{ $service->id }}">Nome</label>
                                <input id="name-{{ $service->id }}" name="name" type="text" maxlength="120" value="{{ $service->name }}" required>
                                @error('name') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="description-{{ $service->id }}">Descrição</label>
                                <textarea id="description-{{ $service->id }}" name="description" rows="2" maxlength="2000">{{ $service->description }}</textarea>
                            </div>
                            <div class="field-row">
                                <div class="field"><label for="price-{{ $service->id }}">Preço (R$)</label><input id="price-{{ $service->id }}" name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ $service->price }}" required></div>
                                <div class="field"><label for="duration-{{ $service->id }}">Duração (min)</label><input id="duration-{{ $service->id }}" name="duration_minutes" type="number" min="5" max="1440" step="5" value="{{ $service->duration_minutes }}" required></div>
                            </div>
                            <div class="service-form-footer">
                                <label class="toggle-label"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($service->is_active)> Disponível para agendamento</label>
                                <button class="button button-small" type="submit">Salvar alterações</button>
                            </div>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <aside class="new-service-panel">
        <p class="eyebrow">NOVO ITEM</p>
        <h2>Adicionar serviço</h2>
        <p class="card-intro">Os campos com asterisco são obrigatórios.</p>
        <form method="POST" action="{{ route('services.store') }}" class="form-stack">
            @csrf
            <div class="field"><label for="new-name">Nome <span>*</span></label><input id="new-name" name="name" type="text" maxlength="120" value="{{ old('name') }}" placeholder="Ex.: Consulta inicial" required>@error('name') <span class="field-error">{{ $message }}</span> @enderror</div>
            <div class="field"><label for="new-description">Descrição</label><textarea id="new-description" name="description" rows="3" maxlength="2000" placeholder="O que está incluído neste serviço?">{{ old('description') }}</textarea>@error('description') <span class="field-error">{{ $message }}</span> @enderror</div>
            <div class="field"><label for="new-price">Preço (R$) <span>*</span></label><input id="new-price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('price') }}" placeholder="0,00" required>@error('price') <span class="field-error">{{ $message }}</span> @enderror</div>
            <div class="field"><label for="new-duration">Duração em minutos <span>*</span></label><input id="new-duration" name="duration_minutes" type="number" min="5" max="1440" step="5" value="{{ old('duration_minutes', 30) }}" required>@error('duration_minutes') <span class="field-error">{{ $message }}</span> @enderror</div>
            <label class="toggle-label"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')> Disponível para agendamento</label>
            <button class="button button-primary button-wide" type="submit">Cadastrar serviço <span aria-hidden="true">→</span></button>
        </form>
    </aside>
</div>
@endsection