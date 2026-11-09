{{--
    ============================================================
    VIEW: Criar Evento
    ============================================================
    Formulário para criação de um novo evento.
    Campos:
    - local_id: Local onde o evento ocorrerá
    - nome: Nome do evento
    - descricao: Descrição detalhada
    - data_inicio: Data de início
    - data_fim: Data de término
    - hora_inicio: Horário de início
    - hora_fim: Horário de término
    - capacidade_total: Capacidade máxima
    - status: Status do evento
--}}

@extends('layouts.admin')

@section('title', 'Novo Evento - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Evento</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('eventos.store') }}" method="POST">
            @csrf
            
            {{-- Linha 1: Local e Nome --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="local_id" class="form-label">
                        Local <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('local_id') is-invalid @enderror" 
                            id="local_id" name="local_id" required>
                        <option value="">Selecione um local...</option>
                        @foreach($locais as $local)
                            <option value="{{ $local->id }}" 
                                    {{ old('local_id') == $local->id ? 'selected' : '' }}>
                                {{ $local->nome }} - {{ $local->cidade }}
                            </option>
                        @endforeach
                    </select>
                    @error('local_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">
                        Nome do Evento <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control @error('nome') is-invalid @enderror" 
                           id="nome" 
                           name="nome" 
                           value="{{ old('nome') }}" 
                           required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            {{-- Linha 2: Descrição --}}
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control @error('descricao') is-invalid @enderror" 
                              id="descricao" 
                              name="descricao" 
                              rows="3">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            {{-- Linha 3: Datas e Horários --}}
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="data_inicio" class="form-label">
                        Data Início <span class="text-danger">*</span>
                    </label>
                    <input type="date" 
                           class="form-control @error('data_inicio') is-invalid @enderror" 
                           id="data_inicio" 
                           name="data_inicio" 
                           value="{{ old('data_inicio') }}" 
                           required>
                    @error('data_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="data_fim" class="form-label">
                        Data Fim <span class="text-danger">*</span>
                    </label>
                    <input type="date" 
                           class="form-control @error('data_fim') is-invalid @enderror" 
                           id="data_fim" 
                           name="data_fim" 
                           value="{{ old('data_fim') }}" 
                           required>
                    @error('data_fim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="hora_inicio" class="form-label">
                        Hora Início <span class="text-danger">*</span>
                    </label>
                    <input type="time" 
                           class="form-control @error('hora_inicio') is-invalid @enderror" 
                           id="hora_inicio" 
                           name="hora_inicio" 
                           value="{{ old('hora_inicio') }}" 
                           required>
                    @error('hora_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="hora_fim" class="form-label">
                        Hora Fim <span class="text-danger">*</span>
                    </label>
                    <input type="time" 
                           class="form-control @error('hora_fim') is-invalid @enderror" 
                           id="hora_fim" 
                           name="hora_fim" 
                           value="{{ old('hora_fim') }}" 
                           required>
                    @error('hora_fim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            {{-- Linha 4: Capacidade e Status --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="capacidade_total" class="form-label">
                        Capacidade Total <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           class="form-control @error('capacidade_total') is-invalid @enderror" 
                           id="capacidade_total" 
                           name="capacidade_total" 
                           value="{{ old('capacidade_total') }}" 
                           required 
                           min="1">
                    @error('capacidade_total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" 
                            name="status">
                        <option value="rascunho" {{ old('status') == 'rascunho' ? 'selected' : '' }}>
                            Rascunho
                        </option>
                        <option value="publicado" {{ old('status') == 'publicado' ? 'selected' : '' }}>
                            Publicado
                        </option>
                        <option value="encerrado" {{ old('status') == 'encerrado' ? 'selected' : '' }}>
                            Encerrado
                        </option>
                        <option value="cancelado" {{ old('status') == 'cancelado' ? 'selected' : '' }}>
                            Cancelado
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            {{-- Botões --}}
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check"></i> Salvar
                </button>
                <a href="{{ route('eventos.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection