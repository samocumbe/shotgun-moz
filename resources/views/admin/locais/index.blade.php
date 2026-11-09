{{--
    ============================================================
    VIEW: Lista de Locais
    ============================================================
    Esta view exibe todos os locais cadastrados no sistema
    com opções de criar, editar, visualizar e excluir.
    
    @extends: Usa o layout administrativo padrão
    @section: Define o título e o conteúdo principal
--}}

@extends('layouts.admin')

@section('title', 'Locais - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    {{-- Título da página --}}
    <h1 class="h2">Locais</h1>
    
    {{-- Botão para criar novo local --}}
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('locais.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Novo Local
        </a>
    </div>
</div>

{{-- Tabela de locais --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Cidade</th>
                        <th>Província</th>
                        <th>Capacidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop através dos locais --}}
                    @foreach($locais as $local)
                    <tr>
                        {{-- ID do local --}}
                        <td>{{ $local->id }}</td>
                        
                        {{-- Nome do local --}}
                        <td>{{ $local->nome }}</td>
                        
                        {{-- Cidade do local --}}
                        <td>{{ $local->cidade }}</td>
                        
                        {{-- Província do local --}}
                        <td>{{ $local->provincia }}</td>
                        
                        {{-- Capacidade formatada com separadores --}}
                        <td>{{ number_format($local->capacidade, 0, ',', '.') }}</td>
                        
                        {{-- Botões de ação --}}
                        <td>
                            {{-- Botão Visualizar (show) --}}
                            <a href="{{ route('locais.show', $local->id) }}" 
                               class="btn btn-sm btn-info" 
                               title="Visualizar detalhes">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            {{-- Botão Editar (edit) --}}
                            <a href="{{ route('locais.edit', $local->id) }}" 
                               class="btn btn-sm btn-warning" 
                               title="Editar local">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            {{-- Botão Excluir (destroy) - com confirmação --}}
                            <button onclick="confirmDelete({{ $local->id }}, '{{ $local->nome }}')" 
                                    class="btn btn-sm btn-danger" 
                                    title="Excluir local">
                                <i class="bi bi-trash"></i>
                            </button>
                            
                            {{-- Formulário oculto para exclusão via DELETE --}}
                            <form id="delete-form-{{ $local->id }}" 
                                  action="{{ route('locais.destroy', $local->id) }}" 
                                  method="POST" 
                                  style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Links de paginação --}}
        {{ $locais->links() }}
    </div>
</div>
@endsection

{{--
    ============================================================
    SCRIPTS ADICIONAIS
    ============================================================
    Função JavaScript para confirmar exclusão com SweetAlert2
--}}
@push('scripts')
<script>
    /**
     * Função para confirmar exclusão de um local
     * 
     * @param {number} id - ID do local a ser excluído
     * @param {string} nome - Nome do local para exibir na confirmação
     */
    function confirmDelete(id, nome) {
        Swal.fire({
            title: 'Tem certeza?',
            html: `Deseja realmente excluir <strong>"${nome}"</strong>?<br><br>Esta ação não pode ser desfeita.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="bi bi-trash"></i> Sim, excluir!',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar'
        }).then((result) => {
            // Se o usuário confirmar, envia o formulário de exclusão
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush

{{--
    ============================================================
    ESTILOS ADICIONAIS
    ============================================================
--}}
@push('styles')
<style>
    /* Badges de status */
    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    /* Badge para publicado */
    .badge-published { 
        background-color: #27ae60; 
        color: white; 
    }
    
    /* Badge para rascunho */
    .badge-draft { 
        background-color: #95a5a6; 
        color: white; 
    }
    
    /* Badge para cancelado */
    .badge-canceled { 
        background-color: #e74c3c; 
        color: white; 
    }
    
    /* Badge para encerrado */
    .badge-ended { 
        background-color: #2c3e50; 
        color: white; 
    }
</style>
@endpush