@props(['size' => null, 'variant' => null, 'scrollable' => false])
@php
    $model = $attributes->wire('model')->value();
    $selectedExpression = $model ? "selected: \$wire.entangle('{$model}')" . ($attributes->wire('model')->hasModifier('live') ? '.live' : '') : null;
    $classes = match ($variant) {
        'pills' => 'flex gap-4 h-8',
        'segmented' => 'inline-flex p-1 rounded-lg bg-zinc-800/5 dark:bg-white/10',
        default => 'flex gap-4 h-10 border-b border-zinc-800/10 dark:border-white/20',
    };
@endphp
<div class="relative {{ $scrollable ? 'overflow-x-auto' : '' }}">
    <div {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.blur', 'wire:model.lazy'])->class($classes) }}
         role="tablist" @if ($selectedExpression) x-data="{ {!! $selectedExpression !!} }" @endif>
        {{ $slot }}
    </div>
</div>
