@php($footerTags = ['Cat Mobil & Motor', 'Kenteng / Las Bodi', 'Garansi Rapi'])

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <h4>{{ $settings['business_name'] ?? 'Bengkel Rudi' }}</h4>
            <p>{{ $settings['about_content'] ?? 'Spesialis perbaikan body repair mobil dan motor, rekondisi bodi penyok, serta pengecatan sebagian maupun seluruh bodi dengan hasil rapi.' }}</p>

            <div class="footer-tags">
                @foreach($footerTags as $tag)
                    <span class="footer-tag">{{ $tag }}</span>
                @endforeach
            </div>
        </div>

        <div class="footer-col">
            <h4>Workshop &amp; Jam Buka</h4>
            <p>📍 {{ $settings['address'] ?? 'RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254' }}</p>
            <p class="footer-hours">🕒 {{ $settings['opening_hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB' }}</p>
        </div>

        <div class="footer-col">
            <h4>Hubungi &amp; Konsultasi</h4>
            <p>Kirim foto kondisi bodi mobil Anda via WhatsApp untuk perkiraan harga dan waktu pengerjaan cepat.</p>

            <p class="footer-cta">
                <a
                    href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin tanya estimasi.') }}"
                    class="btn btn-wa footer-wa"
                >Hubungi WhatsApp</a>
            </p>

            @if(!empty($settings['instagram_url']))
                <p class="footer-social">
                    <a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener">Ikuti Instagram kami &rarr;</a>
                </p>
            @endif
        </div>
    </div>

    <div class="footer-bottom">
        <small>&copy; {{ date('Y') }} {{ $settings['business_name'] ?? 'Bengkel Rudi' }}. Solusi Cat &amp; Body Repair Kendaraan di Kabupaten Batang.</small>
    </div>
</footer>