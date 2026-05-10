import { useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ClipboardCheck, Search } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import TablePagination from '@/components/table-pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { PaginatedData } from '@/types';

interface Pasien {
    id: number;
    name: string;
    nik: string;
}
interface Opd {
    id: number;
    name: string;
}
interface JenisPenanganan {
    id: number;
    name: string;
}
interface JenisKebutuhan {
    id: number;
    name: string;
}

interface Kebutuhan {
    id: number;
    need_detail: string;
    verification_status: string;
    tindak_lanjuts_count: number;
    pasien: Pasien;
    opd: Opd;
    jenis_penanganan: JenisPenanganan;
    jenis_kebutuhan: JenisKebutuhan;
}

interface Props {
    kebutuhans: PaginatedData<Kebutuhan>;
    opds: Opd[];
    filters: {
        search: string;
        status: string;
        opd_id: number | '';
        per_page?: number;
    };
}

const statusConfig: Record<
    string,
    {
        label: string;
        variant: 'default' | 'secondary' | 'destructive' | 'outline' | null;
        className: string;
    }
> = {
    pending: {
        label: 'Menunggu Verifikasi',
        variant: 'outline',
        className: 'border-gray-400 text-gray-600',
    },
    proses: {
        label: 'Proses',
        variant: 'default',
        className: 'bg-blue-100 text-blue-700 border-blue-200',
    },
    pending_bantuan: {
        label: 'Pending Bantuan',
        variant: 'outline',
        className: 'border-amber-400 text-amber-700 bg-amber-50',
    },
    tidak_layak: {
        label: 'Tidak Layak',
        variant: 'destructive',
        className: '',
    },
    selesai: {
        label: 'Selesai',
        variant: 'outline',
        className: 'border-green-500 text-green-700 bg-green-50',
    },
};

export default function TindakLanjutIndex({
    kebutuhans,
    opds,
    filters: initialFilters,
}: Props) {
    const [search, setSearch] = useState(initialFilters.search);
    const [status, setStatus] = useState(initialFilters.status);
    const [opdId, setOpdId] = useState<number | ''>(initialFilters.opd_id);

    const applyFilters = (
        overrides: Partial<{
            search: string;
            status: string;
            opd_id: number | '';
        }> = {},
    ) => {
        const params: Record<string, string | number> = {};
        const s = overrides.search !== undefined ? overrides.search : search;
        const st = overrides.status !== undefined ? overrides.status : status;
        const o = overrides.opd_id !== undefined ? overrides.opd_id : opdId;
        params.per_page = initialFilters.per_page ?? kebutuhans.per_page ?? 10;
        if (s) params.search = s;
        if (st) params.status = st;
        if (o !== '') params.opd_id = o;
        router.get(route('tindak-lanjut.index'), params, {
            preserveState: true,
            replace: true,
        });
    };

    const statusCounts = useMemo(() => {
        const counts: Record<string, number> = {
            pending: 0,
            proses: 0,
            pending_bantuan: 0,
            tidak_layak: 0,
            selesai: 0,
        };
        kebutuhans.data.forEach((k) => {
            counts[k.verification_status] =
                (counts[k.verification_status] ?? 0) + 1;
        });
        return counts;
    }, [kebutuhans]);

    return (
        <>
            <Head title="Tindak Lanjut" />
            <div className="min-h-screen bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <ClipboardCheck className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Tindak Lanjut Penanganan
                            </h1>
                        </div>
                    </div>

                    {/* Status Summary */}
                    <div className="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
                        {Object.entries(statusConfig).map(([key, cfg]) => (
                            <button
                                key={key}
                                onClick={() => {
                                    const next = status === key ? '' : key;
                                    setStatus(next);
                                    applyFilters({ status: next });
                                }}
                                className={`rounded-lg border p-3 text-center transition-all ${status === key ? 'ring-2 ring-indigo-400' : 'hover:bg-white'} bg-white shadow-sm`}
                            >
                                <p className="text-2xl font-bold text-gray-900">
                                    {statusCounts[key] ?? 0}
                                </p>
                                <p className="mt-1 text-xs text-gray-500">
                                    {cfg.label}
                                </p>
                            </button>
                        ))}
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        {/* Filter Bar */}
                        <div className="border-b border-gray-200 p-4">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div className="relative flex-1 sm:max-w-xs">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        placeholder="Cari pasien / NIK..."
                                        value={search}
                                        onChange={(e) =>
                                            setSearch(e.target.value)
                                        }
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter')
                                                applyFilters({ search });
                                        }}
                                        className="pl-10"
                                    />
                                </div>
                                <select
                                    value={status}
                                    onChange={(e) => {
                                        setStatus(e.target.value);
                                        applyFilters({
                                            status: e.target.value,
                                        });
                                    }}
                                    className="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                                >
                                    <option value="">Semua Status</option>
                                    {Object.entries(statusConfig).map(
                                        ([key, cfg]) => (
                                            <option key={key} value={key}>
                                                {cfg.label}
                                            </option>
                                        ),
                                    )}
                                </select>
                                <select
                                    value={opdId === '' ? '' : String(opdId)}
                                    onChange={(e) => {
                                        const val =
                                            e.target.value === ''
                                                ? ''
                                                : Number(e.target.value);
                                        setOpdId(val);
                                        applyFilters({ opd_id: val });
                                    }}
                                    className="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                                >
                                    <option value="">Semua OPD</option>
                                    {opds.map((opd) => (
                                        <option key={opd.id} value={opd.id}>
                                            {opd.name}
                                        </option>
                                    ))}
                                </select>
                                <Button
                                    variant="outline"
                                    onClick={() => applyFilters({ search })}
                                >
                                    Cari
                                </Button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="px-4 pb-4">
                            <div className="overflow-x-auto rounded-md border border-gray-100">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-gray-50">
                                            <TableHead className="font-semibold">
                                                Pasien
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Jenis Kebutuhan
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                OPD
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Jenis Penanganan
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Status
                                            </TableHead>
                                            <TableHead className="text-center font-semibold">
                                                Tindak Lanjut
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {kebutuhans.data.length > 0 ? (
                                            kebutuhans.data.map((item) => {
                                                const cfg =
                                                    statusConfig[
                                                        item.verification_status
                                                    ] ?? statusConfig.pending;
                                                return (
                                                    <TableRow
                                                        key={item.id}
                                                        className="hover:bg-gray-50"
                                                    >
                                                        <TableCell>
                                                            <p className="font-medium text-gray-900">
                                                                {
                                                                    item.pasien
                                                                        .name
                                                                }
                                                            </p>
                                                            <p className="text-xs text-gray-500">
                                                                {
                                                                    item.pasien
                                                                        .nik
                                                                }
                                                            </p>
                                                        </TableCell>
                                                        <TableCell className="text-sm">
                                                            {item
                                                                .jenis_kebutuhan
                                                                ?.name ?? '-'}
                                                        </TableCell>
                                                        <TableCell className="text-sm">
                                                            {item.opd?.name ??
                                                                '-'}
                                                        </TableCell>
                                                        <TableCell className="text-sm">
                                                            {item
                                                                .jenis_penanganan
                                                                ?.name ?? '-'}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Badge
                                                                variant={
                                                                    cfg.variant ??
                                                                    'outline'
                                                                }
                                                                className={
                                                                    cfg.className
                                                                }
                                                            >
                                                                {cfg.label}
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell className="text-center text-sm">
                                                            {item.tindak_lanjuts_count >
                                                            0 ? (
                                                                <span className="font-medium text-indigo-600">
                                                                    {
                                                                        item.tindak_lanjuts_count
                                                                    }
                                                                    x
                                                                </span>
                                                            ) : (
                                                                <span className="text-gray-400">
                                                                    -
                                                                </span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <Button
                                                                asChild
                                                                variant="outline"
                                                                size="sm"
                                                            >
                                                                <Link
                                                                    href={route(
                                                                        'tindak-lanjut.show',
                                                                        item.id,
                                                                    )}
                                                                >
                                                                    Detail
                                                                </Link>
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })
                                        ) : (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={7}
                                                    className="py-10 text-center text-gray-400"
                                                >
                                                    Tidak ada data penanganan.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={kebutuhans.links}
                            from={kebutuhans.from}
                            to={kebutuhans.to}
                            total={kebutuhans.total}
                        />
                    </div>
                </div>
            </div>
        </>
    );
}
