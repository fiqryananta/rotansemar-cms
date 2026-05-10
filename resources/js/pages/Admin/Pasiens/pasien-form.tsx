import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { UserRound } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Option {
    id: number;
    name: string;
}

interface KelurahanOption extends Option {
    kecamatan_id: number;
}

export interface KebutuhanItem {
    jenis_kebutuhan_id: number | '';
    opd_id: number | '';
    jenis_penanganan_id: number | '';
    need_detail: string;
}

export interface PasienFormData {
    name: string;
    nik: string;
    birth_date: string;
    gender: string;
    faskes_id: number | '';
    puskesmas_id: number | '';
    kecamatan_id: number | '';
    kelurahan_id: number | '';
    address: string;
    coordinates: string;
    new_address: string;
    weight: number | '';
    height: number | '';
    ever_received_assistance: boolean;
    willing_to_help: boolean;
    economic_status: string;
    pekerjaan_id: number | '';
    workplace_name: string;
    workplace_address: string;
    family_head_name: string;
    family_head_nik: string;
    family_head_pekerjaan_id: number | '';
    parent_marital_status: string;
    parenting_pattern: string;
    family_income_range: string;
    respondent: boolean;
    patient_relationship: string;
    tb_so_ro: number | '';
    treatment_start_date: string;
    visit_date: string;
    information_date: string;
    treatment_status: string;
    transmission_source: string;
    follow_up_plan: string;
    pregnancy_status: boolean;
    comorbid_status: boolean;
    smoking_behavior: boolean;
    family_smoking_status: boolean;
    immunization_status: string;
    nutritional_status: string;
    jkn_ownership: boolean;
    home_area: string;
    house_area: string;
    house_type: string;
    house_status: string;
    home_lighting: string;
    home_humidity: string;
    home_cleanliness: string;
    home_floor: string;
    home_ventilation: string;
    home_ceiling: string;
    home_ceiling_condition: string;
    home_wall: string;
    home_bedroom_window: string;
    home_family_room_window: string;
    home_kitchen_smoke_hole: string;
    home_open_family_room_window: string;
    home_clean_house_habit: string;
    sanitation_clean_water: string;
    sanitation_toilet: string;
    sanitation_wastewater_disposal: string;
    sanitation_garbage_water_disposal: string;
    sanitation_trash: string;
    sanitation_feces_disposal: string;
    sanitation_throw_trash_habit: string;
    sanitation_handwashing_habit: string;
    has_livestock: boolean | null;
    has_animal_cage: boolean | null;
    catatan_kebutuhan: string;
    kebutuhans: KebutuhanItem[];
}

interface PasienFormProps {
    data: PasienFormData;
    setData: (
        key: keyof PasienFormData,
        value: PasienFormData[keyof PasienFormData],
    ) => void;
    clearErrors?: (...fields: string[]) => void;
    errors: Record<string, string | undefined>;
    processing: boolean;
    onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
    cancelHref: string;
    submitLabel: string;
    pageTitle: string;
    faskes: Option[];
    puskesmas: Option[];
    kecamatans: Option[];
    kelurahans: KelurahanOption[];
    pekerjaan: Option[];
    opds: Option[];
    jenisPenanganans: Array<Option & { opds?: Option[] }>;
    jenisKebutuhans: Array<Option & { jenis_penanganans?: Option[] }>;
}

const fieldTabMap: Partial<
    Record<
        keyof PasienFormData,
        'data-pasien' | 'kebutuhan' | 'riwayat' | 'lainnya'
    >
> = {
    name: 'data-pasien',
    nik: 'data-pasien',
    birth_date: 'data-pasien',
    gender: 'data-pasien',
    faskes_id: 'data-pasien',
    puskesmas_id: 'data-pasien',
    kecamatan_id: 'data-pasien',
    kelurahan_id: 'data-pasien',
    address: 'data-pasien',
    coordinates: 'data-pasien',
    new_address: 'data-pasien',
    weight: 'data-pasien',
    height: 'data-pasien',
    ever_received_assistance: 'data-pasien',
    willing_to_help: 'data-pasien',
    economic_status: 'data-pasien',
    pekerjaan_id: 'data-pasien',
    workplace_name: 'data-pasien',
    workplace_address: 'data-pasien',
    family_head_name: 'data-pasien',
    family_head_nik: 'data-pasien',
    family_head_pekerjaan_id: 'data-pasien',
    parent_marital_status: 'data-pasien',
    parenting_pattern: 'data-pasien',
    family_income_range: 'data-pasien',
    respondent: 'data-pasien',
    patient_relationship: 'data-pasien',
    tb_so_ro: 'riwayat',
    treatment_start_date: 'riwayat',
    visit_date: 'riwayat',
    information_date: 'riwayat',
    treatment_status: 'riwayat',
    transmission_source: 'riwayat',
    follow_up_plan: 'riwayat',
    pregnancy_status: 'riwayat',
    comorbid_status: 'riwayat',
    smoking_behavior: 'riwayat',
    family_smoking_status: 'riwayat',
    immunization_status: 'riwayat',
    nutritional_status: 'riwayat',
    jkn_ownership: 'riwayat',
    home_area: 'lainnya',
    house_area: 'lainnya',
    house_type: 'lainnya',
    house_status: 'lainnya',
    home_lighting: 'lainnya',
    home_humidity: 'lainnya',
    home_cleanliness: 'lainnya',
    home_floor: 'lainnya',
    home_ventilation: 'lainnya',
    home_ceiling: 'lainnya',
    home_ceiling_condition: 'lainnya',
    home_wall: 'lainnya',
    home_bedroom_window: 'lainnya',
    home_family_room_window: 'lainnya',
    home_kitchen_smoke_hole: 'lainnya',
    home_open_family_room_window: 'lainnya',
    home_clean_house_habit: 'lainnya',
    sanitation_clean_water: 'lainnya',
    sanitation_toilet: 'lainnya',
    sanitation_wastewater_disposal: 'lainnya',
    sanitation_garbage_water_disposal: 'lainnya',
    sanitation_trash: 'lainnya',
    sanitation_feces_disposal: 'lainnya',
    sanitation_throw_trash_habit: 'lainnya',
    sanitation_handwashing_habit: 'lainnya',
    has_livestock: 'lainnya',
    has_animal_cage: 'lainnya',
    catatan_kebutuhan: 'kebutuhan',
    kebutuhans: 'kebutuhan',
};

const textAreaClassName =
    'mt-1 min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]';

export function createEmptyPasienFormData(): PasienFormData {
    return {
        name: '',
        nik: '',
        birth_date: '',
        gender: '',
        faskes_id: '',
        puskesmas_id: '',
        kecamatan_id: '',
        kelurahan_id: '',
        address: '',
        coordinates: '',
        new_address: '',
        weight: '',
        height: '',
        ever_received_assistance: false,
        willing_to_help: false,
        economic_status: '',
        pekerjaan_id: '',
        workplace_name: '',
        workplace_address: '',
        family_head_name: '',
        family_head_nik: '',
        family_head_pekerjaan_id: '',
        parent_marital_status: '',
        parenting_pattern: '',
        family_income_range: '',
        respondent: false,
        patient_relationship: '',
        tb_so_ro: '',
        treatment_start_date: '',
        visit_date: '',
        information_date: '',
        treatment_status: '',
        transmission_source: '',
        follow_up_plan: '',
        pregnancy_status: false,
        comorbid_status: false,
        smoking_behavior: false,
        family_smoking_status: false,
        immunization_status: '',
        nutritional_status: '',
        jkn_ownership: false,
        home_area: '',
        house_area: '',
        house_type: '',
        house_status: '',
        home_lighting: '',
        home_humidity: '',
        home_cleanliness: '',
        home_floor: '',
        home_ventilation: '',
        home_ceiling: '',
        home_ceiling_condition: '',
        home_wall: '',
        home_bedroom_window: '',
        home_family_room_window: '',
        home_kitchen_smoke_hole: '',
        home_open_family_room_window: '',
        home_clean_house_habit: '',
        sanitation_clean_water: '',
        sanitation_toilet: '',
        sanitation_wastewater_disposal: '',
        sanitation_garbage_water_disposal: '',
        sanitation_trash: '',
        sanitation_feces_disposal: '',
        sanitation_throw_trash_habit: '',
        sanitation_handwashing_habit: '',
        has_livestock: null,
        has_animal_cage: null,
        catatan_kebutuhan: '',
        kebutuhans: [
            {
                jenis_kebutuhan_id: '',
                opd_id: '',
                jenis_penanganan_id: '',
                need_detail: '',
            },
        ],
    };
}

export function toPasienFormData(
    raw: Partial<Record<keyof PasienFormData, unknown>>,
): PasienFormData {
    const base = createEmptyPasienFormData();

    const toNumber = (value: unknown): number | '' => {
        if (typeof value === 'number') {
            return value;
        }

        if (typeof value === 'string' && value.trim() !== '') {
            return Number(value);
        }

        return '';
    };

    const toNullableBool = (value: unknown): boolean | null => {
        if (value === null || value === undefined || value === '') {
            return null;
        }

        if (typeof value === 'boolean') {
            return value;
        }

        return String(value) === '1';
    };

    const toBool = (value: unknown): boolean => {
        if (typeof value === 'boolean') {
            return value;
        }

        return String(value) === '1' || String(value).toLowerCase() === 'true';
    };

    const toStringValue = (value: unknown): string => {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value);
    };

    const toKebutuhans = (value: unknown): KebutuhanItem[] => {
        if (!Array.isArray(value) || value.length === 0) {
            return base.kebutuhans;
        }

        return value.map((item) => {
            const row = (item ?? {}) as Record<string, unknown>;

            return {
                jenis_kebutuhan_id: toNumber(row.jenis_kebutuhan_id),
                opd_id: toNumber(row.opd_id),
                jenis_penanganan_id: toNumber(row.jenis_penanganan_id),
                need_detail: toStringValue(row.need_detail),
            };
        });
    };

    return {
        ...base,
        ...raw,
        name: toStringValue(raw.name),
        nik: toStringValue(raw.nik),
        birth_date: toStringValue(raw.birth_date),
        gender: toStringValue(raw.gender),
        faskes_id: toNumber(raw.faskes_id),
        puskesmas_id: toNumber(raw.puskesmas_id),
        kecamatan_id: toNumber(raw.kecamatan_id),
        kelurahan_id: toNumber(raw.kelurahan_id),
        address: toStringValue(raw.address),
        coordinates: toStringValue(raw.coordinates),
        new_address: toStringValue(raw.new_address),
        weight: toNumber(raw.weight),
        height: toNumber(raw.height),
        ever_received_assistance: toBool(raw.ever_received_assistance),
        willing_to_help: toBool(raw.willing_to_help),
        economic_status: toStringValue(raw.economic_status),
        pekerjaan_id: toNumber(raw.pekerjaan_id),
        workplace_name: toStringValue(raw.workplace_name),
        workplace_address: toStringValue(raw.workplace_address),
        family_head_name: toStringValue(raw.family_head_name),
        family_head_nik: toStringValue(raw.family_head_nik),
        family_head_pekerjaan_id: toNumber(raw.family_head_pekerjaan_id),
        parent_marital_status: toStringValue(raw.parent_marital_status),
        parenting_pattern: toStringValue(raw.parenting_pattern),
        family_income_range: toStringValue(raw.family_income_range),
        respondent: toBool(raw.respondent),
        patient_relationship: toStringValue(raw.patient_relationship),
        tb_so_ro: toNumber(raw.tb_so_ro),
        treatment_start_date: toStringValue(raw.treatment_start_date),
        visit_date: toStringValue(raw.visit_date),
        information_date: toStringValue(raw.information_date),
        treatment_status: toStringValue(raw.treatment_status),
        transmission_source: toStringValue(raw.transmission_source),
        follow_up_plan: toStringValue(raw.follow_up_plan),
        pregnancy_status: toBool(raw.pregnancy_status),
        comorbid_status: toBool(raw.comorbid_status),
        smoking_behavior: toBool(raw.smoking_behavior),
        family_smoking_status: toBool(raw.family_smoking_status),
        immunization_status: toStringValue(raw.immunization_status),
        nutritional_status: toStringValue(raw.nutritional_status),
        jkn_ownership: toBool(raw.jkn_ownership),
        home_area: toStringValue(raw.home_area),
        house_area: toStringValue(raw.house_area),
        house_type: toStringValue(raw.house_type),
        house_status: toStringValue(raw.house_status),
        home_lighting: toStringValue(raw.home_lighting),
        home_humidity: toStringValue(raw.home_humidity),
        home_cleanliness: toStringValue(raw.home_cleanliness),
        home_floor: toStringValue(raw.home_floor),
        home_ventilation: toStringValue(raw.home_ventilation),
        home_ceiling: toStringValue(raw.home_ceiling),
        home_ceiling_condition: toStringValue(raw.home_ceiling_condition),
        home_wall: toStringValue(raw.home_wall),
        home_bedroom_window: toStringValue(raw.home_bedroom_window),
        home_family_room_window: toStringValue(raw.home_family_room_window),
        home_kitchen_smoke_hole: toStringValue(raw.home_kitchen_smoke_hole),
        home_open_family_room_window: toStringValue(
            raw.home_open_family_room_window,
        ),
        home_clean_house_habit: toStringValue(raw.home_clean_house_habit),
        sanitation_clean_water: toStringValue(raw.sanitation_clean_water),
        sanitation_toilet: toStringValue(raw.sanitation_toilet),
        sanitation_wastewater_disposal: toStringValue(
            raw.sanitation_wastewater_disposal,
        ),
        sanitation_garbage_water_disposal: toStringValue(
            raw.sanitation_garbage_water_disposal,
        ),
        sanitation_trash: toStringValue(raw.sanitation_trash),
        sanitation_feces_disposal: toStringValue(raw.sanitation_feces_disposal),
        sanitation_throw_trash_habit: toStringValue(
            raw.sanitation_throw_trash_habit,
        ),
        sanitation_handwashing_habit: toStringValue(
            raw.sanitation_handwashing_habit,
        ),
        has_livestock: toNullableBool(raw.has_livestock),
        has_animal_cage: toNullableBool(raw.has_animal_cage),
        catatan_kebutuhan: toStringValue(raw.catatan_kebutuhan),
        kebutuhans: toKebutuhans(raw.kebutuhans),
    };
}

function renderError(message?: string) {
    if (!message) {
        return null;
    }

    return <p className="mt-1 text-sm text-red-600">{message}</p>;
}

export default function PasienForm({
    data,
    setData,
    clearErrors,
    errors,
    processing,
    onSubmit,
    cancelHref,
    submitLabel,
    pageTitle,
    faskes,
    puskesmas,
    kecamatans,
    kelurahans,
    pekerjaan,
    opds,
    jenisPenanganans,
    jenisKebutuhans,
}: PasienFormProps) {
    const tabOrder: Array<'data-pasien' | 'riwayat' | 'kebutuhan' | 'lainnya'> =
        ['data-pasien', 'riwayat', 'kebutuhan', 'lainnya'];
    const [activeTab, setActiveTab] = useState<
        'data-pasien' | 'kebutuhan' | 'riwayat' | 'lainnya'
    >('data-pasien');
    const [isLocating, setIsLocating] = useState(false);

    const currentTabIndex = tabOrder.indexOf(activeTab);
    const isFirstTab = currentTabIndex <= 0;
    const isLastTab = currentTabIndex === tabOrder.length - 1;

    const handleFormSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        if (!isLastTab) {
            event.preventDefault();
            setActiveTab(
                tabOrder[Math.min(currentTabIndex + 1, tabOrder.length - 1)],
            );
            return;
        }

        onSubmit(event);
    };

    const errorEntries = useMemo(() => {
        return Object.entries(errors).filter(([, value]) => Boolean(value));
    }, [errors]);

    // Keep tab navigation fully manual. Do not auto-jump tabs when errors exist.

    const addKebutuhan = () => {
        setData('kebutuhans', [
            ...data.kebutuhans,
            {
                jenis_kebutuhan_id: '',
                opd_id: '',
                jenis_penanganan_id: '',
                need_detail: '',
            },
        ]);
    };

    const removeKebutuhan = (index: number) => {
        if (data.kebutuhans.length <= 1) {
            return;
        }

        setData(
            'kebutuhans',
            data.kebutuhans.filter((_, itemIndex) => itemIndex !== index),
        );
    };

    const updateKebutuhan = (
        index: number,
        key: keyof KebutuhanItem,
        value: KebutuhanItem[keyof KebutuhanItem],
    ) => {
        const nextRows: KebutuhanItem[] = data.kebutuhans.map(
            (item, itemIndex) => {
                if (itemIndex !== index) {
                    return item;
                }

                if (key === 'opd_id') {
                    return {
                        ...item,
                        opd_id: value as number | '',
                        jenis_penanganan_id: '',
                    };
                }

                if (key === 'jenis_kebutuhan_id') {
                    return {
                        ...item,
                        jenis_kebutuhan_id: value as number | '',
                        opd_id: '',
                        jenis_penanganan_id: '',
                    };
                }

                if (key === 'jenis_penanganan_id') {
                    return {
                        ...item,
                        jenis_penanganan_id: value as number | '',
                    };
                }

                return {
                    ...item,
                    need_detail: value as string,
                };
            },
        );

        setData('kebutuhans', nextRows);
        clearErrors?.('kebutuhans', `kebutuhans.${index}.${String(key)}`);
    };

    // OPDs available for the selected jenis kebutuhan (via its linked jenis penanganans)
    const getOpdOptions = (jenisKebutuhanId: number | '') => {
        if (jenisKebutuhanId === '') return [];

        const selectedJK = jenisKebutuhans.find(
            (jk) => jk.id === jenisKebutuhanId,
        );
        if (!selectedJK?.jenis_penanganans?.length) return [];

        const linkedJPIds = new Set(
            selectedJK.jenis_penanganans.map((jp) => jp.id),
        );
        const linkedJPs = jenisPenanganans.filter((jp) =>
            linkedJPIds.has(jp.id),
        );
        const availableOpdIds = new Set(
            linkedJPs.flatMap((jp) => (jp.opds ?? []).map((opd) => opd.id)),
        );

        return opds.filter((opd) => availableOpdIds.has(opd.id));
    };

    // Jenis Penanganan available for the selected jenis kebutuhan + opd
    const getJenisPenangananOptions = (
        jenisKebutuhanId: number | '',
        opdId: number | '',
    ) => {
        if (jenisKebutuhanId === '') return [];

        const selectedJK = jenisKebutuhans.find(
            (jk) => jk.id === jenisKebutuhanId,
        );
        if (!selectedJK?.jenis_penanganans?.length) return [];

        const linkedJPIds = new Set(
            selectedJK.jenis_penanganans.map((jp) => jp.id),
        );
        let linked = jenisPenanganans.filter((jp) => linkedJPIds.has(jp.id));

        if (opdId !== '') {
            linked = linked.filter((jp) =>
                (jp.opds ?? []).some((opd) => opd.id === opdId),
            );
        }

        return linked;
    };

    const filteredKelurahans = useMemo(() => {
        if (!data.kecamatan_id) {
            return kelurahans;
        }

        return kelurahans.filter(
            (item) => item.kecamatan_id === data.kecamatan_id,
        );
    }, [data.kecamatan_id, kelurahans]);

    const setString =
        (key: keyof PasienFormData) =>
        (
            event: React.ChangeEvent<
                HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
            >,
        ) => {
            setData(key, event.target.value);
            clearErrors?.(String(key));
        };

    const setNumber =
        (key: keyof PasienFormData) =>
        (event: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
            const value = event.target.value;
            setData(key, value === '' ? '' : Number(value));
            clearErrors?.(String(key));
        };

    const setBoolean =
        (key: keyof PasienFormData) =>
        (event: React.ChangeEvent<HTMLSelectElement>) => {
            setData(key, event.target.value === '1');
            clearErrors?.(String(key));
        };

    const setNullableBoolean =
        (key: keyof PasienFormData) =>
        (event: React.ChangeEvent<HTMLSelectElement>) => {
            const value = event.target.value;
            if (value === '') {
                setData(key, null);
                clearErrors?.(String(key));
                return;
            }

            setData(key, value === '1');
            clearErrors?.(String(key));
        };

    const fillCoordinateFromCurrentLocation = () => {
        if (!navigator.geolocation) {
            return;
        }

        setIsLocating(true);

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = position.coords.latitude.toFixed(6);
                const longitude = position.coords.longitude.toFixed(6);
                setData('coordinates', `${latitude}, ${longitude}`);
                setIsLocating(false);
            },
            () => {
                setIsLocating(false);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
            },
        );
    };

    return (
        <div className="bg-gray-50">
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="mb-8 flex items-start gap-4">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                        <UserRound className="h-6 w-6" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">
                            {pageTitle}
                        </h1>
                    </div>
                </div>

                <form
                    onSubmit={handleFormSubmit}
                    className="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                >
                    {errorEntries.length > 0 && (
                        <div className="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            <p className="font-semibold">
                                Data belum bisa disimpan. Periksa field berikut:
                            </p>
                            <ul className="mt-1 list-disc pl-5">
                                {errorEntries
                                    .slice(0, 6)
                                    .map(([key, value]) => (
                                        <li key={String(key)}>{value}</li>
                                    ))}
                            </ul>
                        </div>
                    )}

                    <div className="flex flex-wrap gap-2 rounded-lg bg-gray-100 p-2">
                        <button
                            type="button"
                            onClick={() => setActiveTab('data-pasien')}
                            className={`rounded-md px-4 py-2 text-sm font-medium ${activeTab === 'data-pasien' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'}`}
                        >
                            Data Pasien
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveTab('riwayat')}
                            className={`rounded-md px-4 py-2 text-sm font-medium ${activeTab === 'riwayat' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'}`}
                        >
                            Riwayat Kesehatan
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveTab('kebutuhan')}
                            className={`rounded-md px-4 py-2 text-sm font-medium ${activeTab === 'kebutuhan' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'}`}
                        >
                            Kebutuhan
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveTab('lainnya')}
                            className={`rounded-md px-4 py-2 text-sm font-medium ${activeTab === 'lainnya' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'}`}
                        >
                            Data Lainnya
                        </button>
                    </div>

                    {activeTab === 'data-pasien' && (
                        <div className="space-y-8">
                            <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Data Diri
                                </h2>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="name">
                                            Nama Pasien
                                        </Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={setString('name')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.name)}
                                    </div>
                                    <div>
                                        <Label htmlFor="nik">
                                            NIK (16 Digit)
                                        </Label>
                                        <Input
                                            id="nik"
                                            value={data.nik}
                                            onChange={setString('nik')}
                                            maxLength={16}
                                            className="mt-1"
                                        />
                                        {renderError(errors.nik)}
                                    </div>
                                    <div>
                                        <Label htmlFor="birth_date">
                                            Tanggal Lahir
                                        </Label>
                                        <DatePicker
                                            id="birth_date"
                                            value={data.birth_date}
                                            onChange={(value) =>
                                                setData('birth_date', value)
                                            }
                                            className="mt-1"
                                        />
                                        {renderError(errors.birth_date)}
                                    </div>
                                    <div>
                                        <Label htmlFor="gender">
                                            Jenis Kelamin
                                        </Label>
                                        <select
                                            id="gender"
                                            value={data.gender}
                                            onChange={setString('gender')}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih jenis kelamin
                                            </option>
                                            <option value="laki-laki">
                                                Laki-Laki
                                            </option>
                                            <option value="perempuan">
                                                Perempuan
                                            </option>
                                        </select>
                                        {renderError(errors.gender)}
                                    </div>
                                    <div>
                                        <Label htmlFor="faskes_id">
                                            Faskes
                                        </Label>
                                        <select
                                            id="faskes_id"
                                            value={
                                                data.faskes_id === ''
                                                    ? ''
                                                    : String(data.faskes_id)
                                            }
                                            onChange={setNumber('faskes_id')}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih faskes
                                            </option>
                                            {faskes.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                        {renderError(errors.faskes_id)}
                                    </div>
                                    <div>
                                        <Label htmlFor="puskesmas_id">
                                            Puskesmas
                                        </Label>
                                        <select
                                            id="puskesmas_id"
                                            value={
                                                data.puskesmas_id === ''
                                                    ? ''
                                                    : String(data.puskesmas_id)
                                            }
                                            onChange={setNumber('puskesmas_id')}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih puskesmas
                                            </option>
                                            {puskesmas.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                        {renderError(errors.puskesmas_id)}
                                    </div>
                                    <div>
                                        <Label htmlFor="kecamatan_id">
                                            Kecamatan
                                        </Label>
                                        <select
                                            id="kecamatan_id"
                                            value={
                                                data.kecamatan_id === ''
                                                    ? ''
                                                    : String(data.kecamatan_id)
                                            }
                                            onChange={(event) => {
                                                const value =
                                                    event.target.value;
                                                const nextKecamatan =
                                                    value === ''
                                                        ? ''
                                                        : Number(value);
                                                setData(
                                                    'kecamatan_id',
                                                    nextKecamatan,
                                                );
                                                clearErrors?.('kecamatan_id');

                                                if (data.kelurahan_id === '') {
                                                    return;
                                                }

                                                const selectedKelurahan =
                                                    kelurahans.find(
                                                        (item) =>
                                                            item.id ===
                                                            data.kelurahan_id,
                                                    );
                                                if (
                                                    !selectedKelurahan ||
                                                    selectedKelurahan.kecamatan_id !==
                                                        nextKecamatan
                                                ) {
                                                    setData('kelurahan_id', '');
                                                    clearErrors?.('kelurahan_id');
                                                }
                                            }}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih kecamatan
                                            </option>
                                            {kecamatans.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                        {renderError(errors.kecamatan_id)}
                                    </div>
                                    <div>
                                        <Label htmlFor="kelurahan_id">
                                            Kelurahan
                                        </Label>
                                        <select
                                            id="kelurahan_id"
                                            value={
                                                data.kelurahan_id === ''
                                                    ? ''
                                                    : String(data.kelurahan_id)
                                            }
                                            onChange={setNumber('kelurahan_id')}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih kelurahan
                                            </option>
                                            {filteredKelurahans.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                        {renderError(errors.kelurahan_id)}
                                    </div>
                                </div>
                                <div>
                                    <Label htmlFor="address">Alamat</Label>
                                    <textarea
                                        id="address"
                                        value={data.address}
                                        onChange={setString('address')}
                                        className={textAreaClassName}
                                    />
                                    {renderError(errors.address)}
                                </div>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="coordinates">
                                            Koordinat (Lat, Long / Maps Picker)
                                        </Label>
                                        <Input
                                            id="coordinates"
                                            value={data.coordinates}
                                            onChange={setString('coordinates')}
                                            className="mt-1"
                                        />
                                        <div className="mt-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={
                                                    fillCoordinateFromCurrentLocation
                                                }
                                                disabled={isLocating}
                                            >
                                                {isLocating
                                                    ? 'Mengambil lokasi...'
                                                    : 'Ambil dari Lokasi Saat Ini'}
                                            </Button>
                                        </div>
                                        {renderError(errors.coordinates)}
                                    </div>
                                    <div>
                                        <Label htmlFor="new_address">
                                            Alamat Baru (Opsional)
                                        </Label>
                                        <Input
                                            id="new_address"
                                            value={data.new_address}
                                            onChange={setString('new_address')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.new_address)}
                                    </div>
                                    <div>
                                        <Label htmlFor="weight">
                                            Berat Badan (kg)
                                        </Label>
                                        <Input
                                            id="weight"
                                            type="number"
                                            min={0}
                                            value={data.weight}
                                            onChange={setNumber('weight')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.weight)}
                                    </div>
                                    <div>
                                        <Label htmlFor="height">
                                            Tinggi Badan (cm)
                                        </Label>
                                        <Input
                                            id="height"
                                            type="number"
                                            min={0}
                                            value={data.height}
                                            onChange={setNumber('height')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.height)}
                                    </div>
                                    <div>
                                        <Label htmlFor="ever_received_assistance">
                                            Pernah Mendapatkan Bantuan
                                        </Label>
                                        <select
                                            id="ever_received_assistance"
                                            value={
                                                data.ever_received_assistance
                                                    ? '1'
                                                    : '0'
                                            }
                                            onChange={setBoolean(
                                                'ever_received_assistance',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                        {renderError(
                                            errors.ever_received_assistance,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="willing_to_help">
                                            Bersedia Dibantu
                                        </Label>
                                        <select
                                            id="willing_to_help"
                                            value={
                                                data.willing_to_help ? '1' : '0'
                                            }
                                            onChange={setBoolean(
                                                'willing_to_help',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                        {renderError(errors.willing_to_help)}
                                    </div>
                                </div>
                            </section>

                            <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Pekerjaan
                                </h2>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="economic_status">
                                            Status Ekonomi
                                        </Label>
                                        <select
                                            id="economic_status"
                                            value={data.economic_status}
                                            onChange={setString(
                                                'economic_status',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih status ekonomi
                                            </option>
                                            <option value="miskin">
                                                Miskin
                                            </option>
                                            <option value="sederhana">
                                                Sederhana
                                            </option>
                                            <option value="mampu">Mampu</option>
                                        </select>
                                        {renderError(errors.economic_status)}
                                    </div>
                                    <div>
                                        <Label htmlFor="pekerjaan_id">
                                            Pekerjaan
                                        </Label>
                                        <select
                                            id="pekerjaan_id"
                                            value={
                                                data.pekerjaan_id === ''
                                                    ? ''
                                                    : String(data.pekerjaan_id)
                                            }
                                            onChange={setNumber('pekerjaan_id')}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih pekerjaan
                                            </option>
                                            {pekerjaan.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                        {renderError(errors.pekerjaan_id)}
                                    </div>
                                    <div>
                                        <Label htmlFor="workplace_name">
                                            Nama Tempat Bekerja
                                        </Label>
                                        <Input
                                            id="workplace_name"
                                            value={data.workplace_name}
                                            onChange={setString(
                                                'workplace_name',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.workplace_name)}
                                    </div>
                                    <div>
                                        <Label htmlFor="workplace_address">
                                            Alamat Tempat Bekerja
                                        </Label>
                                        <Input
                                            id="workplace_address"
                                            value={data.workplace_address}
                                            onChange={setString(
                                                'workplace_address',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.workplace_address)}
                                    </div>
                                </div>
                            </section>

                            <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Keluarga
                                </h2>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="family_head_name">
                                            Nama Kepala Keluarga
                                        </Label>
                                        <Input
                                            id="family_head_name"
                                            value={data.family_head_name}
                                            onChange={setString(
                                                'family_head_name',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.family_head_name)}
                                    </div>
                                    <div>
                                        <Label htmlFor="family_head_nik">
                                            NIK Kepala Keluarga
                                        </Label>
                                        <Input
                                            id="family_head_nik"
                                            value={data.family_head_nik}
                                            onChange={setString(
                                                'family_head_nik',
                                            )}
                                            maxLength={16}
                                            className="mt-1"
                                        />
                                        {renderError(errors.family_head_nik)}
                                    </div>
                                    <div>
                                        <Label htmlFor="family_head_pekerjaan_id">
                                            Pekerjaan Kepala Keluarga
                                        </Label>
                                        <select
                                            id="family_head_pekerjaan_id"
                                            value={
                                                data.family_head_pekerjaan_id ===
                                                ''
                                                    ? ''
                                                    : String(
                                                          data.family_head_pekerjaan_id,
                                                      )
                                            }
                                            onChange={setNumber(
                                                'family_head_pekerjaan_id',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih pekerjaan
                                            </option>
                                            {pekerjaan.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                        {renderError(
                                            errors.family_head_pekerjaan_id,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="parent_marital_status">
                                            Status Pernikahan Orang Tua
                                        </Label>
                                        <select
                                            id="parent_marital_status"
                                            value={data.parent_marital_status}
                                            onChange={setString(
                                                'parent_marital_status',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih status
                                            </option>
                                            <option value="menikah">
                                                Menikah
                                            </option>
                                            <option value="cerai">Cerai</option>
                                        </select>
                                        {renderError(
                                            errors.parent_marital_status,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="parenting_pattern">
                                            Pola Asuh
                                        </Label>
                                        <Input
                                            id="parenting_pattern"
                                            value={data.parenting_pattern}
                                            onChange={setString(
                                                'parenting_pattern',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.parenting_pattern)}
                                    </div>
                                    <div>
                                        <Label htmlFor="family_income_range">
                                            Total Pendapatan Keluarga / Bulan
                                        </Label>
                                        <select
                                            id="family_income_range"
                                            value={data.family_income_range}
                                            onChange={setString(
                                                'family_income_range',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih range
                                            </option>
                                            <option value="<1000000">
                                                {'< 1.000.000'}
                                            </option>
                                            <option value=">1000000">
                                                {'> 1.000.000'}
                                            </option>
                                        </select>
                                        {renderError(
                                            errors.family_income_range,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="respondent">
                                            Responden
                                        </Label>
                                        <select
                                            id="respondent"
                                            value={data.respondent ? '1' : '0'}
                                            onChange={setBoolean('respondent')}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                        {renderError(errors.respondent)}
                                    </div>
                                    <div>
                                        <Label htmlFor="patient_relationship">
                                            Hubungan Pasien
                                        </Label>
                                        <select
                                            id="patient_relationship"
                                            value={data.patient_relationship}
                                            onChange={setString(
                                                'patient_relationship',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">
                                                Pilih hubungan
                                            </option>
                                            <option value="pasien-sendiri">
                                                Pasien Sendiri
                                            </option>
                                            <option value="orang-tua">
                                                Orang Tua
                                            </option>
                                            <option value="anak">Anak</option>
                                        </select>
                                        {renderError(
                                            errors.patient_relationship,
                                        )}
                                    </div>
                                </div>
                            </section>
                        </div>
                    )}

                    {activeTab === 'kebutuhan' && (
                        <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 className="text-lg font-semibold text-gray-900">
                                        Kebutuhan
                                    </h2>
                                    <p className="text-sm text-gray-500">
                                        Tambahkan penanganan per OPD sesuai
                                        kebutuhan pasien.
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={addKebutuhan}
                                >
                                    Tambah Penanganan
                                </Button>
                            </div>

                            <div>
                                <Label htmlFor="catatan_kebutuhan">
                                    Catatan Kebutuhan (Opsional)
                                </Label>
                                <textarea
                                    id="catatan_kebutuhan"
                                    value={data.catatan_kebutuhan}
                                    onChange={setString('catatan_kebutuhan')}
                                    placeholder="Tuliskan catatan umum mengenai kebutuhan pasien jika ada..."
                                    className={textAreaClassName}
                                />
                                {renderError(errors.catatan_kebutuhan)}
                            </div>

                            {errors.kebutuhans && (
                                <p className="text-sm text-red-600">
                                    {errors.kebutuhans}
                                </p>
                            )}

                            <div className="space-y-4">
                                {data.kebutuhans.map((item, index) => {
                                    const opdOptions = getOpdOptions(
                                        item.jenis_kebutuhan_id,
                                    );
                                    const jenisOptions =
                                        getJenisPenangananOptions(
                                            item.jenis_kebutuhan_id,
                                            item.opd_id,
                                        );

                                    return (
                                        <div
                                            key={index}
                                            className="rounded-lg border border-gray-200 bg-gray-50 p-4"
                                        >
                                            <div className="mb-3 flex items-center justify-between">
                                                <h3 className="text-sm font-semibold text-gray-700">
                                                    Penanganan {index + 1}
                                                </h3>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onClick={() =>
                                                        removeKebutuhan(index)
                                                    }
                                                    disabled={
                                                        data.kebutuhans
                                                            .length <= 1
                                                    }
                                                >
                                                    Hapus
                                                </Button>
                                            </div>

                                            <div className="grid gap-4 md:grid-cols-3">
                                                <div>
                                                    <Label
                                                        htmlFor={`kebutuhan-jenis-kebutuhan-${index}`}
                                                    >
                                                        Jenis Kebutuhan
                                                    </Label>
                                                    <select
                                                        id={`kebutuhan-jenis-kebutuhan-${index}`}
                                                        value={
                                                            item.jenis_kebutuhan_id ===
                                                            ''
                                                                ? ''
                                                                : String(
                                                                      item.jenis_kebutuhan_id,
                                                                  )
                                                        }
                                                        onChange={(event) => {
                                                            const value =
                                                                event.target
                                                                    .value;
                                                            updateKebutuhan(
                                                                index,
                                                                'jenis_kebutuhan_id',
                                                                value === ''
                                                                    ? ''
                                                                    : Number(
                                                                          value,
                                                                      ),
                                                            );
                                                        }}
                                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                                    >
                                                        <option value="">
                                                            Pilih Jenis
                                                            Kebutuhan
                                                        </option>
                                                        {jenisKebutuhans.map(
                                                            (jk) => (
                                                                <option
                                                                    key={jk.id}
                                                                    value={
                                                                        jk.id
                                                                    }
                                                                >
                                                                    {jk.name}
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                    {renderError(
                                                        errors[
                                                            `kebutuhans.${index}.jenis_kebutuhan_id`
                                                        ],
                                                    )}
                                                </div>

                                                <div>
                                                    <Label
                                                        htmlFor={`kebutuhan-opd-${index}`}
                                                    >
                                                        OPD Terkait
                                                    </Label>
                                                    <select
                                                        id={`kebutuhan-opd-${index}`}
                                                        value={
                                                            item.opd_id === ''
                                                                ? ''
                                                                : String(
                                                                      item.opd_id,
                                                                  )
                                                        }
                                                        onChange={(event) => {
                                                            const value =
                                                                event.target
                                                                    .value;
                                                            updateKebutuhan(
                                                                index,
                                                                'opd_id',
                                                                value === ''
                                                                    ? ''
                                                                    : Number(
                                                                          value,
                                                                      ),
                                                            );
                                                        }}
                                                        disabled={
                                                            item.jenis_kebutuhan_id ===
                                                                '' ||
                                                            opdOptions.length ===
                                                                0
                                                        }
                                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        <option value="">
                                                            {item.jenis_kebutuhan_id ===
                                                            ''
                                                                ? 'Pilih jenis kebutuhan dulu'
                                                                : opdOptions.length ===
                                                                    0
                                                                  ? 'Tidak ada OPD tersedia'
                                                                  : 'Pilih OPD'}
                                                        </option>
                                                        {opdOptions.map(
                                                            (opd) => (
                                                                <option
                                                                    key={opd.id}
                                                                    value={
                                                                        opd.id
                                                                    }
                                                                >
                                                                    {opd.name}
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                    {renderError(
                                                        errors[
                                                            `kebutuhans.${index}.opd_id`
                                                        ],
                                                    )}
                                                </div>

                                                <div>
                                                    <Label
                                                        htmlFor={`kebutuhan-jenis-${index}`}
                                                    >
                                                        Jenis Penanganan
                                                    </Label>
                                                    <select
                                                        id={`kebutuhan-jenis-${index}`}
                                                        value={
                                                            item.jenis_penanganan_id ===
                                                            ''
                                                                ? ''
                                                                : String(
                                                                      item.jenis_penanganan_id,
                                                                  )
                                                        }
                                                        onChange={(event) => {
                                                            const value =
                                                                event.target
                                                                    .value;
                                                            updateKebutuhan(
                                                                index,
                                                                'jenis_penanganan_id',
                                                                value === ''
                                                                    ? ''
                                                                    : Number(
                                                                          value,
                                                                      ),
                                                            );
                                                        }}
                                                        disabled={
                                                            item.opd_id ===
                                                                '' ||
                                                            jenisOptions.length ===
                                                                0
                                                        }
                                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        <option value="">
                                                            {item.opd_id === ''
                                                                ? 'Pilih OPD dulu'
                                                                : jenisOptions.length ===
                                                                    0
                                                                  ? 'Tidak ada jenis penanganan'
                                                                  : 'Pilih Jenis Penanganan'}
                                                        </option>
                                                        {jenisOptions.map(
                                                            (jenis) => (
                                                                <option
                                                                    key={
                                                                        jenis.id
                                                                    }
                                                                    value={
                                                                        jenis.id
                                                                    }
                                                                >
                                                                    {jenis.name}
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                    {renderError(
                                                        errors[
                                                            `kebutuhans.${index}.jenis_penanganan_id`
                                                        ],
                                                    )}
                                                </div>
                                            </div>

                                            <div className="mt-4">
                                                <Label
                                                    htmlFor={`kebutuhan-detail-${index}`}
                                                >
                                                    Uraian Kebutuhan Detail
                                                </Label>
                                                <textarea
                                                    id={`kebutuhan-detail-${index}`}
                                                    value={item.need_detail}
                                                    onChange={(event) =>
                                                        updateKebutuhan(
                                                            index,
                                                            'need_detail',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className={
                                                        textAreaClassName
                                                    }
                                                />
                                                {renderError(
                                                    errors[
                                                        `kebutuhans.${index}.need_detail`
                                                    ],
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </section>
                    )}

                    {activeTab === 'riwayat' && (
                        <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Riwayat Kesehatan
                            </h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label htmlFor="tb_so_ro">TB SO/RO</Label>
                                    <Input
                                        id="tb_so_ro"
                                        type="number"
                                        min={0}
                                        value={data.tb_so_ro}
                                        onChange={setNumber('tb_so_ro')}
                                        className="mt-1"
                                    />
                                    {renderError(errors.tb_so_ro)}
                                </div>
                                <div>
                                    <Label htmlFor="treatment_start_date">
                                        Tanggal Mulai Pengobatan
                                    </Label>
                                    <DatePicker
                                        id="treatment_start_date"
                                        value={data.treatment_start_date}
                                        onChange={(value) =>
                                            setData(
                                                'treatment_start_date',
                                                value,
                                            )
                                        }
                                        className="mt-1"
                                    />
                                    {renderError(errors.treatment_start_date)}
                                </div>
                                <div>
                                    <Label htmlFor="visit_date">
                                        Tanggal Kunjungan
                                    </Label>
                                    <DatePicker
                                        id="visit_date"
                                        value={data.visit_date}
                                        onChange={(value) =>
                                            setData('visit_date', value)
                                        }
                                        className="mt-1"
                                    />
                                    {renderError(errors.visit_date)}
                                </div>
                                <div>
                                    <Label htmlFor="information_date">
                                        Tanggal Informasi
                                    </Label>
                                    <DatePicker
                                        id="information_date"
                                        value={data.information_date}
                                        onChange={(value) =>
                                            setData('information_date', value)
                                        }
                                        className="mt-1"
                                    />
                                    {renderError(errors.information_date)}
                                </div>
                                <div>
                                    <Label htmlFor="treatment_status">
                                        Status Pengobatan
                                    </Label>
                                    <select
                                        id="treatment_status"
                                        value={data.treatment_status}
                                        onChange={setString('treatment_status')}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="">Pilih status</option>
                                        <option value="terlaksana">
                                            Terlaksana
                                        </option>
                                        <option value="belum">Belum</option>
                                    </select>
                                    {renderError(errors.treatment_status)}
                                </div>
                                <div>
                                    <Label htmlFor="transmission_source">
                                        Sumber Penularan
                                    </Label>
                                    <Input
                                        id="transmission_source"
                                        value={data.transmission_source}
                                        onChange={setString(
                                            'transmission_source',
                                        )}
                                        className="mt-1"
                                    />
                                    {renderError(errors.transmission_source)}
                                </div>
                            </div>
                            <div>
                                <Label htmlFor="follow_up_plan">
                                    Rencana Tindak Lanjut
                                </Label>
                                <textarea
                                    id="follow_up_plan"
                                    value={data.follow_up_plan}
                                    onChange={setString('follow_up_plan')}
                                    className={textAreaClassName}
                                />
                                {renderError(errors.follow_up_plan)}
                            </div>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div>
                                    <Label htmlFor="pregnancy_status">
                                        Status Hamil
                                    </Label>
                                    <select
                                        id="pregnancy_status"
                                        value={
                                            data.pregnancy_status ? '1' : '0'
                                        }
                                        onChange={setBoolean(
                                            'pregnancy_status',
                                        )}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                    {renderError(errors.pregnancy_status)}
                                </div>
                                <div>
                                    <Label htmlFor="comorbid_status">
                                        Penyakit Penyerta
                                    </Label>
                                    <select
                                        id="comorbid_status"
                                        value={data.comorbid_status ? '1' : '0'}
                                        onChange={setBoolean('comorbid_status')}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                    {renderError(errors.comorbid_status)}
                                </div>
                                <div>
                                    <Label htmlFor="smoking_behavior">
                                        Perilaku Merokok
                                    </Label>
                                    <select
                                        id="smoking_behavior"
                                        value={
                                            data.smoking_behavior ? '1' : '0'
                                        }
                                        onChange={setBoolean(
                                            'smoking_behavior',
                                        )}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                    {renderError(errors.smoking_behavior)}
                                </div>
                                <div>
                                    <Label htmlFor="family_smoking_status">
                                        Anggota Keluarga Merokok
                                    </Label>
                                    <select
                                        id="family_smoking_status"
                                        value={
                                            data.family_smoking_status
                                                ? '1'
                                                : '0'
                                        }
                                        onChange={setBoolean(
                                            'family_smoking_status',
                                        )}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                    {renderError(errors.family_smoking_status)}
                                </div>
                                <div>
                                    <Label htmlFor="immunization_status">
                                        Status Imunisasi
                                    </Label>
                                    <select
                                        id="immunization_status"
                                        value={data.immunization_status}
                                        onChange={setString(
                                            'immunization_status',
                                        )}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="">Pilih status</option>
                                        <option value="lengkap">Lengkap</option>
                                        <option value="tidak-lengkap">
                                            Tidak Lengkap
                                        </option>
                                    </select>
                                    {renderError(errors.immunization_status)}
                                </div>
                                <div>
                                    <Label htmlFor="nutritional_status">
                                        Status Gizi
                                    </Label>
                                    <select
                                        id="nutritional_status"
                                        value={data.nutritional_status}
                                        onChange={setString(
                                            'nutritional_status',
                                        )}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="">Pilih status</option>
                                        <option value="normal">Normal</option>
                                        <option value="tidak-normal">
                                            Tidak Normal
                                        </option>
                                    </select>
                                    {renderError(errors.nutritional_status)}
                                </div>
                                <div>
                                    <Label htmlFor="jkn_ownership">
                                        Kepemilikan JKN
                                    </Label>
                                    <select
                                        id="jkn_ownership"
                                        value={data.jkn_ownership ? '1' : '0'}
                                        onChange={setBoolean('jkn_ownership')}
                                        className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                    {renderError(errors.jkn_ownership)}
                                </div>
                            </div>
                        </section>
                    )}

                    {activeTab === 'lainnya' && (
                        <div className="space-y-8">
                            <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Kondisi Rumah
                                </h2>
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <Label htmlFor="home_area">
                                            Luas Area
                                        </Label>
                                        <Input
                                            id="home_area"
                                            value={data.home_area}
                                            onChange={setString('home_area')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_area)}
                                    </div>
                                    <div>
                                        <Label htmlFor="house_area">
                                            Luas Rumah
                                        </Label>
                                        <Input
                                            id="house_area"
                                            value={data.house_area}
                                            onChange={setString('house_area')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.house_area)}
                                    </div>
                                    <div>
                                        <Label htmlFor="house_type">Tipe</Label>
                                        <Input
                                            id="house_type"
                                            value={data.house_type}
                                            onChange={setString('house_type')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.house_type)}
                                    </div>
                                    <div>
                                        <Label htmlFor="house_status">
                                            Status
                                        </Label>
                                        <Input
                                            id="house_status"
                                            value={data.house_status}
                                            onChange={setString('house_status')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.house_status)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_lighting">
                                            Pencahayaan
                                        </Label>
                                        <Input
                                            id="home_lighting"
                                            value={data.home_lighting}
                                            onChange={setString(
                                                'home_lighting',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_lighting)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_humidity">
                                            Kelembapan
                                        </Label>
                                        <Input
                                            id="home_humidity"
                                            value={data.home_humidity}
                                            onChange={setString(
                                                'home_humidity',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_humidity)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_cleanliness">
                                            Kebersihan
                                        </Label>
                                        <Input
                                            id="home_cleanliness"
                                            value={data.home_cleanliness}
                                            onChange={setString(
                                                'home_cleanliness',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_cleanliness)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_floor">
                                            Lantai
                                        </Label>
                                        <Input
                                            id="home_floor"
                                            value={data.home_floor}
                                            onChange={setString('home_floor')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_floor)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_ventilation">
                                            Ventilasi
                                        </Label>
                                        <Input
                                            id="home_ventilation"
                                            value={data.home_ventilation}
                                            onChange={setString(
                                                'home_ventilation',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_ventilation)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_ceiling">
                                            Langit-Langit
                                        </Label>
                                        <Input
                                            id="home_ceiling"
                                            value={data.home_ceiling}
                                            onChange={setString('home_ceiling')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_ceiling)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_ceiling_condition">
                                            Kondisi Langit-Langit
                                        </Label>
                                        <Input
                                            id="home_ceiling_condition"
                                            value={data.home_ceiling_condition}
                                            onChange={setString(
                                                'home_ceiling_condition',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.home_ceiling_condition,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_wall">
                                            Dinding
                                        </Label>
                                        <Input
                                            id="home_wall"
                                            value={data.home_wall}
                                            onChange={setString('home_wall')}
                                            className="mt-1"
                                        />
                                        {renderError(errors.home_wall)}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_bedroom_window">
                                            Jendela Kamar Tidur
                                        </Label>
                                        <Input
                                            id="home_bedroom_window"
                                            value={data.home_bedroom_window}
                                            onChange={setString(
                                                'home_bedroom_window',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.home_bedroom_window,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_family_room_window">
                                            Jendela Ruang Keluarga
                                        </Label>
                                        <Input
                                            id="home_family_room_window"
                                            value={data.home_family_room_window}
                                            onChange={setString(
                                                'home_family_room_window',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.home_family_room_window,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_kitchen_smoke_hole">
                                            Lubang Asap Dapur
                                        </Label>
                                        <Input
                                            id="home_kitchen_smoke_hole"
                                            value={data.home_kitchen_smoke_hole}
                                            onChange={setString(
                                                'home_kitchen_smoke_hole',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.home_kitchen_smoke_hole,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_open_family_room_window">
                                            Membuka Jendela Ruang Keluarga
                                        </Label>
                                        <Input
                                            id="home_open_family_room_window"
                                            value={
                                                data.home_open_family_room_window
                                            }
                                            onChange={setString(
                                                'home_open_family_room_window',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.home_open_family_room_window,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="home_clean_house_habit">
                                            Membersihkan Rumah
                                        </Label>
                                        <Input
                                            id="home_clean_house_habit"
                                            value={data.home_clean_house_habit}
                                            onChange={setString(
                                                'home_clean_house_habit',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.home_clean_house_habit,
                                        )}
                                    </div>
                                </div>
                            </section>

                            <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Kondisi Sanitasi
                                </h2>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="sanitation_clean_water">
                                            Sarana Air Bersih
                                        </Label>
                                        <Input
                                            id="sanitation_clean_water"
                                            value={data.sanitation_clean_water}
                                            onChange={setString(
                                                'sanitation_clean_water',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.sanitation_clean_water,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="sanitation_toilet">
                                            Jamban
                                        </Label>
                                        <Input
                                            id="sanitation_toilet"
                                            value={data.sanitation_toilet}
                                            onChange={setString(
                                                'sanitation_toilet',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.sanitation_toilet)}
                                    </div>
                                    <div>
                                        <Label htmlFor="sanitation_wastewater_disposal">
                                            Sarana Pembuangan Air Limbah
                                        </Label>
                                        <Input
                                            id="sanitation_wastewater_disposal"
                                            value={
                                                data.sanitation_wastewater_disposal
                                            }
                                            onChange={setString(
                                                'sanitation_wastewater_disposal',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.sanitation_wastewater_disposal,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="sanitation_garbage_water_disposal">
                                            Sarana Pembuangan Air Sampah
                                        </Label>
                                        <Input
                                            id="sanitation_garbage_water_disposal"
                                            value={
                                                data.sanitation_garbage_water_disposal
                                            }
                                            onChange={setString(
                                                'sanitation_garbage_water_disposal',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.sanitation_garbage_water_disposal,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="sanitation_trash">
                                            Sampah
                                        </Label>
                                        <Input
                                            id="sanitation_trash"
                                            value={data.sanitation_trash}
                                            onChange={setString(
                                                'sanitation_trash',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(errors.sanitation_trash)}
                                    </div>
                                    <div>
                                        <Label htmlFor="sanitation_feces_disposal">
                                            Pembuangan Tinja
                                        </Label>
                                        <Input
                                            id="sanitation_feces_disposal"
                                            value={
                                                data.sanitation_feces_disposal
                                            }
                                            onChange={setString(
                                                'sanitation_feces_disposal',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.sanitation_feces_disposal,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="sanitation_throw_trash_habit">
                                            Membuang Sampah
                                        </Label>
                                        <Input
                                            id="sanitation_throw_trash_habit"
                                            value={
                                                data.sanitation_throw_trash_habit
                                            }
                                            onChange={setString(
                                                'sanitation_throw_trash_habit',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.sanitation_throw_trash_habit,
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="sanitation_handwashing_habit">
                                            Kebiasaan Cuci Tangan Pakai Sabun
                                        </Label>
                                        <Input
                                            id="sanitation_handwashing_habit"
                                            value={
                                                data.sanitation_handwashing_habit
                                            }
                                            onChange={setString(
                                                'sanitation_handwashing_habit',
                                            )}
                                            className="mt-1"
                                        />
                                        {renderError(
                                            errors.sanitation_handwashing_habit,
                                        )}
                                    </div>
                                </div>
                            </section>

                            <section className="space-y-4 rounded-lg border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Kondisi Hewan
                                </h2>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="has_livestock">
                                            Memiliki Hewan Ternak
                                        </Label>
                                        <select
                                            id="has_livestock"
                                            value={
                                                data.has_livestock === null
                                                    ? ''
                                                    : data.has_livestock
                                                      ? '1'
                                                      : '0'
                                            }
                                            onChange={setNullableBoolean(
                                                'has_livestock',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">Pilih</option>
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                        {renderError(errors.has_livestock)}
                                    </div>
                                    <div>
                                        <Label htmlFor="has_animal_cage">
                                            Memiliki Kandang Hewan
                                        </Label>
                                        <select
                                            id="has_animal_cage"
                                            value={
                                                data.has_animal_cage === null
                                                    ? ''
                                                    : data.has_animal_cage
                                                      ? '1'
                                                      : '0'
                                            }
                                            onChange={setNullableBoolean(
                                                'has_animal_cage',
                                            )}
                                            className="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                                        >
                                            <option value="">Pilih</option>
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                        {renderError(errors.has_animal_cage)}
                                    </div>
                                </div>
                            </section>
                        </div>
                    )}

                    <div className="flex gap-3 border-t border-gray-200 pt-4">
                        {isFirstTab ? (
                            <Button
                                asChild
                                type="button"
                                variant="outline"
                                className="flex-1"
                            >
                                <Link href={cancelHref}>Batal</Link>
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                variant="outline"
                                className="flex-1"
                                onClick={() =>
                                    setActiveTab(tabOrder[currentTabIndex - 1])
                                }
                            >
                                Kembali
                            </Button>
                        )}

                        {isLastTab ? (
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1"
                            >
                                {processing ? 'Menyimpan...' : submitLabel}
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                className="flex-1"
                                onClick={() =>
                                    setActiveTab(tabOrder[currentTabIndex + 1])
                                }
                            >
                                Selanjutnya
                            </Button>
                        )}
                    </div>
                </form>
            </div>
        </div>
    );
}
