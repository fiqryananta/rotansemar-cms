import { Head, Link, useForm } from '@inertiajs/react';
import { MapPinned } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Kecamatan {
    id: number;
    name: string;
}

interface Kelurahan {
    id: number;
    name: string;
    kecamatan_id: number;
}

interface Props {
    kelurahan: Kelurahan;
    kecamatans: Kecamatan[];
}

export default function KelurahansEdit({ kelurahan, kecamatans }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        kecamatan_id: String(kelurahan.kecamatan_id),
        name: kelurahan.name,
    });

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        patch(route('kelurahans.update', kelurahan.id));
    };

    return (
        <>
            <Head title="Edit Kelurahan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <MapPinned className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Edit Kelurahan
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Perbarui data kelurahan.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="kecamatan_id">Kecamatan</Label>
                            <select
                                id="kecamatan_id"
                                value={data.kecamatan_id}
                                onChange={(event) =>
                                    setData('kecamatan_id', event.target.value)
                                }
                                className="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">Pilih Kecamatan</option>
                                {kecamatans.map((kecamatan) => (
                                    <option
                                        key={kecamatan.id}
                                        value={kecamatan.id}
                                    >
                                        {kecamatan.name}
                                    </option>
                                ))}
                            </select>
                            {errors.kecamatan_id && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.kecamatan_id}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="name">Nama Kelurahan</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                className="mt-1"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.name}
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
                                <Link href={route('kelurahans.index')}>
                                    Batal
                                </Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1"
                            >
                                {processing
                                    ? 'Menyimpan...'
                                    : 'Update Kelurahan'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
