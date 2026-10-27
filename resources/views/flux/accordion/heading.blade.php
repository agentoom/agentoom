<button type="button" x-on:click="expanded = ! expanded" x-bind:aria-expanded="expanded" {{ $attributes->class('flex w-full items-center text-left') }}>
    {{ $slot }}
</button>
