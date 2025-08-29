{{-- resources/views/livewire/nfe-listagem.blade.php --}}

<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-900">Notas Fiscais Emitidas</h2>
                <a href="{{ route('nfe.emissor') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150">
                    Nova NFe/NFCe
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-6 bg-gray-50 border-b">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input wire:model.live="search" type="text" placeholder="Buscar por número ou cliente..." class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <select wire:model.live="statusFilter" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os status</option>
                        <option value="rascunho">Rascunho</option>
                        <option value="processando">Processando</option>
                        <option value="autorizada">Autorizada</option>
                        <option value="rejeitada">Rejeitada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                
                <div>
                    <select wire:model.live="tipoFilter" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os tipos</option>
                        <option value="nfe">NFe</option>
                        <option value="nfce">NFCe</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($nfes as $nfe)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $nfe->numeroFormatado }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $nfe->tipo === 'nfe' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ strtoupper($nfe->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $nfe->cliente->nome }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'rascunho' => 'bg-gray-100 text-gray-800',
                                        'processando' => 'bg-yellow-100 text-yellow-800',
                                        'autorizada' => 'bg-green-100 text-green-800',
                                        'rejeitada' => 'bg-red-100 text-red-800',
                                        'cancelada' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$nfe->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($nfe->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $nfe->data_emissao->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                @if($nfe->xml_path)
                                    <button wire:click="downloadXml({{ $nfe->id }})" class="text-blue-600 hover:text-blue-900">XML</button>
                                @endif
                                @if($nfe->pdf_path)
                                    <button wire:click="downloadPdf({{ $nfe->id }})" class="text-green-600 hover:text-green-900">PDF</button>
                                @endif
                                <button class="text-indigo-600 hover:text-indigo-900">Ver</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Nenhuma nota fiscal encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="px-6 py-4 border-t">
            {{ $nfes->links() }}
        </div>
    </div>
</div>