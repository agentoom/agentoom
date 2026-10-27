@props(['align' => 'start', 'variant' => null, 'sticky' => false])

<td {{ $attributes->class([
    'px-3 py-3 text-sm first:ps-0 last:pe-0',
    'text-center [&>*]:mx-auto' => $align === 'center',
    'text-right [&>*]:ms-auto' => in_array($align, ['end', 'right'], true),
    'font-medium text-zinc-800 dark:text-white' => $variant === 'strong',
    'text-zinc-500 dark:text-zinc-300' => $variant !== 'strong',
    'sticky z-10 bg-white dark:bg-zinc-900' => $sticky,
]) }}>
    {{ $slot }}
</td>
