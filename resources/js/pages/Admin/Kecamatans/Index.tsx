import { useEffect, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Building, Edit2, Plus, Search, Trash2 } from 'lucide-react';
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
    kelurahans_count?: number;
}

interface Props {
    kecamatans: PaginatedData<Kecamatan>;
    filters: {
        search?: string;
        per_page?: number;
    };
}

export default function KecamatansIndex({ kecamatans, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [deletingKecamatan, setDeletingKecamatan] =
        useState<Kecamatan | null>(null);

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('kecamatans.index'),
                {
                    search: searchTerm || undefined,
                    per_page: filters?.per_page ?? kecamatans.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm]);

    const handleDelete = (id: number) => {
        destroy(route('kecamatans.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingKecamatan(null),
        });
    };

    return (
        <>
            <Head title="Manajemen Kecamatan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <Building className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Kecamatan
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
                                        placeholder="Cari Kecamatan"
                                        value={searchTerm}
                                        onChange={(e) =>
                                            setSearchTerm(e.target.value)
                                        }
                                        className="pl-10"
                                    />
                                </div>
                                <Button asChild className="gap-2">
                                    <Link href={route('kecamatans.create')}>
                                        <Plus className="h-4 w-4" /> Tambah
                                        Kecamatan{' '}
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
                                                Nama Kecamatan
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Jumlah Kelurahan
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {kecamatans.data.length > 0 ? (
                                            kecamatans.data.map((kecamatan) => (
                                                <TableRow
                                                    key={kecamatan.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <TableCell className="font-medium">
                                                        {kecamatan.name}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant="secondary">
                                                            {kecamatan.kelurahans_count ||
                                                                0}
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
                                                                        'kecamatans.edit',
                                                                        kecamatan.id,
                                                                    )}
                                                                >
                                                                    <Edit2 className="h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                            <button
                                                                onClick={() =>
                                                                    setDeletingKecamatan(
                                                                        kecamatan,
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
                                                    Belum Ada Kecamatan{' '}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={kecamatans.links}
                            from={kecamatans.from}
                            to={kecamatans.to}
                            total={kecamatans.total}
                        />
                    </div>
                </div>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(deletingKecamatan)}
                onOpenChange={(open) => !open && setDeletingKecamatan(null)}
                title="Hapus Kecamatan"
                description={`Yakin ingin menghapus Kecamatan ${deletingKecamatan?.name ?? ''}? Tindakan ini tidak bisa dibatalkan.`}
                onConfirm={() =>
                    deletingKecamatan && handleDelete(deletingKecamatan.id)
                }
                processing={processing}
            />
        </>
    );
}
