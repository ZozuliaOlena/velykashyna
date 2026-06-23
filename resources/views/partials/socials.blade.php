{{-- resources/views/partials/socials.blade.php — рядок іконок соцмереж --}}
@php($s = config('site.socials'))
<div class="footer-socials">
    <a href="{{ $s['facebook'] }}" aria-label="Facebook" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.52 1.49-3.91 3.78-3.91 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94z" />
        </svg>
    </a>
    <a href="{{ $s['instagram'] }}" aria-label="Instagram" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="2" width="20" height="20" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <line x1="17.5" y1="6.5" x2="17.5" y2="6.5" />
        </svg>
    </a>
    <a href="{{ $s['youtube'] }}" aria-label="YouTube" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M23 12s0-3.2-.41-4.74a2.5 2.5 0 0 0-1.76-1.77C19.29 5.08 12 5.08 12 5.08s-7.29 0-8.83.41A2.5 2.5 0 0 0 1.41 7.26C1 8.8 1 12 1 12s0 3.2.41 4.74a2.5 2.5 0 0 0 1.76 1.77c1.54.41 8.83.41 8.83.41s7.29 0 8.83-.41a2.5 2.5 0 0 0 1.76-1.77C23 15.2 23 12 23 12zM9.75 15.02V8.98L15.5 12l-5.75 3.02z" />
        </svg>
    </a>
    <a href="{{ $s['tiktok'] }}" aria-label="TikTok" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M16.5 3c.3 2 1.6 3.6 3.5 4v2.6c-1.3 0-2.5-.4-3.5-1v6.2a5.6 5.6 0 1 1-5.6-5.6c.3 0 .6 0 .9.1v2.7a2.9 2.9 0 1 0 2 2.8V3h2.7z" />
        </svg>
    </a>
</div>
