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

interface MedicationPickup {
    id: number;
    pasien_id: number;
    pasien?: Pasien;
    faskes_id: number | null;
    faskes?: Faskes | null;
    puskesmas_id: number | null;
    puskesmas?: Puskesmas | null;
    scheduled_date: string;
    status: string;
    actual_date: string | null;
    transfer_date: string | null;
    next_pickup_date: string | null;
    target_faskes_id: number | null;
    is_outside_city: boolean;
    notes: string | null;
}

interface Props {
    pickup: MedicationPickup;
    pasiens: Pasien[];
    faskes: Faskes[];
    puskesmas: Puskesmas[];
    statuses: Record<string, string>;
}

export default function MedicationPickupEdit({
    pickup,
    pasiens,
    faskes,
    puskesmas,
    statuses,
}: Props) {
    const { data, setData, patch, processing, errors, clearErrors } = useForm({
        pasien_id: pickup.pasien_id,
        faskes_id: pickup.faskes_id,
        puskesmas_id: pickup.puskesmas_id,
        scheduled_date: pickup.scheduled_date,
        status: pickup.status,
        actual_date: pickup.actual_date || '',
        transfer_date: pickup.transfer_date || '',
        next_pickup_date: pickup.next_pickup_date || '',
        target_faskes_id: pickup.target_faskes_id,
        is_outside_city: pickup.is_outside_city,
        notes: pickup.notes || '',
    });

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        patch(route('medication-pickups.update', pickup.id));
    };

    return (
        <>
            <Head title="Edit Pengambilan Obat" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <Pill className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Edit Pengambilan Obat
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
                            clearErrors={clearErrors}
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
                                {processing ? 'Menyimpan...' : 'Update'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
