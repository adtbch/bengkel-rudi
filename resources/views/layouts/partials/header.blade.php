<header class="site-header{{ request()->is('/') ? ' site-header--hero' : '' }}">
    <div class="header-wrap">
        <a href="/" class="brand-logo">
            <img class="brand-logo__image" src="https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png" alt="Logo Bengkel Rudi" width="56" height="56">
            <span class="brand-logo__text">Bengkel Rudi</span>
        </a>

        <nav class="nav-links-desktop" aria-label="Navigasi utama">
            <a href="/">Home</a>
            <a href="/layanan">Layanan</a>
            <a href="/portfolio">Galeri</a>
            <a href="/tentang">Tentang</a>
            <a href="/kontak">Kontak &amp; Lokasi</a>
        </nav>

        <details class="mobile-menu">
            <summary aria-label="Buka menu navigasi">
                <span></span><span></span><span></span>
            </summary>
            <nav aria-label="Navigasi mobile">
                <a href="/">Home</a>
                <a href="/layanan">Layanan</a>
                <a href="/portfolio">Galeri</a>
                <a href="/tentang">Tentang</a>
                <a href="/kontak">Kontak &amp; Lokasi</a>
                <a class="mobile-menu__wa" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin konsultasi perbaikan bodi kendaraan.') }}" target="_blank" rel="noopener">Chat WhatsApp</a>
            </nav>
        </details>
    </div>
</header>