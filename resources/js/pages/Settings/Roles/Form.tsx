import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    role: {
        id: number;
        name: string;
        permissions: string[];
    } | null;
    permissionGroups: Record<string, string[]>;
    protected: boolean;
};

export default function RoleForm({ role, permissionGroups, protected: isProtected }: Props) {
    const isEdit = Boolean(role?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        name: role?.name ?? '',
        permissions: role?.permissions ?? [],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/settings/roles/${role?.id}`);
        } else {
            post('/settings/roles');
        }
    }

    function togglePermission(name: string) {
        setData(
            'permissions',
            data.permissions.includes(name)
                ? data.permissions.filter((p) => p !== name)
                : [...data.permissions, name],
        );
    }

    function toggleGroup(group: string[]) {
        const allSelected = group.every((p) => data.permissions.includes(p));
        if (allSelected) {
            setData(
                'permissions',
                data.permissions.filter((p) => !group.includes(p)),
            );
        } else {
            setData('permissions', Array.from(new Set([...data.permissions, ...group])));
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit role' : 'New role'}>
            <div className="mb-4">
                <Link href="/settings/roles" className="text-sm text-slate-500 hover:text-slate-800">
                    ← Back to roles
                </Link>
            </div>

            <form onSubmit={submit} className="mx-auto max-w-4xl space-y-4">
                <div className="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <label className="block text-sm">
                        <span className="mb-1 block font-medium">Role name</span>
                        <input
                            className="field"
                            value={data.name}
                            disabled={isProtected}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                        {isProtected && (
                            <span className="mt-1 block text-xs text-slate-500">Protected role name cannot be changed.</span>
                        )}
                        {errors.name && <span className="mt-1 block text-rose-600">{errors.name}</span>}
                    </label>
                </div>

                <div className="space-y-4">
                    {Object.entries(permissionGroups).map(([group, permissions]) => (
                        <div key={group} className="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                            <div className="mb-3 flex items-center justify-between gap-3">
                                <h3 className="text-sm font-semibold capitalize">{group.replaceAll('_', ' ')}</h3>
                                <button
                                    type="button"
                                    className="text-xs text-blue-600 hover:underline"
                                    onClick={() => toggleGroup(permissions)}
                                >
                                    Toggle all
                                </button>
                            </div>
                            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                {permissions.map((permission) => (
                                    <label key={permission} className="inline-flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={data.permissions.includes(permission)}
                                            onChange={() => togglePermission(permission)}
                                        />
                                        <span className="font-mono text-xs">{permission}</span>
                                    </label>
                                ))}
                            </div>
                        </div>
                    ))}
                    {errors.permissions && <p className="text-sm text-rose-600">{errors.permissions}</p>}
                </div>

                <div className="flex justify-end gap-2">
                    <Link href="/settings/roles" className="rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700">
                        Cancel
                    </Link>
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        {isEdit ? 'Save role' : 'Create role'}
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
