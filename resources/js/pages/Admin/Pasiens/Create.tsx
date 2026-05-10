import { Head, useForm } from '@inertiajs/react';
import PasienForm, {
    createEmptyPasienFormData,
    PasienFormData,
} from './pasien-form';

interface Option {
    id: number;
    name: string;
}

interface KelurahanOption extends Option {
    kecamatan_id: number;
}

interface Props {
    faskes: Option[];
    puskesmas: Option[];
    kecamatans: Option[];
    kelurahans: KelurahanOption[];
    pekerjaan: Option[];
    opds: Option[];
    jenisPenanganans: Array<Option & { opds?: Option[] }>;
    jenisKebutuhans: Array<Option & { jenis_penanganans?: Option[] }>;
}

export default function PasienCreate({
    faskes,
    puskesmas,
    kecamatans,
    kelurahans,
    pekerjaan,
    opds,
    jenisPenanganans,
    jenisKebutuhans,
}: Props) {
    const { data, setData, post, processing, errors, clearErrors } =
        useForm<PasienFormData>(createEmptyPasienFormData());

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(route('pasiens.store'));
    };

    const updateField = (
        key: keyof PasienFormData,
        value: PasienFormData[keyof PasienFormData],
    ) => {
        setData(key, value);
    };

    return (
        <>
            <Head title="Tambah Pasien" />

            <PasienForm
                data={data}
                setData={updateField}
                clearErrors={clearErrors}
                errors={errors}
                processing={processing}
                onSubmit={handleSubmit}
                cancelHref={route('pasiens.index')}
                submitLabel="Simpan Pasien"
                pageTitle="Tambah Pasien"
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
