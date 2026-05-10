import { Head, Link, useForm } from '@inertiajs/react';
import { Wrench } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface JenisPenanganan {
    id: number;
    name: string;
}

interface Props {
    jenisPenanganans: JenisPenanganan[];
}

export default function JenisKebutuhansCreate({ jenisPenanganans }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        jenis_penanganan_ids: [] as number[],
    });

    const toggleJenisPenanganan = (jenisPenangananId: number) => {
        setData(
            'jenis_penanganan_ids',
            data.jenis_penanganan_ids.includes(jenisPenangananId)
                ? data.jenis_penanganan_ids.filter(
                      (id) => id !== jenisPenangananId,
                  )
                : [...data.jenis_penanganan_ids, jenisPenangananId],
        );
    };

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('jenis-kebutuhans.store'));
    };

    return (
        <>
            <Head title="Tambah Jenis Kebutuhan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <Wrench className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Tambah Jenis Kebutuhan
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Tambahkan jenis kebutuhan dan pilih relasi jenis
                                penanganan.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Nama Kebutuhan</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Masukkan nama kebutuhan"
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
                                Jenis Penanganan (bisa lebih dari 1)
                            </Label>
                            <div className="max-h-72 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4">
                                {jenisPenanganans.map((jenis) => (
                                    <div
                                        key={jenis.id}
                                        className="flex items-start gap-3 rounded-md bg-white px-3 py-2"
                                    >
                                        <Checkbox
                                            id={`jenis-${jenis.id}`}
                                            checked={data.jenis_penanganan_ids.includes(
                                                jenis.id,
                                            )}
                                            onCheckedChange={() =>
                                                toggleJenisPenanganan(jenis.id)
                                            }
                                        />
                                        <Label
                                            htmlFor={`jenis-${jenis.id}`}
                                            className="cursor-pointer leading-relaxed"
                                        >
                                            <span className="block text-sm font-medium text-gray-900">
                                                {jenis.name}
                                            </span>
                                        </Label>
                                    </div>
                                ))}
                            </div>
                            {errors.jenis_penanganan_ids && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.jenis_penanganan_ids}
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
                                <Link href={route('jenis-kebutuhans.index')}>
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
                                    : 'Simpan Jenis Kebutuhan'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
