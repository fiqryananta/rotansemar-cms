import { Head } from '@inertiajs/react';
import { Download, FileBarChart2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Label } from '@/components/ui/label';

export default function LaporanIndex() {
    const [dari, setDari] = useState('');
    const [sampai, setSampai] = useState('');

    const handleDownload = () => {
        if (!dari || !sampai) return;
        window.location.href = `/laporan/export?dari=${encodeURIComponent(dari)}&sampai=${encodeURIComponent(sampai)}`;
    };

    return (
        <>
            <Head title="Laporan" />

            <div className="bg-gray-50 min-h-screen">
                <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <FileBarChart2 className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Laporan</h1>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-800">Pilih Rentang Tanggal</h2>

                        <div className="grid gap-6 md:grid-cols-2">
                            <div>
                                <Label htmlFor="dari">Tanggal Mulai</Label>
                                <DatePicker
                                    id="dari"
                                    value={dari}
                                    onChange={(val) => {
                                        setDari(val);
                                        if (sampai && sampai < val) {
                                            setSampai('');
                                        }
                                    }}
                                />
                            </div>

                            <div>
                                <Label htmlFor="sampai">Tanggal Selesai</Label>
                                <DatePicker
                                    id="sampai"
                                    value={sampai}
                                    onChange={setSampai}
                                />
                            </div>
                        </div>

                        <div className="mt-6 flex items-center gap-3">
                            <Button
                                onClick={handleDownload}
                                disabled={!dari || !sampai}
                                className="bg-cyan-600 hover:bg-cyan-700 text-white"
                            >
                                <Download className="mr-2 h-4 w-4" />
                                Download Laporan
                            </Button>

                            {(!dari || !sampai) && (
                                <p className="text-sm text-gray-400">
                                    Pilih tanggal mulai dan selesai terlebih dahulu
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
