<div class="py-12 bg-paper min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if (session()->has('error'))
            <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-sm text-sm" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($snapToken)
            <!-- Snap Container Wrapper -->
            <div id="snap-container-wrapper" class="mb-12 max-w-5xl mx-auto">
                <div wire:poll.3s="checkPaymentStatus" class="hidden"></div>
                
                <div class="flex justify-between items-center mb-6">
                    <h1 class="font-display font-bold text-2xl text-ink">
                        Selesaikan Pembayaran
                    </h1>
                    <button wire:click="cancelPending" class="font-mono text-xs uppercase text-ink-muted underline hover:text-ink">
                        Batal
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- Kiri: widget Snap Embed -->
                    <div class="flex flex-col gap-4">
                        <div id="snap-container" wire:ignore class="border border-hairline rounded-sm bg-white overflow-hidden min-h-[500px]"></div>
                        <div id="checkout-error" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-sm text-sm">Pembayaran gagal, silakan coba lagi.</div>
                    </div>

                    <!-- Kanan: ringkasan pesanan -->
                    <div class="border border-hairline rounded-sm bg-white p-6">
                        <p class="font-mono text-xs uppercase tracking-widest text-ink-muted mb-4">
                            Ringkasan Pesanan
                        </p>

                        <div class="flex justify-between items-center pb-4 border-b border-hairline mb-4">
                            <span class="font-display font-bold text-lg text-ink">Paket Pro</span>
                            <span class="font-mono font-bold text-2xl text-ink">Rp {{ number_format(config('plans.pro.price'), 0, ',', '.') }}</span>
                        </div>

                        <ul class="space-y-2 text-sm text-ink-muted mb-6">
                            <li class="flex gap-2">
                                <span class="text-amber">✓</span> Semua tools unlimited
                            </li>
                            <li class="flex gap-2">
                                <span class="text-amber">✓</span> Semua preset & fitur terbuka
                            </li>
                            <li class="flex gap-2">
                                <span class="text-amber">✓</span> Ukuran file maksimal lebih besar
                            </li>
                            <li class="flex gap-2">
                                <span class="text-amber">✓</span> Aktif {{ config('plans.pro.duration_days') }} hari
                            </li>
                        </ul>

                        <p class="font-mono text-xs text-ink-muted">
                            Status: <span class="text-ink">Menunggu Pembayaran</span>
                        </p>
                    </div>
                </div>
            </div>
        @elseif($showPhoneForm)
            <!-- Form Lengkapi Nomor HP -->
            <div class="max-w-md mx-auto bg-white border border-hairline rounded-sm p-6 mb-12 shadow-sm text-left">
                <h3 class="text-xl font-display font-bold text-ink mb-2">Lengkapi Nomor HP</h3>
                <p class="text-sm text-ink-muted mb-6">Untuk melanjutkan pembayaran, silakan masukkan nomor handphone Anda.</p>
                <form wire:submit="savePhone">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-ink mb-1">Nomor HP</label>
                        <div class="flex shadow-sm rounded-sm">
                            <select wire:model="country_code" class="w-1/3 md:w-1/4 rounded-l-sm border-hairline focus:border-amber focus:ring-amber/20 text-sm bg-paper/50">
                                <option value="+62">🇮🇩 +62</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+60">🇲🇾 +60</option>
                                <option value="+65">🇸🇬 +65</option>
                                <option value="+61">🇦🇺 +61</option>
                            </select>
                            <input type="text" wire:model="phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" class="w-2/3 md:w-3/4 rounded-r-sm border-l-0 border-hairline focus:border-amber focus:ring-amber/20 text-sm" placeholder="81234567890">
                        </div>
                        @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showPhoneForm', false)" class="px-4 py-2 text-sm font-medium text-ink-muted hover:text-ink">Batal</button>
                        <button type="submit" class="bg-amber hover:bg-amber/90 text-ink font-medium py-2 px-6 rounded-sm transition-colors text-sm flex items-center gap-2">
                            <span wire:loading.remove wire:target="savePhone">Lanjutkan</span>
                            <span wire:loading wire:target="savePhone">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- Header -->
            <div class="text-center mb-12" id="pricing-header">
                <h2 class="text-4xl font-display font-extrabold text-ink">
                    Pilih Paket Anda
                </h2>
                <p class="mt-3 text-lg text-ink-muted">
                    Tingkatkan produktivitas tanpa batas dengan ToolBox Pro.
                </p>
            </div>
            
            <!-- Pricing Tiers -->
            <div id="pricing-tiers" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                
                <!-- Free Plan -->
                <div class="border border-hairline rounded-sm bg-white overflow-hidden flex flex-col h-full">
                    <div class="px-6 py-8 border-b border-hairline">
                        <h3 class="font-bold text-lg text-ink uppercase tracking-wider mb-4">Free</h3>
                        <div class="flex items-baseline text-ink font-mono tracking-tighter">
                            <span class="text-5xl font-bold">Rp 0</span>
                            <span class="ml-2 text-sm text-ink-muted font-sans font-medium">/ selamanya</span>
                        </div>
                        <p class="mt-4 text-sm text-ink-muted">Cocok untuk penggunaan kasual.</p>
                    </div>
                    <div class="px-6 py-6 bg-paper/30 flex-1">
                        <ul class="space-y-4 text-sm text-ink-muted">
                            <li class="flex items-start">
                                <span class="text-green-500 mr-3">✓</span> Compress Gambar: 5x/hari (Basic)
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-3">✓</span> Convert Gambar: 5x/hari
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-3">✓</span> PDF ke Word: 2x/hari (Max 5MB)
                            </li>
                        </ul>
                    </div>
                    <div class="p-6 bg-white border-t border-hairline">
                        <a href="{{ route('dashboard') }}" class="block w-full text-center border border-hairline rounded-sm px-6 py-3 text-sm font-bold text-ink hover:border-amber transition-colors">
                            Lanjutkan Free
                        </a>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="border border-amber rounded-sm bg-white overflow-hidden flex flex-col h-full relative shadow-sm">
                    <div class="absolute top-0 right-0 bg-amber text-ink text-xs font-bold px-3 py-1 border-b border-l border-amber">
                        PRO
                    </div>
                    <div class="px-6 py-8 border-b border-hairline">
                        <h3 class="font-bold text-lg text-amber uppercase tracking-wider mb-4">Pro</h3>
                        <div class="flex items-baseline text-ink font-mono tracking-tighter">
                            <span class="text-5xl font-bold">Rp {{ number_format(config('plans.pro.price'), 0, ',', '.') }}</span>
                            <span class="ml-2 text-sm text-ink-muted font-sans font-medium">/ {{ config('plans.pro.duration_days') }} hr</span>
                        </div>
                        <p class="mt-4 text-sm text-ink-muted">Buka semua fitur tanpa batasan apapun.</p>
                    </div>
                    <div class="px-6 py-6 bg-paper/30 flex-1">
                        <ul class="space-y-4 text-sm text-ink-muted">
                            <li class="flex items-start">
                                <span class="text-amber mr-3 font-bold">✓</span> <span class="font-medium text-ink">Unlimited</span> Compress Gambar
                            </li>
                            <li class="flex items-start">
                                <span class="text-amber mr-3 font-bold">✓</span> <span class="font-medium text-ink">Unlimited</span> Convert Gambar
                            </li>
                            <li class="flex items-start">
                                <span class="text-amber mr-3 font-bold">✓</span> <span class="font-medium text-ink">Unlimited</span> PDF ke Word
                            </li>
                        </ul>
                    </div>
                    <div class="p-6 bg-white border-t border-hairline">
                        @php
                            $hasPending = auth()->check() && auth()->user()->subscriptions()->where('status', 'pending')->where('created_at', '>', now()->subHours(24))->exists();
                        @endphp
                        @if(auth()->check() && auth()->user()->activeSubscription())
                            <a href="{{ route('dashboard.billing') }}" class="block w-full text-center border border-hairline bg-paper text-ink-muted rounded-sm px-6 py-3 text-sm font-bold cursor-not-allowed">
                                Paket Pro Aktif
                            </a>
                        @else
                            <button wire:click="checkout" wire:loading.attr="disabled" class="w-full text-center bg-amber text-ink rounded-sm px-6 py-3 text-sm font-bold hover:bg-amber/90 transition-colors shadow-sm relative">
                                <span wire:loading.remove wire:target="checkout">
                                    {{ $hasPending ? 'Selesaikan Pembayaran' : 'Upgrade ke Pro' }}
                                </span>
                                <span wire:loading wire:target="checkout">Memproses...</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Midtrans Snap Script -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const initSnap = (token) => {
                const container = document.getElementById('snap-container');
                if (container) {
                    snap.embed(token, {
                        embedId: 'snap-container',
                        onSuccess: function (result) {
                            @this.call('handlePaymentStatus', 'success');
                        },
                        onPending: function (result) {
                            @this.call('handlePaymentStatus', 'pending');
                        },
                        onError: function (result) {
                            document.getElementById("checkout-error").classList.remove("hidden");
                        },
                    });
                }
            };

            // If token is already present on load (e.g. from ?action=checkout or pending)
            @if($snapToken)
                setTimeout(() => {
                    initSnap('{{ $snapToken }}');
                }, 100);
            @endif

            // Listen for new token requests (e.g. clicking Upgrade manually)
            window.addEventListener('snap-token-ready', event => {
                setTimeout(() => {
                    initSnap(event.detail.token);
                }, 100); // Give DOM a little time to render the snap-container
            });
        });
    </script>
</div>
