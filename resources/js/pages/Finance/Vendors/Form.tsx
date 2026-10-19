import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    vendor: Record<string, unknown> | null;
    statuses: Record<string, string>;
};

export default function VendorForm({ vendor, statuses }: Props) {
    const isEdit = Boolean(vendor?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        name: (vendor?.name as string) ?? '',
        code: (vendor?.code as string) ?? '',
        email: (vendor?.email as string) ?? '',
        phone: (vendor?.phone as string) ?? '',
        website: (vendor?.website as string) ?? '',
        notes: (vendor?.notes as string) ?? '',
        status: (vendor?.status as string) ?? 'active',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/vendors/${vendor?.id}`);
        } else {
            post('/vendors');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit vendor' : 'New vendor'}>
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4">
                <label className="block text-sm font-medium">
                    Name
                    <input className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                    {errors.name && <span className="mt-1 block text-sm text-red-600">{errors.name}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Code
                    <input className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.code} onChange={(e) => setData('code', e.target.value)} placeholder="aws" />
                    {errors.code && <span className="mt-1 block text-sm text-red-600">{errors.code}</span>}
                </label>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label className="block text-sm font-medium">
                        Email
                        <input type="email" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                    </label>
                    <label className="block text-sm font-medium">
                        Phone
                        <input className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                    </label>
                </div>
                <label className="block text-sm font-medium">
                    Website
                    <input className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.website} onChange={(e) => setData('website', e.target.value)} />
                </label>
                <label className="block text-sm font-medium">
                    Status
                    <select className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        {Object.entries(statuses).map(([v, l]) => (
                            <option key={v} value={v}>{l}</option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Notes
                    <textarea className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" rows={3} value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                </label>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white disabled:opacity-60">
                        {processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}
                    </button>
                    <Link href="/vendors" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
