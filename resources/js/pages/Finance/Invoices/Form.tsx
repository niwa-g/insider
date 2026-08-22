import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Line = {
    description: string;
    quantity: number | string;
    unit_price: number | string;
    client_service_id: string;
};

type Props = {
    invoice: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    clientServices: Array<{ id: string; name: string; client_id: string; selling_price?: string | number }>;
    preselectedClientId: string | null;
};

export default function InvoiceForm({ invoice, clients, clientServices, preselectedClientId }: Props) {
    const isEdit = Boolean(invoice?.id);
    const initialLines = (invoice?.lines as Line[] | undefined) ?? [
        { description: '', quantity: 1, unit_price: 0, client_service_id: '' },
    ];

    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (invoice?.client_id as string) ?? preselectedClientId ?? '',
        issue_date: (invoice?.issue_date as string) ?? new Date().toISOString().slice(0, 10),
        due_date: (invoice?.due_date as string) ?? '',
        tax_amount: (invoice?.tax_amount as string | number) ?? 0,
        notes: (invoice?.notes as string) ?? '',
        lines: initialLines,
    });

    const filteredServices = clientServices.filter((s) => !data.client_id || s.client_id === data.client_id);

    function updateLine(index: number, field: keyof Line, value: string | number) {
        const lines = [...data.lines];
        lines[index] = { ...lines[index], [field]: value };
        if (field === 'client_service_id' && value) {
            const svc = clientServices.find((s) => s.id === value);
            if (svc) {
                lines[index].description = svc.name;
                lines[index].unit_price = svc.selling_price ?? 0;
            }
        }
        setData('lines', lines);
    }

    function addLine() {
        setData('lines', [...data.lines, { description: '', quantity: 1, unit_price: 0, client_service_id: '' }]);
    }

    function removeLine(index: number) {
        setData('lines', data.lines.filter((_, i) => i !== index));
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/invoices/${invoice?.id}`);
        } else {
            post('/invoices');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit Invoice' : 'New Invoice'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <div>
                    <label className="block text-sm font-medium">Client</label>
                    <select
                        value={data.client_id}
                        onChange={(e) => setData('client_id', e.target.value)}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">Select client</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>{c.company_name}</option>
                        ))}
                    </select>
                    {errors.client_id && <p className="text-sm text-red-600">{errors.client_id}</p>}
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium">Issue date</label>
                        <input type="date" value={data.issue_date} onChange={(e) => setData('issue_date', e.target.value)} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium">Due date</label>
                        <input type="date" value={data.due_date} onChange={(e) => setData('due_date', e.target.value)} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                </div>

                <div>
                    <div className="mb-2 flex items-center justify-between">
                        <label className="text-sm font-medium">Line items</label>
                        <button type="button" onClick={addLine} className="text-sm text-blue-600 hover:underline">Add line</button>
                    </div>
                    <div className="space-y-3">
                        {data.lines.map((line, i) => (
                            <div key={i} className="grid gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-800 sm:grid-cols-4">
                                <select
                                    value={line.client_service_id}
                                    onChange={(e) => updateLine(i, 'client_service_id', e.target.value)}
                                    className="rounded border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">Service (optional)</option>
                                    {filteredServices.map((s) => (
                                        <option key={s.id} value={s.id}>{s.name}</option>
                                    ))}
                                </select>
                                <input
                                    placeholder="Description"
                                    value={line.description}
                                    onChange={(e) => updateLine(i, 'description', e.target.value)}
                                    className="rounded border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 sm:col-span-2"
                                />
                                <div className="flex gap-2">
                                    <input type="number" step="0.01" min="0.01" value={line.quantity} onChange={(e) => updateLine(i, 'quantity', e.target.value)} className="w-16 rounded border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950" />
                                    <input type="number" step="0.01" min="0" value={line.unit_price} onChange={(e) => updateLine(i, 'unit_price', e.target.value)} className="flex-1 rounded border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950" />
                                    {data.lines.length > 1 && (
                                        <button type="button" onClick={() => removeLine(i)} className="text-red-600">×</button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                    {errors.lines && <p className="text-sm text-red-600">{errors.lines}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium">Tax amount</label>
                    <input type="number" step="0.01" min="0" value={data.tax_amount} onChange={(e) => setData('tax_amount', e.target.value)} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                </div>

                <div>
                    <label className="block text-sm font-medium">Notes</label>
                    <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} rows={3} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                </div>

                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white disabled:opacity-50">
                        {isEdit ? 'Update' : 'Create'}
                    </button>
                    <Link href="/invoices" className="rounded-lg border border-slate-300 px-4 py-2 text-sm">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
