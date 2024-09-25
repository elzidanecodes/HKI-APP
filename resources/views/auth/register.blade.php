<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg-app dark:bg-gray-800">

    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <img class="max-w-20 mr-2" src="{{ asset('/Logo_HKI.png') }}" alt="Logo HKI">
            Hutama Karya Insfrastruktur
        </a>
        <div
            class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-3 md:space-y-3 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    Daftar akun anda
                </h1>
                <x-validation-errors class="mb-4" />

                <form class="space-y-3 md:space-y-3" method="POST" action="{{ route('register') }}">
                    @csrf
        
                    <div>
                        <x-label class="dark:text-white" for="name" value="{{ __('Nama') }}" />
                        <x-input id="name" class="block mt-1 w-full" placeholder="isi dengan nama panjang anda" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    </div>
        
                    <div class="mt-4">
                        <x-label class="dark:text-white" for="email" value="{{ __('Email') }}" />
                        <x-input id="email" class="block mt-1 w-full" type="email" placeholder="isi dengan email aktif" name="email" :value="old('email')" required autocomplete="username" />
                    </div>
                    <div class="mt-4">
                        <x-label class="dark:text-white" for="gender" value="{{ __('Departemen') }}" />
                        <select id="job_title" name="job_title"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="" selected disabled>Pilih Departemen</option>
                            <option value='HSSE'>HSSE</option>
                            <option value='LOGISTIK'>LOGISTIK</option>
                            <option value='DOKON'>DOKUMEN CONTROL</option>
                        </select>
                    </div>
        
                    <div class="mt-4">
                        <x-label class="dark:text-white" for="password" value="{{ __('Password') }}" />
                        <x-input id="password" class="block mt-1 w-full" placeholder="gunakan password minimal 8 karakter" type="password" name="password" required autocomplete="new-password" />
                    </div>
        
                    <div class="mt-4">
                        <x-label class="dark:text-white" for="password_confirmation" value="{{ __('Confirm Password') }}" />
                        <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    </div>
        
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="mt-4 dark:text-white">
                            <x-label for="terms" class="dark:text-white">
                                <div class="flex items-center">
                                    <x-checkbox name="terms" id="terms" required />
        
                                    <div class="ml-2">
                                        {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                        ]) !!}
                                    </div>
                                </div>
                            </x-label>
                        </div>
                    @endif

                    <button type="submit"
                        class="mt-4 w-full text-white bg-red-700 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        {{ __('Daftar') }}
                    </button>
                    <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            sudah punya akun? <a href="{{ route('login') }}" data-signup class="font-medium text-primary-600 hover:underline dark:text-primary-500">Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
