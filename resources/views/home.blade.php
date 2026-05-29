<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TB Store - Belanja Jadi Mudah</title>
    {{-- SEO dasar — dibaca crawler SEBELUM JS jalan --}}
    <meta name="description" content="Belanja produk" />
    <meta name="robots"      content="index, follow" />
    <meta name="author"      content="TB Store" />
    
    {{-- Open Graph (WhatsApp, Facebook, dll) --}}
    <meta property="og:type"        content="website" />
    <meta property="og:locale"      content="id_ID" />
    <meta property="og:url"         content="{{ url('/') }}" />
    <meta property="og:title"       content="TB Store - Toko Belanja Jadi Mudah" />
    <meta property="og:description" content="Belanja kebutuhan vape berkualitas di TB Store" />
    <meta property="og:image" content="{{ asset('images/og-default.jpg') }}" />

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image" />
    <meta name="twitter:title"       content="TB Store - Belanja jadi mudah" />
    <meta name="twitter:description" content="Belanja produk terbaik di TB Store." />
    <meta property="og:image" content="{{ asset('images/og-default.jpg') }}" />

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url('/') }}" />

    {{-- Favicon --}}
    <link rel="icon"             type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="apple-touch-icon"                  href="{{ asset('apple-touch-icon.png') }}" />

    {{-- JSON-LD structured data --}}
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "TB Store",
        "url": "BASE_URL",
        "description": "Belanja produk terbaik di TB Store.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "BASE_URL/products?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    @endverbatim

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
    
</body>
</html>