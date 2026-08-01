<div>
    <div class="max-w-md mx-auto mt-16 bg-white p-8 rounded-lg shadow-sm border border-gray-200">
        <h2 class="text-2xl font-black text-indigo-600 mb-6 text-center">Login ke ToolBox</h2>

        <form wire:submit="login">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" required>
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" wire:model="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" required>
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">Ingat Saya</label>
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Daftar sekarang</a>
            </p>
        </div>
    </div>
</div>
