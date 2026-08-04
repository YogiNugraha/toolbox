<div class="py-12 bg-paper min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-display font-extrabold text-ink sm:text-4xl">
                Tingkatkan Produktivitas Anda
            </h2>
            <p class="mt-4 text-xl text-ink-muted">
                Pilih paket yang sesuai dengan kebutuhan Anda. Upgrade ke Pro untuk akses tanpa batas.
            </p>
        </div>

        @if (session()->has('error'))
            <div class="mt-8 max-w-2xl mx-auto bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="mt-16 bg-white pb-12 lg:mt-20 lg:pb-20">
            <div class="relative z-0">
                <div class="absolute inset-0 h-5/6 bg-paper lg:h-2/3"></div>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="relative lg:grid lg:grid-cols-2 lg:gap-8">
                        
                        <!-- Free Plan -->
                        <div class="mt-10 max-w-lg mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-start-1 lg:col-end-2 lg:row-start-1 lg:row-end-4 border border-hairline rounded-lg shadow-sm bg-white overflow-hidden flex flex-col">
                            <div class="px-6 py-8 bg-white sm:p-10 sm:pb-6 flex-1">
                                <div>
                                    <h3 class="inline-flex px-4 py-1 rounded-full text-sm font-semibold tracking-wide uppercase bg-ink/10 text-ink" id="tier-free">
                                        Free
                                    </h3>
                                </div>
                                <div class="mt-4 flex items-baseline text-6xl font-extrabold text-ink">
                                    Rp 0
                                    <span class="ml-1 text-2xl font-medium text-ink-muted">
                                        /selamanya
                                    </span>
                                </div>
                                <p class="mt-5 text-lg text-ink-muted">
                                    Cocok untuk penggunaan kasual.
                                </p>
                            </div>
                            <div class="flex-1 flex flex-col justify-between px-6 pt-6 pb-8 bg-paper/30 sm:p-10 sm:pt-6">
                                <ul class="space-y-4">
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-base text-ink-muted">
                                            Compress Gambar: 5x / hari (Preset Basic)
                                        </p>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-base text-ink-muted">
                                            Convert Gambar: 5x / hari
                                        </p>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-base text-ink-muted">
                                            PDF ke Word: 2x / hari (Max 5MB)
                                        </p>
                                    </li>
                                </ul>
                                <div class="mt-8">
                                    <div class="rounded-lg shadow-md">
                                        <a href="{{ route('dashboard') }}" class="block w-full text-center rounded-lg border border-transparent bg-white px-6 py-3 text-base font-medium text-amber hover:bg-paper transition-colors" aria-describedby="tier-free">
                                            Lanjutkan Free
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pro Plan -->
                        <div class="max-w-lg mx-auto lg:max-w-none lg:mx-0 lg:col-start-2 lg:col-end-3 lg:row-start-1 lg:row-end-4 border-2 border-amber rounded-lg shadow-xl bg-white overflow-hidden flex flex-col relative">
                            <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4">
                                <div class="bg-amber text-ink text-xs font-bold px-8 py-2 transform rotate-45">
                                    POPULAR
                                </div>
                            </div>
                            <div class="px-6 py-8 bg-white sm:p-10 sm:pb-6 flex-1">
                                <div>
                                    <h3 class="inline-flex px-4 py-1 rounded-full text-sm font-semibold tracking-wide uppercase bg-amber/20 text-amber" id="tier-pro">
                                        Pro
                                    </h3>
                                </div>
                                <div class="mt-4 flex items-baseline text-6xl font-extrabold text-ink">
                                    Rp {{ number_format(config('plans.pro.price'), 0, ',', '.') }}
                                    <span class="ml-1 text-2xl font-medium text-ink-muted">
                                        /{{ config('plans.pro.duration_days') }} hari
                                    </span>
                                </div>
                                <p class="mt-5 text-lg text-ink-muted">
                                    Buka semua fitur tanpa batasan apapun.
                                </p>
                            </div>
                            <div class="flex-1 flex flex-col justify-between px-6 pt-6 pb-8 bg-paper/30 sm:p-10 sm:pt-6">
                                <ul class="space-y-4">
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-amber" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-base text-ink-muted font-medium">
                                            Compress Gambar: <span class="text-ink font-bold">Unlimited</span> (Semua Preset Terbuka)
                                        </p>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-amber" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-base text-ink-muted font-medium">
                                            Convert Gambar: <span class="text-ink font-bold">Unlimited</span>
                                        </p>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-amber" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-base text-ink-muted font-medium">
                                            PDF ke Word: <span class="text-ink font-bold">Unlimited</span> (Max 50MB)
                                        </p>
                                    </li>
                                </ul>
                                <div class="mt-8">
                                    <div class="rounded-lg shadow-md">
                                        @if(auth()->check() && auth()->user()->activeSubscription())
                                            <a href="{{ route('dashboard.billing') }}" class="block w-full text-center rounded-lg border border-transparent bg-gray-200 px-6 py-4 text-base font-medium text-gray-700 cursor-not-allowed">
                                                Paket Pro Aktif
                                            </a>
                                        @else
                                            <button wire:click="checkout" wire:loading.attr="disabled" class="block w-full text-center rounded-lg border border-transparent bg-amber px-6 py-4 text-base font-bold text-ink hover:bg-amber/90 transition-colors shadow-lg shadow-amber/30">
                                                <span wire:loading.remove wire:target="checkout">Upgrade ke Pro Sekarang</span>
                                                <span wire:loading wire:target="checkout">
                                                    Memproses...
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Midtrans Snap Script -->
    <script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('snap-token-ready', (event) => {
                let token = event.token;
                snap.pay(token, {
                    onSuccess: function(result){
                        window.location.href = "{{ route('dashboard.billing') }}?status=success";
                    },
                    onPending: function(result){
                        window.location.href = "{{ route('dashboard.billing') }}?status=pending";
                    },
                    onError: function(result){
                        alert('Pembayaran gagal, silakan coba lagi.');
                    },
                    onClose: function(){
                        console.log('Customer closed the popup without finishing the payment');
                    }
                })
            });
        });
    </script>
</div>
