@props(['placeholder' => null])
<input type="search" x-model="query" x-on:keydown.enter.prevent placeholder="{{ $placeholder }}" {{ $attributes->class('w-full rounded-md border-zinc-200 bg-transparent text-sm dark:border-white/10') }} />
