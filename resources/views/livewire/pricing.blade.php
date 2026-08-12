<div>
    @section('page_title', 'Pricing - Paket Berlangganan')

    <div class="py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session()->has('error'))
                <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-8" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($snapToken)
                {{-- Snap Container Wrapper --}}
                <div id="snap-container-wrapper" class="mb-12 max-w-7xl mx-auto">
                    <div wire:poll.10s="checkPaymentStatus" class="hidden"></div>

                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-semibold text-slate-800 dark:text-navy-50">
                            Selesaikan Pembayaran
                        </h1>
                        <button wire:click="confirmCancelCheckout" class="btn h-8 rounded-full border border-slate-200 px-3 text-xs+ font-medium text-slate-700 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-500 dark:text-navy-100 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                            Batal
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                        {{-- Kiri: widget Snap Embed --}}
                        <div class="flex flex-col gap-4">
                            <div id="snap-container" wire:ignore class="card overflow-hidden min-h-[500px]"></div>
                            <div id="checkout-error" class="hidden alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5">
                                Pembayaran gagal, silakan coba lagi.
                            </div>
                        </div>

                        {{-- Kanan: ringkasan pesanan --}}
                        <div class="card p-6">
                            <p class="text-xs+ uppercase tracking-wide text-slate-400 dark:text-navy-300 mb-4">
                                Ringkasan Pesanan
                            </p>

                            <div class="mb-6">
                                @php
                                    $pendingSub = auth()->user()->subscriptions()->where('snap_token', $snapToken)->first();
                                    $pendingPlan = $pendingSub ? $pendingSub->plan : null;
                                    $basePrice = $pendingSub ? ($pendingSub->subtotal + $pendingSub->discount) : 0;
                                    $taxPercent = $pendingSub && $pendingSub->subtotal > 0 ? round(($pendingSub->tax / $pendingSub->subtotal) * 100) : 0;
                                    $discountPercent = $basePrice > 0 ? round(($pendingSub->discount / $basePrice) * 100) : 0;
                                @endphp
                                <p class="text-xl font-semibold text-slate-700 dark:text-navy-100 mb-3">{{ $pendingPlan ? $pendingPlan->name : 'Paket' }}</p>
                                
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-500 dark:text-navy-200">Harga Paket</span>
                                        <span class="font-medium text-slate-700 dark:text-navy-100">Rp {{ number_format($basePrice,0,',','.') }}</span>
                                    </div>
                                    @if($pendingSub && $pendingSub->discount > 0)
                                    <div class="flex justify-between items-center text-success">
                                        <span>Diskon ({{ $discountPercent }}%)</span>
                                        <span class="font-medium">-Rp {{ number_format($pendingSub->discount,0,',','.') }}</span>
                                    </div>
                                    @endif
                                    @if($pendingSub && $pendingSub->service_fee > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-500 dark:text-navy-200">Biaya Layanan</span>
                                        <span class="font-medium text-slate-700 dark:text-navy-100">Rp {{ number_format($pendingSub->service_fee,0,',','.') }}</span>
                                    </div>
                                    @endif
                                    @if($pendingSub && $pendingSub->tax > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-500 dark:text-navy-200">Pajak ({{ $taxPercent }}%)</span>
                                        <span class="font-medium text-slate-700 dark:text-navy-100">Rp {{ number_format($pendingSub->tax,0,',','.') }}</span>
                                    </div>
                                    @endif
                                    
                                    <div class="my-4 h-px bg-slate-200 dark:bg-navy-500"></div>
                                    
                                    <div class="flex justify-between items-center font-bold">
                                        <span class="text-slate-700 dark:text-navy-100">Total</span>
                                        <span class="text-xl text-primary dark:text-accent-light">Rp {{ number_format($pendingSub ? $pendingSub->amount : 0,0,',','.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <ul class="space-y-3 text-sm text-slate-600 dark:text-navy-100 mb-6">
                                @if($pendingPlan)
                                    @foreach($pendingPlan->features ?? [] as $feature)
                                        <li class="flex items-start space-x-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            <div class="badge space-x-2 bg-warning/10 text-warning dark:bg-warning/15 w-full justify-center py-2 text-sm">
                                <span>Status:</span>
                                <span class="font-medium">Menunggu Pembayaran</span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($showPhoneForm)
                {{-- Form Lengkapi Nomor HP --}}
                <div class="card max-w-md mx-auto p-6 mb-12 shadow-sm">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-semibold text-slate-700 dark:text-navy-100 mb-2 text-center">Lengkapi Nomor HP</h3>
                    <p class="text-sm text-slate-500 dark:text-navy-300 mb-6 text-center">Untuk melanjutkan pembayaran, silakan masukkan nomor handphone Anda.</p>
                    
                    <form wire:submit="savePhone">
                        <div class="mb-4">
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Nomor HP</span>
                                <div class="flex rounded-lg border border-slate-300 dark:border-navy-450 focus-within:border-primary dark:focus-within:border-accent">
                                    <select wire:model="country_code" class="form-select w-1/3 rounded-l-lg border-0 bg-slate-50 px-3 py-2 hover:bg-slate-100 dark:bg-navy-700 dark:hover:bg-navy-600 focus:ring-0 text-sm">
                                        <option value="+62">🇮🇩 +62</option>
                                        <option value="+1">🇺🇸 +1</option>
                                        <option value="+44">🇬🇧 +44</option>
                                        <option value="+60">🇲🇾 +60</option>
                                        <option value="+65">🇸🇬 +65</option>
                                        <option value="+61">🇦🇺 +61</option>
                                    </select>
                                    <input type="text" wire:model="phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" class="form-input w-2/3 rounded-r-lg border-0 bg-transparent px-3 py-2 placeholder:text-slate-400/70 focus:ring-0 text-sm" placeholder="81234567890">
                                </div>
                            </label>
                            @error('phone')
                                <span class="text-tiny-plus text-error mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" wire:click="$set('showPhoneForm', false)" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">Batal</button>
                            <button type="submit" class="btn space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                <span wire:loading.remove wire:target="savePhone">Lanjutkan</span>
                                <div wire:loading wire:target="savePhone" class="flex items-center space-x-2">
                                    <div class="spinner size-4 animate-spin rounded-full border-[2px] border-current border-r-transparent"></div>
                                    <span>Menyimpan...</span>
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- Header --}}
                <div class="text-center mb-12" id="pricing-header">
                    <h2 class="text-3xl font-bold text-slate-800 dark:text-navy-50 lg:text-4xl">
                        Pilih Paket Anda
                    </h2>
                    <p class="mt-3 text-lg text-slate-500 dark:text-navy-300">
                        Tingkatkan produktivitas tanpa batas dengan {{ config('app.name') }} Pro.
                    </p>
                </div>

                {{-- Pricing Tiers --}}
                @php
                    $currentPlan = app(\App\Services\EntitlementService::class)->getCurrentPlan(auth()->user());
                    $cols = min($plans->count(), 4);
                @endphp
                <div id="pricing-tiers" class="grid grid-cols-1 md:grid-cols-{{ $cols }} gap-6 items-start justify-center max-w-5xl mx-auto">
                    @foreach($plans as $plan)
                        @php
                            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                            $hasPending = auth()->check() && auth()->user()->subscriptions()
                                            ->where('status', 'pending')
                                            ->where('plan_id', $plan->id)
                                            ->where('created_at', '>', now()->subHours(24))
                                            ->exists();
                        @endphp
                        <div class="card flex flex-col h-full {{ $isCurrent ? 'border-2 border-primary dark:border-accent shadow-lg shadow-primary/20' : '' }} relative">
                            @if($isCurrent)
                                <div class="absolute top-0 right-0">
                                    <div class="badge rounded-none rounded-tr-lg rounded-bl-lg bg-primary text-white dark:bg-accent px-3 py-1 font-semibold uppercase tracking-wider text-xs">
                                        AKTIF
                                    </div>
                                </div>
                            @endif
                            
                            <div class="px-6 py-8 border-b border-slate-200 dark:border-navy-500 text-center">
                                @php
                                    $breakdown = app(\App\Services\PriceCalculator::class)->breakdown($plan);
                                @endphp
                                <h3 class="font-bold text-xl uppercase tracking-wider mb-2 {{ $isCurrent ? 'text-primary dark:text-accent-light' : 'text-slate-700 dark:text-navy-100' }}">{{ $plan->name }}</h3>
                                <p class="text-sm text-slate-500 dark:text-navy-300 mb-6 h-10">{{ $plan->description }}</p>
                                
                                <div class="flex flex-col mb-2 items-center justify-center">
                                    @if($breakdown['discount'] > 0)
                                        <span class="text-slate-400 dark:text-navy-300 line-through text-sm">Rp {{ number_format($breakdown['basePrice'],0,',','.') }}</span>
                                    @else
                                        <span class="h-5"></span> {{-- Placeholder for alignment --}}
                                    @endif
                                    
                                    <div class="flex items-baseline text-slate-800 dark:text-navy-50 mt-1">
                                        <span class="text-4xl font-bold tracking-tight">Rp {{ number_format($breakdown['subtotal'], 0, ',', '.') }}</span>
                                        <span class="ml-1 text-sm text-slate-500 dark:text-navy-300 font-medium">/ {{ $plan->duration_days ? $plan->duration_days . ' hr' : 'selamanya' }}</span>
                                    </div>
                                </div>
                                
                                @if($breakdown['tax'] > 0 || $breakdown['serviceFee'] > 0)
                                    <div class="text-[10px] text-slate-400 dark:text-navy-300 mt-1">* Belum termasuk pajak & biaya layanan</div>
                                @else
                                    <div class="text-[10px] text-transparent mt-1">-</div> {{-- Placeholder --}}
                                @endif
                            </div>
                            
                            <div class="px-6 py-8 flex-1 bg-slate-50 dark:bg-navy-800">
                                <ul class="space-y-4 text-sm text-slate-600 dark:text-navy-100">
                                    @foreach($plan->features ?? [] as $feature)
                                        <li class="flex items-start space-x-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 {{ $isCurrent ? 'text-primary dark:text-accent-light' : 'text-success' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            
                            <div class="p-6 border-t border-slate-200 dark:border-navy-500 mt-auto">
                                @if($isCurrent)
                                    <a href="{{ route('dashboard.billing') }}" class="btn w-full border border-slate-300 font-medium text-slate-500 cursor-not-allowed dark:border-navy-450 dark:text-navy-300">
                                        Paket Aktif
                                    </a>
                                @else
                                    <button wire:click="selectPlan({{ $plan->id }})" wire:loading.attr="disabled"
                                        class="btn w-full font-medium transition-colors relative {{ $plan->price > 0 ? 'bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' : 'border border-slate-300 text-slate-700 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90' }}">
                                        <span wire:loading.remove wire:target="selectPlan({{ $plan->id }})">
                                            {{ $hasPending ? 'Selesaikan Pembayaran' : ($plan->price > 0 ? 'Pilih Paket' : 'Lanjutkan') }}
                                        </span>
                                        <div wire:loading wire:target="selectPlan({{ $plan->id }})" class="flex items-center justify-center space-x-2">
                                            <div class="spinner size-4 animate-spin rounded-full border-[2px] border-current border-r-transparent"></div>
                                            <span>Memproses...</span>
                                        </div>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Midtrans Snap Script --}}
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
</div>
