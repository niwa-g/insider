import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type LogRow = {
    id: string;
    action: string;
    status: string;
    message?: string | null;
    created_at?: string;
    integration?: { id: string; name: string; provider: string };
    triggered_by?: { name: string } | null;
};

type Props = {
    logs: { data: LogRow[] };
    filters: Record<string, string>;
    integrations: Array<{ id: string; name: string }>;
};

export default function SyncLogsIndex({ logs, filters, integrations }: Props) {
    const [localFilters, setLocalFilters] = useState(filters);

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/integration-sync-logs', localFilters, { preserveState: true });
    }

    return (
        <AppLayout title="Integration sync logs">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <select
                        className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        value={localFilters.integration_id ?? ''}
                        onChange={(e) => setLocalFilters({ ...localFilters, integration_id: e.target.value })}
                    >
                        <option value="">All integrations</option>
                        {integrations.map((i) => (
                            <option key={i.id} value={i.id}>{i.name}</option>
                        ))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                <Link href="/integrations" className="text-sm text-slate-500">← Integrations</Link>
            </div>

            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Integration</th>
                            <th className="px-4 py-3 font-medium">Action</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium">Message</th>
                            <th className="px-4 py-3 font-medium">When</th>
                        </tr>
                    </thead>
                    <tbody>
                        {logs.data.map((log) => (
                            <tr key={log.id} className="border-b dark:border-slate-800">
                                <td className="px-4 py-3">
                                    {log.integration ? (
                                        <Link href={`/integrations/${log.integration.id}`} className="text-blue-600 hover:underline">{log.integration.name}</Link>
                                    ) : '—'}
                                </td>
                                <td className="px-4 py-3 capitalize">{log.action}</td>
                                <td className="px-4 py-3"><StatusBadge status={log.status} /></td>
                                <td className="px-4 py-3">{log.message ?? '—'}</td>
                                <td className="px-4 py-3 text-xs text-slate-500">{log.created_at ? new Date(log.created_at).toLocaleString() : '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
