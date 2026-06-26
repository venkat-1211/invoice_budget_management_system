@props([
    'title' => null,
    'subtitle' => null,
    'class' => '',
    'headerClass' => '',
    'bodyClass' => '',
    'footer' => null,
    'noPadding' => false
])

<div class="card shadow-sm border-0 {{ $class }}" {{ $attributes }}>
    @if($title || $subtitle)
        <div class="card-header bg-white border-bottom py-3 {{ $headerClass }}">
            @if($title)
                <h5 class="card-title mb-0 fw-bold">{{ $title }}</h5>
            @endif
            @if($subtitle)
                <p class="card-subtitle text-muted mt-1 mb-0 small">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div class="card-body {{ $noPadding ? 'p-0' : '' }} {{ $bodyClass }}">
        {{ $slot }}
    </div>
    @if($footer)
        <div class="card-footer bg-light">
            {{ $footer }}
        </div>
    @endif
</div>
