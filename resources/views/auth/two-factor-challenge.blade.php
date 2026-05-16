@php
    $title = 'Two-factor authentication';
@endphp
@extends('layouts.auth-blade')

@section('content')
    <div class="rounded-2xl bg-white p-8 shadow-xl backdrop-blur-sm">
        <h1 class="text-2xl font-bold text-gray-900" id="two-factor-title">Authentication code</h1>
        <p class="mt-2 text-sm text-gray-600" id="two-factor-description">Masukkan kode dari aplikasi authenticator Anda.</p>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6 space-y-4" id="two-factor-form">
            @csrf

            <div id="code-field">
                <label for="code" class="block text-sm font-semibold text-gray-700">Authentication code</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    autocomplete="one-time-code"
                    autofocus
                    class="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-center text-lg tracking-[0.35em] text-gray-900 transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none"
                />
                @error('code')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="recovery-field" class="hidden">
                <label for="recovery_code" class="block text-sm font-semibold text-gray-700">Recovery code</label>
                <input
                    id="recovery_code"
                    name="recovery_code"
                    type="text"
                    autocomplete="one-time-code"
                    class="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none"
                />
                @error('recovery_code')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-blue-700 hover:to-indigo-700">
                Continue
            </button>

            <p class="text-center text-sm text-gray-600">
                <span id="toggle-prefix">atau gunakan</span>
                <button type="button" id="toggle-recovery" class="ml-1 font-medium text-blue-600 hover:text-blue-700">
                    recovery code
                </button>
            </p>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const toggleButton = document.getElementById('toggle-recovery');
            const codeField = document.getElementById('code-field');
            const recoveryField = document.getElementById('recovery-field');
            const codeInput = document.getElementById('code');
            const recoveryInput = document.getElementById('recovery_code');
            const title = document.getElementById('two-factor-title');
            const description = document.getElementById('two-factor-description');

            if (!toggleButton) {
                return;
            }

            let recoveryMode = false;

            toggleButton.addEventListener('click', function () {
                recoveryMode = !recoveryMode;

                if (recoveryMode) {
                    codeField.classList.add('hidden');
                    recoveryField.classList.remove('hidden');
                    toggleButton.textContent = 'authentication code';
                    title.textContent = 'Recovery code';
                    description.textContent = 'Masukkan salah satu emergency recovery code Anda.';
                    codeInput.value = '';
                    recoveryInput.focus();
                    return;
                }

                recoveryField.classList.add('hidden');
                codeField.classList.remove('hidden');
                toggleButton.textContent = 'recovery code';
                title.textContent = 'Authentication code';
                description.textContent = 'Masukkan kode dari aplikasi authenticator Anda.';
                recoveryInput.value = '';
                codeInput.focus();
            });
        })();
    </script>
@endpush
