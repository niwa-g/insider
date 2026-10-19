import { Link, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Service = {
    id: string;
    name: string;
    client_id: string;
    selling_price: string | number;
    billing_cycle?: string;
};

type Props = {
    clients: Array<{ id: string; company_name: string }>;
    clientServices: Service[];
};

export default function InvoiceFromServices({ clients, clientServices }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        client_id: '',
        client_service_ids: [] as string[],
        issue_date: new Date().toISOString().slice(0, 10),
        due_date: new Date(Date.now() + 14 * 86400000).toISOString().slice(0, 10),
        notes: '',
    });

    const services = useMemo(
        () => clientServices.filter((s) => s.client_id === data.client_id),
        [clientServices, data.client_id],
    );

    const selectedTotal = services
        .filter((s) => data.client_service_ids.includes(s.id))
        .reduce((sum, s) => sum + Number(s.selling_price || 0), 0);

    function toggleService(id: string) {
        setData(
            'client_service_ids',
            data.client_service_ids.includes(id)
                ? data.client_service_ids.filter((x) => x !== id)
                : [...data.client_service_ids, id],
        );
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/invoices/from-services');
    }

    return (
        <AppLayout title="Invoice from services">
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <div>
                    <label className="block text-sm font-medium">Client</label>
                    <select
                        value={data.client_id}
                        onChange={(e) => setData({ ...data, client_id: e.target.value, client_service_ids: [] })}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        required
                    >
                        <option value="">Select client</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>{c.company_name}</option>
                        ))}
                    </select>
                    {errors.client_id && <p className="text-sm text-red-600">{errors.client_id}</p>}
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label className="block text-sm font-medium">Issue date</label>
                        <input
                            type="date"
                            value={data.issue_date}
                            onChange={(e) => setData('issue_date', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium">Due date</label>
                        <input
                            type="date"
                            value={data.due_date}
                            onChange={(e) => setData('due_date', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        />
                    </div>
                </div>

                <div>
                    <div className="mb-2 flex items-center justify-between">
                        <label className="text-sm font-medium">Client services</label>
                        <span className="text-sm tabular-nums text-slate-500">
                            Selected: ${selectedTotal.toFixed(2)}
                        </span>
                    </div>
                    {!data.client_id ? (
                        <p className="text-sm text-slate-500">Select a client to see services.</p>
                    ) : services.length === 0 ? (
                        <p className="text-sm text-slate-500">No services for this client.</p>
                    ) : (
                        <div className="space-y-2 rounded-xl border border-slate-200 p-3 dark:border-slate-800">
                            {services.map((service) => (
                                <label
                                    key={service.id}
                                    className="flex cursor-pointer items-center justify-between gap-3 rounded-lg px-2 py-2 hover:bg-slate-50 dark:hover:bg-slate-900"
                                >
                                    <span className="flex min-w-0 items-center gap-2">
                                        <input
                                            type="checkbox"
                                            checked={data.client_service_ids.includes(service.id)}
                                            onChange={() => toggleService(service.id)}
                                        />
                                        <span className="truncate text-sm">{service.name}</span>
                                    </span>
                                    <span className="shrink-0 text-sm tabular-nums">
                                        ${Number(service.selling_price || 0).toFixed(2)}
                                    </span>
                                </label>
                            ))}
                        </div>
                    )}
                    {errors.client_service_ids && (
                        <p className="text-sm text-red-600">{errors.client_service_ids}</p>
                    )}
                </div>

                <div>
                    <label className="block text-sm font-medium">Notes</label>
                    <textarea
                        value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)}
                        rows={3}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    />
                </div>

                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={processing || data.client_service_ids.length === 0}
                        className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white disabled:opacity-50"
                    >
                        Create draft invoice
                    </button>
                    <Link href="/invoices" className="rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
