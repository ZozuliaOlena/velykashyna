{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
<title>{{ $store }}</title>
<link>{{ url('/') }}</link>
<description>Каталог шин «{{ $store }}»</description>
@foreach ($items as $i)
<item>
<g:id>{{ $i['id'] }}</g:id>
<title>{{ $i['title'] }}</title>
<description>{{ $i['description'] }}</description>
<link>{{ $i['link'] }}</link>
<g:image_link>{{ $i['image_link'] }}</g:image_link>
<g:availability>{{ $i['availability'] }}</g:availability>
<g:condition>{{ $i['condition'] }}</g:condition>
<g:price>{{ $i['price'] }}</g:price>
@if ($i['sale_price'])<g:sale_price>{{ $i['sale_price'] }}</g:sale_price>
@endif
@if ($i['brand'])<g:brand>{{ $i['brand'] }}</g:brand>
@endif
<g:identifier_exists>{{ $i['identifier_exists'] }}</g:identifier_exists>
<g:google_product_category>{{ $i['google_product_category'] }}</g:google_product_category>
@if ($i['product_type'])<g:product_type>{{ $i['product_type'] }}</g:product_type>
@endif
</item>
@endforeach
</channel>
</rss>
