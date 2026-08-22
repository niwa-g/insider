import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type RuleFormData = {
    name: string;
    description: string;
    trigger: string;
    conditions: { days_ahead?: number };
    actions: Array<{ type: string; role?: string }>;
    notify_in_app: boolean;
    notify_email: boolean;
    is_enabled: boolean;
};

type Props = {
    rule: Record<string, unknown> | null;
    triggers: Record<string, string>;
};

export default function RuleForm({ rule, triggers }: Props) {
    const isEdit = Boolean(rule?.id);
    const { data, setData, post, put, processing } = useForm<RuleFormData>({
        name: (rule?.name as string) ?? '',
        description: (rule?.description as string) ?? '',
        trigger: (rule?.trigger as string) ?? 'renewal_expiring',
        conditions: (rule?.conditions as RuleFormData['conditions']) ?? { days_ahead: 14 },
        actions: (rule?.actions as RuleFormData['actions']) ?? [{ type: 'notify_users', role: 'Administrator' }],
        notify_in_app: (rule?.notify_in_app as boolean) ?? true,
        notify_email: (rule?.notify_email as boolean) ?? false,
        is_enabled: (rule?.is_enabled as boolean) ?? true,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/automation/rules/${rule?.id}`);
        else post('/automation/rules');
    }

    return (
        <AppLayout title={isEdit ? 'Edit automation rule' : 'New automation rule'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <label className="block text-sm font-medium">
                    Name
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                </label>
                <label className="block text-sm font-medium">
                    Description
                    <textarea className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.description} onChange={(e) => setData('description', e.target.value)} rows={2} />
                </label>
                <label className="block text-sm font-medium">
                    Trigger
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.trigger} onChange={(e) => setData('trigger', e.target.value)}>
                        {Object.entries(triggers).map(([v, l]) => (
                            <option key={v} value={v}>{l}</option>
                        ))}
                    </select>
                </label>
                {data.trigger === 'renewal_expiring' && (
                    <label className="block text-sm font-medium">
                        Days ahead
                        <input type="number" min={1} max={365} className="mt-1 w-32 rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.conditions.days_ahead ?? 14} onChange={(e) => setData('conditions', { ...data.conditions, days_ahead: Number(e.target.value) })} />
                    </label>
                )}
                <label className="block text-sm font-medium">
                    Notify role
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.actions[0]?.role ?? 'Administrator'} onChange={(e) => setData('actions', [{ type: 'notify_users', role: e.target.value }])} />
                </label>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.notify_in_app} onChange={(e) => setData('notify_in_app', e.target.checked)} /> In-app notification
                </label>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.notify_email} onChange={(e) => setData('notify_email', e.target.checked)} /> Email (stub)
                </label>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.is_enabled} onChange={(e) => setData('is_enabled', e.target.checked)} /> Enabled
                </label>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : 'Save'}</button>
                    <Link href="/automation/rules" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
