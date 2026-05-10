import { useEffect, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Edit2, Megaphone, Plus, Search, Trash2 } from 'lucide-react';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDateDMY } from '@/lib/date';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { PaginatedData } from '@/types';

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
    kegiatanPenyuluhans: PaginatedData<KegiatanPenyuluhan>;
    filters: {
        search?: string;
        per_page?: number;
    };
}

export default function KegiatanPenyuluhanIndex({
    kegiatanPenyuluhans,
    filters,
}: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [deletingData, setDeletingData] = useState<KegiatanPenyuluhan | null>(
        null,
    );

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('kegiatan-penyuluhan.index'),
                {
                    search: searchTerm || undefined,
                    per_page:
                        filters?.per_page ?? kegiatanPenyuluhans.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm]);

    const handleDelete = (id: number) => {
        destroy(route('kegiatan-penyuluhan.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingData(null),
        });
    };

    return (
        <>
            <Head title="Kegiatan Penyuluhan" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <Megaphone className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Kegiatan Penyuluhan
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
                                        placeholder="Cari Kegiatan"
                                        value={searchTerm}
                                        onChange={(event) =>
                                            setSearchTerm(event.target.value)
                                        }
                                        className="pl-10"
                                    />
                                </div>
                                <Button asChild className="gap-2">
                                    <Link
                                        href={route(
                                            'kegiatan-penyuluhan.create',
                                        )}
                                    >
                                        <Plus className="h-4 w-4" /> Tambah
                                        Kegiatan{' '}
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
                                                Nama Kegiatan
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Tanggal
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Lokasi
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Sasaran
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Jumlah Sasaran
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Foto
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {kegiatanPenyuluhans.data.length > 0 ? (
                                            kegiatanPenyuluhans.data.map(
                                                (item) => (
                                                    <TableRow
                                                        key={item.id}
                                                        className="hover:bg-gray-50"
                                                    >
                                                        <TableCell className="font-medium">
                                                            {item.nama_kegiatan}
                                                        </TableCell>
                                                        <TableCell>
                                                            {formatDateDMY(
                                                                item.tanggal_kegiatan,
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            {
                                                                item.lokasi_kegiatan
                                                            }
                                                        </TableCell>
                                                        <TableCell>
                                                            {item.sasaran}
                                                        </TableCell>
                                                        <TableCell>
                                                            {
                                                                item.jumlah_sasaran
                                                            }
                                                        </TableCell>
                                                        <TableCell>
                                                            {item.foto_kegiatan
                                                                ?.length ??
                                                                0}{' '}
                                                            foto
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
                                                                            'kegiatan-penyuluhan.edit',
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
                                                ),
                                            )
                                        ) : (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={7}
                                                    className="py-8 text-center text-gray-500"
                                                >
                                                    {' '}
                                                    Belum Ada Kegiatan
                                                    Penyuluhan{' '}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={kegiatanPenyuluhans.links}
                            from={kegiatanPenyuluhans.from}
                            to={kegiatanPenyuluhans.to}
                            total={kegiatanPenyuluhans.total}
                        />
                    </div>
                </div>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(deletingData)}
                onOpenChange={(open) => !open && setDeletingData(null)}
                title="Hapus Kegiatan Penyuluhan"
                description={`Yakin ingin menghapus ${deletingData?.nama_kegiatan ?? ''}? Tindakan ini tidak bisa dibatalkan.`}
                onConfirm={() => deletingData && handleDelete(deletingData.id)}
                processing={processing}
            />
        </>
    );
}
