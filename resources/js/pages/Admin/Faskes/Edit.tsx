import { Head, Link, useForm } from '@inertiajs/react';
import { Hospital } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Faskes {
    id: number;
    name: string;
}

interface Props {
    faskes: Faskes;
}

export default function FaskesEdit({ faskes }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: faskes.name,
    });

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        patch(route('faskes.update', faskes.id));
    };

    return (
        <>
            <Head title="Edit Faskes" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <Hospital className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Edit Faskes
                            </h1>
                            <p className="mt-2 text-gray-600">
                                Perbarui data faskes.
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <Label htmlFor="name">Nama Faskes</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Masukkan nama faskes"
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
                                <Link href={route('faskes.index')}>Batal</Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1"
                            >
                                {processing ? 'Menyimpan...' : 'Update Faskes'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
