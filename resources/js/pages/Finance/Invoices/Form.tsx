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
    discountReasons: Array<{ id: string; name: string; code: string }>;
    preselectedClientId: string | null;
    canManageReasons: boolean;
};

export default function InvoiceForm({
    invoice,
    clients,
    clientServices,
    discountReasons = [],
    preselectedClientId,
    canManageReasons = false,
}: Props) {
    const isEdit = Boolean(invoice?.id);
    const initialLines = (invoice?.lines as Line[] | undefined) ?? [
        { description: '', quantity: 1, unit_price: 0, client_service_id: '' },
    ];

    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (invoice?.client_id as string) ?? preselectedClientId ?? '',
        issue_date: (invoice?.issue_date as string) ?? new Date().toISOString().slice(0, 10),
        due_date: (invoice?.due_date as string) ?? '',
        discount_type: (invoice?.discount_type as string) ?? 'flat',
        discount_value: (invoice?.discount_value as string | number) ?? 0,
        discount_reason_id: (invoice?.discount_reason_id as string) ?? '',
        tax_amount: (invoice?.tax_amount as string | number) ?? 0,
        notes: (invoice?.notes as string) ?? '',
        lines: initialLines,
    });

    const linesSubtotal = data.lines.reduce((sum, line) => {
        return sum + Number(line.quantity || 0) * Number(line.unit_price || 0);
    }, 0);
    const discountValue = Math.max(Number(data.discount_value || 0), 0);
    const discount =
        data.discount_type === 'percentage'
            ? Math.min(Math.round(linesSubtotal * (Math.min(discountValue, 100) / 100) * 100) / 100, linesSubtotal)
            : Math.min(discountValue, linesSubtotal);
    const tax = Math.max(Number(data.tax_amount || 0), 0);
    const previewTotal = Math.max(0, linesSubtotal - discount) + tax;

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
            <form onSubmit={submit} className="mx-auto w-full max-w-3xl space-y-4">
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

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label className="block text-sm font-medium">Issue date</label>
                        <input type="date" value={data.issue_date} onChange={(e) => setData('issue_date', e.target.value)} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium">Due date</label>
                        <input type="date" value={data.due_date} onChange={(e) => setData('due_date', e.target.value)} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                </div>

                <div className="min-w-0">
                    <div className="mb-2 flex items-center justify-between gap-3">
                        <label className="text-sm font-medium">Line items</label>
                        <button type="button" onClick={addLine} className="shrink-0 text-sm text-blue-600 hover:underline">Add line</button>
                    </div>
                    <div className="space-y-3">
                        {data.lines.map((line, i) => {
                            const lineTotal = Number(line.quantity || 0) * Number(line.unit_price || 0);
                            return (
                                <div
                                    key={i}
                                    className="min-w-0 space-y-3 rounded-lg border border-slate-200 p-3 dark:border-slate-800"
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <span className="text-xs font-medium text-slate-500">Line {i + 1}</span>
                                        {data.lines.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeLine(i)}
                                                className="shrink-0 text-xs text-red-600 hover:underline"
                                            >
                                                Remove
                                            </button>
                                        )}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-500">Service</label>
                                        <select
                                            value={line.client_service_id}
                                            onChange={(e) => updateLine(i, 'client_service_id', e.target.value)}
                                            className="mt-1 w-full min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                        >
                                            <option value="">Service (optional)</option>
                                            {filteredServices.map((s) => (
                                                <option key={s.id} value={s.id}>{s.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-500">Description</label>
                                        <input
                                            placeholder="Description"
                                            value={line.description}
                                            onChange={(e) => updateLine(i, 'description', e.target.value)}
                                            className="mt-1 w-full min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                        />
                                    </div>
                                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                        <div className="min-w-0">
                                            <label className="block text-xs font-medium text-slate-500">Qty</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                value={line.quantity}
                                                onChange={(e) => updateLine(i, 'quantity', e.target.value)}
                                                className="mt-1 w-full min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                            />
                                        </div>
                                        <div className="min-w-0">
                                            <label className="block text-xs font-medium text-slate-500">Unit price</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={line.unit_price}
                                                onChange={(e) => updateLine(i, 'unit_price', e.target.value)}
                                                className="mt-1 w-full min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                            />
                                        </div>
                                        <div className="col-span-2 min-w-0 sm:col-span-1">
                                            <label className="block text-xs font-medium text-slate-500">Line total</label>
                                            <div className="mt-1 truncate rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-right text-sm font-medium tabular-nums dark:border-slate-800 dark:bg-slate-950">
                                                ${lineTotal.toFixed(2)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                    {errors.lines && <p className="text-sm text-red-600">{errors.lines}</p>}
                </div>

                <div className="min-w-0 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                    <h3 className="mb-3 text-sm font-semibold">Discount & tax</h3>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="min-w-0">
                            <label className="block text-sm font-medium">Discount type</label>
                            <select
                                value={data.discount_type}
                                onChange={(e) => setData('discount_type', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                            >
                                <option value="flat">Flat amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                            {errors.discount_type && <p className="text-sm text-red-600">{errors.discount_type}</p>}
                        </div>
                        <div className="min-w-0">
                            <label className="block text-sm font-medium">
                                {data.discount_type === 'percentage' ? 'Discount %' : 'Discount amount'}
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max={data.discount_type === 'percentage' ? 100 : undefined}
                                value={data.discount_value}
                                onChange={(e) => {
                                    const value = e.target.value;
                                    setData({
                                        ...data,
                                        discount_value: value,
                                        discount_reason_id: Number(value || 0) <= 0 ? '' : data.discount_reason_id,
                                    });
                                }}
                                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                            />
                            {errors.discount_value && <p className="text-sm text-red-600">{errors.discount_value}</p>}
                        </div>
                        <div className="min-w-0">
                            <label className="block text-sm font-medium">Tax amount</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                value={data.tax_amount}
                                onChange={(e) => setData('tax_amount', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                            />
                            {errors.tax_amount && <p className="text-sm text-red-600">{errors.tax_amount}</p>}
                        </div>
                    </div>
                    <div className="mt-4 min-w-0">
                        <div className="mb-1 flex flex-wrap items-center justify-between gap-2">
                            <label className="block text-sm font-medium">
                                Discount reason{discountValue > 0 ? '' : ' (optional unless discount set)'}
                            </label>
                            {canManageReasons && (
                                <Link href="/discount-reasons" className="shrink-0 text-xs text-blue-600 hover:underline">
                                    Manage types
                                </Link>
                            )}
                        </div>
                        <select
                            value={data.discount_reason_id}
                            onChange={(e) => setData('discount_reason_id', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                            required={discountValue > 0}
                        >
                            <option value="">Select reason</option>
                            {discountReasons.map((reason) => (
                                <option key={reason.id} value={reason.id}>
                                    {reason.name}
                                </option>
                            ))}
                        </select>
                        {errors.discount_reason_id && (
                            <p className="text-sm text-red-600">{errors.discount_reason_id}</p>
                        )}
                    </div>
                    <div className="mt-4 space-y-2 text-sm">
                        <div className="flex items-baseline justify-between gap-4 text-slate-600">
                            <span className="min-w-0 shrink">Subtotal</span>
                            <span className="shrink-0 tabular-nums">${linesSubtotal.toFixed(2)}</span>
                        </div>
                        <div className="flex items-baseline justify-between gap-4 text-slate-600">
                            <span className="min-w-0 truncate">
                                Discount
                                {data.discount_type === 'percentage' && discountValue > 0
                                    ? ` (${discountValue}%)`
                                    : ''}
                            </span>
                            <span className="shrink-0 tabular-nums">-${discount.toFixed(2)}</span>
                        </div>
                        <div className="flex items-baseline justify-between gap-4 text-slate-600">
                            <span className="min-w-0 shrink">Tax</span>
                            <span className="shrink-0 tabular-nums">${tax.toFixed(2)}</span>
                        </div>
                        <div className="flex items-baseline justify-between gap-4 border-t border-slate-200 pt-2 font-semibold dark:border-slate-700">
                            <span className="min-w-0 shrink">Total</span>
                            <span className="shrink-0 tabular-nums">${previewTotal.toFixed(2)}</span>
                        </div>
                    </div>
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
