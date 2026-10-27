@props(['toolbar' => null, 'invalid' => null, 'variant' => null, 'name' => null])

@php
    $model = $attributes->wire('model')->value();
    $label = $attributes->get('label');
    $description = $attributes->get('description');
    $placeholder = $attributes->get('placeholder');
    $invalid ??= ($model && $errors->has($model));
@endphp

<div {{ $attributes->except(['wire:model', 'wire:model.live', 'label', 'description', 'placeholder'])->class('block w-full') }}
    x-data="{
        value: {{ $model ? '$wire.entangle(' . \Illuminate\Support\Js::from($model)->toHtml() . ')' : "''" }},
        editor: null,
        init() {
            this.editor = this.$refs.content;
            this.editor.innerHTML = this.value || '';
            this.$watch('value', (value) => {
                if (value !== this.editor.innerHTML) this.editor.innerHTML = value || '';
            });
        },
        update() { this.value = this.editor.innerHTML; },
        command(command, value = null) { this.editor.focus(); document.execCommand(command, false, value); this.update(); }
    }">
    @if ($label)
        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    @endif
    <div class="overflow-hidden rounded-lg border bg-white shadow-xs dark:bg-white/10 {{ $invalid ? 'border-red-500' : 'border-zinc-200 dark:border-white/10' }}">
        <div class="flex flex-wrap gap-1 border-b border-zinc-200 p-2 dark:border-white/10" role="toolbar">
            <button type="button" class="rounded px-2 py-1 text-sm hover:bg-zinc-100 dark:hover:bg-white/10" x-on:click="command('bold')"><strong>B</strong></button>
            <button type="button" class="rounded px-2 py-1 text-sm italic hover:bg-zinc-100 dark:hover:bg-white/10" x-on:click="command('italic')">I</button>
            <button type="button" class="rounded px-2 py-1 text-sm underline hover:bg-zinc-100 dark:hover:bg-white/10" x-on:click="command('underline')">U</button>
            <button type="button" class="rounded px-2 py-1 text-sm hover:bg-zinc-100 dark:hover:bg-white/10" x-on:click="command('insertUnorderedList')">• List</button>
        </div>
        <div x-ref="content" contenteditable="true" role="textbox" aria-multiline="true" @input="update()" @blur="update()"
            @if ($model) wire:ignore @endif
            class="min-h-32 p-3 text-sm text-zinc-700 outline-none empty:before:text-zinc-400 empty:before:content-[attr(data-placeholder)]"
            @if ($placeholder) data-placeholder="{{ $placeholder }}" @endif></div>
    </div>
    @if ($description)
        <p class="mt-1 text-sm text-zinc-500">{{ $description }}</p>
    @endif
</div>
