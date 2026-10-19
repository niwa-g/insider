import { Link, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Service = {
    id: string;
    name: string;
    client_id: string;
    vendor_id: string | null;
    cost: string | number;
};

type Props = {
    bill: null;
    vendors: Array<{ id: string; name: string }>;
    clients: Array<{ id: string; company_name: string }>;
    clientServices: Service[];
};

export default function VendorBillForm({ vendors, clients, clientServices }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        vendor_id: '',
        client_id: '',
        client_service_id: '',
        description: '',
        amount: '',
        issue_date: new Date().toISOString().slice(0, 10),
        due_date: new Date(Date.now() + 14 * 86400000).toISOString().slice(0, 10),
        notes: '',
    });

    const services = useMemo(
        () => clientServices.filter((s) => {
            if (data.client_id && s.client_id !== data.client_id) return false;
            if (data.vendor_id && s.vendor_id && s.vendor_id !== data.vendor_id) return false;
            return true;
        }),
        [clientServices, data.client_id, data.vendor_id],
    );

    function onServiceChange(id: string) {
        const service = clientServices.find((s) => s.id === id);
        setData({
            ...data,
            client_service_id: id,
            client_id: service?.client_id ?? data.client_id,
            vendor_id: service?.vendor_id || data.vendor_id,
            amount: service ? String(service.cost) : data.amount,
            description: service ? `Vendor cost for ${service.name}` : data.description,
        });
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/vendor-bills');
    }

    return (
        <AppLayout title="New vendor bill">
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4">
                <label className="block text-sm font-medium">
                    Vendor
                    <select className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.vendor_id} onChange={(e) => setData('vendor_id', e.target.value)} required>
                        <option value="">Select vendor</option>
                        {vendors.map((v) => (
                            <option key={v.id} value={v.id}>{v.name}</option>
                        ))}
                    </select>
                    {errors.vendor_id && <span className="mt-1 block text-sm text-red-600">{errors.vendor_id}</span>}
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
                    <select className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.client_service_id} onChange={(e) => onServiceChange(e.target.value)}>
                        <option value="">None</option>
                        {services.map((s) => (
                            <option key={s.id} value={s.id}>{s.name} (${Number(s.cost).toFixed(2)})</option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Description
                    <input className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.description} onChange={(e) => setData('description', e.target.value)} required />
                    {errors.description && <span className="mt-1 block text-sm text-red-600">{errors.description}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Amount
                    <input type="number" step="0.01" min="0.01" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.amount} onChange={(e) => setData('amount', e.target.value)} required />
                    {errors.amount && <span className="mt-1 block text-sm text-red-600">{errors.amount}</span>}
                </label>
                <div className="grid grid-cols-2 gap-4">
                    <label className="block text-sm font-medium">
                        Issue date
                        <input type="date" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.issue_date} onChange={(e) => setData('issue_date', e.target.value)} required />
                    </label>
                    <label className="block text-sm font-medium">
                        Due date
                        <input type="date" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.due_date} onChange={(e) => setData('due_date', e.target.value)} />
                    </label>
                </div>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white disabled:opacity-50">Create</button>
                    <Link href="/vendor-bills" className="rounded-lg border border-slate-300 px-4 py-2 text-sm">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
