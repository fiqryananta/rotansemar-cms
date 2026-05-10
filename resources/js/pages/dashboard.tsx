import { Head } from '@inertiajs/react';
import { ClipboardCheck, Hourglass, LoaderCircle, ShieldX, CheckCircle2 } from 'lucide-react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type StatusCounts = {
    pending: number;
    proses: number;
    pending_bantuan: number;
    tidak_layak: number;
    selesai: number;
};

type KebutuhanSummary = {
    jenis_kebutuhan_id: number;
    jenis_kebutuhan_name: string;
    total_pasien: number;
    pending_count: number;
    proses_count: number;
    pending_bantuan_count: number;
    tidak_layak_count: number;
    selesai_count: number;
};

interface Props {
    statusCounts: StatusCounts;
    kebutuhanSummary: KebutuhanSummary[];
}

export default function Dashboard({ statusCounts, kebutuhanSummary }: Props) {
    const statusCards = [
        {
            key: 'pending',
            label: 'Pending',
            value: statusCounts.pending,
            icon: Hourglass,
            accent: 'bg-gray-100 text-gray-700',
        },
        {
            key: 'proses',
            label: 'Proses',
            value: statusCounts.proses,
            icon: LoaderCircle,
            accent: 'bg-blue-100 text-blue-700',
        },
        {
            key: 'pending_bantuan',
            label: 'Pending Bantuan',
            value: statusCounts.pending_bantuan,
            icon: ClipboardCheck,
            accent: 'bg-amber-100 text-amber-700',
        },
        {
            key: 'tidak_layak',
            label: 'Tidak Layak',
            value: statusCounts.tidak_layak,
            icon: ShieldX,
            accent: 'bg-rose-100 text-rose-700',
        },
        {
            key: 'selesai',
            label: 'Selesai',
            value: statusCounts.selesai,
            icon: CheckCircle2,
            accent: 'bg-emerald-100 text-emerald-700',
        },
    ] as const;

    return (
        <>
            <Head title="Dashboard" />

            <div className="bg-gray-50 min-h-screen">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <ClipboardCheck className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
                        </div>
                    </div>

                    <div className="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        {statusCards.map((card) => {
                            const Icon = card.icon;
                            return (
                                <div key={card.key} className="rounded-lg border bg-white p-4 shadow-sm">
                                    <div className="mb-3 flex items-center justify-between">
                                        <span className={`inline-flex rounded-md px-2 py-1 text-xs font-semibold ${card.accent}`}>
                                            {card.label}
                                        </span>
                                        <Icon className="h-4 w-4 text-gray-500" />
                                    </div>
                                    <p className="text-3xl font-bold text-gray-900">{card.value}</p>
                                </div>
                            );
                        })}
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div className="border-b border-gray-200 p-4">
                            <h2 className="text-lg font-semibold text-gray-900">Jumlah Pasien Berdasarkan Kebutuhan</h2>
                        </div>

                        <div className="px-4 pb-4">
                            <div className="overflow-x-auto rounded-md border border-gray-100">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-gray-50">
                                            <TableHead className="font-semibold">Jenis Kebutuhan</TableHead>
                                            <TableHead className="text-center font-semibold">Total Pasien</TableHead>
                                            <TableHead className="text-center font-semibold">Pending</TableHead>
                                            <TableHead className="text-center font-semibold">Proses</TableHead>
                                            <TableHead className="text-center font-semibold">Pending Bantuan</TableHead>
                                            <TableHead className="text-center font-semibold">Tidak Layak</TableHead>
                                            <TableHead className="text-center font-semibold">Selesai</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {kebutuhanSummary.length > 0 ? (
                                            kebutuhanSummary.map((row) => (
                                                <TableRow key={row.jenis_kebutuhan_id} className="hover:bg-gray-50">
                                                    <TableCell className="font-medium">{row.jenis_kebutuhan_name}</TableCell>
                                                    <TableCell className="text-center font-semibold">{row.total_pasien}</TableCell>
                                                    <TableCell className="text-center">{row.pending_count}</TableCell>
                                                    <TableCell className="text-center">{row.proses_count}</TableCell>
                                                    <TableCell className="text-center">{row.pending_bantuan_count}</TableCell>
                                                    <TableCell className="text-center">{row.tidak_layak_count}</TableCell>
                                                    <TableCell className="text-center">{row.selesai_count}</TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell colSpan={7} className="py-10 text-center text-gray-400">
                                                    Belum ada data kebutuhan pasien.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
