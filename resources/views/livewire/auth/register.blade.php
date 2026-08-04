<div class="min-h-screen flex font-sans">
  <!-- Panel kiri: brand, disembunyikan di mobile -->
  <div class="hidden lg:flex w-1/2 bg-ink text-white flex-col justify-between p-12">
    <span class="font-display font-bold text-xl"><a href="/">ToolBox</a></span>
    <div>
      <p class="font-display text-4xl font-bold mb-3 leading-tight">Mulai lebih cepat.</p>
      <p class="text-slate-400 text-sm">Bergabunglah dengan ribuan pengguna ToolBox lainnya.</p>
    </div>
    <p class="font-mono text-xs text-slate-500">© {{ date('Y') }} ToolBox</p>
  </div>

  <!-- Panel kanan: form -->
  <div class="w-full lg:w-1/2 flex items-center justify-center bg-paper p-8">
    <div class="w-full max-w-sm">
      <h1 class="font-display font-bold text-3xl text-ink mb-1">Daftar Akun</h1>
      <p class="text-ink-muted text-sm mb-8">Buat akun gratis Anda sekarang</p>

      <form wire:submit="register">
        <label for="name" class="text-xs font-mono uppercase text-ink-muted tracking-wide">Nama</label>
        <input type="text" id="name" wire:model="name" class="w-full border border-hairline rounded-sm px-4 py-2.5 text-sm mb-2 mt-1 focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber bg-white" required>
        @error('name') <span class="text-red-500 text-xs block mb-4">{{ $message }}</span> @enderror
        @if(!$errors->has('name')) <div class="mb-4"></div> @endif

        <label for="email" class="text-xs font-mono uppercase text-ink-muted tracking-wide">Email</label>
        <input type="email" id="email" wire:model="email" class="w-full border border-hairline rounded-sm px-4 py-2.5 text-sm mb-2 mt-1 focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber bg-white" required>
        @error('email') <span class="text-red-500 text-xs block mb-4">{{ $message }}</span> @enderror
        @if(!$errors->has('email')) <div class="mb-4"></div> @endif

        <label for="password" class="text-xs font-mono uppercase text-ink-muted tracking-wide">Password</label>
        <input type="password" id="password" wire:model="password" class="w-full border border-hairline rounded-sm px-4 py-2.5 text-sm mb-2 mt-1 focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber bg-white" required>
        @error('password') <span class="text-red-500 text-xs block mb-4">{{ $message }}</span> @enderror
        @if(!$errors->has('password')) <div class="mb-4"></div> @endif

        <label for="password_confirmation" class="text-xs font-mono uppercase text-ink-muted tracking-wide">Konfirmasi Password</label>
        <input type="password" id="password_confirmation" wire:model="password_confirmation" class="w-full border border-hairline rounded-sm px-4 py-2.5 text-sm mb-8 mt-1 focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber bg-white" required>

        <button type="submit" class="w-full bg-amber text-ink font-medium py-3 rounded-sm hover:bg-amber/90 transition-colors shadow-sm">Daftar</button>
      </form>
      
      <p class="text-center text-sm text-ink-muted mt-8">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-steel font-medium hover:text-amber transition-colors">Login</a>
      </p>
    </div>
  </div>
</div>
