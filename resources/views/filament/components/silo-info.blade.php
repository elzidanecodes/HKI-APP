@php
    /**
     * Ambil record Alat Berat dari Page (Filament v2)
     * $this = EditAlatBerats / CreateAlatBerats page
     */
    /** @var \App\Models\AlatBerats|null $record */
    $record = method_exists($this, 'getRecord')
        ? $this->getRecord()
        : null;

    // Apakah ada SILO aktif
    $hasActive = $record && $record->hasActiveSilo();

    // SILO yang ditampilkan:
    // - Jika aktif → activeSilo
    // - Jika expired → latestSilo (terakhir)
    $silo = $hasActive
        ? $record->activeSilo()
        : ($record ? $record->latestSilo() : null);

    // Sisa hari hanya relevan jika SILO aktif
    $days = $record ? $record->siloRemainingDays() : null;
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-4">
    <h3 class="text-sm font-semibold mb-3">
        Informasi SILO
    </h3>

    <table class="w-full text-sm">
        {{-- STATUS --}}
        <tr class="border-b">
            <td class="py-2 text-gray-600 w-1/3">
                Status
            </td>
            <td class="py-2 font-semibold">
                @if ($hasActive)
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold
                               text-success-600 bg-success-200">
                        AKTIF
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold
                               text-danger-600">
                        EXPIRED
                    </span>
                @endif
            </td>
        </tr>

        {{-- TANGGAL EXPIRED --}}
        <tr class="border-b">
            <td class="py-2 text-gray-600">
                Tanggal Expired
            </td>
            <td class="py-2">
                {{ $silo ? $silo->tanggal_expired->format('d M Y') : '-' }}
            </td>
        </tr>

        {{-- SISA MASA BERLAKU --}}
        <tr>
            <td class="py-2 text-gray-600">
                Sisa Masa Berlaku
            </td>
            <td class="py-2 font-semibold">
                @if (! $hasActive)
                    -
                @elseif ($days <= 30)
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                               bg-status-warning-bg text-status-warning-text">
                        {{ $days }} hari
                    </span>
                @else
                    {{ $days }} hari
                @endif
            </td>
        </tr>
    </table>
</div>