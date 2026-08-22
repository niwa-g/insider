import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    account: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    providers: Array<{ id: string; name: string }>;
    statuses: Record<string, string>;
    preselectedClientId?: string | null;
};

export default function HostingAccountForm({ account, clients, preselectedClientId }: Props) {
    const isEdit = Boolean(account?.id);
    const { data, setData, post, put, processing } = useForm({
        client_id: (account?.client_id as string) ?? preselectedClientId ?? '',
        provider_id: (account?.provider_id as string) ?? '',
        name: (account?.name as string) ?? '',
        plan: (account?.plan as string) ?? '',
        primary_domain: (account?.primary_domain as string) ?? '',
        username: (account?.username as string) ?? '',
        expires_at: (account?.expires_at as string) ?? '',
        cost: (account?.cost as string | number) ?? '',
        status: (account?.status as string) ?? 'active',
        notes: (account?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/hosting-accounts/${account?.id}`);
        else post('/hosting-accounts');
    }

    return (
        <AppLayout title={isEdit ? 'Edit hosting account' : 'New hosting account'}>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4">
                <label className="text-sm">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="text-sm">Client<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)} required disabled={isEdit}>{clients.map((c) => (<option key={c.id} value={c.id}>{c.company_name}</option>))}</select></label>
                <label className="text-sm">Expires<input type="date" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.expires_at} onChange={(e) => setData('expires_at', e.target.value)} /></label>
                <label className="text-sm">Cost<input type="number" step="0.01" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.cost} onChange={(e) => setData('cost', e.target.value)} /></label>
                <div className="flex gap-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href="/hosting-accounts" className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
