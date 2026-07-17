@php
  // Logo OSM berbentuk lebar. Width/height dipakai sebagai batas, bukan
  // dipaksakan menjadi kotak, supaya proporsi huruf dan orbit tidak gepeng.
  $logoWidth = $width ?? (($height ?? 32) * 2.35);
  $logoHeight = $height ?? null;
@endphp

<span class="d-inline-flex align-items-center justify-content-center flex-shrink-0 osm-logo-wrap">
  <img src="{{ asset('assets/img/logo/osm-brand-mark-v2.png') }}"
    width="{{ $logoWidth }}" @if($logoHeight) height="{{ $logoHeight }}" @endif
    alt="OSM" class="osm-logo-image" loading="eager">
</span>
