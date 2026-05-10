import { Head, Link, useForm } from '@inertiajs/react';
import { Download, FileSpreadsheet, Upload } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface ImportSummary {
    inserted: number;
    updated: number;
    skipped: number;
    total: number;
}

interface Props {
    importSummary?: ImportSummary | null;
    importErrors?: string[];
    templateColumns: string[];
}

export default function PasienImport({ importSummary, importErrors = [], templateColumns }: Props) {
    const { data, setData, post, processing, errors } = useForm<{
        file: File | null;
    }>({
        file: null,
    });

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('pasiens.import.store'), {
            forceFormData: true,
        });
    };

    return (
        <>
            <Head title="Import Pasien" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <FileSpreadsheet className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Import Pasien</h1>
                            <p className="mt-1 text-sm text-gray-500">
                                Upload data pasien dalam format template Excel
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900">Upload File</h2>

                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <Label htmlFor="file">File Excel</Label>
                                    <Input
                                        id="file"
                                        type="file"
                                        accept=".xlsx,.xls,.csv"
                                        onChange={(event) =>
                                            setData('file', event.target.files?.[0] ?? null)
                                        }
                                    />
                                    {errors.file && (
                                        <p className="mt-1 text-sm text-red-600">{errors.file}</p>
                                    )}
                                </div>

                                <div className="flex items-center gap-3">
                                    <Button type="submit" disabled={processing || !data.file} className="gap-2">
                                        <Upload className="h-4 w-4" />
                                        {processing ? 'Memproses...' : 'Import Data'}
                                    </Button>
                                    <Button asChild variant="outline" className="gap-2">
                                        <a href={route('pasiens.import.template')}>
                                            <Download className="h-4 w-4" />
                                            Download Template
                                        </a>
                                    </Button>
                                    <Button asChild variant="ghost">
                                        <Link href={route('pasiens.index')}>Kembali ke daftar pasien</Link>
                                    </Button>
                                </div>
                            </form>

                            {importSummary && (
                                <div className="mt-6 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                                    <p className="font-medium">Ringkasan Import</p>
                                    <p className="mt-1">Total baris: {importSummary.total}</p>
                                    <p>Berhasil tambah: {importSummary.inserted}</p>
                                    <p>Berhasil update: {importSummary.updated}</p>
                                    <p>Dilewati: {importSummary.skipped}</p>
                                </div>
                            )}

                            {importErrors.length > 0 && (
                                <div className="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    <p className="font-medium">Detail Baris Dilewati</p>
                                    <ul className="mt-2 list-disc space-y-1 pl-4">
                                        {importErrors.slice(0, 20).map((message) => (
                                            <li key={message}>{message}</li>
                                        ))}
                                    </ul>
                                    {importErrors.length > 20 && (
                                        <p className="mt-2">Menampilkan 20 error pertama dari {importErrors.length} baris.</p>
                                    )}
                                </div>
                            )}
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h2 className="mb-3 text-lg font-semibold text-gray-900">Format Template</h2>
                            <p className="mb-3 text-sm text-gray-600">
                                Gunakan nama kolom persis seperti berikut pada baris header:
                            </p>
                            <ul className="space-y-2 text-sm text-gray-700">
                                {templateColumns.map((column) => (
                                    <li key={column} className="rounded bg-gray-50 px-2 py-1 font-mono text-xs">
                                        {column}
                                    </li>
                                ))}
                            </ul>
                            <p className="mt-4 text-xs text-gray-500">
                                Nilai gender harus salah satu: laki-laki atau perempuan.
                            </p>
                            <p className="mt-1 text-xs text-gray-500">
                                Format tanggal: YYYY-MM-DD (contoh: 2026-05-10).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
