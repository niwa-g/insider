import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    environment: Record<string, unknown> | null;
    applications: Array<{ id: string; name: string }>;
    slugs: Record<string, string>;
    statuses: Record<string, string>;
    preselectedApplicationId?: string | null;
};

export default function EnvironmentForm({ environment, applications, slugs, statuses, preselectedApplicationId }: Props) {
    const isEdit = Boolean(environment?.id);
    const { data, setData, post, put, processing } = useForm({
        application_id: (environment?.application_id as string) ?? preselectedApplicationId ?? '',
        name: (environment?.name as string) ?? '',
        slug: (environment?.slug as string) ?? 'production',
        url: (environment?.url as string) ?? '',
        status: (environment?.status as string) ?? 'active',
        branch: (environment?.branch as string) ?? '',
        notes: (environment?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/environments/${environment?.id}`);
        else post('/environments');
    }

    return (
        <AppLayout title={isEdit ? 'Edit environment' : 'New environment'}>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4">
                <label className="block text-sm font-medium">Application<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.application_id} onChange={(e) => setData('application_id', e.target.value)} required disabled={isEdit}>{applications.map((a) => (<option key={a.id} value={a.id}>{a.name}</option>))}</select></label>
                <label className="block text-sm font-medium">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="block text-sm font-medium">Slug<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.slug} onChange={(e) => setData('slug', e.target.value)}>{Object.entries(slugs).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <label className="block text-sm font-medium">URL<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.url} onChange={(e) => setData('url', e.target.value)} /></label>
                <label className="block text-sm font-medium">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <div className="flex gap-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href="/environments" className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
