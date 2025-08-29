{{-- resources/views/livewire/configuracoes.blade.php --}}

<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-900">Configurações do Sistema</h2>
        </div>

        <form wire:submit.prevent="salvar" class="p-6 space-y-6">
            <!-- Configurações Gerais -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações Gerais</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ambiente</label>
                        <select wire:model="configuracoes.ambiente" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="2">Homologação</option>
                            <option value="1">Produção</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Ambiente para emissão das NFes</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Timeout (segundos)</label>
                        <input type="number" wire:model="configuracoes.timeout" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Tempo limite para comunicação com SEFAZ</p>
                    </div>
                </div>
            </div>

            <!-- Configurações de Email -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notificações por Email</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="configuracoes.enviar_email_autorizacao" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">Enviar email quando NFe for autorizada</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="configuracoes.enviar_email_rejeicao" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">Enviar email quando NFe for rejeitada</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="configuracoes.enviar_email_cancelamento" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">Enviar email quando NFe for cancelada</label>
                    </div>
                </div>
            </div>

            <!-- Configurações de Backup -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Backup Automático</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">