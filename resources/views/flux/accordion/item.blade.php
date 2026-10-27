@props(['expanded' => false])
<div x-data="{ expanded: @js($expanded) }" {{ $attributes }}>
    {{ $slot }}
</div>
