{{-- The dark-mode control. One definition, included wherever chrome has room
     for it: the admin and dashboard shells today, the shared directory header
     once that partial is free to edit.

     No script of its own — `pages/partials/theme.blade.php` wires every
     `.theme-toggle` on the page by delegation, so this can be included more
     than once (desktop bar + mobile menu) without duplicate handlers.

     Icons are inline SVG rather than lucide: this control renders inside a
     <template> for the floating fallback, where `lucide.createIcons()` has
     already run and would never see it.

     Self-guarding: seven certificate views include the shared directory header,
     and a document ships neither the theme API nor the click delegation, so a
     control rendered there would be dead furniture on a printed page. The same
     condition `pages/partials/theme.blade.php` uses is evaluated here, and the
     partial renders nothing at all on a locked document. --}}
@unless (($lockLightTheme ?? false) || \App\Support\Theme::routeIsDocument())
<button type="button"
        class="theme-toggle"
        role="switch"
        aria-checked="false"
        aria-label="{{ ($lang ?? 'fr') === 'fr' ? 'Basculer le thème sombre' : 'Toggle dark theme' }}"
        title="{{ ($lang ?? 'fr') === 'fr' ? 'Thème clair / sombre' : 'Light / dark theme' }}">
    <span class="theme-toggle__label">{{ ($lang ?? 'fr') === 'fr' ? 'Thème' : 'Theme' }}</span>
    <span class="theme-toggle__track" aria-hidden="true">
        <span class="theme-toggle__knob">
            <svg class="theme-toggle__sun" viewBox="0 0 24 24" fill="none" stroke="#3B2A05" stroke-width="3" stroke-linecap="round">
                <circle cx="12" cy="12" r="4.5"/>
                <path d="M12 1.5v2M12 20.5v2M1.5 12h2M20.5 12h2M4.6 4.6l1.4 1.4M18 18l1.4 1.4M19.4 4.6L18 6M6 18l-1.4 1.4"/>
            </svg>
            <svg class="theme-toggle__moon" viewBox="0 0 24 24" fill="none" stroke="#3B2A05" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a6.6 6.6 0 0 0 10.5 10.5z"/>
            </svg>
        </span>
    </span>
</button>
@endunless
