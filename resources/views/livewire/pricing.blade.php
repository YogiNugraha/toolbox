<div class="py-12 bg-paper min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session()->has('error'))
            <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-sm text-sm" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($snapToken)
            <!-- Snap Container Wrapper -->
            <div id="snap-container-wrapper" class="mb-12 max-w-7xl mx-auto">
                <div wire:poll.10s="checkPaymentStatus" class="hidden"></div>

                <div class="flex justify-between items-center mb-6">
                    <h1 class="font-display font-bold text-2xl text-ink">
                        Selesaikan Pembayaran
                    </h1>
                    <button wire:click="confirmCancelCheckout"
                        class="font-mono text-xs uppercase text-ink-muted underline hover:text-ink">
                        Batal
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- Kiri: widget Snap Embed -->
                    <div class="flex flex-col gap-4">
                        <div id="snap-container" wire:ignore
                            class="border border-hairline rounded-sm bg-white overflow-hidden min-h-125"></div>
                        <div id="checkout-error"
                            class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-sm text-sm">
                            Pembayaran gagal, silakan coba lagi.</div>
                    </div>

                    <!-- Kanan: ringkasan pesanan -->
                    <div class="border border-hairline rounded-sm bg-white p-6">
                        <p class="font-mono text-xs uppercase tracking-widest text-ink-muted mb-4">
                            Ringkasan Pesanan
                        </p>

                        <div class="mb-6">
                            @php
                                $pendingSub = auth()->user()->subscriptions()->where('snap_token', $snapToken)->first();
                                $pendingPlan = $pendingSub ? $pendingSub->plan : null;
                                $basePrice = $pendingSub ? ($pendingSub->subtotal + $pendingSub->discount) : 0;
                            @endphp
                            <p class="font-display font-bold text-lg text-ink mb-3">{{ $pendingPlan ? $pendingPlan->name : 'Paket' }}</p>
                            
                            <div class="space-y-1.5 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-ink-muted">Harga Paket</span>
                                    <span class="font-mono">Rp {{ number_format($basePrice,0,',','.') }}</span>
                                </div>
                                @if($pendingSub && $pendingSub->discount > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Diskon</span>
                                    <span class="font-mono">-Rp {{ number_format($pendingSub->discount,0,',','.') }}</span>
                                </div>
                                @endif
                                @if($pendingSub && $pendingSub->service_fee > 0)
                                <div class="flex justify-between">
                                    <span class="text-ink-muted">Biaya Layanan</span>
                                    <span class="font-mono">Rp {{ number_format($pendingSub->service_fee,0,',','.') }}</span>
                                </div>
                                @endif
                                @if($pendingSub && $pendingSub->tax > 0)
                                <div class="flex justify-between">
                                    <span class="text-ink-muted">Pajak</span>
                                    <span class="font-mono">Rp {{ number_format($pendingSub->tax,0,',','.') }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between font-bold border-t border-hairline pt-2 mt-2">
                                    <span>Total</span>
                                    <span class="font-mono text-lg">Rp {{ number_format($pendingSub ? $pendingSub->amount : 0,0,',','.') }}</span>
                                </div>
                            </div>
                        </div>

                        <ul class="space-y-2 text-sm text-ink-muted mb-6">
                            @if($pendingPlan)
                                @foreach($pendingPlan->features ?? [] as $feature)
                                    <li class="flex items-start gap-2">
                                        <span class="text-amber mt-0.5">✓</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach

                            @endif
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
                <p class="text-sm text-ink-muted mb-6">Untuk melanjutkan pembayaran, silakan masukkan nomor handphone
                    Anda.</p>
                <form wire:submit="savePhone">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-ink mb-1">Nomor HP</label>
                        <div class="flex shadow-sm rounded-sm">
                            <select wire:model="country_code"
                                class="w-1/3 md:w-1/4 rounded-l-sm border-hairline focus:border-amber focus:ring-amber/20 text-sm bg-paper/50">
                                <option value="+62">🇮🇩 +62</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+60">🇲🇾 +60</option>
                                <option value="+65">🇸🇬 +65</option>
                                <option value="+61">🇦🇺 +61</option>
                            </select>
                            <input type="text" wire:model="phone"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')"
                                class="w-2/3 md:w-3/4 rounded-r-sm border-l-0 border-hairline focus:border-amber focus:ring-amber/20 text-sm"
                                placeholder="81234567890">
                        </div>
                        @error('phone')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showPhoneForm', false)"
                            class="px-4 py-2 text-sm font-medium text-ink-muted hover:text-ink">Batal</button>
                        <button type="submit"
                            class="bg-amber hover:bg-amber/90 text-ink font-medium py-2 px-6 rounded-sm transition-colors text-sm flex items-center gap-2">
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
                    Tingkatkan produktivitas tanpa batas dengan {{ config('app.name') }} Pro.
                </p>
            </div>

            <!-- Pricing Tiers -->
            @php
                $currentPlan = app(\App\Services\EntitlementService::class)->getCurrentPlan(auth()->user());
                $cols = min($plans->count(), 4);
            @endphp
            <div id="pricing-tiers" class="grid grid-cols-1 md:grid-cols-{{ $cols }} gap-6 items-start">
                @foreach($plans as $plan)
                    @php
                        $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                        $hasPending = auth()->check() && auth()->user()->subscriptions()
                                        ->where('status', 'pending')
                                        ->where('plan_id', $plan->id)
                                        ->where('created_at', '>', now()->subHours(24))
                                        ->exists();
                    @endphp
                    <div class="border {{ $isCurrent ? 'border-amber shadow-sm' : 'border-hairline' }} rounded-sm bg-white overflow-hidden flex flex-col h-full relative">
                        @if($isCurrent)
                        <div class="absolute top-0 right-0 bg-amber text-ink text-xs font-bold px-3 py-1 border-b border-l border-amber">
                            AKTIF
                        </div>
                        @endif
                        
                        <div class="px-6 py-8 border-b border-hairline">
                            @php
                                $breakdown = app(\App\Services\PriceCalculator::class)->breakdown($plan);
                            @endphp
                            <h3 class="font-bold text-lg {{ $isCurrent ? 'text-amber' : 'text-ink' }} uppercase tracking-wider mb-4">{{ $plan->name }}</h3>
                            <div class="flex flex-col mb-4">
                                @if($breakdown['discount'] > 0)
                                    <span class="text-ink-muted line-through text-sm font-mono text-left">Rp {{ number_format($breakdown['basePrice'],0,',','.') }}</span>
                                @endif
                                <div class="flex items-baseline text-ink font-mono tracking-tighter">
                                    <span class="text-5xl font-bold">Rp {{ number_format($breakdown['subtotal'], 0, ',', '.') }}</span>
                                    <span class="ml-2 text-sm text-ink-muted font-sans font-medium">/ {{ $plan->duration_days ? $plan->duration_days . ' hr' : 'selamanya' }}</span>
                                </div>
                                @if($breakdown['tax'] > 0 || $breakdown['serviceFee'] > 0)
                                    <span class="text-[10px] text-ink-muted font-sans mt-1">* Belum termasuk pajak & biaya layanan</span>
                                @endif
                            </div>
                            <p class="mt-4 text-sm text-ink-muted">{{ $plan->description }}</p>
                        </div>
                        
                        <div class="px-6 py-6 bg-paper/30 flex-1">
                            <ul class="space-y-4 text-sm text-ink-muted">
                                @foreach($plan->features ?? [] as $feature)
                                    <li class="flex items-start">
                                        <span class="{{ $isCurrent ? 'text-amber font-bold' : 'text-green-500' }} mr-3 mt-0.5">✓</span> 
                                        <span class="text-ink">{{ $feature }}</span>
                                    </li>
                                @endforeach

                            </ul>
                        </div>
                        
                        <div class="p-6 bg-white border-t border-hairline">
                            @if($isCurrent)
                                <a href="{{ route('dashboard.billing') }}" class="block w-full text-center border border-hairline bg-paper text-ink-muted rounded-sm px-6 py-3 text-sm font-bold cursor-not-allowed">
                                    Paket Aktif
                                </a>
                            @else
                                <button wire:click="selectPlan({{ $plan->id }})" wire:loading.attr="disabled"
                                    class="w-full text-center {{ $plan->price > 0 ? 'bg-amber text-ink hover:bg-amber/90 shadow-sm' : 'border border-hairline text-ink hover:border-amber' }} rounded-sm px-6 py-3 text-sm font-bold transition-colors relative">
                                    <span wire:loading.remove wire:target="selectPlan({{ $plan->id }})">
                                        {{ $hasPending ? 'Selesaikan Pembayaran' : ($plan->price > 0 ? 'Pilih Paket' : 'Lanjutkan') }}
                                    </span>
                                    <span wire:loading wire:target="selectPlan({{ $plan->id }})">Memproses...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Midtrans Snap Script -->
    @assets
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endassets

    @script
    <script>
        const initSnap = (token) => {
            const container = document.getElementById('snap-container');
            if (container) {
                snap.embed(token, {
                    embedId: 'snap-container',
                    onSuccess: function(result) {
                        $wire.handlePaymentStatus('success');
                    },
                    onPending: function(result) {
                        $wire.handlePaymentStatus('pending');
                    },
                    onError: function(result) {
                        document.getElementById("checkout-error").classList.remove("hidden");
                    },
                    onClose: function() {
                        window.location.href = "{{ route('dashboard.billing') }}";
                    }
                });
            }
        };

        // If token is already present on load
        if ($wire.snapToken) {
            setTimeout(() => {
                initSnap($wire.snapToken);
            }, 100);
        }

        // Listen for new token requests
        $wire.on('snap-token-ready', (event) => {
            let token = event.token || (event[0] && event[0].token);
            setTimeout(() => {
                initSnap(token);
            }, 100);
        });
    </script>
    @endscript
</div>
