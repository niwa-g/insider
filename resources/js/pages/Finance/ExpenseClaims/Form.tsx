import { Link, useForm } from '@inertiajs/react';
import { ChangeEvent, FormEvent, useMemo } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    categories: Record<string, string>;
    clients: Array<{ id: string; company_name: string }>;
    clientServices: Array<{ id: string; name: string; client_id: string }>;
};

export default function ExpenseClaimForm({ categories, clients, clientServices }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        category: 'other',
        description: '',
        amount: '',
        incurred_on: new Date().toISOString().slice(0, 10),
        client_id: '',
        client_service_id: '',
        receipt: null as File | null,
    });

    const services = useMemo(
        () => clientServices.filter((s) => !data.client_id || s.client_id === data.client_id),
        [clientServices, data.client_id],
    );

    function onReceipt(e: ChangeEvent<HTMLInputElement>) {
        setData('receipt', e.target.files?.[0] ?? null);
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/expense-claims');
    }

    return (
        <AppLayout title="Submit expense claim">
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4">
                <label className="block text-sm font-medium">
                    Title
                    <input className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                    {errors.title && <span className="mt-1 block text-sm text-red-600">{errors.title}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Category
                    <select className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.category} onChange={(e) => setData('category', e.target.value)}>
                        {Object.entries(categories).map(([v, l]) => (
                            <option key={v} value={v}>{l}</option>
                        ))}
                    </select>
                </label>
                <div className="grid grid-cols-2 gap-4">
                    <label className="block text-sm font-medium">
                        Amount
                        <input type="number" step="0.01" min="0.01" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.amount} onChange={(e) => setData('amount', e.target.value)} required />
                        {errors.amount && <span className="mt-1 block text-sm text-red-600">{errors.amount}</span>}
                    </label>
                    <label className="block text-sm font-medium">
                        Incurred on
                        <input type="date" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.incurred_on} onChange={(e) => setData('incurred_on', e.target.value)} required />
                    </label>
                </div>
                <label className="block text-sm font-medium">
                    Description
                    <textarea className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" rows={3} value={data.description} onChange={(e) => setData('description', e.target.value)} />
                </label>
                <label className="block text-sm font-medium">
                    Client (optional)
                    <select className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData({ ...data, client_id: e.target.value, client_service_id: '' })}>
                        <option value="">None</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>{c.company_name}</option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Client service (optional)
                    <select className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.client_service_id} onChange={(e) => setData('client_service_id', e.target.value)}>
                        <option value="">None</option>
                        {services.map((s) => (
                            <option key={s.id} value={s.id}>{s.name}</option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Receipt (optional, PDF/image up to 5MB)
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" onChange={onReceipt} className="mt-1 w-full text-sm" />
                    {errors.receipt && <span className="mt-1 block text-sm text-red-600">{errors.receipt}</span>}
                </label>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white disabled:opacity-50">
                        {processing ? 'Submitting…' : 'Submit claim'}
                    </button>
                    <Link href="/expense-claims" className="rounded-lg border border-slate-300 px-4 py-2 text-sm">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
