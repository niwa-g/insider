import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Run = {
    id: string;
    trigger: string;
    status: string;
    message?: string | null;
    started_at?: string | null;
    rule?: { name: string } | null;
    schedule?: { name: string } | null;
};

type Props = {
    runs: { data: Run[] };
    filters: Record<string, string>;
    triggers: Record<string, string>;
    statuses: Record<string, string>;
};

export default function RunsIndex({ runs, filters, triggers, statuses }: Props) {
    const [localFilters, setLocalFilters] = useState(filters ?? {});

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/automation/runs', localFilters, { preserveState: true });
    }

    return (
        <AppLayout title="Automation Runs">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <select className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={localFilters.trigger ?? ''} onChange={(e) => setLocalFilters({ ...localFilters, trigger: e.target.value })}>
                        <option value="">All triggers</option>
                        {Object.entries(triggers).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                    <select className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={localFilters.status ?? ''} onChange={(e) => setLocalFilters({ ...localFilters, status: e.target.value })}>
                        <option value="">All statuses</option>
                        {Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                <Link href="/automation/rules" className="rounded-lg border px-3 py-2 text-sm">Rules</Link>
            </div>

            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 text-left">Trigger</th>
                            <th className="px-4 py-3 text-left">Source</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-left">Message</th>
                            <th className="px-4 py-3 text-left">Started</th>
                        </tr>
                    </thead>
                    <tbody>
                        {runs.data.map((run) => (
                            <tr key={run.id} className="border-t dark:border-slate-800">
                                <td className="px-4 py-3">{triggers[run.trigger] ?? run.trigger}</td>
                                <td className="px-4 py-3">{run.rule?.name ?? run.schedule?.name ?? '—'}</td>
                                <td className="px-4 py-3"><StatusBadge status={run.status} /></td>
                                <td className="px-4 py-3">{run.message ?? '—'}</td>
                                <td className="px-4 py-3">
                                    <Link href={`/automation/runs/${run.id}`} className="text-blue-600">
                                        {run.started_at ? new Date(run.started_at).toLocaleString() : '—'}
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {runs.data.length === 0 && <p className="p-4 text-sm text-slate-500">No runs yet.</p>}
            </div>
        </AppLayout>
    );
}
