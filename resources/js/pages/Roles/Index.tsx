import { useMemo, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Edit2, Plus, Search, Shield, Trash2 } from 'lucide-react';
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
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

interface Role {
    id: number;
    name: string;
    users_count?: number;
    permissions_count?: number;
}

interface Permission {
    id: number;
    name: string;
}

interface Props {
    roles: Role[];
    permissions: Permission[];
}

export default function RolesIndex({ roles: initialRoles, permissions }: Props) {
    const [searchTerm, setSearchTerm] = useState('');
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingRole, setEditingRole] = useState<Role | null>(null);
    const [selectedPermissions, setSelectedPermissions] = useState<number[]>([]);

    const { data, setData, post, patch, delete: destroy, processing, errors } = useForm({
        name: '',
    });

    const filteredRoles = useMemo(() => {
        return initialRoles.filter(role =>
            role.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [initialRoles, searchTerm]);

    const handleCloseDialog = () => {
        setIsDialogOpen(false);
        setEditingRole(null);
        setData('name', '');
        setSelectedPermissions([]);
    };

    const handleOpenDialog = (role?: Role) => {
        if (role) {
            setEditingRole(role);
            setData('name', role.name);
            // Note: In a real app, you'd fetch the role's permissions
            setSelectedPermissions([]);
        } else {
            setEditingRole(null);
            setData('name', '');
            setSelectedPermissions([]);
        }
        setIsDialogOpen(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingRole) {
            patch(route('roles.update', editingRole.id), {
                preserveScroll: true,
                onSuccess: () => setIsDialogOpen(false),
            });
        } else {
            post(route('roles.store'), {
                preserveScroll: true,
                onSuccess: () => setIsDialogOpen(false),
            });
        }
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this role?')) {
            destroy(route('roles.destroy', id));
        }
    };

    const togglePermission = (permissionId: number) => {
        setSelectedPermissions(prev =>
            prev.includes(permissionId)
                ? prev.filter(id => id !== permissionId)
                : [...prev, permissionId]
        );
    };

    return (
        <>
            <Head title="Roles Management" />

            <div className="bg-gray-50">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-start gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <Shield className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Roles</h1>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="relative flex-1 sm:max-w-xs">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        type="text"
                                        placeholder="Search Roles..."
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
                                    Add Role
                                </Button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-50">
                                        <TableHead className="font-semibold">Name</TableHead>
                                        <TableHead className="font-semibold">Users</TableHead>
                                        <TableHead className="font-semibold">Permissions</TableHead>
                                        <TableHead className="text-right font-semibold">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredRoles.length > 0 ? (
                                        filteredRoles.map((role) => (
                                            <TableRow key={role.id} className="hover:bg-gray-50">
                                                <TableCell className="font-medium">{role.name}</TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">
                                                        {role.users_count || 0}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline">
                                                        {role.permissions_count || 0}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            onClick={() => handleOpenDialog(role)}
                                                            className="inline-flex items-center gap-2 rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                                                        >
                                                            <Edit2 className="h-4 w-4" />
                                                            Edit
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(role.id)}
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
                                            <TableCell colSpan={4} className="py-8 text-center text-gray-500">
                                                No roles found
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
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>{editingRole ? 'Edit Role' : 'Add New Role'}</DialogTitle>
                        <DialogDescription>
                            {editingRole ? 'Update nama role dan daftar permission.' : 'Tambahkan role baru dan pilih permission yang sesuai.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <Label htmlFor="name">Role Name</Label>
                            <Input
                                id="name"
                                type="text"
                                placeholder="e.g., Administrator, Editor"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                            )}
                        </div>

                        <div>
                            <Label className="mb-3 block">Assign Permissions</Label>
                            <div className="grid grid-cols-2 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 md:grid-cols-3">
                                {permissions.map(permission => (
                                    <div key={permission.id} className="flex items-center gap-2">
                                        <Checkbox
                                            id={`perm-${permission.id}`}
                                            checked={selectedPermissions.includes(permission.id)}
                                            onCheckedChange={() => togglePermission(permission.id)}
                                        />
                                        <Label
                                            htmlFor={`perm-${permission.id}`}
                                            className="cursor-pointer text-sm font-normal"
                                        >
                                            {permission.name}
                                        </Label>
                                    </div>
                                ))}
                            </div>
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
                                {processing ? 'Saving...' : editingRole ? 'Update Role' : 'Create Role'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
