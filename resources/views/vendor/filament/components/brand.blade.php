@if (filled($brand = config('filament.brand')))
    <div
        @class([
            'filament-brand text-xl font-bold leading-5 tracking-tight',
            'dark:text-white' => config('filament.dark_mode'),
        ])
    >
    <a href="#" class="flex items-center mb-6 text-sm font-semibold text-gray-900 dark:text-white">
        <img class="h-10 mr-2" src="{{ asset('/Logo_HKI.png') }}" alt="Logo HKI">
        Hutama Karya Insfrastruktur
    </a>  
    </div>
@endif
