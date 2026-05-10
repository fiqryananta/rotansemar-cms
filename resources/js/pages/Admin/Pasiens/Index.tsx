import { useEffect, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Edit2, Eye, FileUp, Plus, Search, Trash2, UserRound } from 'lucide-react';
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
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import type { PaginatedData } from '@/types';

interface RelationOption {
    id: number;
    name: string;
}

interface Pasien {
    id: number;
    name: string;
    nik: string;
    treatment_start_date: string;
    faskes?: RelationOption;
    kelurahan?: RelationOption;
}

interface Props {
    pasiens: PaginatedData<Pasien>;
    filters?: {
        search?: string;
        per_page?: number;
    };
}

export default function PasienIndex({ pasiens, filters }: Props) {
    const { auth } = usePage().props as { auth?: { roles?: string[] } };
    const roles = auth?.roles ?? [];
    const isAdmin = roles.some((role) => role.toLowerCase() === 'admin');
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [deletingPasien, setDeletingPasien] = useState<Pasien | null>(null);

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('pasiens.index'),
                {
                    search: searchTerm || undefined,
                    per_page: filters?.per_page ?? pasiens.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm]);

    const handleDelete = (id: number) => {
        destroy(route('pasiens.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingPasien(null),
        });
    };

    return (
        <>
            <Head title="Manajemen Pasien" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            <UserRound className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Pasien
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
                                        placeholder="Cari Pasien"
                                        value={searchTerm}
                                        onChange={(event) =>
                                            setSearchTerm(event.target.value)
                                        }
                                        className="pl-10"
                                    />
                                </div>

                                <div className="flex items-center gap-2">
                                    {isAdmin && (
                                        <Button asChild variant="outline" className="gap-2">
                                            <Link href={route('pasiens.import.index')}>
                                                <FileUp className="h-4 w-4" /> Import Pasien
                                            </Link>
                                        </Button>
                                    )}
                                    <Button asChild className="gap-2">
                                        <Link href={route('pasiens.create')}>
                                            <Plus className="h-4 w-4" /> Tambah
                                            Pasien{' '}
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div className="px-4 pb-4">
                            <div className="overflow-x-auto rounded-md border border-gray-100">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-gray-50">
                                            <TableHead className="font-semibold">
                                                Identitas
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Tanggal Mulai Pengobatan
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Faskes
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
                                        {pasiens.data.length > 0 ? (
                                            pasiens.data.map((item) => (
                                                <TableRow
                                                    key={item.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <TableCell>
                                                        <p className="font-medium text-gray-900">
                                                            {item.name}
                                                        </p>
                                                        <p className="text-sm text-gray-500">
                                                            {item.nik}
                                                        </p>
                                                    </TableCell>
                                                    <TableCell>
                                                        {formatDateDMY(
                                                            item.treatment_start_date,
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        {item.faskes?.name ??
                                                            '-'}
                                                    </TableCell>
                                                    <TableCell>
                                                        {item.kelurahan?.name ??
                                                            '-'}
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
                                                                        'pasiens.show',
                                                                        item.id,
                                                                    )}
                                                                >
                                                                    <Eye className="h-4 w-4" />
                                                                    Detail
                                                                </Link>
                                                            </Button>
                                                            <Button
                                                                asChild
                                                                variant="outline"
                                                                className="h-9"
                                                            >
                                                                <Link
                                                                    href={route(
                                                                        'pasiens.edit',
                                                                        item.id,
                                                                    )}
                                                                >
                                                                    <Edit2 className="h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                            <button
                                                                onClick={() =>
                                                                    setDeletingPasien(
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
                                                    className="py-8 text-center text-gray-500"
                                                >
                                                    {' '}
                                                    Belum Ada Pasien{' '}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={pasiens.links}
                            from={pasiens.from}
                            to={pasiens.to}
                            total={pasiens.total}
                        />
                    </div>
                </div>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(deletingPasien)}
                onOpenChange={(open) => !open && setDeletingPasien(null)}
                title="Hapus Pasien"
                description={`Yakin ingin menghapus data pasien ${deletingPasien?.name ?? ''}? Tindakan ini tidak bisa dibatalkan.`}
                onConfirm={() =>
                    deletingPasien && handleDelete(deletingPasien.id)
                }
                processing={processing}
            />
        </>
    );
}
