{{-- ALCO lockup for the Filament panel. Inline styles: Filament's prebuilt CSS does not include app Tailwind classes. --}}
@php($color ??= '#062849')

<span style="display: inline-flex; height: 100%; align-items: center; gap: 0.6rem; color: {{ $color }}; white-space: nowrap">
    <svg viewBox="0 0 190 180" fill="currentColor" aria-hidden="true" style="height: 88%; width: auto; flex-shrink: 0">
        <path d="M12 164 91 13h13l68 129h-27L98 51 49 143h115l12 21H12Z" />
        <path d="m68 131 27-51h11l26 51h-25l-9-22-11 22H68Z" />
    </svg>
    <span style="font-size: 1.3rem; font-weight: 600; letter-spacing: 0.12em; line-height: 1">ALCO</span>
    <span style="border-left: 1px solid color-mix(in srgb, currentColor 40%, transparent); padding-left: 0.6rem; font-size: 0.68rem; font-weight: 500; line-height: 1.1; opacity: 0.85">Associate<br>Solutions</span>
</span>
