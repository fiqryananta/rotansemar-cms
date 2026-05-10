import { useMemo, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Edit2, Lock, Plus, Search, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { Label } from '@/components/ui/label';

interface Permission {
    id: number;
    name: string;
    roles_count?: number;
}

interface Props {
    permissions: Permission[];
}

export default function PermissionsIndex({ permissions: initialPermissions }: Props) {
    const [searchTerm, setSearchTerm] = useState('');
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingPermission, setEditingPermission] = useState<Permission | null>(null);

    const { data, setData, post, patch, delete: destroy, processing, errors } = useForm({
        name: '',
    });

    const filteredPermissions = useMemo(() => {
        return initialPermissions.filter(permission =>
            permission.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [initialPermissions, searchTerm]);

    const handleCloseDialog = () => {
        setIsDialogOpen(false);
        setEditingPermission(null);
        setData('name', '');
    };

    const handleOpenDialog = (permission?: Permission) => {
        if (permission) {
            setEditingPermission(permission);
            setData('name', permission.name);
        } else {
            setEditingPermission(null);
            setData('name', '');
        }
        setIsDialogOpen(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingPermission) {
            patch(route('permissions.update', editingPermission.id), {
                preserveScroll: true,
                onSuccess: () => setIsDialogOpen(false),
            });
        } else {
            post(route('permissions.store'), {
                preserveScroll: true,
                onSuccess: () => setIsDialogOpen(false),
            });
        }
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this permission?')) {
            destroy(route('permissions.destroy', id));
        }
    };

    return (
        <>
            <Head title="Permissions Management" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <Lock className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Permissions</h1>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="relative flex-1 sm:max-w-xs">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        type="text"
                                        placeholder="Search Permissions..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                                <Button
                                    onClick={() => handleOpenDialog()}
                                    className="gap-2"
                                >
                                    <Plus className="h-4 w-4" />
                                    Add Permission
                                </Button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-50">
                                        <TableHead className="font-semibold">Permission Name</TableHead>
                                        <TableHead className="font-semibold">Used by Roles</TableHead>
                                        <TableHead className="text-right font-semibold">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredPermissions.length > 0 ? (
                                        filteredPermissions.map((permission) => (
                                            <TableRow key={permission.id} className="hover:bg-gray-50">
                                                <TableCell className="font-mono font-medium">{permission.name}</TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">
                                                        {permission.roles_count || 0}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            onClick={() => handleOpenDialog(permission)}
                                                            className="inline-flex items-center gap-2 rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                                                        >
                                                            <Edit2 className="h-4 w-4" />
                                                            Edit
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(permission.id)}
                                                            className="inline-flex items-center gap-2 rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                            Delete
                                                        </button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={3} className="py-8 text-center text-gray-500">
                                                No permissions found
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>

            <Dialog open={isDialogOpen} onOpenChange={(open) => !open && handleCloseDialog()}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{editingPermission ? 'Edit Permission' : 'Add New Permission'}</DialogTitle>
                        <DialogDescription>
                            {editingPermission ? 'Update nama permission yang digunakan aplikasi.' : 'Tambahkan permission baru dengan format dot notation.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <Label htmlFor="name">Permission Name</Label>
                            <Input
                                id="name"
                                type="text"
                                placeholder="e.g., users.view, posts.edit"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 font-mono"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                            )}
                            <p className="mt-2 text-xs text-gray-500">
                                Use dot notation: resource.action (e.g., users.create, posts.delete)
                            </p>
                        </div>

                        <div className="flex gap-3 pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleCloseDialog}
                                className="flex-1"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1"
                            >
                                {processing ? 'Saving...' : editingPermission ? 'Update Permission' : 'Create Permission'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
