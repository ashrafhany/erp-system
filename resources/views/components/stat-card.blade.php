@props([
    'label',
    'value',
    'icon',
    'color' => 'primary',
    'decimals' => 0,
    'suffix' => null,
    'hint' => null,
    'trend' => null,
])

<div {{ $attributes->merge(['class' => "card stat-card stat-{$color} h-100"]) }}>
    <span class="stat-shine" aria-hidden="true"></span>
    <i class="{{ $icon }} stat-bg-icon" aria-hidden="true"></i>
    <div class="card-body">
        <span class="stat-icon"><i class="{{ $icon }}"></i></span>
        <div class="stat-body">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value">
                <span data-count="{{ (float) $value }}" data-decimals="{{ $decimals }}">{{ number_format((float) $value, $decimals) }}</span>
                @if($suffix)<small>{{ $suffix }}</small>@endif
            </div>
            @if($trend !== null || $hint)
                <div class="stat-hint">
                    @if($trend !== null)
                        <span class="stat-trend {{ $trend >= 0 ? 'up' : 'down' }}">
                            <i class="fas fa-arrow-{{ $trend >= 0 ? 'up' : 'down' }}"></i>{{ abs($trend) }}%
                        </span>
                    @endif
                    {{ $hint }}
                </div>
            @endif
        </div>
    </div>
</div>
