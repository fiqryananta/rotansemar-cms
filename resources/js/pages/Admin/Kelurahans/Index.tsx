import { useEffect, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Edit2, MapPinned, Plus, Search, Trash2 } from 'lucide-react';
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

interface Props {
    kelurahans: PaginatedData<Kelurahan>;
    filters: {
        search?: string;
        per_page?: number;
    };
}

export default function KelurahansIndex({ kelurahans, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [deletingKelurahan, setDeletingKelurahan] =
        useState<Kelurahan | null>(null);

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('kelurahans.index'),
                {
                    search: searchTerm || undefined,
                    per_page: filters?.per_page ?? kelurahans.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm]);

    const handleDelete = (id: number) => {
        destroy(route('kelurahans.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingKelurahan(null),
        });
    };

    return (
        <>
            <Head title="Manajemen Kelurahan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <MapPinned className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Kelurahan
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
                                        placeholder="Cari Kelurahan"
                                        value={searchTerm}
                                        onChange={(e) =>
                                            setSearchTerm(e.target.value)
                                        }
                                        className="pl-10"
                                    />
                                </div>
                                <Button asChild className="gap-2">
                                    <Link href={route('kelurahans.create')}>
                                        <Plus className="h-4 w-4" /> Tambah
                                        Kelurahan{' '}
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
                                                Kecamatan
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Nama Kelurahan
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {kelurahans.data.length > 0 ? (
                                            kelurahans.data.map((kelurahan) => (
                                                <TableRow
                                                    key={kelurahan.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <TableCell className="text-gray-600">
                                                        {
                                                            kelurahan.kecamatan
                                                                ?.name
                                                        }
                                                    </TableCell>
                                                    <TableCell className="font-medium">
                                                        {kelurahan.name}
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
                                                                        'kelurahans.edit',
                                                                        kelurahan.id,
                                                                    )}
                                                                >
                                                                    <Edit2 className="h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                            <button
                                                                onClick={() =>
                                                                    setDeletingKelurahan(
                                                                        kelurahan,
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
                                                    Belum Ada Kelurahan{' '}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={kelurahans.links}
                            from={kelurahans.from}
                            to={kelurahans.to}
                            total={kelurahans.total}
                        />
                    </div>
                </div>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(deletingKelurahan)}
                onOpenChange={(open) => !open && setDeletingKelurahan(null)}
                title="Hapus Kelurahan"
                description={`Yakin ingin menghapus Kelurahan ${deletingKelurahan?.name ?? ''}? Tindakan ini tidak bisa dibatalkan.`}
                onConfirm={() =>
                    deletingKelurahan && handleDelete(deletingKelurahan.id)
                }
                processing={processing}
            />
        </>
    );
}
