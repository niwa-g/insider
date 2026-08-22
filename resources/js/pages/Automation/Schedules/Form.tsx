import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    schedule: Record<string, unknown> | null;
    jobTypes: Record<string, string>;
    frequencies: Record<string, string>;
};

export default function ScheduleForm({ schedule, jobTypes, frequencies }: Props) {
    const isEdit = Boolean(schedule?.id);
    const { data, setData, post, put, processing } = useForm({
        name: (schedule?.name as string) ?? '',
        job_type: (schedule?.job_type as string) ?? 'scan_renewals',
        frequency: (schedule?.frequency as string) ?? 'daily',
        config: (schedule?.config as { renewal_days?: number[] }) ?? { renewal_days: [7, 14, 30] },
        is_enabled: (schedule?.is_enabled as boolean) ?? true,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/automation/schedules/${schedule?.id}`);
        else post('/automation/schedules');
    }

    return (
        <AppLayout title={isEdit ? 'Edit schedule' : 'New schedule'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <label className="block text-sm font-medium">
                    Name
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                </label>
                <label className="block text-sm font-medium">
                    Job type
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.job_type} onChange={(e) => setData('job_type', e.target.value)}>
                        {Object.entries(jobTypes).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Frequency
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.frequency} onChange={(e) => setData('frequency', e.target.value)}>
                        {Object.entries(frequencies).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.is_enabled} onChange={(e) => setData('is_enabled', e.target.checked)} /> Enabled
                </label>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : 'Save'}</button>
                    <Link href="/automation/schedules" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
