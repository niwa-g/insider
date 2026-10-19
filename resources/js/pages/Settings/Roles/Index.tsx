import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type RoleRow = {
    id: number;
    name: string;
    permissions_count: number;
    protected: boolean;
};

type Props = {
    roles: RoleRow[];
    can: { create: boolean; update: boolean; delete: boolean };
};

export default function RolesIndex({ roles, can }: Props) {
    function destroyRole(role: RoleRow) {
        if (role.protected) {
            return;
        }
        if (!confirm(`Delete role "${role.name}"?`)) {
            return;
        }
        router.delete(`/settings/roles/${role.id}`);
    }

    return (
        <AppLayout title="Roles">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 className="text-lg font-semibold">Roles & permissions</h2>
                    <p className="text-sm text-slate-500">Define what each role can access.</p>
                </div>
                {can.create && (
                    <Link href="/settings/roles/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        New role
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Role</th>
                            <th className="px-4 py-3 font-medium">Permissions</th>
                            {(can.update || can.delete) && <th className="px-4 py-3 font-medium">Actions</th>}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                        {roles.map((role) => (
                            <tr key={role.id}>
                                <td className="px-4 py-3 font-medium">
                                    {role.name}
                                    {role.protected && (
                                        <span className="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500 dark:bg-slate-800">
                                            Protected
                                        </span>
                                    )}
                                </td>
                                <td className="px-4 py-3 tabular-nums">{role.permissions_count}</td>
                                {(can.update || can.delete) && (
                                    <td className="px-4 py-3">
                                        <div className="flex gap-3">
                                            {can.update && (
                                                <Link href={`/settings/roles/${role.id}/edit`} className="text-sm text-blue-600 hover:underline">
                                                    Edit
                                                </Link>
                                            )}
                                            {can.delete && !role.protected && (
                                                <button type="button" onClick={() => destroyRole(role)} className="text-sm text-rose-600 hover:underline">
                                                    Delete
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
