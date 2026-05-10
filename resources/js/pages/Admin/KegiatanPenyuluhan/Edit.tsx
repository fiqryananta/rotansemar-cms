import { Head, Link, useForm } from '@inertiajs/react';
import { ImagePlus, Megaphone, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface KegiatanPenyuluhan {
    id: number;
    nama_kegiatan: string;
    tanggal_kegiatan: string;
    uraian_kegiatan: string;
    lokasi_kegiatan: string;
    koordinat_lokasi: string;
    sasaran: string;
    jumlah_sasaran: number;
    foto_kegiatan?: string[];
}

interface Props {
    kegiatanPenyuluhan: KegiatanPenyuluhan;
}

export default function KegiatanPenyuluhanEdit({ kegiatanPenyuluhan }: Props) {
    const fileRef = useRef<HTMLInputElement>(null);
    const [previews, setPreviews] = useState<string[]>([]);

    const { data, setData, patch, processing, errors } = useForm({
        nama_kegiatan: kegiatanPenyuluhan.nama_kegiatan,
        tanggal_kegiatan: kegiatanPenyuluhan.tanggal_kegiatan,
        uraian_kegiatan: kegiatanPenyuluhan.uraian_kegiatan,
        lokasi_kegiatan: kegiatanPenyuluhan.lokasi_kegiatan,
        koordinat_lokasi: kegiatanPenyuluhan.koordinat_lokasi,
        sasaran: kegiatanPenyuluhan.sasaran,
        jumlah_sasaran: String(kegiatanPenyuluhan.jumlah_sasaran),
        existing_foto_kegiatan: kegiatanPenyuluhan.foto_kegiatan ?? [],
        foto_kegiatan: [] as File[],
    });
    const [locationStatus, setLocationStatus] = useState('Koordinat otomatis akan dicoba diambil ulang dari lokasi saat ini.');

    const loadCurrentLocation = () => {
        if (!navigator.geolocation) {
            setLocationStatus('Browser tidak mendukung pengambilan lokasi otomatis.');
            return;
        }

        setLocationStatus('Mengambil koordinat lokasi saat ini...');
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const value = `${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)}`;
                setData('koordinat_lokasi', value);
                setLocationStatus(`Koordinat otomatis didapat: ${value}`);
            },
            () => {
                setLocationStatus('Koordinat tidak tersedia. Data lama tetap dipakai bila tidak diubah.');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 },
        );
    };

    useEffect(() => {
        if (kegiatanPenyuluhan.koordinat_lokasi) {
            setLocationStatus(`Koordinat tersimpan: ${kegiatanPenyuluhan.koordinat_lokasi}`);
        }

        loadCurrentLocation();
    }, []);

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        patch(route('kegiatan-penyuluhan.update', kegiatanPenyuluhan.id));
    };

    const removeExistingPhoto = (path: string) => {
        setData(
            'existing_foto_kegiatan',
            data.existing_foto_kegiatan.filter((item) => item !== path),
        );
    };

    const handleFiles = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (!e.target.files) return;
        const newFiles = Array.from(e.target.files);
        setData('foto_kegiatan', newFiles);
        setPreviews(newFiles.map((f) => URL.createObjectURL(f)));
        if (fileRef.current) fileRef.current.value = '';
    };

    const removePreview = (index: number) => {
        const next = data.foto_kegiatan.filter((_, i) => i !== index);
        setData('foto_kegiatan', next);
        setPreviews(next.map((f) => URL.createObjectURL(f)));
    };

    return (
        <>
            <Head title="Edit Kegiatan Penyuluhan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <Megaphone className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Edit Kegiatan Penyuluhan
                            </h1>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="nama_kegiatan">
                                    Nama Kegiatan
                                </Label>
                                <Input
                                    id="nama_kegiatan"
                                    value={data.nama_kegiatan}
                                    onChange={(event) =>
                                        setData(
                                            'nama_kegiatan',
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1"
                                />
                                {errors.nama_kegiatan && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.nama_kegiatan}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="tanggal_kegiatan">
                                    Tanggal Kegiatan
                                </Label>
                                <DatePicker
                                    id="tanggal_kegiatan"
                                    value={data.tanggal_kegiatan}
                                    onChange={(value) =>
                                        setData('tanggal_kegiatan', value)
                                    }
                                    className="mt-1"
                                />
                                {errors.tanggal_kegiatan && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.tanggal_kegiatan}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="lokasi_kegiatan">
                                    Lokasi Kegiatan
                                </Label>
                                <Input
                                    id="lokasi_kegiatan"
                                    value={data.lokasi_kegiatan}
                                    onChange={(event) =>
                                        setData(
                                            'lokasi_kegiatan',
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1"
                                />
                                {errors.lokasi_kegiatan && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.lokasi_kegiatan}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label>Koordinat Lokasi</Label>
                                <div className="mt-1 rounded-md border border-dashed border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
                                    {locationStatus}
                                </div>
                                <button
                                    type="button"
                                    onClick={loadCurrentLocation}
                                    className="mt-2 text-sm font-medium text-teal-700 hover:text-teal-800"
                                >
                                    Ambil ulang lokasi
                                </button>
                                {errors.koordinat_lokasi && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.koordinat_lokasi}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="sasaran">Sasaran</Label>
                                <Input
                                    id="sasaran"
                                    value={data.sasaran}
                                    onChange={(event) =>
                                        setData('sasaran', event.target.value)
                                    }
                                    className="mt-1"
                                />
                                {errors.sasaran && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.sasaran}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="jumlah_sasaran">
                                    Jumlah Sasaran
                                </Label>
                                <Input
                                    id="jumlah_sasaran"
                                    type="number"
                                    min={1}
                                    value={data.jumlah_sasaran}
                                    onChange={(event) =>
                                        setData(
                                            'jumlah_sasaran',
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1"
                                />
                                {errors.jumlah_sasaran && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.jumlah_sasaran}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div>
                            <Label htmlFor="uraian_kegiatan">
                                Uraian Kegiatan
                            </Label>
                            <textarea
                                id="uraian_kegiatan"
                                value={data.uraian_kegiatan}
                                onChange={(event) =>
                                    setData(
                                        'uraian_kegiatan',
                                        event.target.value,
                                    )
                                }
                                rows={4}
                                className="mt-1 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            />
                            {errors.uraian_kegiatan && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.uraian_kegiatan}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label>Foto Kegiatan Saat Ini</Label>
                            {data.existing_foto_kegiatan.length > 0 ? (
                                <div className="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                    {data.existing_foto_kegiatan.map(
                                        (photoPath) => (
                                            <div
                                                key={photoPath}
                                                className="group relative overflow-hidden rounded-md border border-gray-200"
                                            >
                                                <img
                                                    src={`/storage/${photoPath}`}
                                                    alt="Foto kegiatan"
                                                    className="aspect-square h-full w-full object-cover"
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        removeExistingPhoto(
                                                            photoPath,
                                                        )
                                                    }
                                                    className="absolute top-1 right-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white opacity-0 transition group-hover:opacity-100"
                                                >
                                                    <X className="h-3 w-3" />
                                                </button>
                                            </div>
                                        ),
                                    )}
                                </div>
                            ) : (
                                <p className="mt-2 text-sm text-gray-500">
                                    Belum ada foto tersimpan.
                                </p>
                            )}
                        </div>

                        <div>
                            <Label className="text-gray-700">
                                Tambah Foto Kegiatan{' '}
                                <span className="text-red-500">*</span>{' '}
                                <span className="text-xs font-normal text-gray-600">
                                    (bisa lebih dari 1, max 5MB/foto,
                                    JPG/PNG/WebP)
                                </span>
                            </Label>
                            <div className="mt-2">
                                <button
                                    type="button"
                                    onClick={() => fileRef.current?.click()}
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
                            {errors.foto_kegiatan && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.foto_kegiatan}
                                </p>
                            )}
                            {errors['foto_kegiatan.0'] && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors['foto_kegiatan.0']}
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
                                                    removePreview(idx)
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

                        <div className="flex gap-3 pt-2">
                            <Button
                                asChild
                                type="button"
                                variant="outline"
                                className="flex-1"
                            >
                                <Link href={route('kegiatan-penyuluhan.index')}>
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
                                    : 'Update Kegiatan'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
