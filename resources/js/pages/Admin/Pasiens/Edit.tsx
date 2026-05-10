import { Head, useForm } from '@inertiajs/react';
import PasienForm, { PasienFormData, toPasienFormData } from './pasien-form';

interface Option {
    id: number;
    name: string;
}

interface KelurahanOption extends Option {
    kecamatan_id: number;
}

interface Pasien extends Partial<Record<keyof PasienFormData, unknown>> {
    id: number;
}

interface Props {
    pasien: Pasien;
    faskes: Option[];
    puskesmas: Option[];
    kecamatans: Option[];
    kelurahans: KelurahanOption[];
    pekerjaan: Option[];
    opds: Option[];
    jenisPenanganans: Array<Option & { opds?: Option[] }>;
    jenisKebutuhans: Array<Option & { jenis_penanganans?: Option[] }>;
}

export default function PasienEdit({
    pasien,
    faskes,
    puskesmas,
    kecamatans,
    kelurahans,
    pekerjaan,
    opds,
    jenisPenanganans,
    jenisKebutuhans,
}: Props) {
    const { data, setData, patch, processing, errors, clearErrors } =
        useForm<PasienFormData>(toPasienFormData(pasien));

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        patch(route('pasiens.update', pasien.id));
    };

    const updateField = (
        key: keyof PasienFormData,
        value: PasienFormData[keyof PasienFormData],
    ) => {
        setData(key, value);
    };

    return (
        <>
            <Head title="Edit Pasien" />

            <PasienForm
                data={data}
                setData={updateField}
                clearErrors={clearErrors}
                errors={errors}
                processing={processing}
                onSubmit={handleSubmit}
                cancelHref={route('pasiens.index')}
                submitLabel="Update Pasien"
                pageTitle="Edit Pasien"
                faskes={faskes}
                puskesmas={puskesmas}
                kecamatans={kecamatans}
                kelurahans={kelurahans}
                pekerjaan={pekerjaan}
                opds={opds}
                jenisPenanganans={jenisPenanganans}
                jenisKebutuhans={jenisKebutuhans}
            />
        </>
    );
}
