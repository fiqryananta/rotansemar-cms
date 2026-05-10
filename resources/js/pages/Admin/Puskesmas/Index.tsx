import { useEffect, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Edit2, HeartPulse, Plus, Search, Trash2 } from 'lucide-react';
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
import { Badge } from '@/components/ui/badge';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import type { PaginatedData } from '@/types';

interface Kecamatan {
    id: number;
    name: string;
}

interface Kelurahan {
    id: number;
    name: string;
    kecamatan_id: number;
    kecamatan?: Kecamatan;
}

interface Puskesmas {
    id: number;
    name: string;
    kelurahans: Kelurahan[];
}

interface Props {
    puskesmas: PaginatedData<Puskesmas>;
    filters: {
        search?: string;
        per_page?: number;
    };
}

export default function PuskesmasIndex({ puskesmas, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [deletingPuskesmas, setDeletingPuskesmas] =
        useState<Puskesmas | null>(null);

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('puskesmas.index'),
                {
                    search: searchTerm || undefined,
                    per_page: filters?.per_page ?? puskesmas.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm]);

    const handleDelete = (id: number) => {
        destroy(route('puskesmas.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingPuskesmas(null),
        });
    };

    return (
        <>
            <Head title="Manajemen Puskesmas" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <HeartPulse className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Puskesmas
                            </h1>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="relative flex-1 sm:max-w-xs">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        type="text"
                                        placeholder="Cari Puskesmas"
                                        value={searchTerm}
                                        onChange={(event) =>
                                            setSearchTerm(event.target.value)
                                        }
                                        className="pl-10"
                                    />
                                </div>
                                <Button asChild className="gap-2">
                                    <Link href={route('puskesmas.create')}>
                                        <Plus className="h-4 w-4" /> Tambah
                                        Puskesmas{' '}
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
                                                Nama Puskesmas
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Kelurahan
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {puskesmas.data.length > 0 ? (
                                            puskesmas.data.map((item) => (
                                                <TableRow
                                                    key={item.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <TableCell className="font-medium">
                                                        {item.name}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex flex-wrap gap-2">
                                                            {item.kelurahans
                                                                .length > 0 ? (
                                                                item.kelurahans.map(
                                                                    (
                                                                        kelurahan,
                                                                    ) => (
                                                                        <Badge
                                                                            key={
                                                                                kelurahan.id
                                                                            }
                                                                            variant="secondary"
                                                                        >
                                                                            {
                                                                                kelurahan.name
                                                                            }
                                                                        </Badge>
                                                                    ),
                                                                )
                                                            ) : (
                                                                <span className="text-sm text-gray-500">
                                                                    Belum ada
                                                                    kelurahan
                                                                </span>
                                                            )}
                                                        </div>
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
                                                                        'puskesmas.edit',
                                                                        item.id,
                                                                    )}
                                                                >
                                                                    <Edit2 className="h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                            <button
                                                                onClick={() =>
                                                                    setDeletingPuskesmas(
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
                                                    colSpan={3}
                                                    className="py-8 text-center text-gray-500"
                                                >
                                                    {' '}
                                                    Belum Ada Puskesmas{' '}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={puskesmas.links}
                            from={puskesmas.from}
                            to={puskesmas.to}
                            total={puskesmas.total}
                        />
                    </div>
                </div>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(deletingPuskesmas)}
                onOpenChange={(open) => !open && setDeletingPuskesmas(null)}
                title="Hapus Puskesmas"
                description={`Yakin ingin menghapus Puskesmas ${deletingPuskesmas?.name ?? ''}? Tindakan ini tidak bisa dibatalkan.`}
                onConfirm={() =>
                    deletingPuskesmas && handleDelete(deletingPuskesmas.id)
                }
                processing={processing}
            />
        </>
    );
}
