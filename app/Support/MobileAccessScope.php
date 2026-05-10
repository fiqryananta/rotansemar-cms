<?php

namespace App\Support;

use App\Models\Pasien;
use App\Models\PasienKebutuhan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MobileAccessScope
{
    public static function roles(User $user): array
    {
        return $user->getRoleNames()->map(fn ($name) => strtolower($name))->values()->all();
    }

    public static function accessMatrix(User $user): array
    {
        $roles = collect(self::roles($user));

        return [
            'can_access_faskes' => $roles->contains('admin') || $roles->contains('puskesmas') || $roles->contains('faskes') || $roles->contains('fasyankes'),
            'can_access_wilker' => $roles->contains('admin') || $roles->contains('puskesmas') || $roles->contains('kecamatan') || $roles->contains('kelurahan') || $roles->contains('epidemiolog'),
        ];
    }

    public static function scopeDashboard(Builder $query, User $user): Builder
    {
        $roles = collect(self::roles($user));

        if ($roles->contains('admin')) {
            return $query;
        }

        if ($roles->contains('faskes') && $user->faskes_id) {
            return $query->whereHas('pasien', fn ($q) => $q->where('faskes_id', $user->faskes_id));
        }

        if ($roles->contains('opd') && $user->opd_id) {
            return $query->where('opd_id', $user->opd_id);
        }

        if ($roles->contains('puskesmas') && $user->puskesmas_id) {
            return $query->whereHas('pasien', fn ($q) => $q->where('puskesmas_id', $user->puskesmas_id));
        }

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            return $query->whereHas('pasien', fn ($q) => $q->where('kecamatan_id', $user->kecamatan_id));
        }

        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            return $query->whereHas('pasien', fn ($q) => $q->where('kelurahan_id', $user->kelurahan_id));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function scopeFaskesPatients(Builder $query, User $user): Builder
    {
        $roles = collect(self::roles($user));

        if ($roles->contains('admin')) {
            return $query;
        }

        if ($roles->contains('faskes') && $user->faskes_id) {
            return $query->where('faskes_id', $user->faskes_id);
        }

        if ($roles->contains('puskesmas') && $user->puskesmas_id) {
            return $query->where('puskesmas_id', $user->puskesmas_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function scopeWilkerPatients(Builder $query, User $user): Builder
    {
        $roles = collect(self::roles($user));

        if ($roles->contains('admin')) {
            return $query;
        }

        if ($roles->contains('puskesmas') && $user->puskesmas_id) {
            return $query->where('puskesmas_id', $user->puskesmas_id);
        }

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            return $query->where('kecamatan_id', $user->kecamatan_id);
        }

        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            return $query->where('kelurahan_id', $user->kelurahan_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function pasienBaseQuery(): Builder
    {
        return Pasien::query()->with([
            'faskes:id,name',
            'puskesmas:id,name',
            'kecamatan:id,name',
            'kelurahan:id,name',
        ]);
    }

    public static function kebutuhanBaseQuery(): Builder
    {
        return PasienKebutuhan::query();
    }
}
