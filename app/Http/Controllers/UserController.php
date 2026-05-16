<?php

namespace App\Http\Controllers;

use App\Models\Faskes;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Opd;
use App\Models\Puskesmas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $users = User::query()
            ->with([
                'roles:id,name',
                'opd:id,name',
                'faskes:id,name',
                'puskesmas:id,name',
                'kecamatan:id,name',
                'kelurahan:id,name,kecamatan_id',
            ])
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $roles = Role::query()->orderBy('name')->get(['id', 'name']);

        $payload = [
            'users' => $users,
            'links' => $users->linkCollection(),
            'roles' => $roles,
            'filters' => [
                'search' => $request->search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('users.index', $payload);
    }

    public function create()
    {
        $payload = [
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'opds' => Opd::query()->orderBy('name')->get(['id', 'name']),
            'faskes' => Faskes::query()->orderBy('name')->get(['id', 'name']),
            'puskesmas' => Puskesmas::query()->orderBy('name')->get(['id', 'name']),
            'kecamatans' => Kecamatan::query()->orderBy('name')->get(['id', 'name']),
            'kelurahans' => Kelurahan::query()->orderBy('name')->get(['id', 'name', 'kecamatan_id']),
        ];

        return view('users.create', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'opd_id' => 'nullable|exists:opds,id',
            'faskes_id' => 'nullable|exists:faskes,id',
            'puskesmas_id' => 'nullable|exists:puskesmas,id',
            'kecamatan_id' => 'nullable|exists:kecamatans,id',
            'kelurahan_id' => 'nullable|exists:kelurahans,id',
        ]);

        $role = Role::query()->findOrFail((int) $validated['role_id']);
        $validated = $this->applyRoleScope($validated, $role);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
            'opd_id' => $validated['opd_id'],
            'faskes_id' => $validated['faskes_id'],
            'puskesmas_id' => $validated['puskesmas_id'],
            'kecamatan_id' => $validated['kecamatan_id'],
            'kelurahan_id' => $validated['kelurahan_id'],
        ]);

        $user->syncRoles([$role->name]);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        $user->load(['roles:id,name']);
        $userRoleId = $user->roles->first()?->id;

        $payload = [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'opds' => Opd::query()->orderBy('name')->get(['id', 'name']),
            'faskes' => Faskes::query()->orderBy('name')->get(['id', 'name']),
            'puskesmas' => Puskesmas::query()->orderBy('name')->get(['id', 'name']),
            'kecamatans' => Kecamatan::query()->orderBy('name')->get(['id', 'name']),
            'kelurahans' => Kelurahan::query()->orderBy('name')->get(['id', 'name', 'kecamatan_id']),
            'userRoleId' => $userRoleId,
        ];

        return view('users.edit', $payload);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'opd_id' => 'nullable|exists:opds,id',
            'faskes_id' => 'nullable|exists:faskes,id',
            'puskesmas_id' => 'nullable|exists:puskesmas,id',
            'kecamatan_id' => 'nullable|exists:kecamatans,id',
            'kelurahan_id' => 'nullable|exists:kelurahans,id',
        ]);

        $role = Role::query()->findOrFail((int) $validated['role_id']);
        $validated = $this->applyRoleScope($validated, $role);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'opd_id' => $validated['opd_id'],
            'faskes_id' => $validated['faskes_id'],
            'puskesmas_id' => $validated['puskesmas_id'],
            'kecamatan_id' => $validated['kecamatan_id'],
            'kelurahan_id' => $validated['kelurahan_id'],
            ...(isset($validated['password']) && $validated['password']
                ? ['password' => bcrypt($validated['password'])]
                : []),
        ]);

        $user->syncRoles([$role->name]);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Cannot delete your own account');
        }

        $user->syncRoles([]);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }

    private function applyRoleScope(array $validated, Role $role): array
    {
        $validated['opd_id'] = $validated['opd_id'] ?? null;
        $validated['faskes_id'] = $validated['faskes_id'] ?? null;
        $validated['puskesmas_id'] = $validated['puskesmas_id'] ?? null;
        $validated['kecamatan_id'] = $validated['kecamatan_id'] ?? null;
        $validated['kelurahan_id'] = $validated['kelurahan_id'] ?? null;

        $roleName = strtolower($role->name);

        if ($roleName === 'opd') {
            if (!$validated['opd_id']) {
                throw ValidationException::withMessages(['opd_id' => 'Role OPD wajib memilih OPD.']);
            }

            $validated['faskes_id'] = null;
            $validated['puskesmas_id'] = null;
            $validated['kecamatan_id'] = null;
            $validated['kelurahan_id'] = null;

            return $validated;
        }

        if ($roleName === 'faskes') {
            if (!$validated['faskes_id']) {
                throw ValidationException::withMessages(['faskes_id' => 'Role Faskes wajib memilih Faskes.']);
            }

            $validated['opd_id'] = null;
            $validated['puskesmas_id'] = null;
            $validated['kecamatan_id'] = null;
            $validated['kelurahan_id'] = null;

            return $validated;
        }

        if ($roleName === 'puskesmas') {
            if (!$validated['puskesmas_id']) {
                throw ValidationException::withMessages(['puskesmas_id' => 'Role Puskesmas wajib memilih Puskesmas.']);
            }

            $validated['opd_id'] = null;
            $validated['faskes_id'] = null;
            $validated['kecamatan_id'] = null;
            $validated['kelurahan_id'] = null;

            return $validated;
        }

        if ($roleName === 'kecamatan') {
            if (!$validated['kecamatan_id']) {
                throw ValidationException::withMessages(['kecamatan_id' => 'Role Kecamatan wajib memilih Kecamatan.']);
            }

            $validated['opd_id'] = null;
            $validated['faskes_id'] = null;
            $validated['puskesmas_id'] = null;
            $validated['kelurahan_id'] = null;

            return $validated;
        }

        if ($roleName === 'kelurahan') {
            if (!$validated['kecamatan_id']) {
                throw ValidationException::withMessages(['kecamatan_id' => 'Role Kelurahan wajib memilih Kecamatan.']);
            }
            if (!$validated['kelurahan_id']) {
                throw ValidationException::withMessages(['kelurahan_id' => 'Role Kelurahan wajib memilih Kelurahan.']);
            }

            $kelurahan = Kelurahan::query()->find($validated['kelurahan_id']);
            if (!$kelurahan || $kelurahan->kecamatan_id !== (int) $validated['kecamatan_id']) {
                throw ValidationException::withMessages(['kelurahan_id' => 'Kelurahan tidak sesuai dengan kecamatan yang dipilih.']);
            }

            $validated['opd_id'] = null;
            $validated['faskes_id'] = null;
            $validated['puskesmas_id'] = null;

            return $validated;
        }

        // Admin and any other role names are treated as global scope.
        $validated['opd_id'] = null;
        $validated['faskes_id'] = null;
        $validated['puskesmas_id'] = null;
        $validated['kecamatan_id'] = null;
        $validated['kelurahan_id'] = null;

        return $validated;
    }
}


