import { Link, useForm, router } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    document: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    projects: Array<{ id: string; name: string; client_id: string }>;
    tickets: Array<{ id: string; number: string; subject: string }>;
    visibilities: Record<string, string>;
    statuses: Record<string, string>;
};

export default function DocumentForm({ document, clients, visibilities, statuses }: Props) {
    const isEdit = Boolean(document?.id);
    const { data, setData, post, processing, errors } = useForm({
        client_id: (document?.client_id as string) ?? '',
        project_id: (document?.project_id as string) ?? '',
        ticket_id: (document?.ticket_id as string) ?? '',
        title: (document?.title as string) ?? '',
        description: (document?.description as string) ?? '',
        visibility: (document?.visibility as string) ?? 'internal',
        status: (document?.status as string) ?? 'active',
        file: null as File | null,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) router.post(`/documents/${document?.id}`, { ...data, _method: 'put' }, { forceFormData: true });
        else post('/documents', { forceFormData: true });
    }

    return (
        <AppLayout title={isEdit ? 'Edit document' : 'Upload document'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <label className="block text-sm font-medium">
                    Title
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                    {errors.title && <span className="text-sm text-red-600">{errors.title}</span>}
                </label>
                <label className="block text-sm font-medium">
                    File {!isEdit && '*'}
                    <input type="file" className="mt-1 w-full text-sm" onChange={(e) => setData('file', e.target.files?.[0] ?? null)} required={!isEdit} />
                </label>
                <label className="block text-sm font-medium">
                    Client
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)}>
                        <option value="">None</option>
                        {clients.map((c) => (<option key={c.id} value={c.id}>{c.company_name}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Visibility
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.visibility} onChange={(e) => setData('visibility', e.target.value)}>
                        {Object.entries(visibilities).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Status
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        {Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : isEdit ? 'Save' : 'Upload'}</button>
                    <Link href="/documents" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
