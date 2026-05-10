import { Head, Link, useForm } from '@inertiajs/react';
import { Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Role {
    id: number;
    name: string;
}

interface Option {
    id: number;
    name: string;
}

interface KelurahanOption extends Option {
    kecamatan_id: number;
}

interface Props {
    roles: Role[];
    opds: Option[];
    faskes: Option[];
    puskesmas: Option[];
    kecamatans: Option[];
    kelurahans: KelurahanOption[];
}

export default function UsersCreate({
    roles,
    opds,
    faskes,
    puskesmas,
    kecamatans,
    kelurahans,
}: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role_id: '' as number | '',
        opd_id: '' as number | '',
        faskes_id: '' as number | '',
        puskesmas_id: '' as number | '',
        kecamatan_id: '' as number | '',
        kelurahan_id: '' as number | '',
    });

    const selectedRole = roles.find((role) => role.id === Number(data.role_id));
    const selectedRoleName = (selectedRole?.name ?? '').toLowerCase();

    const filteredKelurahans = data.kecamatan_id
        ? kelurahans.filter(
              (item) => item.kecamatan_id === Number(data.kecamatan_id),
          )
        : [];

    const handleRoleChange = (nextRoleId: number | '') => {
        setData((prev) => ({
            ...prev,
            role_id: nextRoleId,
            opd_id: '',
            faskes_id: '',
            puskesmas_id: '',
            kecamatan_id: '',
            kelurahan_id: '',
        }));
    };

    const showOpd = selectedRoleName === 'opd';
    const showFaskes = selectedRoleName === 'faskes';
    const showPuskesmas = selectedRoleName === 'puskesmas';
    const showKecamatan =
        selectedRoleName === 'kecamatan' || selectedRoleName === 'kelurahan';
    const showKelurahan = selectedRoleName === 'kelurahan';

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('users.store'));
    };

    return (
        <>
            <Head title="Tambah User" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <Users className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Tambah User
                            </h1>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Full Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Nama lengkap"
                                className="mt-1"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="email">Email Address</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(event) =>
                                    setData('email', event.target.value)
                                }
                                placeholder="email@contoh.com"
                                className="mt-1"
                            />
                            {errors.email && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(event) =>
                                    setData('password', event.target.value)
                                }
                                placeholder="Minimal 8 karakter"
                                className="mt-1"
                            />
                            {errors.password && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="password_confirmation">
                                Confirm Password
                            </Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(event) =>
                                    setData(
                                        'password_confirmation',
                                        event.target.value,
                                    )
                                }
                                placeholder="Ulangi password"
                                className="mt-1"
                            />
                        </div>

                        <div>
                            <Label htmlFor="role_id">Role</Label>
                            <select
                                id="role_id"
                                value={data.role_id}
                                onChange={(event) => {
                                    const value = event.target.value
                                        ? Number(event.target.value)
                                        : '';
                                    handleRoleChange(value);
                                }}
                                className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                            >
                                <option value="">Pilih role</option>
                                {roles.map((role) => (
                                    <option key={role.id} value={role.id}>
                                        {role.name}
                                    </option>
                                ))}
                            </select>
                            {errors.role_id && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.role_id}
                                </p>
                            )}
                        </div>

                        {showOpd && (
                            <div>
                                <Label htmlFor="opd_id">OPD</Label>
                                <select
                                    id="opd_id"
                                    value={data.opd_id}
                                    onChange={(event) =>
                                        setData(
                                            'opd_id',
                                            event.target.value
                                                ? Number(event.target.value)
                                                : '',
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                                >
                                    <option value="">Pilih OPD</option>
                                    {opds.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.opd_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.opd_id}
                                    </p>
                                )}
                            </div>
                        )}

                        {showFaskes && (
                            <div>
                                <Label htmlFor="faskes_id">Faskes</Label>
                                <select
                                    id="faskes_id"
                                    value={data.faskes_id}
                                    onChange={(event) =>
                                        setData(
                                            'faskes_id',
                                            event.target.value
                                                ? Number(event.target.value)
                                                : '',
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                                >
                                    <option value="">Pilih Faskes</option>
                                    {faskes.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.faskes_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.faskes_id}
                                    </p>
                                )}
                            </div>
                        )}

                        {showPuskesmas && (
                            <div>
                                <Label htmlFor="puskesmas_id">Puskesmas</Label>
                                <select
                                    id="puskesmas_id"
                                    value={data.puskesmas_id}
                                    onChange={(event) =>
                                        setData(
                                            'puskesmas_id',
                                            event.target.value
                                                ? Number(event.target.value)
                                                : '',
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                                >
                                    <option value="">Pilih Puskesmas</option>
                                    {puskesmas.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.puskesmas_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.puskesmas_id}
                                    </p>
                                )}
                            </div>
                        )}

                        {showKecamatan && (
                            <div>
                                <Label htmlFor="kecamatan_id">Kecamatan</Label>
                                <select
                                    id="kecamatan_id"
                                    value={data.kecamatan_id}
                                    onChange={(event) => {
                                        const value = event.target.value
                                            ? Number(event.target.value)
                                            : '';
                                        setData((prev) => ({
                                            ...prev,
                                            kecamatan_id: value,
                                            kelurahan_id: '',
                                        }));
                                    }}
                                    className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                                >
                                    <option value="">Pilih Kecamatan</option>
                                    {kecamatans.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.kecamatan_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.kecamatan_id}
                                    </p>
                                )}
                            </div>
                        )}

                        {showKelurahan && (
                            <div>
                                <Label htmlFor="kelurahan_id">Kelurahan</Label>
                                <select
                                    id="kelurahan_id"
                                    value={data.kelurahan_id}
                                    onChange={(event) =>
                                        setData(
                                            'kelurahan_id',
                                            event.target.value
                                                ? Number(event.target.value)
                                                : '',
                                        )
                                    }
                                    disabled={!data.kecamatan_id}
                                    className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm disabled:bg-gray-100"
                                >
                                    <option value="">
                                        {data.kecamatan_id
                                            ? 'Pilih Kelurahan'
                                            : 'Pilih Kecamatan dulu'}
                                    </option>
                                    {filteredKelurahans.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.kelurahan_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.kelurahan_id}
                                    </p>
                                )}
                            </div>
                        )}

                        {!selectedRole && (
                            <p className="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                                Pilih role terlebih dahulu untuk menentukan
                                relasi unit.
                            </p>
                        )}

                        {selectedRoleName === 'admin' && (
                            <p className="rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                                Role Admin memiliki akses seluruh modul tanpa
                                relasi unit khusus.
                            </p>
                        )}

                        <div className="flex gap-3 pt-2">
                            <Button
                                asChild
                                type="button"
                                variant="outline"
                                className="flex-1"
                            >
                                <Link href={route('users.index')}>Batal</Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1"
                            >
                                {processing ? 'Menyimpan...' : 'Simpan User'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
