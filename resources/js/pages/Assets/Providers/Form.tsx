import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    provider: Record<string, unknown> | null;
    types: Record<string, string>;
    statuses: Record<string, string>;
};

export default function ProviderForm({ provider, types, statuses }: Props) {
    const isEdit = Boolean(provider?.id);
    const { data, setData, post, put, processing } = useForm({
        name: (provider?.name as string) ?? '',
        code: (provider?.code as string) ?? '',
        type: (provider?.type as string) ?? 'hosting',
        website: (provider?.website as string) ?? '',
        status: (provider?.status as string) ?? 'active',
        notes: (provider?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/providers/${provider?.id}`);
        else post('/providers');
    }

    return (
        <AppLayout title={isEdit ? 'Edit provider' : 'New provider'}>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4">
                <label className="text-sm">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="text-sm">Type<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.type} onChange={(e) => setData('type', e.target.value)}>{Object.entries(types).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <label className="text-sm">Website<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.website} onChange={(e) => setData('website', e.target.value)} /></label>
                <label className="text-sm">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <div className="flex gap-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href="/providers" className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
