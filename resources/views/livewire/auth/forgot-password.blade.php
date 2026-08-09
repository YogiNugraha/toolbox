<div class="min-h-screen flex font-sans">
  <!-- Panel kiri: brand, disembunyikan di mobile -->
  <div class="hidden lg:flex w-1/2 bg-ink text-white flex-col justify-between p-12">
    <span class="font-display font-bold text-xl"><a href="/">{{ config('app.name') }}</a></span>
    <div>
      <p class="font-display text-4xl font-bold mb-3 leading-tight">Lupa Password?</p>
      <p class="text-slate-400 text-sm">Jangan khawatir, kami akan membantu memulihkan akses Anda.</p>
    </div>
    <p class="font-mono text-xs text-slate-500">© {{ date('Y') }} {{ config('app.name') }}</p>
  </div>

  <!-- Panel kanan: form -->
  <div class="w-full lg:w-1/2 flex items-center justify-center bg-paper p-8">
    <div class="w-full max-w-sm">
      <h1 class="font-display font-bold text-3xl text-ink mb-1">Reset Password</h1>
      <p class="text-ink-muted text-sm mb-8">Masukkan email yang terdaftar untuk menerima link reset password.</p>

      <form wire:submit="sendResetLink" x-data="{ cooldown: 0 }" x-on:cooldown-start.window="cooldown = $event.detail.seconds; let t = setInterval(() => { cooldown--; if (cooldown <= 0) clearInterval(t); }, 1000);">
        <label for="email" class="text-xs font-mono uppercase text-ink-muted tracking-wide">Email</label>
        <input type="email" id="email" wire:model="email" class="w-full border border-hairline rounded-sm px-4 py-2.5 text-sm mb-2 mt-1 focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber bg-white" required>
        @error('email') <span class="text-red-500 text-xs block mb-4">{{ $message }}</span> @enderror
        @if(!$errors->has('email')) <div class="mb-4"></div> @endif

        <button type="submit" 
                x-bind:disabled="cooldown > 0"
                class="w-full bg-amber text-ink font-medium py-3 rounded-sm hover:bg-amber/90 transition-colors shadow-sm mb-4 disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="sendResetLink" x-show="cooldown === 0">Kirim Link Reset</span>
            <span wire:loading wire:target="sendResetLink">Mengirim...</span>
            <span wire:loading.remove wire:target="sendResetLink" x-show="cooldown > 0" x-text="'Terkirim — Kirim Ulang (' + cooldown + 's)'" style="display: none;"></span>
        </button>
      </form>
      
      <p class="text-center text-sm text-ink-muted mt-8">
        Ingat password Anda? <a href="{{ route('login') }}" class="text-steel font-medium hover:text-amber transition-colors">Kembali ke Login</a>
      </p>
    </div>
  </div>
</div>
