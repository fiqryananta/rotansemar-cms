import { Head, Link, useForm } from '@inertiajs/react';
import { Pill } from 'lucide-react';
import { Button } from '@/components/ui/button';
import MedicationPickupForm from './form';

interface Pasien {
    id: number;
    name: string;
    nik: string;
}

interface Faskes {
    id: number;
    name: string;
}

interface Puskesmas {
    id: number;
    name: string;
}

interface Props {
    pasiens: Pasien[];
    faskes: Faskes[];
    puskesmas: Puskesmas[];
    statuses: Record<string, string>;
}

export default function MedicationPickupCreate({
    pasiens,
    faskes,
    puskesmas,
    statuses,
}: Props) {
    const { data, setData, post, processing, errors } = useForm({
        pasien_id: '',
        faskes_id: null as number | null,
        puskesmas_id: null as number | null,
        scheduled_date: '',
        status: '',
        actual_date: '',
        transfer_date: '',
        next_pickup_date: '',
        target_faskes_id: null as number | null,
        is_outside_city: false,
        notes: '',
    });

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('medication-pickups.store'));
    };

    return (
        <>
            <Head title="Tambah Pengambilan Obat" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <Pill className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Tambah Pengambilan Obat
                            </h1>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <MedicationPickupForm
                            data={data}
                            setData={setData}
                            errors={errors}
                            pasiens={pasiens}
                            faskes={faskes}
                            puskesmas={puskesmas}
                            statuses={statuses}
                        />

                        <div className="flex gap-3 pt-2">
                            <Button
                                asChild
                                type="button"
                                variant="outline"
                                className="flex-1"
                            >
                                <Link
                                    href={route('medication-pickups.index')}
                                >
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
                                    : 'Simpan Pengambilan Obat'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
