<x-filament::card class="space-y-4">
    <div class="flex items-center gap-2">
        <h2 class="text-lg font-semibold text-danger-600">
            Dokumen Expired
        </h2>
    </div>

    @if($expiredSio->count() || $expiredSilo->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-separate border-spacing-y-2">
                <thead class="text-left text-gray-400 border-b border-gray-700">
                    <tr>
                        <th class="py-1">Nama</th>
                        <th class="py-1">Dokumen</th>
                        <th class="py-1">Expired</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- SIO --}}
                    @foreach($expiredSio as $sio)
                        <tr class="bg-gray-800/40 rounded-lg">
                            <td class="px-3 py-2 rounded-l-lg">
                                {{ $sio->operator->nama_operator }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="text-xs font-medium bg-orange-200 text-orange-400">
                                    SIO Operator
                                </span>
                            </td>
                            <td class="px-3 py-2 rounded-r-lg text-danger-500 font-semibold">
                                {{ $sio->tanggal_expired->format('d M Y') }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- SILO --}}
                    @foreach($expiredSilo as $silo)
                        <tr class="bg-gray-800/40 rounded-lg">
                            <td class="px-3 py-2 rounded-l-lg">
                                {{ $silo->alatBerat->nama_alat }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="text-xs font-medium text-orange-400">
                                    SILO Alat
                                </span>
                            </td>
                            <td class="px-3 py-2 rounded-r-lg text-danger-500 font-semibold">
                                {{ $silo->tanggal_expired->format('d M Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-success-600 flex items-center gap-2">
            <span>✅</span>
            <span>Semua dokumen masih berlaku</span>
        </div>
    @endif
</x-filament::card>