import AppLayout from '../../../layouts/AppLayout';

type RoleRow = {
    id: number;
    name: string;
    permissions_count: number;
};

type Props = {
    roles: RoleRow[];
};

export default function RolesIndex({ roles }: Props) {
    return (
        <AppLayout title="Roles">
            <p className="mb-4 text-sm text-slate-500">Read-only view of role definitions and permission counts.</p>
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Role</th>
                            <th className="px-4 py-3 font-medium">Permissions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                        {roles.map((role) => (
                            <tr key={role.id}>
                                <td className="px-4 py-3 font-medium">{role.name}</td>
                                <td className="px-4 py-3 tabular-nums">{role.permissions_count}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
