{{-- Google Аналітика / реклама. Керується в адмінці (Налаштування → Google
     Аналітика та реклама). Рендериться лише те, що заповнено. --}}
@php($gtmId = trim((string) \App\Models\Setting::get('gtm_container_id')))
@php($gaId = trim((string) \App\Models\Setting::get('ga_measurement_id')))
@php($adsId = trim((string) \App\Models\Setting::get('google_ads_id')))
@php($headCode = (string) \App\Models\Setting::get('tracking_head_code'))

@if($gtmId)
{{-- Google Tag Manager --}}
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
@endif

@if($gaId || $adsId)
{{-- Google tag (gtag.js) - GA4 та/або Google Ads --}}
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId ?: $adsId }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    @if($gaId) gtag('config', '{{ $gaId }}'); @endif
    @if($adsId) gtag('config', '{{ $adsId }}'); @endif
</script>
@endif

@if(trim($headCode) !== '')
{{-- Власний код адміністратора (вставляється як є) --}}
{!! $headCode !!}
@endif
