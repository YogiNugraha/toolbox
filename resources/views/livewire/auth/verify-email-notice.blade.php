<div class="min-h-screen flex font-sans" wire:poll="checkVerification">
  <!-- Panel kiri: brand, disembunyikan di mobile -->
  <div class="hidden lg:flex w-1/2 bg-ink text-white flex-col justify-between p-12">
    <span class="font-display font-bold text-xl"><a href="/">{{ config('app.name') }}</a></span>
    <div>
      <p class="font-display text-4xl font-bold mb-3 leading-tight">Keamanan Akun.</p>
      <p class="text-slate-400 text-sm">Verifikasi identitas Anda untuk melanjutkan.</p>
    </div>
    <p class="font-mono text-xs text-slate-500">© {{ date('Y') }} {{ config('app.name') }}</p>
  </div>

  <!-- Panel kanan: form -->
  <div class="w-full lg:w-1/2 flex items-center justify-center bg-paper p-8">
    <div class="w-full max-w-sm">
      <h1 class="font-display font-bold text-3xl text-ink mb-1">Verifikasi Email</h1>
      <p class="text-ink-muted text-sm mb-8">
        Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda (<strong>{{ auth()->user()->email }}</strong>).
      </p>

      <div class="mb-4 text-sm text-ink-muted">
        Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan yang baru.
      </div>

      <div class="flex flex-col space-y-4" x-data="{ cooldown: 0 }" x-on:cooldown-start.window="cooldown = $event.detail.seconds; let t = setInterval(() => { cooldown--; if (cooldown <= 0) clearInterval(t); }, 1000);">
          <button wire:click="resend" type="button" :disabled="cooldown > 0" class="w-full bg-amber text-ink font-medium py-3 rounded-sm hover:bg-amber/90 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="resend" x-show="cooldown === 0">Kirim Ulang Email Verifikasi</span>
            <span wire:loading wire:target="resend">Mengirim...</span>
            <span wire:loading.remove wire:target="resend" x-show="cooldown > 0" x-text="'Terkirim — Kirim Ulang (' + cooldown + 's)'" style="display: none;"></span>
          </button>
          
          <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full bg-transparent border border-hairline text-ink font-medium py-3 rounded-sm hover:bg-slate-50 transition-colors shadow-sm">
                Log Out
            </button>
          </form>
      </div>
    </div>
  </div>
</div>
