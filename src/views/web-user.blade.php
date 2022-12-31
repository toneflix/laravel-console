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
    @if (isset($errors) || isset($messages) || isset($code))
        @if ($errors)
            <x-laravel-visualconsole::alert :message="$errors->first()" color="red" />
        @endif
        @isset($messages)
            <div class="errors m-5">{{ $messages->first() }}</div>
        @endisset
    @endif
    <div class="container px-6 mx-auto grid" xs-data="webuser">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            @isset($choose)
                Choose Backup Signature
            @else
                {{ $action['label'] }}
            @endisset
        </h2>
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
            <!-- Card -->
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path
                            d="M9 1v2h6V1h2v2h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4V1h2zm11 7H4v11h16V8zm-4.964 2.136l1.414 1.414-4.95 4.95-3.536-3.536L9.38 11.55l2.121 2.122 3.536-3.536z"
                            fill="rgba(3,142,11,1)" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                        Total Jobs
                    </p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        {{ $total_jobs }}
                    </p>
                </div>
            </div>
            <!-- Card -->
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div
                    class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path
                            d="M9 1v2h6V1h2v2h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4V1h2zm11 7H4v11h16V8zm-4.964 2.136l1.414 1.414-4.95 4.95-3.536-3.536L9.38 11.55l2.121 2.122 3.536-3.536z"
                            fill="rgba(254,92,0,1)" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                        Failed Jobs
                    </p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        {{ $failed_jobs }}
                    </p>
                </div>
            </div>
            <!-- Card -->
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path
                            d="M14 10h-4v4h4v-4zm2 0v4h3v-4h-3zm-2 9v-3h-4v3h4zm2 0h3v-3h-3v3zM14 5h-4v3h4V5zm2 0v3h3V5h-3zm-8 5H5v4h3v-4zm0 9v-3H5v3h3zM8 5H5v3h3V5zM4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"
                            fill="rgba(10,116,84,1)" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                        Total Tables
                    </p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        {{ $tables_count }}
                    </p>
                </div>
            </div>
        </div>
        @isset($code)
            <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div
                    class="code-holder bg-black text-green-600 p-4 max-h-96 min-h-fit text-xs overflow-hidden overflow-y-auto">
                    <code class="code m-5">{!! $code->first() !!}</code>
                </div>
            </div>
        @endisset
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <label class="block mt-4 text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                    {{ $action['label'] }}
                </span>
                <div class="relative text-gray-500 focus-within:text-red-600">
                    @if ($action === 'choose' || $action === 'download')
                        <select x-ref="artisan"
                            @input="$refs.artisan_run.setAttribute('data-href', $refs.artisan.value)" id="artisan"
                            :da="$refs.artisan.value"
                            class="block mb-3 w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-red-400 focus:outline-none focus:shadow-outline-red dark:focus:shadow-outline-gray">
                            <option value="" readonly>Choose {{ $action ? 'Signature' : 'Action' }}</option>
                            @isset($signatures)
                                @foreach ($signatures as $signature)
                                    <option
                                        value="{{ url(($action === 'choose' ? 'artisan/system:reset -r -s ' : 'downloads/') . $signature) }}">
                                        {{ $signature }}
                                    </option>
                                @endforeach
                            @endisset
                            {{-- <option value="{{ url('artisan/list') }}">Go Back</option> --}}
                            {{-- @else --}}
                            {{-- @foreach ($commands as $command => $label)
                                <option value="{{ url($command) }}">{{ $label }}</option>
                            @endforeach --}}
                        </select>
                        <button x-ref="artisan_run" data-href="{{ url('artisan/list') }}"
                            @click="run($refs.artisan_run.dataset.href, $refs.confirmation, ['reset', 'seed'])"
                            class="absolute inset-y-0 right-0 px-4 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-r-md active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                            {{ $action === 'download' ? 'Select' : 'Run' }}
                        </button>
                    @endif
                </div>
            </label>
            <x-laravel-visualconsole::commands :commands="$commands" />
        </div>
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
