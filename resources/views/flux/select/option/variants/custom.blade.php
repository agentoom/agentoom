@props(['value' => null])
<option value="{{ $value }}" {{ $attributes }}>{{ strip_tags((string) $slot) }}</option>
