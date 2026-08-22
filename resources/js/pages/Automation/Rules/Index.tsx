import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Rule = {
    id: string;
    name: string;
    trigger: string;
    is_enabled: boolean;
    last_run_at?: string | null;
    last_status?: string | null;
};

type Props = {
    rules: { data: Rule[] };
    filters: Record<string, string>;
    triggers: Record<string, string>;
    can: { create: boolean };
};

export default function RulesIndex({ rules, filters, triggers, can }: Props) {
    const [localFilters, setLocalFilters] = useState(filters ?? {});

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/automation/rules', localFilters, { preserveState: true });
    }

    function toggle(id: string) {
        router.post(`/automation/rules/${id}/toggle`);
    }

    function runNow(id: string) {
        router.post(`/automation/rules/${id}/run`);
    }

    return (
        <AppLayout title="Automation Rules">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <select
                        className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        value={localFilters.trigger ?? ''}
                        onChange={(e) => setLocalFilters({ ...localFilters, trigger: e.target.value })}
                    >
                        <option value="">All triggers</option>
                        {Object.entries(triggers).map(([v, l]) => (
                            <option key={v} value={v}>{l}</option>
                        ))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                <div className="flex gap-2">
                    <Link href="/automation/schedules" className="rounded-lg border px-3 py-2 text-sm">Schedules</Link>
                    <Link href="/automation/runs" className="rounded-lg border px-3 py-2 text-sm">Runs</Link>
                    {can.create && (
                        <Link href="/automation/rules/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">New rule</Link>
                    )}
                </div>
            </div>

            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 text-left">Name</th>
                            <th className="px-4 py-3 text-left">Trigger</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-left">Last run</th>
                            <th className="px-4 py-3 text-left"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {rules.data.map((rule) => (
                            <tr key={rule.id} className="border-t dark:border-slate-800">
                                <td className="px-4 py-3">
                                    <Link href={`/automation/rules/${rule.id}`} className="font-medium text-blue-600">{rule.name}</Link>
                                </td>
                                <td className="px-4 py-3">{triggers[rule.trigger] ?? rule.trigger}</td>
                                <td className="px-4 py-3">
                                    {rule.is_enabled ? <StatusBadge status="active" /> : <StatusBadge status="inactive" />}
                                </td>
                                <td className="px-4 py-3 text-slate-500">
                                    {rule.last_run_at ? new Date(rule.last_run_at).toLocaleString() : 'Never'}
                                    {rule.last_status ? ` (${rule.last_status})` : ''}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-2">
                                        <button type="button" onClick={() => toggle(rule.id)} className="text-slate-600 hover:underline">
                                            {rule.is_enabled ? 'Disable' : 'Enable'}
                                        </button>
                                        <button type="button" onClick={() => runNow(rule.id)} className="text-blue-600 hover:underline">Run now</button>
                                        <Link href={`/automation/rules/${rule.id}/edit`} className="text-blue-600">Edit</Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {rules.data.length === 0 && <p className="p-4 text-sm text-slate-500">No automation rules yet.</p>}
            </div>
        </AppLayout>
    );
}
