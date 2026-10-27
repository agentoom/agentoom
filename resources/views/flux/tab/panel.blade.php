@props(['name' => null, 'selected' => false])

@php
    $slug = $name !== null ? str_replace([' ', '.'], '-', (string) $name) : null;
@endphp

<div @if ($name) id="panel-{{ $slug }}" role="tabpanel" aria-labelledby="tab-{{ $slug }}" wire:key="{{ $name }}" @endif
    @if ($name) x-show="selected === @js($name)" style="display: none;" @endif
    {{ $attributes->class('pt-8') }}>
    {{ $slot }}
</div>
