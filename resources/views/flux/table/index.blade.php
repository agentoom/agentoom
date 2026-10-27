@props(['paginate' => null])

<div {{ $attributes->except(['container:class'])->class('flex flex-col') }}>
    {{ $header ?? '' }}
    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed whitespace-nowrap border-separate border-spacing-0 text-zinc-800 dark:text-zinc-100 [&_dialog]:whitespace-normal [&_[popover]]:whitespace-normal">
            {{ $slot }}
        </table>
    </div>
    {{ $footer ?? '' }}
    @if ($paginate)
        <div class="shrink-0 border-t border-zinc-200 pt-4 dark:border-white/10">
            {{ $paginate->links() }}
        </div>
    @endif
</div>
