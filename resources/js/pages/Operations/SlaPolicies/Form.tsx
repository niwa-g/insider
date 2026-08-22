import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Target = { priority: string; respond_within_minutes: number; resolve_within_minutes: number };

type Props = {
    policy: Record<string, unknown> | null;
    targets: Target[];
    statuses: Record<string, string>;
    priorities: Record<string, string>;
};

export default function SlaPolicyForm({ policy, targets: initialTargets, statuses, priorities }: Props) {
    const isEdit = Boolean(policy?.id);
    const { data, setData, post, put, processing } = useForm({
        name: (policy?.name as string) ?? '',
        code: (policy?.code as string) ?? '',
        description: (policy?.description as string) ?? '',
        is_default: (policy?.is_default as boolean) ?? false,
        status: (policy?.status as string) ?? 'active',
        targets: initialTargets,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/sla-policies/${policy?.id}`);
        else post('/sla-policies');
    }

    function updateTarget(index: number, field: keyof Target, value: string | number) {
        const next = [...data.targets];
        next[index] = { ...next[index], [field]: value };
        setData('targets', next);
    }

    return (
        <AppLayout title={isEdit ? 'Edit SLA policy' : 'New SLA policy'}>
            <form onSubmit={submit} className="mx-auto max-w-3xl space-y-4">
                <label className="block text-sm font-medium">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="block text-sm font-medium">Code<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.code} onChange={(e) => setData('code', e.target.value)} /></label>
                <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={data.is_default} onChange={(e) => setData('is_default', e.target.checked)} /> Default policy</label>
                <label className="block text-sm font-medium">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>

                <h3 className="font-semibold">Targets by priority</h3>
                <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-2 text-left">Priority</th><th className="px-4 py-2 text-left">Respond (min)</th><th className="px-4 py-2 text-left">Resolve (min)</th></tr></thead>
                        <tbody>
                            {data.targets.map((t, i) => (
                                <tr key={t.priority} className="border-t dark:border-slate-800">
                                    <td className="px-4 py-2">{priorities[t.priority] ?? t.priority}</td>
                                    <td className="px-4 py-2"><input type="number" min={1} className="w-24 rounded border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" value={t.respond_within_minutes} onChange={(e) => updateTarget(i, 'respond_within_minutes', Number(e.target.value))} /></td>
                                    <td className="px-4 py-2"><input type="number" min={1} className="w-24 rounded border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" value={t.resolve_within_minutes} onChange={(e) => updateTarget(i, 'resolve_within_minutes', Number(e.target.value))} /></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : 'Save'}</button>
                    <Link href="/sla-policies" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
