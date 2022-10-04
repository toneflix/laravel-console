<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @empty($api_welcome)
        <title>Console Login - {{ config('settings.site_name') }} API v{{ config('app.api.version.code', '1.0.0') }}</title>
    @else
        <title>{{ config('settings.site_name') }} API v{{ config('app.api.version.code', '1.0.0') }}</title>
    @endempty
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('web/assets/css/tailwind.output.css') }}" />
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="{{ asset('web/assets/js/init-alpine.js') }}"></script>
</head>

<body>
    <div class="flex items-center min-h-screen p-6 bg-gray-50 dark:bg-gray-900">
        <div class="flex-1 h-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex flex-col overflow-y-auto md:flex-row">
                <div class="h-32 md:h-auto md:w-1/2">
                    <img aria-hidden="true" class="object-cover w-full h-full dark:hidden"
                        src="{{ asset((new Media())->default_media) }}" alt="Banner" />
                    <img aria-hidden="true" class="hidden object-cover w-full h-full dark:block"
                        src="{{ asset((new Media())->default_media) }}" alt="Banner" />
                </div>
                <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
                    @isset($api_welcome)
                        @foreach ($api_welcome as $label => $data)
                            <div class="text-center mt-4 text-sm font-medium text-red-600 dark:text-red-400">
                                {{ $label }}
                                <div class="p-3 shadow bg-red-100">
                                    <code>
                                        {{ json_encode($data, JSON_PRETTY_PRINT) }}
                                    </code>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <form class="w-full" action="{{ route('console.login') }}" method="POST">
                            <h1 class="mb-4 text-xl font-semibold text-gray-700 dark:text-gray-200">
                                Console Login
                            </h1>
                            @csrf
                            <label class="block text-sm">
                                <span class="text-gray-700 dark:text-gray-400">Email</span>
                                <input
                                    class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-red-400 focus:outline-none focus:shadow-outline-red dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                                    placeholder="user@example.com" name="email" />
                                @error('email')
                                    <span class="text-xs text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </label>
                            <label class="block mt-4 text-sm">
                                <span class="text-gray-700 dark:text-gray-400">Password</span>
                                <input
                                    class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-red-400 focus:outline-none focus:shadow-outline-red dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                                    placeholder="***************" name="password" type="password" />
                                @error('password')
                                    <span class="text-xs text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </label>

                            <!-- You should use a button here, as the anchor is only used for the example  -->
                            <button
                                class="block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red"
                                type="submit">
                                Log in
                            </button>

                            <div class="text-center mt-4 text-sm font-medium text-red-600 dark:text-red-400">This page
                                does not give you access to
                                {{ config('settings.site_name') }}, but <a
                                    href="{{ env('FRONTEND_LINK', 'http://localhost:8080') . '/login' }}">this</a> does!
                            </div>
                        </form>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</body>

</html>
