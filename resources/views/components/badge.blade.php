@props(['variant' => 'primary'])

@php
    $variantClasses = [
        'primary' => 'bg-primary-subtle text-primary',
        'success' => 'bg-success-subtle text-success',
        'danger' => 'bg-danger-subtle text-danger',
        'warning' => 'bg-warning-subtle text-warning',
    ];
    
    $classes = $variantClasses[$variant] ?? $variantClasses['primary'];
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $classes]) }}>
    {{ $slot }}
</span>
