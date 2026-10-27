@props(['multiple' => false, 'placeholder' => null])
@php
    $model = $attributes->wire('model')->value();
    $binding = $model
        ? '$wire.entangle(' . Js::from($model)->toHtml() . ')' . ($attributes->wire('model')->hasModifier('live') ? '.live' : '')
        : 'null';
@endphp

<div
    x-data="{
        open: false,
        query: '',
        selected: {{ $binding }},
        options: [],
        init() {
            this.options = [...this.$refs.source.options].map(option => ({
                value: option.value,
                label: option.textContent.trim(),
                disabled: option.disabled,
            }));
            this.syncSource();
        },
        get selectedValues() {
            return {{ $multiple ? 'Array.isArray(this.selected) ? this.selected.map(String) : []' : '[this.selected ?? \'\'].map(String)' }};
        },
        get selectedOptions() {
            return this.options.filter(option => this.selectedValues.includes(String(option.value)));
        },
        get visibleOptions() {
            const needle = this.query.toLowerCase();
            return this.options.filter(option => !needle || option.label.toLowerCase().includes(needle));
        },
        label() {
            const labels = this.selectedOptions.map(option => option.label);
            return labels.length ? labels.join(', ') : @js($placeholder ?: __('Select an option'));
        },
        choose(value) {
            if ({{ $multiple ? 'true' : 'false' }}) {
                const values = this.selectedValues;
                this.selected = values.includes(String(value))
                    ? values.filter(item => item !== String(value))
                    : [...values, String(value)];
            } else {
                this.selected = value;
                this.open = false;
            }
            this.syncSource(true);
        },
        syncSource(dispatch = false) {
            if (!this.$refs.source) return;
            const values = this.selectedValues;
            [...this.$refs.source.options].forEach(option => {
                option.selected = values.includes(String(option.value));
            });
            if (dispatch) this.$refs.source.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative w-full"
>
    <select x-ref="source" class="sr-only" @if ($multiple) multiple @endif
        {{ $attributes->except(['variant', 'multiple', 'searchable', 'clearable', 'placeholder', 'label', 'description', 'invalid', 'size', 'name'])->whereStartsWith('wire:model') }}>
        @if ($placeholder && ! $multiple)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open" aria-haspopup="listbox"
        {{ $attributes->except(['variant', 'multiple', 'searchable', 'clearable', 'placeholder', 'label', 'description', 'invalid', 'size', 'name'])->whereDoesntStartWith('wire:model')->class('flex w-full items-center justify-between gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm text-zinc-800 shadow-xs transition hover:border-zinc-300 focus:border-(--color-accent-content) focus:outline-none focus:ring-2 focus:ring-(--color-accent-content)/20 dark:border-white/10 dark:bg-white/10 dark:text-zinc-100 dark:hover:border-white/20') }}>
        <span class="min-w-0 flex-1 truncate" x-text="label()"></span>
        <flux:icon.chevron-down class="size-4 shrink-0 text-zinc-400" />
    </button>

    <div x-show="open" x-transition style="display: none;" class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-white/10 dark:bg-zinc-800">
        @if (isset($search) && ! $search->isEmpty())
            <div class="border-b border-zinc-200 p-2 dark:border-white/10">{{ $search }}</div>
        @elseif ($attributes->has('searchable'))
            <div class="border-b border-zinc-200 p-2 dark:border-white/10">
                <input type="search" x-model="query" x-on:keydown.enter.prevent placeholder="{{ __('Search...') }}" class="w-full rounded-md border-zinc-200 bg-transparent text-sm dark:border-white/10" />
            </div>
        @endif
        <div class="max-h-60 overflow-y-auto p-1" role="listbox" @if ($multiple) aria-multiselectable="true" @endif>
            <template x-for="option in visibleOptions" :key="option.value">
                <button type="button" role="option" x-on:click="!option.disabled && choose(option.value)"
                    x-bind:aria-selected="selectedValues.includes(String(option.value))"
                    x-bind:disabled="option.disabled"
                    class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 aria-selected:bg-zinc-100 aria-selected:font-medium dark:text-zinc-200 dark:hover:bg-white/10 dark:aria-selected:bg-white/10">
                    <span x-show="{{ $multiple ? 'selectedValues.includes(String(option.value))' : 'false' }}" class="size-4 text-(--color-accent-content)">✓</span>
                    <span x-text="option.label"></span>
                </button>
            </template>
            <div x-show="visibleOptions.length === 0" class="px-3 py-2 text-sm text-zinc-500">{{ __('No results') }}</div>
        </div>
    </div>
</div>
