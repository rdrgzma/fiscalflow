{{-- resources/views/livewire/relatorio-vendas.blade.php --}}

<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-900">Relatório de Vendas</h2>
        </div>

        <!-- Filtros -->
        <div class="p-6 bg-gray-50 border-b">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data Início</label>
                    <input type="date" wire:model="dataInicio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data Fim</label>
                    <input type="date" wire:model="dataFim" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cliente</label>
                    <select wire:model="cliente_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="autorizada">Autorizada</option>
                        <option value="rejeitada">Rejeitada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select wire:model="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="nfe">NFe</option>
                        <option value="nfce">NFCe</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button wire:click="gerarRelatorio" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150">
                        Gerar Relatório
                    </button>
                </div>
            </div>
        </div>

        <!-- Resumo -->
        @if(count($relatorio) > 0)
            <div class="p-6 bg-blue-50 border-b">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-blue-600">{{ $quantidadeTotal }}</h3>
                        <p class="text-sm text-gray-600">NFes Autorizadas</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-green-600">R$ {{ number_format($totalGeral, 2, ',', '.') }}</h3>
                        <p class="text-sm text-gray-600">Valor Total</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-purple-600">R$ {{ $quantidadeTotal > 0 ? number_format($totalGeral / $quantidadeTotal, 2, ',', '.') : '0,00' }}</h3>
                        <p class="text-sm text-gray-600">Ticket Médio</p>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="p-6 border-b flex space-x-3">
                <button wire:click="exportarPdf" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-150">
                    Exportar PDF
                </button>
                <button wire:click="exportarExcel" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-150">
                    Exportar Excel
                </button>
            </div>
        @endif

        <!-- Dados do Relatório -->
        @if(count($relatorio) > 0)
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NFe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($relatorio as $nfe)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $nfe['numero'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $nfe['tipo'] }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $nfe['cliente'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $nfe['data_emissao'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'autorizada' => 'bg-green-100 text-green-800',
                                            'rejeitada' => 'bg-red-100 text-red-800',
                                            'cancelada' => 'bg-red-100 text-red-800',
                                            'processando' => 'bg-yellow-100 text-yellow-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$nfe['status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($nfe['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    R$ {{ number_format($nfe['valor_total'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($relatorio !== null && count($relatorio) === 0)
            <div class="p-6 text-center text-gray-500">
                Nenhum registro encontrado para os filtros selecionados.
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                Clique em "Gerar Relatório" para visualizar os dados.
            </div>
        @endif
    </div>
</div>
