import { Head, Link, useForm } from '@inertiajs/react';
import { Shield } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Permission {
    id: number;
    name: string;
}

interface Props {
    permissions: Permission[];
}

export default function RolesCreate({ permissions }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        permissions: [] as number[],
    });

    const togglePermission = (permissionId: number) => {
        setData(
            'permissions',
            data.permissions.includes(permissionId)
                ? data.permissions.filter((id) => id !== permissionId)
                : [...data.permissions, permissionId],
        );
    };

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('roles.store'));
    };

    return (
        <>
            <Head title="Tambah Role" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <Shield className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Tambah Role
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Tambahkan role baru dan pilih permission.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Role Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Administrator"
                                className="mt-1"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label className="mb-3 block">
                                Assign Permissions
                            </Label>
                            <div className="grid grid-cols-2 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 md:grid-cols-3">
                                {permissions.map((permission) => (
                                    <div
                                        key={permission.id}
                                        className="flex items-center gap-2"
                                    >
                                        <Checkbox
                                            id={`perm-${permission.id}`}
                                            checked={data.permissions.includes(
                                                permission.id,
                                            )}
                                            onCheckedChange={() =>
                                                togglePermission(permission.id)
                                            }
                                        />
                                        <Label
                                            htmlFor={`perm-${permission.id}`}
                                            className="cursor-pointer text-sm font-normal"
                                        >
                                            {permission.name}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                            {errors.permissions && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.permissions}
                                </p>
                            )}
                        </div>

                        <div className="flex gap-3 pt-2">
                            <Button
                                asChild
                                type="button"
                                variant="outline"
                                className="flex-1"
                            >
                                <Link href={route('roles.index')}>Batal</Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1"
                            >
                                {processing ? 'Menyimpan...' : 'Simpan Role'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
