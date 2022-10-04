<x-layout>
    <x-slot name="title">
        @isset($choose)
            Choose Backup Signature
        @else
            Command Console
        @endisset
    </x-slot>
    @if (isset($errors) || isset($messages) || isset($code))
        @if ($errors)
            <x-alert :message="$errors->first()" color="red" />
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
                Command Console
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
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <label class="block mt-4 text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                    Command Console
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
            <x-commands :commands="$commands" />
        </div>
    </div>

    @push('bottom')
        <x-modal title="Confirm Action" name="confirm" x-cloak>
            Are you sure you want to perform this action? This might have very dangerous consequences.
            <x-slot name="buttons">
                <button x-ref="confirmation" @click="location.href = $refs.confirmation.dataset.href"
                    class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                    Accept
                </button>
            </x-slot>
        </x-modal>

        <script>
            let artisan = document.querySelector('select#artisan');
        </script>
    @endpush
</x-layout>
