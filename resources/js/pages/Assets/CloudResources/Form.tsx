import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    resource: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    providers: Array<{ id: string; name: string }>;
    resourceTypes: Record<string, string>;
    statuses: Record<string, string>;
    preselectedClientId?: string | null;
};

export default function CloudResourceForm({ resource, clients, resourceTypes, preselectedClientId }: Props) {
    const isEdit = Boolean(resource?.id);
    const { data, setData, post, put, processing } = useForm({
        client_id: (resource?.client_id as string) ?? preselectedClientId ?? '',
        provider_id: (resource?.provider_id as string) ?? '',
        name: (resource?.name as string) ?? '',
        resource_type: (resource?.resource_type as string) ?? 'compute',
        region: (resource?.region as string) ?? '',
        external_id: (resource?.external_id as string) ?? '',
        monthly_cost: (resource?.monthly_cost as string | number) ?? '',
        status: (resource?.status as string) ?? 'active',
        notes: (resource?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/cloud-resources/${resource?.id}`);
        else post('/cloud-resources');
    }

    return (
        <AppLayout title={isEdit ? 'Edit cloud resource' : 'New cloud resource'}>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4">
                <label className="text-sm">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="text-sm">Client<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)} required disabled={isEdit}>{clients.map((c) => (<option key={c.id} value={c.id}>{c.company_name}</option>))}</select></label>
                <label className="text-sm">Type<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.resource_type} onChange={(e) => setData('resource_type', e.target.value)}>{Object.entries(resourceTypes).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <label className="text-sm">Monthly cost<input type="number" step="0.01" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.monthly_cost} onChange={(e) => setData('monthly_cost', e.target.value)} /></label>
                <div className="flex gap-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href="/cloud-resources" className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
