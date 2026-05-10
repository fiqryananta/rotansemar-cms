import { Head, Link, useForm } from '@inertiajs/react';
import { HeartPulse } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Kecamatan {
    id: number;
    name: string;
}

interface Kelurahan {
    id: number;
    name: string;
    kecamatan?: Kecamatan;
}

interface Puskesmas {
    id: number;
    name: string;
    kelurahans: { id: number }[];
}

interface Props {
    puskesmas: Puskesmas;
    kelurahans: Kelurahan[];
}

export default function PuskesmasEdit({ puskesmas, kelurahans }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: puskesmas.name,
        kelurahan_ids: puskesmas.kelurahans.map((item) => item.id),
    });

    const toggleKelurahan = (kelurahanId: number) => {
        setData(
            'kelurahan_ids',
            data.kelurahan_ids.includes(kelurahanId)
                ? data.kelurahan_ids.filter((id) => id !== kelurahanId)
                : [...data.kelurahan_ids, kelurahanId],
        );
    };

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        patch(route('puskesmas.update', puskesmas.id));
    };

    return (
        <>
            <Head title="Edit Puskesmas" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <HeartPulse className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Edit Puskesmas
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Perbarui puskesmas dan kelurahan cakupan.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Nama Puskesmas</Label>
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

                        <div>
                            <Label className="mb-3 block">
                                Kelurahan Cakupan
                            </Label>
                            <div className="max-h-72 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4">
                                {kelurahans.map((kelurahan) => (
                                    <div
                                        key={kelurahan.id}
                                        className="flex items-start gap-3 rounded-md bg-white px-3 py-2"
                                    >
                                        <Checkbox
                                            id={`kelurahan-${kelurahan.id}`}
                                            checked={data.kelurahan_ids.includes(
                                                kelurahan.id,
                                            )}
                                            onCheckedChange={() =>
                                                toggleKelurahan(kelurahan.id)
                                            }
                                        />
                                        <Label
                                            htmlFor={`kelurahan-${kelurahan.id}`}
                                            className="cursor-pointer leading-relaxed"
                                        >
                                            <span className="block text-sm font-medium text-gray-900">
                                                {kelurahan.name}
                                            </span>
                                            <span className="block text-xs text-gray-500">
                                                {kelurahan.kecamatan?.name}
                                            </span>
                                        </Label>
                                    </div>
                                ))}
                            </div>
                            {errors.kelurahan_ids && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.kelurahan_ids}
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
                                <Link href={route('puskesmas.index')}>
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
                                    : 'Update Puskesmas'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
