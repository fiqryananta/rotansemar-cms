import { Head, Link, useForm } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Opd {
    id: number;
    name: string;
}

interface Props {
    opd: Opd;
}

export default function OpdsEdit({ opd }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: opd.name,
    });

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        patch(route('opds.update', opd.id));
    };

    return (
        <>
            <Head title="Edit OPD" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <Building2 className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Edit OPD
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Perbarui data OPD.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Nama OPD</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Masukkan nama OPD"
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
                                <Link href={route('opds.index')}>Batal</Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1"
                            >
                                {processing ? 'Menyimpan...' : 'Update OPD'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
