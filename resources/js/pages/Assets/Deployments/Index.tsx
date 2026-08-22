import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Props = {
    deployments: { data: Array<Record<string, unknown> & { id: string; status?: string }> };
    filters: { environment_id: string; application_id: string };
    statuses: Record<string, string>;
    environments: Array<{ id: string; name: string; application?: { name: string } }>;
    applications: Array<{ id: string; name: string }>;
    can: { create: boolean };
};

export default function DeploymentsIndex({ deployments, filters, statuses, environments, applications: _applications, can }: Props) {
    const { data, setData, post, processing, reset } = useForm({
        environment_id: filters.environment_id || (environments[0]?.id ?? ''),
        version: '',
        status: 'success',
        commit_sha: '',
        notes: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/deployments', { onSuccess: () => reset('version', 'commit_sha', 'notes') });
    }

    return (
        <AppLayout title="Deployments">
            {can.create && (
                <form onSubmit={submit} className="mb-6 grid max-w-3xl gap-3 rounded-xl border p-4 dark:border-slate-800 md:grid-cols-2">
                    <label className="text-sm">Environment<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.environment_id} onChange={(e) => setData('environment_id', e.target.value)} required>{environments.map((e) => (<option key={e.id} value={e.id}>{e.application?.name} — {e.name}</option>))}</select></label>
                    <label className="text-sm">Version<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.version} onChange={(e) => setData('version', e.target.value)} /></label>
                    <label className="text-sm">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                    <label className="text-sm">Commit SHA<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.commit_sha} onChange={(e) => setData('commit_sha', e.target.value)} /></label>
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white md:col-span-2">Record deployment</button>
                </form>
            )}
            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm"><thead className="bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-2 text-left">Environment</th><th className="px-4 py-2 text-left">Version</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Deployed</th></tr></thead>
                    <tbody>{deployments.data.map((d) => (<tr key={d.id} className="border-t dark:border-slate-800"><td className="px-4 py-2">{String((d.environment as { name?: string })?.name ?? '—')}</td><td className="px-4 py-2">{String(d.version ?? '—')}</td><td className="px-4 py-2">{d.status ? <StatusBadge status={String(d.status)} /> : '—'}</td><td className="px-4 py-2">{String(d.deployed_at ?? '—')}</td></tr>))}</tbody>
                </table>
            </div>
        </AppLayout>
    );
}
