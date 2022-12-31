<x-laravel-visualconsole::layout>
    @php
        $action = LaravelVisualConsole::routes(url()->current());
    @endphp
    <x-slot name="title">
        @isset($choose)
            Choose Backup Signature
        @else
            {{ $action['label'] }}
        @endisset
    </x-slot>
    @if (isset($errors) || isset($messages))
        <x-laravel-visualconsole::alert :message="($errors ?? $messages)->first()" :color="isset($errors) ? 'red' : 'green'" />
    @endif

    @if (!env('GOOGLE_DRIVE_CLIENT_ID') ||
        !env('GOOGLE_DRIVE_CLIENT_SECRET') ||
        !env('GOOGLE_DRIVE_REFRESH_TOKEN') ||
        config('laravel-visualconsole.backup_disk', 'google') !== 'google')
        <x-laravel-visualconsole::alert
            message="Google Drive backups are not enabled on this server. Enter your Google Drive credentials and set backup drive to google to enable."
            color="red" />
    @endif

    <div class="container px-6 mx-auto grid" xs-data="webuser">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            @isset($choose)
                Choose Backup Signature
            @else
                {{ $action['label'] }}
            @endisset
        </h2>

        @isset($code)
            <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div
                    class="code-holder bg-black text-green-600 p-4 max-h-96 min-h-fit text-xs overflow-hidden overflow-y-auto">
                    <code class="code m-5">{!! $code->first() !!}</code>
                </div>
            </div>
        @endisset
        <!-- component -->
        @if ($backups->count())
            <div
                class="flex flex-col space-y-4 animated fadeIn faster justify-center w-1/2 inset-0 z-50 outline-none focus:outline-none my-5 h-72 overflow-auto">
                @foreach ($backups as $backup)
                    <div
                        class="flex flex-col p-2 bg-white shadow-md hover:shodow-lg rounded-2xl {{ $loop->first == 0 ? 'mt-20' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-10 h-10 rounded-2xl p-3 border border-blue-100 text-blue-400 bg-blue-50"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex flex-col ml-3">
                                    <div class="font-medium leading-none">{{ $backup }}</div>
                                    <p class="text-sm text-gray-600 leading-none mt-1">Availabel for download
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route(config('laravel-visualconsole.route_prefix', 'system') . '.secure.download', $backup) }}"
                                class="flex-no-shrink bg-red-500 px-5 ml-4 py-2 text-sm shadow-sm hover:shadow-lg font-medium tracking-wider border-2 border-red-500 text-white rounded-full">Download</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if (config('laravel-visualconsole.backup_disk', 'google') === 'google')
            <form method="post"
                action="{{ route(config('laravel-visualconsole.route_prefix', 'system') . '.console.controls', 'backup') }}"
                class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800 grid grid-cols-2 gap-4">
                @method('post')
                @csrf
                @php
                    $fields = ['GOOGLE_DRIVE_CLIENT_ID', 'GOOGLE_DRIVE_CLIENT_SECRET', 'GOOGLE_DRIVE_REFRESH_TOKEN', 'GOOGLE_DRIVE_FOLDER', 'GOOGLE_DRIVE_TEAM_DRIVE_ID'];
                @endphp
                @foreach ($fields as $field)
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">
                            {{ str($field)->replace('_', ' ')->title() }}
                        </span>
                        <input name="{{ $field }}" type="text" value="{{ env($field) }}"
                            class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray form-input"
                            placeholder="{{ str($field)->replace('_', ' ')->title() }}">
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            Enter your {{ str($field)->replace('_', ' ')->lower() }}.
                        </span>
                    </label>
                @endforeach

                <div class="col-span-2">
                    <button type="submit"
                        class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                        Save
                    </button>
                </div>
            </form>
        @endif
    </div>

    @push('bottom')
        <x-laravel-visualconsole::modal title="Confirm Action" name="confirm" x-cloak>
            Are you sure you want to perform this action? This might have very dangerous consequences.
            <x-slot name="buttons">
                <button x-ref="confirmation" @click="location.href = $refs.confirmation.dataset.href"
                    class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                    Accept
                </button>
            </x-slot>
        </x-laravel-visualconsole::modal>

        <script>
            let artisan = document.querySelector('select#artisan');
        </script>
    @endpush
</x-laravel-visualconsole::layout>
