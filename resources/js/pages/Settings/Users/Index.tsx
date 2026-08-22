import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type UserRow = {
    id: number;
    name: string;
    email: string;
    status: string | null;
    roles: Array<{ id: number; name: string }>;
};

type Props = {
    users: {
        data: UserRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    roles: Array<{ id: number; name: string }>;
    can: { update: boolean };
};

export default function UsersIndex({ users, roles, can }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const [selectedRoles, setSelectedRoles] = useState<string[]>([]);

    function startEdit(user: UserRow) {
        setEditingId(user.id);
        setSelectedRoles(user.roles.map((r) => r.name));
    }

    function saveRoles(e: FormEvent, userId: number) {
        e.preventDefault();
        router.patch(`/settings/users/${userId}/roles`, { role_names: selectedRoles }, {
            onSuccess: () => setEditingId(null),
        });
    }

    return (
        <AppLayout title="Users">
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Email</th>
                            <th className="px-4 py-3 font-medium">Roles</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            {can.update && <th className="px-4 py-3 font-medium" />}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                        {users.data.map((user) => (
                            <tr key={user.id}>
                                <td className="px-4 py-3 font-medium">{user.name}</td>
                                <td className="px-4 py-3 text-slate-500">{user.email}</td>
                                <td className="px-4 py-3">
                                    {editingId === user.id ? (
                                        <form onSubmit={(e) => saveRoles(e, user.id)} className="flex flex-wrap gap-2">
                                            <select
                                                multiple
                                                value={selectedRoles}
                                                onChange={(e) =>
                                                    setSelectedRoles(
                                                        Array.from(e.target.selectedOptions, (o) => o.value),
                                                    )
                                                }
                                                className="min-w-40 rounded border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950"
                                            >
                                                {roles.map((role) => (
                                                    <option key={role.id} value={role.name}>
                                                        {role.name}
                                                    </option>
                                                ))}
                                            </select>
                                            <button type="submit" className="text-sm text-blue-600">
                                                Save
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => setEditingId(null)}
                                                className="text-sm text-slate-500"
                                            >
                                                Cancel
                                            </button>
                                        </form>
                                    ) : (
                                        user.roles.map((r) => r.name).join(', ') || '—'
                                    )}
                                </td>
                                <td className="px-4 py-3 capitalize">{user.status ?? 'active'}</td>
                                {can.update && (
                                    <td className="px-4 py-3">
                                        {editingId !== user.id && (
                                            <button
                                                type="button"
                                                onClick={() => startEdit(user)}
                                                className="text-sm text-blue-600 hover:underline"
                                            >
                                                Edit roles
                                            </button>
                                        )}
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
