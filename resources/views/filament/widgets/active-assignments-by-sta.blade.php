<x-filament::widget>
    <x-filament::card class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">
                Assignment Aktif per STA
            </h2>

            <span class="text-xs text-success-500 flex items-center gap-1">
                <span class="w-3 h-3 rounded-full bg-success-500"></span>
                Aktif
            </span>
        </div>

        @if ($assignments->isEmpty())
            <div class="flex items-center gap-2 text-gray-400 text-sm">
                <span>Tidak ada assignment aktif</span>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-700">
                            <th class="py-2">Operator</th>
                            <th class="py-2">Alat</th>
                            <th class="py-2">STA</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-800">
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td class="py-2 font-medium">
                                    {{ $assignment->operator->nama_operator ?? '-' }}
                                </td>
                                <td class="py-2">
                                    {{ $assignment->alatBerat->nama_alat ?? '-' }}
                                </td>
                                <td class="py-2">
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded bg-gray-700/60 text-gray-200">
                                        {{ $assignment->alatBerat->sta_lokasi ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::card>
</x-filament::widget>