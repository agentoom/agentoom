@props(['placeholder' => null])
<input
    type="search"
    name="listbox_search"
    x-ref="searchInput"
    x-model="query"
    x-bind:disabled="!open"
    x-on:keydown.enter.prevent
    placeholder="{{ $placeholder }}"
    autocomplete="off"
    autocorrect="off"
    autocapitalize="off"
    spellcheck="false"
    role="searchbox"
    aria-autocomplete="list"
    data-bwignore="true"
    data-1p-ignore
    data-lpignore="true"
    data-form-type="other"
    {{ $attributes->class('w-full rounded-md border-zinc-200 bg-transparent text-sm dark:border-white/10') }}
/>
