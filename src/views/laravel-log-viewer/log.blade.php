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
    <div class="container px-6 mx-auto grid" x-data="{}">
        {{-- isModalOpen: false --}}
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            {{ $action['label'] }}
        </h2>
        <!-- New Table -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
            <div class="col-span-1">

                <div class="list-group div-scroll">
                    @foreach ($folders as $folder)
                        <div class="flex items-center p-2 bg-gray-200 shadow-xs dark:bg-white"">
                            <?php
                            \Rap2hpoutre\LaravelLogViewer\LaravelLogViewer::DirectoryTreeStructure($storage_path, $structure);
                            ?>
                        </div>
                    @endforeach
                    @foreach ($files as $file)
                        <a href="?l={{ \Illuminate\Support\Facades\Crypt::encrypt($file) }}"
                            class="flex items-center p-2 bg-{{ $current_file == $file ? 'white' : 'gray-200' }}  shadow-xs dark:bg-{{ $current_file == $file ? 'gray-500' : 'white' }}">
                            {{ $file }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-span-3">
                <div class="w-full overflow-hidden rounded-lg shadow-xs">
                    <div class="w-full overflow-x-auto">
                        @if ($logs->count() < 1)
                            <div
                                class="mb-5 px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800 w-full h-20 flex items-center justify-center">
                                Log file >50M, please download it.
                            </div>
                        @else
                            <table class="w-full whitespace-no-wrap">
                                <thead>
                                    <tr
                                        class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                        @if ($standardFormat)
                                            <th class="px-4 py-3">Level</th>
                                            <th class="px-4 py-3">Context</th>
                                            <th class="px-4 py-3">Date</th>
                                        @else
                                            <th class="px-4 py-3">Line number</th>
                                        @endif
                                        <th class="px-4 py-3">Content</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                                    @foreach ($logs as $key => $log)
                                        @php
                                            $stack = collect(['data' => $log['stack'], 'title' => str($log['text'])])->toJson();
                                        @endphp
                                        <tr class="text-gray-700 dark:text-gray-400"
                                            data-display="stack{{ $key }}" x-data="{
                                                stackData: {{ $stack }},
                                                showStack: false
                                            }">
                                            @if ($standardFormat)
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center text-sm">
                                                        <!-- Avatar with inset shadow -->
                                                        <div
                                                            class="relative hidden w-10 h-10 mr-3 rounded-full md:block">
                                                            <div
                                                                class="flex justify-center items-center w-10 h-10 rounded-full bg-{{ $log['level_class'] }}-300">
                                                                <i class="{{ $log['level_img'] }} text-{{ $log['level_class'] }}-700 text-3xl"
                                                                    aria-hidden="true"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p class="font-semibold uppercase">{{ $log['level'] }}</p>
                                                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                                                10x Developer
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text px-4 py-3">{{ $log['context'] }}</td>
                                                <td class="px-4 py-3 text-sm">
                                                    {{ $log['date'] }}
                                                </td>
                                            @endif
                                            <td class="text px-4 py-3">
                                                {{ str($log['text'])->limit(40) }}
                                                @if (isset($log['in_file']))
                                                    <br />{{ str($log['in_file'])->limit(30) }}
                                                @endif
                                                @if ($log['stack'])
                                                    <div class="stack" id="stack{{ $key }}"
                                                        style="display: none; white-space: pre-wrap;">
                                                        {{-- {{ trim($log['stack']) }} --}}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center space-x-4 text-sm">
                                                    <button
                                                        class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                                        aria-label="Expand" @click="openModal('stack', {stackData})">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                            width="24" height="24" fill="currentColor">
                                                            <path fill="none" d="M0 0h24v24H0z" />
                                                            <path
                                                                d="M18.031 16.617l4.283 4.282-1.415 1.415-4.282-4.283A8.96 8.96 0 0 1 11 20c-4.968 0-9-4.032-9-9s4.032-9 9-9 9 4.032 9 9a8.96 8.96 0 0 1-1.969 5.617zm-5.853-9.44a4 4 0 1 0 2.646 2.646 2 2 0 1 1-2.646-2.647z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
                <!-- Pagination -->
                {{ $logs->onEachSide(0)->links('laravel-visualconsole::pagination.tailwind') }}
                <div class="p-3">
                    @if ($current_file)
                        <a class="px-1 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple cursor-pointer"
                            data-href="?dl={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
                            @click.prevent="run($el.dataset.href, $refs.confirmation, ['dhl'])">
                            <i class="ri-download-fill"></i> Download file
                        </a>
                        -
                        <a class="px-1 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple cursor-pointer"
                            id="clean-log"
                            data-href="?clean={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
                            @click.prevent="run($el.dataset.href, $refs.confirmation, ['clean'])">
                            <i class="ri-refresh-fill"></i> Clean file
                        </a>
                        -
                        <a class="px-1 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple cursor-pointer"
                            id="delete-log"
                            data-href="?del={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
                            @click.prevent="run($el.dataset.href, $refs.confirmation, ['del'])">
                            <i class="ri-delete-bin-fill"></i> Delete file
                        </a>
                        @if (count($files) > 1)
                            -
                            <a class="px-1 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple cursor-pointer"
                                id="delete-all-log"
                                data-href="?delall=true{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
                                @click.prevent="run($el.dataset.href, $refs.confirmation, ['delall'])">
                                <i class="ri-delete-bin-3-fill"></i> Delete all files
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <x-laravel-visualconsole::modal name="stack" x-cloak>
            <div class="mt-4 mb-6" style="height: 70vh; overflow-y: auto;">
                <!-- Modal title -->
                <template x-if="stackTrace?.title">
                    <p class="mb-2 text-sm font-semibold text-red-700 dark:text-red-300" x-html="stackTrace?.title">
                    </p>
                </template>
                <!-- Modal description -->
                <template x-if="stackTrace">
                    <template x-for="data,i in stackTrace?.data" x-data="{
                        show: {}
                    }">
                        <div class="flex flex-col mb-2">
                            <button
                                class="text-xs p-1 font-semibold bg-gray-100 text-gray-700 dark:text-gray-300 cursor-pointer"
                                x-html="data.toString().substring(45, 120)" @click="show[i] = !show[i]">
                            </button>
                            <div class="text-xs p-3 text-gray-700 dark:text-gray-400 border-x-2 border-b-2"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-y-90"
                                x-transition:enter-end="opacity-100 scale-y-100"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100 scale-y-100"
                                x-transition:leave-end="opacity-0 scale-y-90" x-show="show[i]" x-html="data.toString()">
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </x-laravel-visualconsole::modal>
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
    @endpush

    @push('top')
        <style>
            #table-log {
                font-size: 0.85rem;
            }

            .sidebar {
                font-size: 0.85rem;
                line-height: 1;
            }

            .btn {
                font-size: 0.7rem;
            }

            .stack {
                font-size: 0.85em;
            }

            .date {
                min-width: 75px;
            }

            .text {
                word-break: break-all;
            }

            a.llv-active {
                z-index: 2;
                background-color: #f5f5f5;
                border-color: #777;
            }

            .list-group-item {
                word-break: break-word;
            }

            .folder {
                padding-top: 15px;
            }

            .div-scroll {
                height: 80vh;
                overflow: hidden auto;
            }

            .nowrap {
                white-space: nowrap;
            }

            .list-group {
                padding: 5px;
            }
        </style>
    @endpush
</x-laravel-visualconsole::layout>
