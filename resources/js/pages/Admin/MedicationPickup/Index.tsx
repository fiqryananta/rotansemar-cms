import { useEffect, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Edit2, Pill, Plus, Search, Trash2 } from 'lucide-react';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import type { PaginatedData } from '@/types';

interface Pasien {
    id: number;
    name: string;
    nik: string;
}

interface Faskes {
    id: number;
    name: string;
}

interface Puskesmas {
    id: number;
    name: string;
}

interface MedicationPickup {
    id: number;
    pasien_id: number;
    pasien?: Pasien;
    faskes_id: number | null;
    faskes?: Faskes | null;
    puskesmas_id: number | null;
    puskesmas?: Puskesmas | null;
    scheduled_date: string;
    status: string;
    actual_date: string | null;
    transfer_date: string | null;
    next_pickup_date: string | null;
    notes: string | null;
}

interface Props {
    pickups: PaginatedData<MedicationPickup>;
    statuses: Record<string, string>;
    filters: {
        search?: string;
        status?: string;
        per_page?: number;
    };
}

const statusColors: Record<string, string> = {
    menunggu: 'bg-slate-100 text-slate-800',
    terrealisasi: 'bg-green-100 text-green-800',
    pindah: 'bg-purple-100 text-purple-800',
    putus_obat: 'bg-orange-100 text-orange-800',
    meninggal: 'bg-red-100 text-red-800',
    obat_terakhir: 'bg-blue-100 text-blue-800',
};

const formatDateDMY = (value: string) => {
    if (!value) return '-';

    const match = value.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) return value;

    const [, year, month, day] = match;
    return `${Number(day)}-${Number(month)}-${year.slice(-2)}`;
};

export default function MedicationPickupIndex({
    pickups,
    statuses,
    filters,
}: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [statusFilter, setStatusFilter] = useState(filters?.status ?? '');
    const [deletingPickup, setDeletingPickup] =
        useState<MedicationPickup | null>(null);

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('medication-pickups.index'),
                {
                    search: searchTerm || undefined,
                    status: statusFilter || undefined,
                    per_page:
                        filters?.per_page ?? pickups.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm, statusFilter]);

    const handleDelete = (id: number) => {
        destroy(route('medication-pickups.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingPickup(null),
        });
    };

    return (
        <>
            <Head title="Manajemen Pengambilan Obat" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <Pill className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Pengambilan Obat
                            </h1>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex flex-1 flex-col gap-3 sm:flex-row sm:max-w-2xl">
                                    <div className="relative flex-1">
                                        <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                        <Input
                                            type="text"
                                            placeholder="Cari pasien atau faskes"
                                            value={searchTerm}
                                            onChange={(event) =>
                                                setSearchTerm(
                                                    event.target.value,
                                                )
                                            }
                                            className="pl-10"
                                        />
                                    </div>
                                    <Select
                                        value={statusFilter || ''}
                                        onValueChange={setStatusFilter}
                                    >
                                        <SelectTrigger className="w-full sm:w-40">
                                            <SelectValue placeholder="Semua Status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(statuses).map(
                                                ([key, label]) => (
                                                    <SelectItem
                                                        key={key}
                                                        value={key}
                                                    >
                                                        {label}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <Button asChild className="gap-2">
                                    <Link
                                        href={route(
                                            'medication-pickups.create',
                                        )}
                                    >
                                        <Plus className="h-4 w-4" /> Tambah
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <div className="px-4 pb-4">
                            <div className="overflow-x-auto rounded-md border border-gray-100">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-gray-50">
                                            <TableHead className="font-semibold">
                                                Pasien
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Faskes / Puskesmas
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Jadwal
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Status
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {pickups.data.length > 0 ? (
                                            pickups.data.map((item) => (
                                                <TableRow
                                                    key={item.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <TableCell className="font-medium">
                                                        <div>
                                                            <p>
                                                                {item.pasien
                                                                    ?.name}
                                                            </p>
                                                            <p className="text-sm text-gray-500">
                                                                {item.pasien
                                                                    ?.nik}
                                                            </p>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="text-sm">
                                                            {item.faskes && (
                                                                <p className="font-medium">
                                                                    {
                                                                        item
                                                                            .faskes
                                                                            .name
                                                                    }
                                                                </p>
                                                            )}
                                                            {item.puskesmas && (
                                                                <p className="text-gray-600">
                                                                    {
                                                                        item
                                                                            .puskesmas
                                                                            .name
                                                                    }
                                                                </p>
                                                            )}
                                                            {!item.faskes &&
                                                                !item.puskesmas && (
                                                                    <p className="text-gray-500">
                                                                        -
                                                                    </p>
                                                                )}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {formatDateDMY(item.scheduled_date)}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge
                                                            className={
                                                                statusColors[
                                                                    item.status
                                                                ] || 'bg-gray-100 text-gray-800'
                                                            }
                                                        >
                                                            {
                                                                statuses[
                                                                    item.status
                                                                ]
                                                            }
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <div className="flex justify-end gap-2">
                                                            <Button
                                                                asChild
                                                                variant="outline"
                                                                className="h-9"
                                                            >
                                                                <Link
                                                                    href={route(
                                                                        'medication-pickups.edit',
                                                                        item.id,
                                                                    )}
                                                                >
                                                                    <Edit2 className="h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    setDeletingPickup(
                                                                        item,
                                                                    )
                                                                }
                                                                className="inline-flex items-center gap-2 rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                                                            >
                                                                <Trash2 className="h-4 w-4" />{' '}
                                                                Hapus{' '}
                                                            </button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={5}
                                                    className="py-8 text-center"
                                                >
                                                    <p className="text-gray-500">
                                                        Tidak ada data
                                                        pengambilan obat
                                                    </p>
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        {pickups.last_page > 1 && (
                            <div className="border-t border-gray-200 px-4 py-4">
                                <TablePagination paginator={pickups} />
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {deletingPickup && (
                <ConfirmDeleteDialog
                    open={Boolean(deletingPickup)}
                    onOpenChange={(open) => !open && setDeletingPickup(null)}
                    title="Hapus Pengambilan Obat"
                    description={`Apakah Anda yakin ingin menghapus data pengambilan obat untuk ${deletingPickup.pasien?.name}?`}
                    processing={processing}
                    onConfirm={() => handleDelete(deletingPickup.id)}
                />
            )}
        </>
    );
}
