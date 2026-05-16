@php
    $activeRoute = request()->route()?->getName();
@endphp

<div class="rounded-lg bg-white p-2 shadow-sm ring-1 ring-gray-200">
    <nav class="grid gap-2 sm:grid-cols-3" aria-label="Menu pengaturan">
        <a
            href="{{ route('profile.edit') }}"
            @class([
                'rounded-md px-3 py-2 text-sm font-medium transition',
                'bg-sky-600 text-white' => $activeRoute === 'profile.edit',
                'text-gray-700 hover:bg-gray-100' => $activeRoute !== 'profile.edit',
            ])
        >
            Profil
        </a>
        <a
            href="{{ route('security.edit') }}"
            @class([
                'rounded-md px-3 py-2 text-sm font-medium transition',
                'bg-sky-600 text-white' => $activeRoute === 'security.edit',
                'text-gray-700 hover:bg-gray-100' => $activeRoute !== 'security.edit',
            ])
        >
            Keamanan
        </a>
        <a
            href="{{ route('appearance.edit') }}"
            @class([
                'rounded-md px-3 py-2 text-sm font-medium transition',
                'bg-sky-600 text-white' => $activeRoute === 'appearance.edit',
                'text-gray-700 hover:bg-gray-100' => $activeRoute !== 'appearance.edit',
            ])
        >
            Tampilan
        </a>
    </nav>
</div>