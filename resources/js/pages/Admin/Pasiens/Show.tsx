import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, UserRound } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatDateDMY } from '@/lib/date';

interface RelationOption {
    id: number;
    name: string;
}

interface Pasien {
    id: number;
    name: string;
    nik: string;
    birth_date: string;
    gender: string;
    faskes?: RelationOption;
    puskesmas?: RelationOption;
    kecamatan?: RelationOption;
    kelurahan?: RelationOption;
    address: string;
    coordinates: string;
    new_address?: string | null;
    weight?: number | null;
    height?: number | null;
    ever_received_assistance: boolean;
    willing_to_help: boolean;
    economic_status: string;
    pekerjaan?: RelationOption | null;
    workplace_name?: string | null;
    workplace_address?: string | null;
    family_head_name: string;
    family_head_nik: string;
    family_head_pekerjaan?: RelationOption | null;
    parent_marital_status?: string | null;
    parenting_pattern?: string | null;
    family_income_range: string;
    respondent: boolean;
    patient_relationship: string;
    tb_so_ro: number;
    treatment_start_date: string;
    visit_date: string;
    information_date: string;
    treatment_status: string;
    transmission_source?: string | null;
    follow_up_plan?: string | null;
    pregnancy_status: boolean;
    comorbid_status: boolean;
    smoking_behavior: boolean;
    family_smoking_status: boolean;
    immunization_status?: string | null;
    nutritional_status: string;
    jkn_ownership: boolean;
    home_area?: string | null;
    house_area?: string | null;
    house_type?: string | null;
    house_status?: string | null;
    home_lighting?: string | null;
    home_humidity?: string | null;
    home_cleanliness?: string | null;
    home_floor?: string | null;
    home_ventilation?: string | null;
    home_ceiling?: string | null;
    home_ceiling_condition?: string | null;
    home_wall?: string | null;
    home_bedroom_window?: string | null;
    home_family_room_window?: string | null;
    home_kitchen_smoke_hole?: string | null;
    home_open_family_room_window?: string | null;
    home_clean_house_habit?: string | null;
    sanitation_clean_water?: string | null;
    sanitation_toilet?: string | null;
    sanitation_wastewater_disposal?: string | null;
    sanitation_garbage_water_disposal?: string | null;
    sanitation_trash?: string | null;
    sanitation_feces_disposal?: string | null;
    sanitation_throw_trash_habit?: string | null;
    sanitation_handwashing_habit?: string | null;
    has_livestock?: boolean | null;
    has_animal_cage?: boolean | null;
    catatan_kebutuhan?: string | null;
    jenis_kebutuhans?: RelationOption[];
    kebutuhans?: Array<{
        id: number;
        need_detail: string;
        verification_status: string;
        jenis_kebutuhan?: RelationOption;
        opd?: RelationOption;
        jenis_penanganan?: RelationOption;
    }>;
}

interface Props {
    pasien: Pasien;
}

function renderValue(value: string | number | null | undefined): string {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    return String(value);
}

function renderBoolean(value: boolean | null | undefined): string {
    if (value === null || value === undefined) {
        return '-';
    }

    return value ? 'Ya' : 'Tidak';
}

function LabelValue({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-md border border-gray-200 bg-white p-3">
            <p className="text-xs font-medium tracking-wide text-gray-500 uppercase">
                {label}
            </p>
            <p className="mt-1 text-sm text-gray-900">{value}</p>
        </div>
    );
}

function Section({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="space-y-4 rounded-lg border border-gray-200 bg-gray-50 bg-white p-4">
            <h2 className="text-lg font-semibold text-gray-900">{title}</h2>
            {children}
        </section>
    );
}

export default function PasienShow({ pasien }: Props) {
    const selectedJenisKebutuhans = pasien.jenis_kebutuhans ?? [];

    return (
        <>
            <Head title={`Detail Pasien - ${pasien.name}`} />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start justify-between gap-4">
                        <div className="flex items-start gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                                <UserRound className="h-6 w-6" />
                            </div>
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">
                                    Detail Pasien
                                </h1>
                            </div>
                        </div>

                        <div className="flex gap-2">
                            <Button asChild variant="outline">
                                <Link href={route('pasiens.index')}>
                                    <ArrowLeft className="h-4 w-4" />
                                    Kembali
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link href={route('pasiens.edit', pasien.id)}>
                                    Edit Pasien
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <Section title="Data Pasien - Data Diri">
                            <div className="grid gap-3 md:grid-cols-3">
                                <LabelValue
                                    label="Nama Pasien"
                                    value={renderValue(pasien.name)}
                                />
                                <LabelValue
                                    label="NIK"
                                    value={renderValue(pasien.nik)}
                                />
                                <LabelValue
                                    label="Tanggal Lahir"
                                    value={formatDateDMY(pasien.birth_date)}
                                />
                                <LabelValue
                                    label="Jenis Kelamin"
                                    value={renderValue(pasien.gender)}
                                />
                                <LabelValue
                                    label="Faskes"
                                    value={renderValue(pasien.faskes?.name)}
                                />
                                <LabelValue
                                    label="Puskesmas"
                                    value={renderValue(pasien.puskesmas?.name)}
                                />
                                <LabelValue
                                    label="Kecamatan"
                                    value={renderValue(pasien.kecamatan?.name)}
                                />
                                <LabelValue
                                    label="Kelurahan"
                                    value={renderValue(pasien.kelurahan?.name)}
                                />
                                <LabelValue
                                    label="Koordinat"
                                    value={renderValue(pasien.coordinates)}
                                />
                                <LabelValue
                                    label="Berat Badan"
                                    value={renderValue(pasien.weight)}
                                />
                                <LabelValue
                                    label="Tinggi Badan"
                                    value={renderValue(pasien.height)}
                                />
                                <LabelValue
                                    label="Pernah Mendapatkan Bantuan"
                                    value={renderBoolean(
                                        pasien.ever_received_assistance,
                                    )}
                                />
                                <LabelValue
                                    label="Bersedia Dibantu"
                                    value={renderBoolean(
                                        pasien.willing_to_help,
                                    )}
                                />
                            </div>
                            <div className="grid gap-3 md:grid-cols-2">
                                <LabelValue
                                    label="Alamat"
                                    value={renderValue(pasien.address)}
                                />
                                <LabelValue
                                    label="Alamat Baru"
                                    value={renderValue(pasien.new_address)}
                                />
                            </div>
                        </Section>

                        <Section title="Data Pasien - Pekerjaan & Keluarga">
                            <div className="grid gap-3 md:grid-cols-3">
                                <LabelValue
                                    label="Status Ekonomi"
                                    value={renderValue(pasien.economic_status)}
                                />
                                <LabelValue
                                    label="Pekerjaan"
                                    value={renderValue(pasien.pekerjaan?.name)}
                                />
                                <LabelValue
                                    label="Nama Tempat Bekerja"
                                    value={renderValue(pasien.workplace_name)}
                                />
                                <LabelValue
                                    label="Alamat Tempat Bekerja"
                                    value={renderValue(
                                        pasien.workplace_address,
                                    )}
                                />
                                <LabelValue
                                    label="Nama Kepala Keluarga"
                                    value={renderValue(pasien.family_head_name)}
                                />
                                <LabelValue
                                    label="NIK Kepala Keluarga"
                                    value={renderValue(pasien.family_head_nik)}
                                />
                                <LabelValue
                                    label="Pekerjaan Kepala Keluarga"
                                    value={renderValue(
                                        pasien.family_head_pekerjaan?.name,
                                    )}
                                />
                                <LabelValue
                                    label="Status Pernikahan Orang Tua"
                                    value={renderValue(
                                        pasien.parent_marital_status,
                                    )}
                                />
                                <LabelValue
                                    label="Pola Asuh"
                                    value={renderValue(
                                        pasien.parenting_pattern,
                                    )}
                                />
                                <LabelValue
                                    label="Pendapatan Keluarga/Bulan"
                                    value={renderValue(
                                        pasien.family_income_range,
                                    )}
                                />
                                <LabelValue
                                    label="Responden"
                                    value={renderBoolean(pasien.respondent)}
                                />
                                <LabelValue
                                    label="Hubungan Pasien"
                                    value={renderValue(
                                        pasien.patient_relationship,
                                    )}
                                />
                            </div>
                        </Section>

                        <Section title="Riwayat Kesehatan">
                            <div className="grid gap-3 md:grid-cols-3">
                                <LabelValue
                                    label="TB SO/RO"
                                    value={renderValue(pasien.tb_so_ro)}
                                />
                                <LabelValue
                                    label="Tanggal Mulai Pengobatan"
                                    value={formatDateDMY(
                                        pasien.treatment_start_date,
                                    )}
                                />
                                <LabelValue
                                    label="Tanggal Kunjungan"
                                    value={formatDateDMY(pasien.visit_date)}
                                />
                                <LabelValue
                                    label="Tanggal Informasi"
                                    value={formatDateDMY(
                                        pasien.information_date,
                                    )}
                                />
                                <LabelValue
                                    label="Status Pengobatan"
                                    value={renderValue(pasien.treatment_status)}
                                />
                                <LabelValue
                                    label="Sumber Penularan"
                                    value={renderValue(
                                        pasien.transmission_source,
                                    )}
                                />
                                <LabelValue
                                    label="Rencana Tindak Lanjut"
                                    value={renderValue(pasien.follow_up_plan)}
                                />
                                <LabelValue
                                    label="Status Hamil"
                                    value={renderBoolean(
                                        pasien.pregnancy_status,
                                    )}
                                />
                                <LabelValue
                                    label="Penyakit Penyerta"
                                    value={renderBoolean(
                                        pasien.comorbid_status,
                                    )}
                                />
                                <LabelValue
                                    label="Perilaku Merokok"
                                    value={renderBoolean(
                                        pasien.smoking_behavior,
                                    )}
                                />
                                <LabelValue
                                    label="Anggota Keluarga Merokok"
                                    value={renderBoolean(
                                        pasien.family_smoking_status,
                                    )}
                                />
                                <LabelValue
                                    label="Status Imunisasi"
                                    value={renderValue(
                                        pasien.immunization_status,
                                    )}
                                />
                                <LabelValue
                                    label="Status Gizi"
                                    value={renderValue(
                                        pasien.nutritional_status,
                                    )}
                                />
                                <LabelValue
                                    label="Kepemilikan JKN"
                                    value={renderBoolean(pasien.jkn_ownership)}
                                />
                            </div>
                        </Section>

                        <Section title="Kebutuhan Penanganan">
                            <div className="mb-3 rounded-md border border-gray-200 bg-white p-3">
                                <p className="text-xs font-medium tracking-wide text-gray-500 uppercase">
                                    Catatan Kebutuhan
                                </p>
                                <p className="mt-1 text-sm text-gray-900">
                                    {renderValue(pasien.catatan_kebutuhan)}
                                </p>
                            </div>
                            <div className="mb-3 rounded-md border border-gray-200 bg-white p-3">
                                <p className="text-xs font-medium tracking-wide text-gray-500 uppercase">
                                    Jenis Kebutuhan
                                </p>
                                <p className="mt-1 text-sm text-gray-900">
                                    {selectedJenisKebutuhans.length > 0
                                        ? selectedJenisKebutuhans
                                              .map((item) => item.name)
                                              .join(', ')
                                        : '-'}
                                </p>
                            </div>

                            {pasien.kebutuhans &&
                            pasien.kebutuhans.length > 0 ? (
                                <div className="space-y-3">
                                    {pasien.kebutuhans.map((item, index) => (
                                        <div
                                            key={item.id}
                                            className="rounded-md border border-gray-200 bg-white p-3"
                                        >
                                            <p className="text-sm font-semibold text-gray-900">
                                                Penanganan {index + 1}
                                            </p>
                                            <div className="mt-2 grid gap-3 md:grid-cols-4">
                                                <LabelValue
                                                    label="Jenis Kebutuhan"
                                                    value={renderValue(
                                                        item.jenis_kebutuhan
                                                            ?.name,
                                                    )}
                                                />
                                                <LabelValue
                                                    label="OPD Terkait"
                                                    value={renderValue(
                                                        item.opd?.name,
                                                    )}
                                                />
                                                <LabelValue
                                                    label="Jenis Penanganan"
                                                    value={renderValue(
                                                        item.jenis_penanganan
                                                            ?.name,
                                                    )}
                                                />
                                                <LabelValue
                                                    label="Status Verifikasi"
                                                    value={renderValue(
                                                        item.verification_status,
                                                    )}
                                                />
                                            </div>
                                            <div className="mt-3">
                                                <LabelValue
                                                    label="Uraian Kebutuhan Detail"
                                                    value={renderValue(
                                                        item.need_detail,
                                                    )}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500">
                                    Belum ada data kebutuhan.
                                </p>
                            )}
                        </Section>

                        <Section title="Data Lainnya - Kondisi Rumah">
                            <div className="grid gap-3 md:grid-cols-3">
                                <LabelValue
                                    label="Luas Area"
                                    value={renderValue(pasien.home_area)}
                                />
                                <LabelValue
                                    label="Luas Rumah"
                                    value={renderValue(pasien.house_area)}
                                />
                                <LabelValue
                                    label="Tipe"
                                    value={renderValue(pasien.house_type)}
                                />
                                <LabelValue
                                    label="Status"
                                    value={renderValue(pasien.house_status)}
                                />
                                <LabelValue
                                    label="Pencahayaan"
                                    value={renderValue(pasien.home_lighting)}
                                />
                                <LabelValue
                                    label="Kelembapan"
                                    value={renderValue(pasien.home_humidity)}
                                />
                                <LabelValue
                                    label="Kebersihan"
                                    value={renderValue(pasien.home_cleanliness)}
                                />
                                <LabelValue
                                    label="Lantai"
                                    value={renderValue(pasien.home_floor)}
                                />
                                <LabelValue
                                    label="Ventilasi"
                                    value={renderValue(pasien.home_ventilation)}
                                />
                                <LabelValue
                                    label="Langit-Langit"
                                    value={renderValue(pasien.home_ceiling)}
                                />
                                <LabelValue
                                    label="Kondisi Langit-Langit"
                                    value={renderValue(
                                        pasien.home_ceiling_condition,
                                    )}
                                />
                                <LabelValue
                                    label="Dinding"
                                    value={renderValue(pasien.home_wall)}
                                />
                                <LabelValue
                                    label="Jendela Kamar Tidur"
                                    value={renderValue(
                                        pasien.home_bedroom_window,
                                    )}
                                />
                                <LabelValue
                                    label="Jendela Ruang Keluarga"
                                    value={renderValue(
                                        pasien.home_family_room_window,
                                    )}
                                />
                                <LabelValue
                                    label="Lubang Asap Dapur"
                                    value={renderValue(
                                        pasien.home_kitchen_smoke_hole,
                                    )}
                                />
                                <LabelValue
                                    label="Membuka Jendela Ruang Keluarga"
                                    value={renderValue(
                                        pasien.home_open_family_room_window,
                                    )}
                                />
                                <LabelValue
                                    label="Membersihkan Rumah"
                                    value={renderValue(
                                        pasien.home_clean_house_habit,
                                    )}
                                />
                            </div>
                        </Section>

                        <Section title="Data Lainnya - Kondisi Sanitasi & Hewan">
                            <div className="grid gap-3 md:grid-cols-3">
                                <LabelValue
                                    label="Sarana Air Bersih"
                                    value={renderValue(
                                        pasien.sanitation_clean_water,
                                    )}
                                />
                                <LabelValue
                                    label="Jamban"
                                    value={renderValue(
                                        pasien.sanitation_toilet,
                                    )}
                                />
                                <LabelValue
                                    label="Sarana Pembuangan Air Limbah"
                                    value={renderValue(
                                        pasien.sanitation_wastewater_disposal,
                                    )}
                                />
                                <LabelValue
                                    label="Sarana Pembuangan Air Sampah"
                                    value={renderValue(
                                        pasien.sanitation_garbage_water_disposal,
                                    )}
                                />
                                <LabelValue
                                    label="Sampah"
                                    value={renderValue(pasien.sanitation_trash)}
                                />
                                <LabelValue
                                    label="Pembuangan Tinja"
                                    value={renderValue(
                                        pasien.sanitation_feces_disposal,
                                    )}
                                />
                                <LabelValue
                                    label="Membuang Sampah"
                                    value={renderValue(
                                        pasien.sanitation_throw_trash_habit,
                                    )}
                                />
                                <LabelValue
                                    label="Kebiasaan Cuci Tangan Pakai Sabun"
                                    value={renderValue(
                                        pasien.sanitation_handwashing_habit,
                                    )}
                                />
                                <LabelValue
                                    label="Memiliki Hewan Ternak"
                                    value={renderBoolean(pasien.has_livestock)}
                                />
                                <LabelValue
                                    label="Memiliki Kandang Hewan"
                                    value={renderBoolean(
                                        pasien.has_animal_cage,
                                    )}
                                />
                            </div>
                        </Section>
                    </div>
                </div>
            </div>
        </>
    );
}
