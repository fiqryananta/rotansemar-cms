import { useEffect, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Edit2, Plus, Search, Trash2, Users } from 'lucide-react';
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

interface User {
    id: number;
    name: string;
    email: string;
    roles?: { id: number; name: string }[];
    opd?: { id: number; name: string } | null;
    faskes?: { id: number; name: string } | null;
    puskesmas?: { id: number; name: string } | null;
    kecamatan?: { id: number; name: string } | null;
    kelurahan?: { id: number; name: string } | null;
}

interface Props {
    users: PaginatedData<User>;
    filters: {
        search?: string;
        per_page?: number;
    };
}

export default function UsersIndex({ users, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search ?? '');
    const [deletingUser, setDeletingUser] = useState<User | null>(null);

    const { delete: destroy, processing } = useForm({});

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('users.index'),
                {
                    search: searchTerm || undefined,
                    per_page: filters?.per_page ?? users.per_page ?? 10,
                },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [searchTerm]);

    const handleDelete = (id: number) => {
        destroy(route('users.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeletingUser(null),
        });
    };

    return (
        <>
            <Head title="Manajemen User" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <Users className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                Users
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
                                        placeholder="Cari User"
                                        value={searchTerm}
                                        onChange={(e) =>
                                            setSearchTerm(e.target.value)
                                        }
                                        className="pl-10"
                                    />
                                </div>
                                <Button asChild className="gap-2">
                                    <Link href={route('users.create')}>
                                        <Plus className="h-4 w-4" /> Tambah
                                        User{' '}
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
                                                Name
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Email
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Roles
                                            </TableHead>
                                            <TableHead className="font-semibold">
                                                Unit Scope
                                            </TableHead>
                                            <TableHead className="text-right font-semibold">
                                                Aksi
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {users.data.length > 0 ? (
                                            users.data.map((user) => (
                                                <TableRow
                                                    key={user.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <TableCell className="font-medium">
                                                        {user.name}
                                                    </TableCell>
                                                    <TableCell className="text-gray-600">
                                                        {user.email}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex flex-wrap gap-2">
                                                            {user.roles &&
                                                            user.roles.length >
                                                                0 ? (
                                                                user.roles.map(
                                                                    (role) => (
                                                                        <Badge
                                                                            key={
                                                                                role.id
                                                                            }
                                                                            variant="outline"
                                                                        >
                                                                            {
                                                                                role.name
                                                                            }
                                                                        </Badge>
                                                                    ),
                                                                )
                                                            ) : (
                                                                <span className="text-sm text-gray-500">
                                                                    Belum ada
                                                                    role
                                                                </span>
                                                            )}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="text-gray-600">
                                                        {user.opd?.name ??
                                                            user.faskes?.name ??
                                                            user.puskesmas
                                                                ?.name ??
                                                            user.kecamatan
                                                                ?.name ??
                                                            user.kelurahan
                                                                ?.name ??
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
                                                                        'users.edit',
                                                                        user.id,
                                                                    )}
                                                                >
                                                                    <Edit2 className="h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                            <button
                                                                onClick={() =>
                                                                    setDeletingUser(
                                                                        user,
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
                                                    Belum Ada User{' '}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <TablePagination
                            links={users.links}
                            from={users.from}
                            to={users.to}
                            total={users.total}
                        />
                    </div>
                </div>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(deletingUser)}
                onOpenChange={(open) => !open && setDeletingUser(null)}
                title="Hapus User"
                description={`Yakin ingin menghapus user ${deletingUser?.name ?? ''}? Tindakan ini tidak bisa dibatalkan.`}
                onConfirm={() => deletingUser && handleDelete(deletingUser.id)}
                processing={processing}
            />
        </>
    );
}
