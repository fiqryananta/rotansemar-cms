import { Head, Link, useForm } from '@inertiajs/react';
import { ClipboardList } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Opd {
    id: number;
    name: string;
}

interface Props {
    opds: Opd[];
}

export default function JenisPenanganansCreate({ opds }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        opd_ids: [] as number[],
    });

    const toggleOpd = (opdId: number) => {
        setData(
            'opd_ids',
            data.opd_ids.includes(opdId)
                ? data.opd_ids.filter((id) => id !== opdId)
                : [...data.opd_ids, opdId],
        );
    };

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('jenis-penanganans.store'));
    };

    return (
        <>
            <Head title="Tambah Jenis Penanganan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <ClipboardList className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Tambah Jenis Penanganan
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Tambahkan jenis penanganan dan pilih OPD
                                berwenang.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Nama Jenis Penanganan</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Masukkan nama jenis penanganan"
                                className="mt-1"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label className="mb-3 block">OPD Berwenang</Label>
                            <div className="max-h-72 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4">
                                {opds.map((opd) => (
                                    <div
                                        key={opd.id}
                                        className="flex items-start gap-3 rounded-md bg-white px-3 py-2"
                                    >
                                        <Checkbox
                                            id={`opd-${opd.id}`}
                                            checked={data.opd_ids.includes(
                                                opd.id,
                                            )}
                                            onCheckedChange={() =>
                                                toggleOpd(opd.id)
                                            }
                                        />
                                        <Label
                                            htmlFor={`opd-${opd.id}`}
                                            className="cursor-pointer leading-relaxed"
                                        >
                                            <span className="block text-sm font-medium text-gray-900">
                                                {opd.name}
                                            </span>
                                        </Label>
                                    </div>
                                ))}
                            </div>
                            {errors.opd_ids && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.opd_ids}
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
                                <Link href={route('jenis-penanganans.index')}>
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
                                    : 'Simpan Jenis Penanganan'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
