@props([
    'name' => null,
    'selected' => false,
    'disabled' => false,
    'icon' => null,
    'iconTrailing' => null,
    'variant' => null,
    'size' => null,
    'accent' => true,
])

@php
    $id = 'tab-' . str_replace([' ', '.'], '-', (string) $name);
@endphp
<button type="button" id="{{ $id }}" role="tab" @if ($name) aria-controls="panel-{{ str_replace([' ', '.'], '-', (string) $name) }}" wire:key="{{ $name }}" @endif
    @disabled($disabled)
    @if ($name) x-init="if (selected === null) selected = @js($name)" x-on:click="selected = @js($name)" x-on:keydown.arrow-right.prevent="const tabs = [...$el.closest('[role=tablist]').querySelectorAll('[role=tab]:not([disabled])')]; tabs[(tabs.indexOf($el) + 1) % tabs.length]?.focus()" x-on:keydown.arrow-left.prevent="const tabs = [...$el.closest('[role=tablist]').querySelectorAll('[role=tab]:not([disabled])')]; tabs[(tabs.indexOf($el) - 1 + tabs.length) % tabs.length]?.focus()" x-bind:aria-selected="selected === @js($name)" x-bind:data-selected="selected === @js($name)" @endif
    {{ $attributes->class([
        'inline-flex items-center gap-2 whitespace-nowrap text-sm font-medium transition',
        'rounded-full px-3' => $variant === 'pills',
        'flex-1 justify-center rounded-md px-4' => $variant === 'segmented',
        'border-b-2 border-transparent px-2 -mb-px' => ! in_array($variant, ['pills', 'segmented'], true),
        'py-2' => $variant !== 'segmented',
        'text-zinc-600 hover:text-zinc-900 dark:text-white/70 dark:hover:text-white' => true,
        'data-[selected=true]:bg-zinc-800/10 dark:data-[selected=true]:bg-white/10' => $variant === 'pills',
        'data-[selected=true]:bg-white data-[selected=true]:shadow-xs dark:data-[selected=true]:bg-white/20' => $variant === 'segmented',
        'data-[selected=true]:border-(--color-accent-content) data-[selected=true]:text-(--color-accent-content)' => ! in_array($variant, ['pills', 'segmented'], true),
        'opacity-50 cursor-default' => $disabled,
    ]) }}>
    @if (is_string($icon) && $icon !== '')
        <flux:icon :icon="$icon" variant="mini" class="size-5" />
    @elseif ($icon)
        {{ $icon }}
    @endif
    {{ $slot }}
    @if (is_string($iconTrailing) && $iconTrailing !== '')
        <flux:icon :icon="$iconTrailing" variant="micro" />
    @elseif ($iconTrailing)
        {{ $iconTrailing }}
    @endif
</button>
