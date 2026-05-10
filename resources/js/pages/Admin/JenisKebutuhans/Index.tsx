import { useEffect, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Edit2, Plus, Search, Trash2, Wrench } from 'lucide-react';
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

interface JenisPenanganan {
    id: number;
    name: string;
}

interface JenisKebutuhan {
    id: number;
    name: string;
    jenis_penanganans?: JenisPenanganan[];
}

interface Props {
    jenisKebutuhans: PaginatedData<JenisKebutuhan>;
    filters: {
        search?: string;
        per_page?: number;
    };
}

export default function JenisKebutuhansIndex({
    jenisKebutuhans,
    filters,
}: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [deletingData, setDeletingData] = useState<JenisKebutuhan | null>(
        null,
    );

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('jenis-kebutuhans.index'),
                {
                    search: searchTerm || undefined,
                    per_page:
                        filters?.per_page ?? jenisKebutuhans.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm]);

    const handleDelete = (id: number) => {
        destroy(route('jenis-kebutuhans.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingData(null),
        });
    };

    return (
        <>
            <Head title="Manajemen Jenis Kebutuhan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <Wrench className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Jenis Kebutuhan
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
                                        placeholder="Cari Jenis Kebutuhan"
                                        value={searchTerm}
                                        onChange={(event) =>
                                            setSearchTerm(event.target.value)
                                        }
                                        className="pl-10"
                                    />
                                </div>
                                <Button asChild className="gap-2">
                                    <Link
                                        href={route('jenis-kebutuhans.create')}
                                    >
                                        <Plus className="h-4 w-4" /> Tambah
                                        Jenis Kebutuhan{' '}
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
                                                Nama Kebutuhan
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Jenis Penanganan
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {jenisKebutuhans.data.length > 0 ? (
                                            jenisKebutuhans.data.map((item) => (
                                                <TableRow
                                                    key={item.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <TableCell className="font-medium">
                                                        {item.name}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex flex-wrap gap-2">
                                                            {(
                                                                item.jenis_penanganans ??
                                                                []
                                                            ).length > 0 ? (
                                                                (
                                                                    item.jenis_penanganans ??
                                                                    []
                                                                ).map(
                                                                    (jenis) => (
                                                                        <Badge
                                                                            key={
                                                                                jenis.id
                                                                            }
                                                                            variant="secondary"
                                                                        >
                                                                            {
                                                                                jenis.name
                                                                            }
                                                                        </Badge>
                                                                    ),
                                                                )
                                                            ) : (
                                                                <span className="text-sm text-gray-500">
                                                                    Belum ada
                                                                    jenis
                                                                    penanganan
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
                                                                        'jenis-kebutuhans.edit',
                                                                        item.id,
                                                                    )}
                                                                >
                                                                    <Edit2 className="h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                            <button
                                                                onClick={() =>
                                                                    setDeletingData(
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
                                                    Belum Ada Jenis
                                                    Kebutuhan{' '}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={jenisKebutuhans.links}
                            from={jenisKebutuhans.from}
                            to={jenisKebutuhans.to}
                            total={jenisKebutuhans.total}
                        />
                    </div>
                </div>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(deletingData)}
                onOpenChange={(open) => !open && setDeletingData(null)}
                title="Hapus Jenis Kebutuhan"
                description={`Yakin ingin menghapus ${deletingData?.name ?? ''}? Tindakan ini tidak bisa dibatalkan.`}
                onConfirm={() => deletingData && handleDelete(deletingData.id)}
                processing={processing}
            />
        </>
    );
}
