@props(['commands' => []])
<div class="flex space-x-2 space-y-2 justify-center">
    <div>
        @foreach ($commands as $key => $command)
            @if (str($command['command'])->contains(['reset', 'seed']))
                <button type="button" x-ref="artisan_run" data-href="{{ url($command['command']) }}"
                    @click.prevent="run($el.dataset.href, $refs.confirmation, ['reset', 'seed'])"
                    class="inline-block m-0.5 px-2 py-1 border border-red-600 text-red-600 text-xs rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">{{ $command['label'] }}</button>
            @else
                <button type="button" x-ref="artisan_run" data-href="{{ url($command['command']) }}"
                    @click.prevent="run($el.dataset.href, $refs.confirmation, ['reset', 'seed'])"
                    class="inline-block m-0.5 px-2 py-1 border border-blue-400 text-blue-400 text-xs rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">{{ $command['label'] }}</button>
            @endif
        @endforeach
    </div>
</div>
