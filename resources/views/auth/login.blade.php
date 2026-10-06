<x-app-layout>
    @section('title', 'Login - ' . config('app.name'))

    <div class="container-app py-16 flex items-center justify-center min-h-[70vh]">
        <div class="w-full max-w-md">
            <div class="card p-8 shadow-2xl border border-border/80">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center mx-auto mb-3 text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-text">Iniciar Sesión</h1>
                    <p class="text-text-secondary text-sm mt-1">Accede al panel de administración</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 p-3 rounded-lg bg-danger/10 border border-danger/20 text-danger text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-text-secondary text-xs uppercase tracking-wider mb-1 font-medium">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input w-full" placeholder="admin@example.com" required autofocus>
                    </div>
                    <div>
                        <label class="block text-text-secondary text-xs uppercase tracking-wider mb-1 font-medium">Contraseña</label>
                        <input type="password" name="password" class="input w-full" placeholder="••••••••" required>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-text-secondary text-xs cursor-pointer">
                            <input type="checkbox" name="remember" value="1" class="rounded border-border bg-surface text-primary focus:ring-primary/50">
                            Recordarme
                        </label>
                    </div>
                    <button type="submit" class="btn-primary w-full py-2.5 font-semibold text-sm shadow-lg shadow-primary/25 cursor-pointer">
                        Ingresar al Panel
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-border text-center space-y-2">
                    <p class="text-text-muted text-xs font-medium">Cuentas disponibles para pruebas:</p>
                    <div class="flex flex-col gap-1.5 text-xs">
                        <div>
                            <span class="text-text-secondary">Admin:</span>
                            <code class="text-primary bg-surface px-2 py-0.5 rounded ml-1">admin@example.com / password</code>
                        </div>
                        <div>
                            <span class="text-text-secondary">User:</span>
                            <code class="text-text bg-surface px-2 py-0.5 rounded ml-1">user@example.com / password</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
