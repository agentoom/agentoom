@props(['align' => 'start', 'sortable' => false, 'sticky' => false])

<th {{ $attributes->class([
    'px-3 py-3 text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400 first:ps-0 last:pe-0',
    'text-center' => $align === 'center',
    'text-right' => in_array($align, ['end', 'right'], true),
    'sticky left-0 z-10 bg-white dark:bg-zinc-900' => $sticky,
]) }} scope="col">
    @if ($sortable)
        <span class="inline-flex items-center gap-1" aria-label="Sortable column">
            {{ $slot }}
            <span aria-hidden="true" class="text-zinc-400">↕</span>
        </span>
    @else
        {{ $slot }}
    @endif
</th>
