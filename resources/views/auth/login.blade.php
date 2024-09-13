<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg-app dark:bg-gray-900">

    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <img class="max-w-24 mr-2" src="{{ asset('/Logo_HKI.png') }}" alt="Logo HKI">
            Hutama Karya Insfrastruktur
        </a>
        <div
            class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    masuk ke akun anda
                </h1>
                <x-validation-errors class="mb-4" />

                @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
                @endif

                <form class="space-y-4 md:space-y-6" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div>
                        <x-label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="email"
                            value="{{ __('Email') }}" />
                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                            required autofocus autocomplete="username" />
                    </div>

                    <div class="mt-4">
                        <x-label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="password"
                            value="{{ __('Password') }}" />
                        <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                            autocomplete="current-password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-start">
                            <label for="remember_me" class="flex items-center">
                                <x-checkbox id="remember_me" name="remember" />
                                <span class="ml-2 text-gray-500 dark:text-gray-300">{{ __('Remember me') }}</span>
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-primary-600 hover:underline dark:text-white"
                            href="{{ route('password.request') }}">
                            {{ __('Lupa Kata Sandi?') }}
                        </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full text-white bg-red-700 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        {{ __('Masuk') }}</button>
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            tidak punya akun? <a href="{{ route('register') }}" data-signup class="font-medium text-primary-600 hover:underline dark:text-primary-500">Daftar</a>
                            </p>

                </form>
            </div>
        </div>
    </div>




</body>

</html>
