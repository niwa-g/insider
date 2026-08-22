import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Schedule = {
    id: string;
    name: string;
    job_type: string;
    frequency: string;
    is_enabled: boolean;
    last_run_at?: string | null;
    last_status?: string | null;
};

type Props = {
    schedules: { data: Schedule[] };
    filters: Record<string, string>;
    jobTypes: Record<string, string>;
    frequencies: Record<string, string>;
    can: { create: boolean };
};

export default function SchedulesIndex({ schedules, filters, jobTypes, frequencies, can }: Props) {
    const [localFilters, setLocalFilters] = useState(filters ?? {});

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/automation/schedules', localFilters, { preserveState: true });
    }

    function runNow(id: string) {
        router.post(`/automation/schedules/${id}/run`);
    }

    function toggle(id: string) {
        router.post(`/automation/schedules/${id}/toggle`);
    }

    return (
        <AppLayout title="Automation Schedules">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex gap-2">
                    <select className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={localFilters.job_type ?? ''} onChange={(e) => setLocalFilters({ ...localFilters, job_type: e.target.value })}>
                        <option value="">All job types</option>
                        {Object.entries(jobTypes).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                <div className="flex gap-2">
                    <Link href="/automation/rules" className="rounded-lg border px-3 py-2 text-sm">Rules</Link>
                    {can.create && <Link href="/automation/schedules/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">New schedule</Link>}
                </div>
            </div>

            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 text-left">Name</th>
                            <th className="px-4 py-3 text-left">Job</th>
                            <th className="px-4 py-3 text-left">Frequency</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-left">Last run</th>
                            <th className="px-4 py-3 text-left"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {schedules.data.map((s) => (
                            <tr key={s.id} className="border-t dark:border-slate-800">
                                <td className="px-4 py-3 font-medium">{s.name}</td>
                                <td className="px-4 py-3">{jobTypes[s.job_type] ?? s.job_type}</td>
                                <td className="px-4 py-3">{frequencies[s.frequency] ?? s.frequency}</td>
                                <td className="px-4 py-3">{s.is_enabled ? <StatusBadge status="active" /> : <StatusBadge status="inactive" />}</td>
                                <td className="px-4 py-3 text-slate-500">{s.last_run_at ? new Date(s.last_run_at).toLocaleString() : 'Never'}</td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-2">
                                        <button type="button" onClick={() => runNow(s.id)} className="text-blue-600 hover:underline">Run now</button>
                                        <button type="button" onClick={() => toggle(s.id)} className="text-slate-600 hover:underline">{s.is_enabled ? 'Disable' : 'Enable'}</button>
                                        <Link href={`/automation/schedules/${s.id}/edit`} className="text-blue-600">Edit</Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {schedules.data.length === 0 && <p className="p-4 text-sm text-slate-500">No schedules yet.</p>}
            </div>
        </AppLayout>
    );
}
