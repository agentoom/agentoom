@props(['name' => null])
@php($model = $attributes->wire('model')->value())
<div x-data="{ input: null, fileName: '', choose() { this.$refs.input.click() }, sync(event) { this.fileName = event.target.files[0]?.name || ''; } }" class="relative">
    <input x-ref="input" type="file" class="sr-only" wire:ignore @change="sync($event)"
        @if ($name ?? $model) name="{{ $name ?? $model }}" @endif
        {{ $attributes->except(['name'])->whereStartsWith('wire:model') }} />
    {{ $slot }}
    <p x-show="fileName" x-text="fileName" class="mt-2 text-sm text-zinc-600 dark:text-zinc-300"></p>
</div>
