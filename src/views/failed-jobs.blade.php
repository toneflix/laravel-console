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
        <!-- Cards -->
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

        <!-- New Table -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Job</th>
                            <th class="px-4 py-3">Attempts</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                            @if ($type == 'failed')
                                <th class="px-4 py-3">Exception</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($jobs->items() as $key => $job)
                            @php
                                $exception = collect($job->exception ?? null)->toJson();
                            @endphp
                            <tr class="text-gray-700 dark:text-gray-400" x-data="{
                                exception: {{ $exception }},
                                showException: false
                            }">
                                {{-- x-html="exception[0]" --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <!-- Avatar with inset shadow -->
                                        <div class="relative hidden w-10 h-10 mr-3 rounded-full md:block">
                                            <div
                                                class="flex justify-center items-center w-10 h-10 rounded-full bg-red-300">
                                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" width="12" height="12">
                                                    <path fill="none" d="M0 0h24v24H0z" />
                                                    <path
                                                        d="M9 1v2h6V1h2v2h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4V1h2zm11 7H4v11h16V8zm-4.964 2.136l1.414 1.414-4.95 4.95-3.536-3.536L9.38 11.55l2.121 2.122 3.536-3.536z"
                                                        fill="rgba(255,255,255,1)" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-semibold">
                                                {{ $job->payload->displayName ?? ($job['payload']['displayName'] ?? '') }}
                                            </p>
                                            <p class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                                {{ $job->payload->uuid ?? ($job['payload']['uuid'] ?? '') }}
                                                <span
                                                    class="ml-1 text-xs flex items-center justify-center text-white bg-gray-600 dark:bg-gray-400 block h-3 min-w-3">
                                                    {{ $job->id ?? ($job['id'] ?? '') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $job->failed_at ?? null ? 'Failed' : $job->attempts }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span
                                        class="px-2 py-1 font-semibold leading-tight text-{{ @$job->failed_at ? 'red' : 'blue' }}-700 bg-{{ @$job->failed_at ? 'red' : 'blue' }}-100 rounded-full dark:bg-{{ @$job->failed_at ? 'red' : 'blue' }}-700 dark:text-{{ @$job->failed_at ? 'red' : 'blue' }}-100">
                                        {{ @$job->failed_at ? 'Failed' : ($job->attempts ? 'Complete' : 'Pending') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ Carbon::parse($job->created_at ?? $job->failed_at) }}
                                </td>
                                @if ($type == 'failed')
                                    <td class="px-4 py-3 text-sm">
                                        <button
                                            class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100"
                                            @click="openModal('exception', {exception})"
                                            x-html="!showException?'Show':'Hide'">
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td colspan="5" class="px-4 py-3">
                                    <div
                                        class="mb-5 px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800 w-full h-20 flex items-center justify-center">
                                        There are no {{ $action['label'] }}.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            {{ $jobs->links('laravel-visualconsole::pagination.tailwind') }}
        </div>
        <x-laravel-visualconsole::modal name="exception" x-cloak>
            <div class="mt-4 mb-6" style="height: 70vh; overflow-y: auto;">
                <!-- Modal title -->
                <template x-if="exceptionData?.title">
                    <p class="mb-2 text-sm font-semibold text-red-700 dark:text-red-300" x-html="exceptionData?.title">
                    </p>
                </template>
                <!-- Modal description -->
                <template x-if="exceptionData?.data">
                    <template x-for="data,i in exceptionData?.data" x-data="{
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
                                x-transition:leave-end="opacity-0 scale-y-90" x-show="show[i]"
                                x-html="data.toString()">
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </x-laravel-visualconsole::modal>
    </div>
</x-laravel-visualconsole::layout>
