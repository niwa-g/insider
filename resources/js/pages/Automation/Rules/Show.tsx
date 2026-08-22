import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Run = {
    id: string;
    status: string;
    message?: string | null;
    started_at?: string | null;
    finished_at?: string | null;
    meta?: Record<string, unknown> | null;
};

type Props = {
    rule: Record<string, unknown> & { id: string; name: string; trigger: string; is_enabled: boolean; description?: string };
    recentRuns: Run[];
    can: { update: boolean; delete: boolean; run: boolean };
};

export default function RuleShow({ rule, recentRuns, can }: Props) {
    function runNow() {
        router.post(`/automation/rules/${rule.id}/run`);
    }

    function toggle() {
        router.post(`/automation/rules/${rule.id}/toggle`);
    }

    return (
        <AppLayout title={rule.name}>
            <div className="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-sm text-slate-500 capitalize">{String(rule.trigger).replace(/_/g, ' ')}</p>
                    {rule.description && <p className="mt-1 text-sm">{String(rule.description)}</p>}
                    <div className="mt-2">{rule.is_enabled ? <StatusBadge status="active" /> : <StatusBadge status="inactive" />}</div>
                </div>
                <div className="flex gap-2">
                    {can.run && <button type="button" onClick={runNow} className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">Run now</button>}
                    {can.update && <button type="button" onClick={toggle} className="rounded-lg border px-3 py-2 text-sm">{rule.is_enabled ? 'Disable' : 'Enable'}</button>}
                    {can.update && <Link href={`/automation/rules/${rule.id}/edit`} className="rounded-lg border px-3 py-2 text-sm">Edit</Link>}
                    <Link href="/automation/rules" className="rounded-lg px-3 py-2 text-sm text-slate-600">Back</Link>
                </div>
            </div>

            <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Recent runs</h2>
            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-left">Message</th>
                            <th className="px-4 py-3 text-left">Started</th>
                        </tr>
                    </thead>
                    <tbody>
                        {recentRuns.map((run) => (
                            <tr key={run.id} className="border-t dark:border-slate-800">
                                <td className="px-4 py-3"><StatusBadge status={run.status} /></td>
                                <td className="px-4 py-3">{run.message ?? '—'}</td>
                                <td className="px-4 py-3 text-slate-500">{run.started_at ? new Date(run.started_at).toLocaleString() : '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {recentRuns.length === 0 && <p className="p-4 text-sm text-slate-500">No runs yet.</p>}
            </div>
        </AppLayout>
    );
}
