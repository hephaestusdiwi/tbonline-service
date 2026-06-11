<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TB Store - Belanja Jadi Mudah</title>
    {{-- SEO dasar — dibaca crawler SEBELUM JS jalan --}}
    <meta name="description" content="TB Store by TwoBrothers Vapestore menyediakan produk vape original, mod, pod, atomizer, liquid premium, coil, cartridge, dan aksesoris vape terpercaya." />
    <meta name="robots"      content="index, follow" />
    <meta name="author"      content="TB Store" />
    
    {{-- Open Graph (WhatsApp, Facebook, dll) --}}
    <meta property="og:type"        content="website" />
    <meta property="og:locale"      content="id_ID" />
    <meta property="og:url"         content="{{ url('/') }}" />
    <meta property="og:title"       content="TB Store by TwoBrothers Vapestore | Vape, Mod, Pod & Aksesoris" />
    <meta property="og:description" content="Online vape store dari TwoBrothers Vapestore yang menyediakan produk vape original, mod, pod system, atomizer, liquid premium, dan aksesoris vape terpercaya." />
    <meta property="og:image" content="https://tbstore.id/storage/logos/footer_1779262552.webp" />

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image" />
    <meta name="twitter:title"       content="TB Store by TwoBrothers Vapestore | Vape, Mod, Pod & Aksesoris" />
    <meta name="twitter:description" content="Online vape store dari TwoBrothers Vapestore yang menyediakan produk vape original, mod, pod system, atomizer, liquid premium, dan aksesoris vape terpercaya." />
    <meta property="twitter:image" content="https://tbstore.id/storage/logos/footer_1779262552.webp">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url('/') }}" />

    {{-- Favicon --}}
    <link rel="icon"             type="image/png" href="https://tbstore.id/storage/logos/favicon_1779286394.png" />
    <link rel="apple-touch-icon"                  href="https://tbstore.id/storage/logos/favicon_1779286394.png" />

    {{-- JSON-LD structured data --}}
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "TB Store",
        "url": "BASE_URL",
        "description": "Online vape store dari TwoBrothers Vapestore yang menyediakan produk vape original, mod, pod system, atomizer, liquid premium, dan aksesoris vape terpercaya.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "BASE_URL/products?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    @endverbatim

    {{-- Preconnect Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
    
</body>
</html>