@props(['heading' => null, 'text' => null, 'inline' => false])
<label x-on:click.prevent="choose()" x-on:keydown.enter.prevent="choose()" x-on:keydown.space.prevent="choose()"
    x-on:dragover.prevent x-on:drop.prevent="const files = $event.dataTransfer.files; $refs.input.files = files; $refs.input.dispatchEvent(new Event('change', { bubbles: true }))"
    tabindex="0" role="button"
    {{ $attributes->class([
        'flex cursor-pointer items-center gap-4 rounded-lg border-2 border-dashed border-zinc-300 p-4 text-left transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500',
        'flex-row' => $inline,
        'flex-col justify-center text-center' => ! $inline,
    ]) }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800">
        <flux:icon icon="arrow-up-tray" class="size-5" />
    </span>
    <span>
        @if ($heading)<span class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $heading }}</span>@endif
        @if ($text)<span class="mt-1 block text-sm text-zinc-500">{{ $text }}</span>@endif
    </span>
</label>
