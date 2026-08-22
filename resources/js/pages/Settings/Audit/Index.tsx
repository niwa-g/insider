import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type LogRow = {
    id: number;
    action: string;
    created_at: string;
    user?: { id: number; name: string; email: string } | null;
};

type Props = {
    logs: {
        data: LogRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search: string };
};

export default function AuditIndex({ logs, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/settings/audit', { search }, { preserveState: true });
    }

    return (
        <AppLayout title="Audit log">
            <form onSubmit={apply} className="mb-4 flex gap-2">
                <input
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Search actions…"
                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                />
                <button
                    type="submit"
                    className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white dark:bg-slate-100 dark:text-slate-900"
                >
                    Filter
                </button>
            </form>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">When</th>
                            <th className="px-4 py-3 font-medium">Action</th>
                            <th className="px-4 py-3 font-medium">User</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                        {logs.data.length === 0 ? (
                            <tr>
                                <td colSpan={3} className="px-4 py-8 text-center text-slate-500">
                                    No audit entries yet.
                                </td>
                            </tr>
                        ) : (
                            logs.data.map((log) => (
                                <tr key={log.id}>
                                    <td className="px-4 py-3 whitespace-nowrap text-slate-500">{log.created_at}</td>
                                    <td className="px-4 py-3 font-medium">{log.action}</td>
                                    <td className="px-4 py-3">{log.user?.name ?? 'System'}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
