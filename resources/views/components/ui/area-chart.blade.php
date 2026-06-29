@props(['data' => [], 'height' => 220, 'id' => 'area'])
@php
    $data = array_values($data);
    if (count($data) < 2) $data = [0, 0];
    $w = 600; $h = (int) $height; $pad = 6;
    $max = max($data); $min = min(min($data), 0); $range = ($max - $min) ?: 1;
    $stepX = ($w - $pad * 2) / (count($data) - 1);
    $pts = [];
    foreach ($data as $i => $v) {
        $pts[] = [$pad + $i * $stepX, $h - $pad - (($v - $min) / $range) * ($h - $pad * 2)];
    }
    $line = 'M '.$pts[0][0].','.$pts[0][1];
    for ($i = 0; $i < count($pts) - 1; $i++) {
        $cx = ($pts[$i][0] + $pts[$i + 1][0]) / 2;
        $line .= " C {$cx},{$pts[$i][1]} {$cx},{$pts[$i+1][1]} {$pts[$i+1][0]},{$pts[$i+1][1]}";
    }
    $area = $line." L {$pts[count($pts)-1][0]},".($h - $pad)." L {$pts[0][0]},".($h - $pad).' Z';
    $last = $pts[count($pts) - 1];
@endphp
<svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full" style="height: {{ $h }}px" preserveAspectRatio="none">
    <defs>
        <linearGradient id="{{ $id }}-fill" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="var(--chart-1)" stop-opacity="0.28" />
            <stop offset="100%" stop-color="var(--chart-1)" stop-opacity="0" />
        </linearGradient>
    </defs>
    @foreach ([0.25, 0.5, 0.75] as $g)
        <line x1="{{ $pad }}" x2="{{ $w - $pad }}" y1="{{ $h * $g }}" y2="{{ $h * $g }}" stroke="var(--border)" stroke-width="1" stroke-dasharray="2 4" />
    @endforeach
    <path d="{{ $area }}" fill="url(#{{ $id }}-fill)" />
    <path d="{{ $line }}" fill="none" stroke="var(--chart-1)" stroke-width="2.5" stroke-linecap="round" />
    <circle cx="{{ $last[0] }}" cy="{{ $last[1] }}" r="4" fill="var(--chart-1)" stroke="var(--bg)" stroke-width="2" />
</svg>
