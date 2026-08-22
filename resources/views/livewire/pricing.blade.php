<div>
    @section('title', 'Price List 1 - ' . config('app.name'))
    @section('page_title', 'Price List')
    @section('page_breadcrumb', 'Price List')

    <div class="space-y-8">
        {{-- Flash Alerts --}}
        @if (session()->has('error'))
            <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($snapToken)
            {{-- Snap Container Checkout Wrapper --}}
            <div id="snap-container-wrapper" class="max-w-5xl mx-auto">
                <div wire:poll.10s="checkPaymentStatus" class="hidden"></div>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl">
                            Selesaikan Pembayaran
                        </h2>
                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-1">Pilih metode pembayaran aman via Midtrans</p>
                    </div>
                    <button wire:click="confirmCancelCheckout" class="btn h-8 rounded-full border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                        Batal
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    {{-- Kiri: widget Snap Embed --}}
                    <div class="flex flex-col gap-4">
                        <div id="snap-container" wire:ignore class="card overflow-hidden min-h-[520px] border border-slate-200/80 dark:border-navy-600"></div>
                        <div id="checkout-error" class="hidden alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5">
                            Pembayaran gagal atau kedaluwarsa, silakan coba lagi.
                        </div>
                    </div>

                    {{-- Kanan: ringkasan pesanan --}}
                    <div class="card p-6 border border-slate-200/80 dark:border-navy-600">
                        <p class="text-xs uppercase tracking-wider font-bold text-slate-400 dark:text-navy-300 mb-4">
                            Ringkasan Tagihan
                        </p>

                        <div class="mb-6">
                            @php
                                $pendingSub = auth()->user()->subscriptions()->where('snap_token', $snapToken)->first();
                                $pendingPlan = $pendingSub ? $pendingSub->plan : null;
                                $basePrice = $pendingSub ? ($pendingSub->subtotal + $pendingSub->discount) : 0;
                                $taxPercent = $pendingSub && $pendingSub->subtotal > 0 ? round(($pendingSub->tax / $pendingSub->subtotal) * 100) : 0;
                                $discountPercent = $basePrice > 0 ? round(($pendingSub->discount / $basePrice) * 100) : 0;
                            @endphp
                            
                            <div class="flex items-center space-x-3 mb-4 pb-4 border-b border-slate-150 dark:border-navy-500">
                                <div class="mask is-squircle flex size-12 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 dark:text-navy-100">
                                        {{ $pendingPlan ? $pendingPlan->name : 'Paket Langganan' }}
                                    </h3>
                                    <p class="text-xs text-slate-400 dark:text-navy-300">
                                        Order #{{ $pendingSub ? $pendingSub->midtrans_order_id : '-' }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500 dark:text-navy-200">Harga Paket</span>
                                    <span class="font-semibold text-slate-700 dark:text-navy-100">Rp {{ number_format($basePrice,0,',','.') }}</span>
                                </div>
                                @if($pendingSub && $pendingSub->discount > 0)
                                <div class="flex justify-between items-center text-success">
                                    <span>Diskon ({{ $discountPercent }}%)</span>
                                    <span class="font-semibold">-Rp {{ number_format($pendingSub->discount,0,',','.') }}</span>
                                </div>
                                @endif
                                @if($pendingSub && $pendingSub->service_fee > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500 dark:text-navy-200">Biaya Layanan</span>
                                    <span class="font-semibold text-slate-700 dark:text-navy-100">Rp {{ number_format($pendingSub->service_fee,0,',','.') }}</span>
                                </div>
                                @endif
                                @if($pendingSub && $pendingSub->tax > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500 dark:text-navy-200">Pajak ({{ $taxPercent }}%)</span>
                                    <span class="font-semibold text-slate-700 dark:text-navy-100">Rp {{ number_format($pendingSub->tax,0,',','.') }}</span>
                                </div>
                                @endif
                                
                                <div class="my-3 h-px bg-slate-200 dark:bg-navy-500"></div>
                                
                                <div class="flex justify-between items-center text-sm font-bold">
                                    <span class="text-slate-800 dark:text-navy-100">Total Pembayaran</span>
                                    <span class="text-lg text-primary dark:text-accent-light">Rp {{ number_format($pendingSub ? $pendingSub->amount : 0,0,',','.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg bg-slate-50 p-3.5 dark:bg-navy-600 mb-6">
                            <p class="text-xs font-semibold text-slate-700 dark:text-navy-100 mb-2">Fitur yang akan diaktifkan:</p>
                            <ul class="space-y-2 text-xs text-slate-600 dark:text-navy-200">
                                @if($pendingPlan)
                                    @foreach($pendingPlan->features ?? [] as $feature)
                                        <li class="flex items-start space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <div class="badge rounded-lg bg-warning/10 text-warning dark:bg-warning/15 w-full justify-center py-2.5 text-xs font-semibold">
                            <span class="size-2 rounded-full bg-warning animate-ping mr-2"></span>
                            <span>Menunggu Penyelesaian Pembayaran</span>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($showPhoneForm)
            {{-- Form Lengkapi Nomor HP --}}
            <div class="card max-w-md mx-auto p-6 sm:p-8 border border-slate-200/80 dark:border-navy-600 shadow-md">
                <div class="mask is-squircle mx-auto flex size-16 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                
                <h3 class="text-xl font-bold text-slate-800 dark:text-navy-100 mb-1 text-center">Lengkapi Nomor HP</h3>
                <p class="text-xs text-slate-500 dark:text-navy-300 mb-6 text-center">Untuk verifikasi gateway pembayaran Midtrans, silakan masukkan nomor handphone aktif Anda.</p>
                
                <form wire:submit="savePhone" class="space-y-4">
                    <div>
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1.5 block">Nomor WhatsApp / HP</span>
                            <div class="flex rounded-lg border border-slate-300 dark:border-navy-450 focus-within:border-primary dark:focus-within:border-accent overflow-hidden">
                                <select wire:model="country_code" class="form-select w-1/3 border-0 bg-slate-100 px-3 py-2 text-xs font-semibold dark:bg-navy-700 text-slate-700 dark:text-navy-100 focus:ring-0">
                                    <option value="+62">🇮🇩 +62</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+60">🇲🇾 +60</option>
                                    <option value="+65">🇸🇬 +65</option>
                                    <option value="+61">🇦🇺 +61</option>
                                </select>
                                <input type="text" wire:model="phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" class="form-input w-2/3 border-0 bg-transparent px-3 py-2 placeholder:text-slate-400 text-xs focus:ring-0" placeholder="81234567890">
                            </div>
                        </label>
                        @error('phone')
                            <span class="text-tiny-plus text-error mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" wire:click="$set('showPhoneForm', false)" class="btn h-9 rounded-lg border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                            Batal
                        </button>
                        <button type="submit" class="btn h-9 rounded-lg bg-primary px-5 text-xs font-semibold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                            <span wire:loading.remove wire:target="savePhone">Lanjutkan ke Pembayaran</span>
                            <span wire:loading wire:target="savePhone">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            {{-- Header (Exact from layouts/price-list-1) --}}
            <div class="py-5 text-center lg:py-6">
                <p class="text-sm uppercase tracking-wider text-slate-500 dark:text-navy-300">PILIH PAKET ANDA</p>
                <h3 class="mt-1 text-xl font-semibold text-slate-600 dark:text-navy-100 lg:text-2xl">
                    Tingkatkan Produktivitas Anda Tanpa Batas
                </h3>
            </div>

            {{-- Price List 1 Grid Layout --}}
            @php
                $currentPlan = app(\App\Services\EntitlementService::class)->getCurrentPlan(auth()->user());
                $cols = min($plans->count(), 3);
            @endphp

            <div class="grid max-w-4xl grid-cols-1 gap-4 sm:grid-cols-{{ $cols }} sm:gap-5 lg:gap-6 mx-auto items-stretch">
                @foreach($plans as $plan)
                    @php
                        $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                        $isRecommended = $plan->price > 0 && ($plan->slug === 'pro' || $plan->slug === 'premium' || $loop->iteration === 2);
                        $breakdown = app(\App\Services\PriceCalculator::class)->breakdown($plan);
                        $hasPending = auth()->check() && auth()->user()->subscriptions()
                                        ->where('status', 'pending')
                                        ->where('plan_id', $plan->id)
                                        ->where('created_at', '>', now()->subHours(24))
                                        ->exists();
                    @endphp

                    <div class="card p-4 text-center sm:p-5 relative flex flex-col justify-between h-full {{ $isCurrent ? 'ring-2 ring-primary dark:ring-accent' : '' }}">
                        {{-- Top Badge for Recommended or Current Plan --}}
                        @if($isCurrent)
                            <div class="absolute top-0 right-0 p-3">
                                <div class="badge rounded-full bg-success/10 text-success dark:bg-success/15 font-semibold text-xs px-3 py-1">
                                    Paket Aktif
                                </div>
                            </div>
                        @elseif($isRecommended)
                            <div class="absolute top-0 right-0 p-3">
                                <div class="badge rounded-full bg-info/10 text-info dark:bg-info/15 font-semibold text-xs px-3 py-1">
                                    Recommended
                                </div>
                            </div>
                        @endif

                        <div>
                            {{-- Icon (Price List 1 Style) --}}
                            <div class="mt-8">
                                @if($plan->price == 0)
                                    <i class="fa fa-car text-6xl text-primary dark:text-accent-light"></i>
                                @elseif($isRecommended)
                                    <i class="fa fa-plane text-6xl text-primary dark:text-accent-light"></i>
                                @else
                                    <i class="fa fa-rocket text-6xl text-primary dark:text-accent-light"></i>
                                @endif
                            </div>

                            {{-- Title & Subtitle --}}
                            <div class="mt-5">
                                <h4 class="text-xl font-semibold text-slate-600 dark:text-navy-100">
                                    {{ $plan->name }}
                                </h4>
                                <p class="text-xs text-slate-400 dark:text-navy-300 mt-1 min-h-[20px]">
                                    {{ $plan->description ?? 'Pilihan terbaik untuk kebutuhan Anda' }}
                                </p>
                            </div>

                            @php
                                $planFeatures = $plan->features;
                                if (empty($planFeatures)) {
                                    if ($plan->price == 0) {
                                        $planFeatures = [
                                            '5x / hari Kompres Gambar',
                                            '5x / hari Convert Gambar',
                                            '2x / hari PDF ke Word (Maks 5MB)',
                                            'Waktu Proses Standar'
                                        ];
                                    } else {
                                        $planFeatures = [
                                            'Tanpa Batas Kuota Harian',
                                            'Buka Semua Fitur Preset Kustom',
                                            'Konversi PDF ke Word File Besar (50MB)',
                                            'Prioritas Server Kecepatan Tinggi'
                                        ];
                                    }
                                }
                            @endphp

                            {{-- Price --}}
                            <div class="mt-5 min-h-[56px] flex flex-col justify-center">
                                @if($breakdown['discount'] > 0)
                                    <div class="flex items-center justify-center space-x-1.5 mb-1">
                                        <span class="line-through text-slate-400 dark:text-navy-300 text-xs font-semibold">
                                            Rp {{ number_format($breakdown['basePrice'], 0, ',', '.') }}
                                        </span>
                                        <span class="badge rounded-full bg-success/15 text-success dark:bg-success/20 font-bold text-[10px] px-2 py-0.5">
                                            Hemat {{ $plan->discount_type === 'percent' ? $plan->discount_value . '%' : 'Rp ' . number_format($breakdown['discount'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-3xl sm:text-4xl tracking-tight font-bold text-primary dark:text-accent-light">
                                        Rp {{ number_format($breakdown['subtotal'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-navy-300">
                                        /{{ $plan->duration_days ? $plan->duration_days . ' hari' : 'bulan' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Features List (Price List 1 Exact Style) --}}
                            <div class="mt-8 space-y-4 text-left">
                                @foreach($planFeatures as $feature)
                                    <div class="flex items-start space-x-3">
                                        <div class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">
                                            {{ $feature }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- CTA Button (Price List 1 Style) --}}
                        <div class="mt-8">
                            @if($isCurrent)
                                <a href="{{ route('dashboard.billing') }}" class="btn rounded-full border border-slate-200 font-medium text-slate-400 hover:bg-slate-150 dark:border-navy-500 dark:text-navy-300 w-full text-xs py-2.5">
                                    Paket Aktif
                                </a>
                            @elseif($isRecommended)
                                <button
                                    wire:click="selectPlan({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    class="btn rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 w-full text-xs py-2.5"
                                >
                                    <span wire:loading.remove wire:target="selectPlan({{ $plan->id }})">
                                        {{ $hasPending ? 'Selesaikan Pembayaran' : 'Pilih Paket' }}
                                    </span>
                                    <span wire:loading wire:target="selectPlan({{ $plan->id }})">
                                        Memproses...
                                    </span>
                                </button>
                            @else
                                <button
                                    wire:click="selectPlan({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    class="btn rounded-full border border-slate-200 font-medium text-primary hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-500 dark:text-accent-light dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90 w-full text-xs py-2.5"
                                >
                                    <span wire:loading.remove wire:target="selectPlan({{ $plan->id }})">
                                        {{ $hasPending ? 'Selesaikan' : ($plan->price > 0 ? 'Pilih Paket' : 'Mulai Gratis') }}
                                    </span>
                                    <span wire:loading wire:target="selectPlan({{ $plan->id }})">
                                        Memproses...
                                    </span>
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

        if ($wire.snapToken) {
            setTimeout(() => {
                initSnap($wire.snapToken);
            }, 100);
        }

        $wire.on('snap-token-ready', (event) => {
            let token = event.token || (event[0] && event[0].token);
            setTimeout(() => {
                initSnap(token);
            }, 100);
        });
    </script>
    @endscript
</div>
