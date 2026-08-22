import { Link, router } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import StatusBadge from '../../components/StatusBadge';

type ExternalRef = {
    id: string;
    external_type: string;
    external_id: string;
    external_name?: string | null;
    local_type?: string | null;
    local_id?: string | null;
    last_seen_at?: string | null;
};

type SyncLog = {
    id: string;
    action: string;
    status: string;
    message?: string | null;
    meta?: Record<string, unknown> | null;
    created_at?: string;
};

type Props = {
    integration: Record<string, unknown> & {
        id: string;
        name: string;
        provider: string;
        category: string;
        status: string;
        config?: Record<string, unknown>;
        last_synced_at?: string | null;
        last_error?: string | null;
        connected_by?: { name: string } | null;
    };
    externalRefs: ExternalRef[];
    syncLogs: SyncLog[];
    hasCredentials: boolean;
    can: { update: boolean; delete: boolean; sync: boolean };
};

export default function IntegrationShow({ integration, externalRefs, syncLogs, hasCredentials, can }: Props) {
    function action(path: string) {
        router.post(`/integrations/${integration.id}/${path}`);
    }

    return (
        <AppLayout title={integration.name}>
            <div className="mb-4 flex flex-wrap gap-2">
                <Link href="/integrations" className="text-sm text-slate-500">← Integrations</Link>
                {can.update && <Link href={`/integrations/${integration.id}/edit`} className="rounded-lg border px-3 py-1 text-sm">Edit</Link>}
                {can.delete && (
                    <button type="button" onClick={() => confirm('Delete integration?') && router.delete(`/integrations/${integration.id}`)} className="rounded-lg border border-red-200 px-3 py-1 text-sm text-red-600">Delete</button>
                )}
            </div>

            <div className="mb-6 rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <div className="flex flex-wrap items-center gap-3">
                    <h2 className="text-xl font-semibold">{integration.name}</h2>
                    <StatusBadge status={integration.status} />
                    <span className="text-sm capitalize text-slate-500">{integration.provider}</span>
                </div>
                <p className="mt-2 text-sm text-slate-500">
                    Category: {integration.category} · Credentials: {hasCredentials ? 'configured' : 'none'}
                    {integration.connected_by && <> · Connected by {integration.connected_by.name}</>}
                </p>
                {integration.last_synced_at && (
                    <p className="mt-1 text-xs text-slate-400">Last synced: {new Date(integration.last_synced_at).toLocaleString()}</p>
                )}
                {integration.last_error && (
                    <p className="mt-2 text-sm text-red-600">{integration.last_error}</p>
                )}

                {can.update && (
                    <div className="mt-4 flex flex-wrap gap-2">
                        <button type="button" onClick={() => action('test')} className="rounded-lg border px-3 py-1.5 text-sm">Test connection</button>
                        <button type="button" onClick={() => action('connect')} className="rounded-lg border px-3 py-1.5 text-sm">Connect</button>
                        <button type="button" onClick={() => action('disconnect')} className="rounded-lg border px-3 py-1.5 text-sm">Disconnect</button>
                        {can.sync && (
                            <button type="button" onClick={() => action('sync')} className="rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white">Sync now</button>
                        )}
                    </div>
                )}
            </div>

            <h3 className="mb-2 font-semibold">External references</h3>
            <div className="mb-6 overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Type</th>
                            <th className="px-4 py-3 font-medium">External ID</th>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Linked</th>
                        </tr>
                    </thead>
                    <tbody>
                        {externalRefs.map((ref) => (
                            <tr key={ref.id} className="border-b dark:border-slate-800">
                                <td className="px-4 py-3">{ref.external_type}</td>
                                <td className="px-4 py-3 font-mono text-xs">{ref.external_id}</td>
                                <td className="px-4 py-3">{ref.external_name ?? '—'}</td>
                                <td className="px-4 py-3">{ref.local_id ? 'Yes' : '—'}</td>
                            </tr>
                        ))}
                        {externalRefs.length === 0 && (
                            <tr><td colSpan={4} className="px-4 py-6 text-slate-500">No external refs yet. Run a sync.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>

            <h3 className="mb-2 font-semibold">Recent sync logs</h3>
            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Action</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium">Message</th>
                            <th className="px-4 py-3 font-medium">When</th>
                        </tr>
                    </thead>
                    <tbody>
                        {syncLogs.map((log) => (
                            <tr key={log.id} className="border-b dark:border-slate-800">
                                <td className="px-4 py-3 capitalize">{log.action}</td>
                                <td className="px-4 py-3"><StatusBadge status={log.status} /></td>
                                <td className="px-4 py-3">{log.message ?? '—'}</td>
                                <td className="px-4 py-3 text-xs text-slate-500">{log.created_at ? new Date(log.created_at).toLocaleString() : '—'}</td>
                            </tr>
                        ))}
                        {syncLogs.length === 0 && (
                            <tr><td colSpan={4} className="px-4 py-6 text-slate-500">No sync logs yet.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
