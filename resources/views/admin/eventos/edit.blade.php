{{--
    ============================================================
    VIEW: Editar Evento
    ============================================================
    Formulário para edição de um evento existente.
    
    🔥 Multi-Organizador:
    - Admin pode alterar a organização
    - Organizador não vê o campo de organização
--}}

@extends('layouts.admin')

@section('title', 'Editar Evento - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Evento</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eventos.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('eventos.update', $evento->id) }}" method="POST">
            @csrf
            @method('PUT')
            
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
                                    {{ old('local_id', $evento->local_id) == $local->id ? 'selected' : '' }}>
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
                           value="{{ old('nome', $evento->nome) }}" 
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
                              rows="3">{{ old('descricao', $evento->descricao) }}</textarea>
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
                           value="{{ old('data_inicio', $evento->data_inicio->format('Y-m-d')) }}" 
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
                           value="{{ old('data_fim', $evento->data_fim->format('Y-m-d')) }}" 
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
                           value="{{ old('hora_inicio', $evento->hora_inicio->format('H:i')) }}" 
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
                           value="{{ old('hora_fim', $evento->hora_fim->format('H:i')) }}" 
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
                           value="{{ old('capacidade_total', $evento->capacidade_total) }}" 
                           required 
                           min="1">
                    @error('capacidade_total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" 
                            name="status">
                        <option value="rascunho" {{ old('status', $evento->status) == 'rascunho' ? 'selected' : '' }}>
                            Rascunho
                        </option>
                        <option value="publicado" {{ old('status', $evento->status) == 'publicado' ? 'selected' : '' }}>
                            Publicado
                        </option>
                        <option value="encerrado" {{ old('status', $evento->status) == 'encerrado' ? 'selected' : '' }}>
                            Encerrado
                        </option>
                        <option value="cancelado" {{ old('status', $evento->status) == 'cancelado' ? 'selected' : '' }}>
                            Cancelado
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- 🔥 MULTI-ORGANIZADOR: Campo Organização (apenas admin) --}}
            @if(auth()->user()->isAdmin() && isset($organizacoes) && $organizacoes->count() > 0)
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="organizacao_id" class="form-label">Organização</label>
                    <select name="organizacao_id" id="organizacao_id" class="form-select">
                        <option value="">Nenhuma</option>
                        @foreach($organizacoes as $org)
                            <option value="{{ $org->id }}" 
                                    {{ old('organizacao_id', $evento->organizacao_id) == $org->id ? 'selected' : '' }}>
                                {{ $org->nome }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Selecione a organização proprietária deste evento</small>
                </div>
            </div>
            @endif

            {{-- Recorrência --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="recorrencia" class="form-label">Recorrência</label>
                    <select class="form-select @error('recorrencia') is-invalid @enderror" 
                            id="recorrencia" 
                            name="recorrencia">
                        <option value="nenhuma" {{ old('recorrencia', $evento->recorrencia) == 'nenhuma' ? 'selected' : '' }}>
                            Nenhuma
                        </option>
                        <option value="diario" {{ old('recorrencia', $evento->recorrencia) == 'diario' ? 'selected' : '' }}>
                            Diário
                        </option>
                        <option value="semanal" {{ old('recorrencia', $evento->recorrencia) == 'semanal' ? 'selected' : '' }}>
                            Semanal
                        </option>
                        <option value="mensal" {{ old('recorrencia', $evento->recorrencia) == 'mensal' ? 'selected' : '' }}>
                            Mensal
                        </option>
                        <option value="anual" {{ old('recorrencia', $evento->recorrencia) == 'anual' ? 'selected' : '' }}>
                            Anual
                        </option>
                    </select>
                    @error('recorrencia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="recorrencia_intervalo" class="form-label">Intervalo</label>
                    <input type="number" 
                           class="form-control @error('recorrencia_intervalo') is-invalid @enderror" 
                           id="recorrencia_intervalo" 
                           name="recorrencia_intervalo" 
                           value="{{ old('recorrencia_intervalo', $evento->recorrencia_intervalo) }}" 
                           min="1">
                    <small class="text-muted">A cada quantos dias/semanas/meses/anos</small>
                    @error('recorrencia_intervalo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="recorrencia_data_fim" class="form-label">Data Fim da Recorrência</label>
                    <input type="date" 
                           class="form-control @error('recorrencia_data_fim') is-invalid @enderror" 
                           id="recorrencia_data_fim" 
                           name="recorrencia_data_fim" 
                           value="{{ old('recorrencia_data_fim', $evento->recorrencia_data_fim ? $evento->recorrencia_data_fim->format('Y-m-d') : '') }}">
                    @error('recorrencia_data_fim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Template --}}
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_template" name="is_template" value="1"
                           {{ old('is_template', $evento->is_template) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_template">
                        Salvar como template
                    </label>
                    <small class="text-muted d-block">Marque para salvar este evento como template para reutilização</small>
                </div>
            </div>
            
            {{-- Botões --}}
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check"></i> Atualizar
                </button>
                <a href="{{ route('eventos.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection