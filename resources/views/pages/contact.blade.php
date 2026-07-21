<x-layouts.app title="Kontak">
    <x-page-header title="Hubungi Pemerintah Desa" description="Sampaikan pertanyaan, masukan, atau kebutuhan informasi Anda." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="contact-layout">
                <aside class="contact-details">
                    <span class="section-kicker">Informasi Kontak</span>
                    <h2>Kantor Desa Sukomulyo</h2>
                    <p>Silakan datang pada jam pelayanan atau kirim pesan melalui formulir yang tersedia.</p>
                    <ul>
                        <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span><strong>Alamat</strong>{{ $site['address'] }}</span></li>
                        <li><i class="fas fa-phone" aria-hidden="true"></i><span><strong>Telepon</strong>{{ $site['phone'] }}</span></li>
                        <li><i class="fas fa-envelope" aria-hidden="true"></i><span><strong>Email</strong>{{ $site['email'] }}</span></li>
                        <li><i class="fas fa-clock" aria-hidden="true"></i><span><strong>Jam Pelayanan</strong>Senin–Jumat, 08.00–15.00</span></li>
                    </ul>
                </aside>

                <div class="contact-form-card">
                    <h2>Kirim Pesan</h2>

                    @if (session('success'))
                        <div class="alert alert-success" role="status">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-error" role="alert">
                            <strong>Periksa kembali data berikut:</strong>
                            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form class="contact-form" action="{{ route('kontak.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <label>Nama Lengkap<input type="text" name="name" value="{{ old('name') }}" maxlength="100" required></label>
                            <label>Email<input type="email" name="email" value="{{ old('email') }}" maxlength="150" required></label>
                        </div>
                        <label>Nomor Telepon <span>(opsional)</span><input type="tel" name="phone" value="{{ old('phone') }}" maxlength="25"></label>
                        <label>Pesan<textarea name="message" rows="6" maxlength="2000" required data-message-input>{{ old('message') }}</textarea><small><span data-message-count>0</span>/2000 karakter</small></label>
                        <button class="form-submit" type="submit">Kirim Pesan <i class="fas fa-paper-plane" aria-hidden="true"></i></button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
