import { Head, Link, useForm } from '@inertiajs/react';
import { Building } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Kecamatan {
    id: number;
    name: string;
}

interface Props {
    kecamatan: Kecamatan;
}

export default function KecamatansEdit({ kecamatan }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: kecamatan.name,
    });

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        patch(route('kecamatans.update', kecamatan.id));
    };

    return (
        <>
            <Head title="Edit Kecamatan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <Building className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Edit Kecamatan
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Perbarui data kecamatan.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Nama Kecamatan</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Masukkan nama kecamatan"
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
                                <Link href={route('kecamatans.index')}>
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
                                    : 'Update Kecamatan'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
