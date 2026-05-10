import { useRef, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckCircle2,
    ClipboardCheck,
    ImagePlus,
    Trash2,
    Upload,
    X,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { formatDateDMY } from '@/lib/date';

interface Foto {
    id: number;
    path: string;
    url: string;
}
interface RiwayatItem {
    id: number;
    keterangan: string;
    created_at: string;
    user?: { id: number; name: string; email: string };
    fotos: Foto[];
}

interface Kebutuhan {
    id: number;
    need_detail: string;
    verification_status: string;
    pasien: {
        id: number;
        name: string;
        nik: string;
        address: string;
        catatan_kebutuhan?: string | null;
    };
    opd: { id: number; name: string };
    jenis_penanganan: { id: number; name: string };
    jenis_kebutuhan: { id: number; name: string };
    tindak_lanjuts: RiwayatItem[];
}

interface Props {
    kebutuhan: Kebutuhan;
}

interface PageProps extends Record<string, unknown> {
    flash?: { success?: string; error?: string };
}

const statusConfig: Record<
    string,
    { label: string; color: string; badgeClass: string }
> = {
    pending: {
        label: 'Menunggu Verifikasi',
        color: 'gray',
        badgeClass: 'border-gray-400 text-gray-600 bg-gray-50',
    },
    proses: {
        label: 'Proses',
        color: 'blue',
        badgeClass: 'border-blue-400 text-blue-700 bg-blue-50',
    },
    pending_bantuan: {
        label: 'Pending Bantuan',
        color: 'amber',
        badgeClass: 'border-amber-400 text-amber-700 bg-amber-50',
    },
    tidak_layak: {
        label: 'Tidak Layak',
        color: 'red',
        badgeClass: 'border-red-400 text-red-700 bg-red-50',
    },
    selesai: {
        label: 'Selesai',
        color: 'green',
        badgeClass: 'border-green-500 text-green-700 bg-green-50',
    },
};

function formatDate(dateStr: string) {
    return formatDateDMY(dateStr);
}

export default function TindakLanjutShow({ kebutuhan }: Props) {
    const { props } = usePage<PageProps>();
    const flash = props.flash ?? {};

    const status = kebutuhan.verification_status;
    const cfg = statusConfig[status] ?? statusConfig.pending;
    const canAct = status === 'proses' || status === 'pending_bantuan';
    const isTerminal = status === 'tidak_layak' || status === 'selesai';

    // Verifikasi form
    const {
        data: vData,
        setData: vSetData,
        patch: vPatch,
        processing: vProcessing,
        errors: vErrors,
    } = useForm({ status: '' });

    const handleVerifikasi = (newStatus: string) => {
        vSetData('status', newStatus);
        vPatch(route('tindak-lanjut.verifikasi', kebutuhan.id), {
            data: { status: newStatus },
        } as Parameters<typeof vPatch>[1]);
    };

    // Selesai
    const [confirmSelesai, setConfirmSelesai] = useState(false);
    const handleSelesai = () => {
        router.patch(route('tindak-lanjut.selesai', kebutuhan.id));
    };

    // Tambah Tindak Lanjut form
    const [keterangan, setKeterangan] = useState('');
    const [fotos, setFotos] = useState<File[]>([]);
    const [previews, setPreviews] = useState<string[]>([]);
    const [tlErrors, setTlErrors] = useState<Record<string, string>>({});
    const [uploading, setUploading] = useState(false);
    const fileRef = useRef<HTMLInputElement>(null);

    const handleFiles = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (!e.target.files) return;
        const newFiles = Array.from(e.target.files);
        const combined = [...fotos, ...newFiles];
        setFotos(combined);
        setPreviews(combined.map((f) => URL.createObjectURL(f)));
        if (fileRef.current) fileRef.current.value = '';
    };

    const removePreview = (index: number) => {
        const next = fotos.filter((_, i) => i !== index);
        setFotos(next);
        setPreviews(next.map((f) => URL.createObjectURL(f)));
    };

    const handleTambahSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const errs: Record<string, string> = {};
        if (!keterangan.trim()) errs.keterangan = 'Keterangan wajib diisi.';
        if (fotos.length === 0) errs.fotos = 'Minimal 1 foto wajib diunggah.';
        if (Object.keys(errs).length > 0) {
            setTlErrors(errs);
            return;
        }
        setTlErrors({});

        const formData = new FormData();
        formData.append('keterangan', keterangan);
        fotos.forEach((f) => formData.append('fotos[]', f));

        router.post(
            route('tindak-lanjut.tambahRiwayat', kebutuhan.id),
            formData,
            {
                forceFormData: true,
                onStart: () => setUploading(true),
                onFinish: () => {
                    setUploading(false);
                    setKeterangan('');
                    setFotos([]);
                    setPreviews([]);
                },
            },
        );
    };

    return (
        <>
            <Head title={`Tindak Lanjut - ${kebutuhan.pasien.name}`} />
            <div className="min-h-screen bg-gray-50">
                <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8 flex items-start justify-between gap-4">
                        <div className="flex items-start gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <ClipboardCheck className="h-6 w-6" />
                            </div>
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">
                                    Detail Tindak Lanjut
                                </h1>
                                <p className="mt-2 text-gray-600">
                                    Progress penanganan pasien oleh OPD
                                </p>
                            </div>
                        </div>

                        <Button asChild variant="outline" size="sm">
                            <Link href={route('tindak-lanjut.index')}>
                                <ArrowLeft className="mr-1 h-4 w-4" /> Kembali
                            </Link>
                        </Button>
                    </div>

                    {/* Flash messages */}
                    {flash.success && (
                        <div className="mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            <CheckCircle2 className="h-4 w-4 shrink-0" />
                            {flash.success}
                        </div>
                    )}
                    {flash.error && (
                        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {flash.error}
                        </div>
                    )}

                    <div className="space-y-6">
                        {/* Info Cards */}
                        <div className="grid gap-4 md:grid-cols-2">
                            {/* Pasien */}
                            <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <h2 className="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">
                                    Informasi Pasien
                                </h2>
                                <div className="space-y-2">
                                    <div>
                                        <p className="text-xs text-gray-500">
                                            Nama
                                        </p>
                                        <p className="font-semibold text-gray-900">
                                            {kebutuhan.pasien.name}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">
                                            NIK
                                        </p>
                                        <p className="text-sm text-gray-900">
                                            {kebutuhan.pasien.nik}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">
                                            Alamat
                                        </p>
                                        <p className="text-sm text-gray-900">
                                            {kebutuhan.pasien.address || '-'}
                                        </p>
                                    </div>
                                    {kebutuhan.pasien.catatan_kebutuhan && (
                                        <div>
                                            <p className="text-xs text-gray-500">
                                                Catatan Kebutuhan
                                            </p>
                                            <p className="text-sm text-gray-700 italic">
                                                {
                                                    kebutuhan.pasien
                                                        .catatan_kebutuhan
                                                }
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Kebutuhan */}
                            <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <h2 className="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">
                                    Detail Kebutuhan
                                </h2>
                                <div className="space-y-2">
                                    <div>
                                        <p className="text-xs text-gray-500">
                                            Jenis Kebutuhan
                                        </p>
                                        <p className="text-sm font-medium text-gray-900">
                                            {kebutuhan.jenis_kebutuhan?.name ??
                                                '-'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">
                                            OPD
                                        </p>
                                        <p className="text-sm font-medium text-gray-900">
                                            {kebutuhan.opd?.name ?? '-'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">
                                            Jenis Penanganan
                                        </p>
                                        <p className="text-sm font-medium text-gray-900">
                                            {kebutuhan.jenis_penanganan?.name ??
                                                '-'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">
                                            Uraian
                                        </p>
                                        <p className="text-sm text-gray-700">
                                            {kebutuhan.need_detail || '-'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Status & Verifikasi */}
                        {!isTerminal && (
                            <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <div className="mb-4 flex items-center justify-between">
                                    <h2 className="text-base font-semibold text-gray-900">
                                        Status Penanganan
                                    </h2>
                                    <Badge
                                        variant="outline"
                                        className={`px-3 py-1 text-sm font-medium ${cfg.badgeClass}`}
                                    >
                                        {cfg.label}
                                    </Badge>
                                </div>

                                {/* Verifikasi actions (non-terminal, except selesai) */}
                                <div className="space-y-3">
                                    {status === 'pending' && (
                                        <>
                                            <p className="text-sm text-gray-600">
                                                Lakukan verifikasi kelayakan
                                                pasien untuk penanganan ini:
                                            </p>
                                            <div className="flex flex-wrap gap-2">
                                                <Button
                                                    variant="default"
                                                    className="bg-blue-600 hover:bg-blue-700"
                                                    disabled={vProcessing}
                                                    onClick={() =>
                                                        handleVerifikasi(
                                                            'proses',
                                                        )
                                                    }
                                                >
                                                    ✓ Proses — Layak & Akan
                                                    Ditindaklanjuti
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    className="border-amber-400 text-amber-700 hover:bg-amber-50"
                                                    disabled={vProcessing}
                                                    onClick={() =>
                                                        handleVerifikasi(
                                                            'pending_bantuan',
                                                        )
                                                    }
                                                >
                                                    ⏳ Pending Bantuan — Layak,
                                                    Belum Bisa Ditindaklanjuti
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    className="border-red-400 text-red-700 hover:bg-red-50"
                                                    disabled={vProcessing}
                                                    onClick={() =>
                                                        handleVerifikasi(
                                                            'tidak_layak',
                                                        )
                                                    }
                                                >
                                                    ✗ Tidak Layak
                                                </Button>
                                            </div>
                                        </>
                                    )}

                                    {canAct && (
                                        <div className="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-3">
                                            <div className="flex gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    className={`text-xs ${status === 'proses' ? 'border-blue-300 text-blue-600' : ''}`}
                                                    disabled={
                                                        vProcessing ||
                                                        status === 'proses'
                                                    }
                                                    onClick={() =>
                                                        handleVerifikasi(
                                                            'proses',
                                                        )
                                                    }
                                                >
                                                    Ubah → Proses
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    className={`text-xs ${status === 'pending_bantuan' ? 'border-amber-300 text-amber-600' : ''}`}
                                                    disabled={
                                                        vProcessing ||
                                                        status ===
                                                            'pending_bantuan'
                                                    }
                                                    onClick={() =>
                                                        handleVerifikasi(
                                                            'pending_bantuan',
                                                        )
                                                    }
                                                >
                                                    Ubah → Pending Bantuan
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    className="border-red-300 text-xs text-red-600 hover:bg-red-50"
                                                    disabled={vProcessing}
                                                    onClick={() =>
                                                        handleVerifikasi(
                                                            'tidak_layak',
                                                        )
                                                    }
                                                >
                                                    Ubah → Tidak Layak
                                                </Button>
                                            </div>

                                            {/* Selesai */}
                                            {kebutuhan.tindak_lanjuts.length >
                                                0 &&
                                                (!confirmSelesai ? (
                                                    <Button
                                                        size="sm"
                                                        className="ml-auto bg-green-600 text-xs hover:bg-green-700"
                                                        onClick={() =>
                                                            setConfirmSelesai(
                                                                true,
                                                            )
                                                        }
                                                    >
                                                        <CheckCircle2 className="mr-1 h-3.5 w-3.5" />
                                                        Selesaikan Penanganan
                                                    </Button>
                                                ) : (
                                                    <div className="ml-auto flex items-center gap-2">
                                                        <span className="text-xs text-gray-600">
                                                            Yakin ingin
                                                            menyelesaikan?
                                                        </span>
                                                        <Button
                                                            size="sm"
                                                            className="bg-green-600 text-xs hover:bg-green-700"
                                                            onClick={
                                                                handleSelesai
                                                            }
                                                        >
                                                            Ya, Selesai
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="text-xs"
                                                            onClick={() =>
                                                                setConfirmSelesai(
                                                                    false,
                                                                )
                                                            }
                                                        >
                                                            Batal
                                                        </Button>
                                                    </div>
                                                ))}
                                        </div>
                                    )}

                                    {vErrors.status && (
                                        <p className="text-sm text-red-600">
                                            {vErrors.status}
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Status Display (Read-only for terminal states) */}
                        {isTerminal && (
                            <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <div className="flex items-center justify-between">
                                    <h2 className="text-base font-semibold text-gray-900">
                                        Status Penanganan
                                    </h2>
                                    <Badge
                                        variant="outline"
                                        className={`px-3 py-1 text-sm font-medium ${cfg.badgeClass}`}
                                    >
                                        {cfg.label}
                                    </Badge>
                                </div>
                                <p className="mt-3 text-sm text-gray-500">
                                    {status === 'selesai'
                                        ? 'Penanganan ini telah diselesaikan.'
                                        : 'Pasien dinyatakan tidak layak untuk penanganan ini.'}
                                </p>
                            </div>
                        )}

                        {/* Tambah Tindak Lanjut */}
                        {canAct && (
                            <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <h2 className="mb-4 flex items-center gap-2 text-base font-semibold text-gray-900">
                                    <Upload className="h-5 w-5" />
                                    Tambah Tindak Lanjut
                                </h2>
                                <form
                                    onSubmit={handleTambahSubmit}
                                    className="space-y-4"
                                >
                                    <div>
                                        <Label
                                            htmlFor="keterangan"
                                            className="text-gray-700"
                                        >
                                            Keterangan Tindak Lanjut{' '}
                                            <span className="text-red-500">
                                                *
                                            </span>
                                        </Label>
                                        <textarea
                                            id="keterangan"
                                            value={keterangan}
                                            onChange={(e) =>
                                                setKeterangan(e.target.value)
                                            }
                                            rows={4}
                                            placeholder="Deskripsikan tindak lanjut yang telah dilakukan..."
                                            className="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-100"
                                        />
                                        {tlErrors.keterangan && (
                                            <p className="mt-1 text-sm text-red-600">
                                                {tlErrors.keterangan}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <Label className="text-gray-700">
                                            Foto Bukti{' '}
                                            <span className="text-red-500">
                                                *
                                            </span>{' '}
                                            <span className="text-xs font-normal text-gray-600">
                                                (min. 1 foto, maks. 5MB/foto,
                                                JPG/PNG/WebP)
                                            </span>
                                        </Label>
                                        <div className="mt-2">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    fileRef.current?.click()
                                                }
                                                className="flex items-center gap-2 rounded-md border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-600 transition hover:border-gray-400 hover:bg-gray-100"
                                            >
                                                <ImagePlus className="h-4 w-4" />
                                                Pilih Foto
                                            </button>
                                            <input
                                                ref={fileRef}
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                multiple
                                                className="hidden"
                                                onChange={handleFiles}
                                            />
                                        </div>
                                        {tlErrors.fotos && (
                                            <p className="mt-1 text-sm text-red-600">
                                                {tlErrors.fotos}
                                            </p>
                                        )}

                                        {/* Preview grid */}
                                        {previews.length > 0 && (
                                            <div className="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                                {previews.map((src, idx) => (
                                                    <div
                                                        key={idx}
                                                        className="group relative aspect-square overflow-hidden rounded-md border border-gray-200"
                                                    >
                                                        <img
                                                            src={src}
                                                            alt=""
                                                            className="h-full w-full object-cover"
                                                        />
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                removePreview(
                                                                    idx,
                                                                )
                                                            }
                                                            className="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white opacity-0 transition group-hover:opacity-100"
                                                        >
                                                            <X className="h-3 w-3" />
                                                        </button>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>

                                    <Button
                                        type="submit"
                                        disabled={uploading}
                                        className="bg-indigo-600 hover:bg-indigo-700"
                                    >
                                        {uploading
                                            ? 'Mengunggah...'
                                            : 'Simpan Tindak Lanjut'}
                                    </Button>
                                </form>
                            </div>
                        )}

                        {/* Riwayat */}
                        <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div className="border-b border-gray-100 px-4 py-3">
                                <h2 className="font-semibold text-gray-900">
                                    Riwayat Tindak Lanjut
                                    {kebutuhan.tindak_lanjuts.length > 0 && (
                                        <span className="ml-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">
                                            {kebutuhan.tindak_lanjuts.length}
                                        </span>
                                    )}
                                </h2>
                            </div>

                            {kebutuhan.tindak_lanjuts.length === 0 ? (
                                <div className="py-10 text-center text-sm text-gray-400">
                                    Belum ada riwayat tindak lanjut.
                                </div>
                            ) : (
                                <div className="relative px-4 py-6">
                                    {/* Timeline line */}
                                    <div className="absolute top-8 bottom-0 left-7 w-0.5 bg-gradient-to-b from-indigo-300 to-indigo-100" />

                                    {/* Timeline items */}
                                    <div className="space-y-6">
                                        {kebutuhan.tindak_lanjuts.map(
                                            (item, idx) => (
                                                <div
                                                    key={item.id}
                                                    className="relative pl-16"
                                                >
                                                    {/* Timeline dot */}
                                                    <div className="absolute top-1 left-0 h-4 w-4 rounded-full border-4 border-white bg-indigo-600 shadow-md" />

                                                    {/* Content card */}
                                                    <div className="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                                        {/* Header with date and user */}
                                                        <div className="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                            <div className="flex items-center gap-2">
                                                                {item.user && (
                                                                    <>
                                                                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-600">
                                                                            {item.user.name
                                                                                .charAt(
                                                                                    0,
                                                                                )
                                                                                .toUpperCase()}
                                                                        </div>
                                                                        <div>
                                                                            <p className="text-sm font-semibold text-gray-900">
                                                                                {
                                                                                    item
                                                                                        .user
                                                                                        .name
                                                                                }
                                                                            </p>
                                                                            <p className="text-xs text-gray-500">
                                                                                {
                                                                                    item
                                                                                        .user
                                                                                        .email
                                                                                }
                                                                            </p>
                                                                        </div>
                                                                    </>
                                                                )}
                                                            </div>
                                                            <div className="text-sm font-medium text-gray-600">
                                                                {formatDate(
                                                                    item.created_at,
                                                                )}
                                                            </div>
                                                        </div>

                                                        {/* Divider */}
                                                        <div className="mb-3 border-t border-gray-200" />

                                                        {/* Description */}
                                                        <div className="mb-4">
                                                            <p className="mb-1 text-sm font-semibold text-gray-700">
                                                                Keterangan
                                                                Tindak Lanjut:
                                                            </p>
                                                            <p className="text-sm leading-relaxed whitespace-pre-wrap text-gray-800">
                                                                {
                                                                    item.keterangan
                                                                }
                                                            </p>
                                                        </div>

                                                        {/* Photos */}
                                                        {item.fotos.length >
                                                            0 && (
                                                            <div>
                                                                <p className="mb-2 text-sm font-semibold text-gray-700">
                                                                    Foto Bukti (
                                                                    {
                                                                        item
                                                                            .fotos
                                                                            .length
                                                                    }
                                                                    ):
                                                                </p>
                                                                <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                                                    {item.fotos.map(
                                                                        (
                                                                            foto,
                                                                        ) => (
                                                                            <a
                                                                                key={
                                                                                    foto.id
                                                                                }
                                                                                href={
                                                                                    foto.url
                                                                                }
                                                                                target="_blank"
                                                                                rel="noopener noreferrer"
                                                                                className="group relative aspect-square overflow-hidden rounded-md border border-gray-300 transition hover:border-indigo-400 hover:shadow-lg"
                                                                            >
                                                                                <img
                                                                                    src={
                                                                                        foto.url
                                                                                    }
                                                                                    alt="Bukti tindak lanjut"
                                                                                    className="h-full w-full object-cover transition group-hover:scale-110"
                                                                                />
                                                                                <div className="absolute inset-0 bg-black/0 transition group-hover:bg-black/10" />
                                                                            </a>
                                                                        ),
                                                                    )}
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            ),
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
