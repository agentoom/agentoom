@props([
    'label' => null,
    'rows' => 2,
    'maxRows' => null,
    'disabled' => false,
    'invalid' => false,
    'placeholder' => null,
])

<div>
    @if ($label)
        <label class="{{ $attributes->has('label:sr-only') ? 'sr-only' : 'mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300' }}">{{ $label }}</label>
    @endif
    <div x-data="{ resize(event) {
            const input = event.target;
            input.style.height = 'auto';
            const lineHeight = Number.parseFloat(getComputedStyle(input).lineHeight) || 24;
            const maxHeight = {{ $maxRows ? $maxRows . ' * lineHeight' : 'Infinity' }};
            input.style.height = Math.min(input.scrollHeight, maxHeight) + 'px';
        } }"
        {{ $attributes->except(['label', 'label:sr-only', 'placeholder', 'rows', 'max-rows', 'disabled', 'invalid'])->whereDoesntStartWith('wire:model')->class([
            'relative block w-full rounded-lg border bg-white shadow-xs dark:bg-white/10',
            'border-red-500' => $invalid,
            'border-zinc-200 dark:border-white/10' => ! $invalid,
        ]) }}>
        <textarea rows="{{ $rows }}" placeholder="{{ $placeholder }}" @disabled($disabled)
            {{ $attributes->whereStartsWith('wire:model') }}
            x-on:input="resize($event)"
            class="block w-full resize-none border-0 bg-transparent px-3 py-3 pe-14 text-sm outline-none placeholder:text-zinc-400 focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60"></textarea>
        @if (isset($actionsLeading) && ! $actionsLeading->isEmpty())
            <div class="absolute bottom-2 start-2">{{ $actionsLeading }}</div>
        @endif
        @if (isset($actionsTrailing) && ! $actionsTrailing->isEmpty())
            <div class="absolute bottom-2 end-2">{{ $actionsTrailing }}</div>
        @endif
    </div>
</div>
