@props(['tone' => 'muted', 'dot' => false])
@php
$tones = [
    'success' => 'bg-success-soft text-success',
    'info'    => 'bg-info-soft text-info',
    'warning' => 'bg-warning-soft text-warning',
    'danger'  => 'bg-danger-soft text-danger',
    'primary' => 'bg-primary-soft text-primary',
    'muted'   => 'bg-muted-surface text-muted',
];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium '.($tones[$tone] ?? $tones['muted'])]) }}>
    @if ($dot)<span class="size-1.5 rounded-full bg-current"></span>@endif
    {{ $slot }}
</span>
