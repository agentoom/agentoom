@props(['key' => null, 'sticky' => false])

<tr @if ($key) wire:key="table-{{ $key }}" @endif {{ $attributes->class([
    'border-t border-zinc-800/10 dark:border-white/20' => true,
    'last:sticky last:bottom-0 last:z-20' => $sticky,
]) }}>
    {{ $slot }}
</tr>
