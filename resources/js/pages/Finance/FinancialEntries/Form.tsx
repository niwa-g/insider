import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    entry: Record<string, unknown> | null;
    entryTypes: Record<string, string>;
    categories: Record<string, string>;
    clients: Array<{ id: string; company_name: string }>;
    clientServices: Array<{ id: string; name: string; client_id: string }>;
    preselectedType: string | null;
    preselectedClientId: string | null;
};

export default function FinancialEntryForm({
    entry,
    entryTypes,
    categories,
    clients,
    clientServices,
    preselectedType,
    preselectedClientId,
}: Props) {
    const isEdit = Boolean(entry?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (entry?.client_id as string) ?? preselectedClientId ?? '',
        project_id: (entry?.project_id as string) ?? '',
        client_service_id: (entry?.client_service_id as string) ?? '',
        entry_type: (entry?.entry_type as string) ?? preselectedType ?? 'revenue',
        category: (entry?.category as string) ?? (preselectedType === 'cost' ? 'office' : 'hosting'),
        vendor: (entry?.vendor as string) ?? '',
        description: (entry?.description as string) ?? '',
        amount: (entry?.amount as string | number) ?? 0,
        occurred_on: (entry?.occurred_on as string) ?? new Date().toISOString().slice(0, 10),
        notes: (entry?.notes as string) ?? '',
    });

    const filteredServices = clientServices.filter((s) => !data.client_id || s.client_id === data.client_id);

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/financial-entries/${entry?.id}`);
        } else {
            post('/financial-entries');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit Entry' : 'New Entry'}>
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4">
                <div>
                    <label className="block text-sm font-medium">Type</label>
                    <select
                        value={data.entry_type}
                        onChange={(e) => setData('entry_type', e.target.value)}
                        disabled={isEdit}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        {Object.entries(entryTypes).map(([v, l]) => (
                            <option key={v} value={v}>{l}</option>
                        ))}
                    </select>
                    {errors.entry_type && <p className="text-sm text-red-600">{errors.entry_type}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium">Client</label>
                    <select
                        value={data.client_id}
                        onChange={(e) => setData('client_id', e.target.value)}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">None</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>{c.company_name}</option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-sm font-medium">Client service</label>
                    <select
                        value={data.client_service_id}
                        onChange={(e) => setData('client_service_id', e.target.value)}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">None</option>
                        {filteredServices.map((s) => (
                            <option key={s.id} value={s.id}>{s.name}</option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-sm font-medium">Category</label>
                    <select
                        value={data.category}
                        onChange={(e) => setData('category', e.target.value)}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        {Object.entries(categories).map(([v, l]) => (
                            <option key={v} value={v}>{l}</option>
                        ))}
                    </select>
                </div>

                {data.entry_type === 'cost' && (
                    <div>
                        <label className="block text-sm font-medium">Vendor / supplier</label>
                        <input
                            value={data.vendor}
                            onChange={(e) => setData('vendor', e.target.value)}
                            placeholder="Optional"
                            className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        />
                        {errors.vendor && <p className="text-sm text-red-600">{errors.vendor}</p>}
                    </div>
                )}

                <div>
                    <label className="block text-sm font-medium">Description</label>
                    <input
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    />
                    {errors.description && <p className="text-sm text-red-600">{errors.description}</p>}
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium">Amount (USD)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            value={data.amount}
                            onChange={(e) => setData('amount', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        />
                        {errors.amount && <p className="text-sm text-red-600">{errors.amount}</p>}
                    </div>
                    <div>
                        <label className="block text-sm font-medium">Date</label>
                        <input
                            type="date"
                            value={data.occurred_on}
                            onChange={(e) => setData('occurred_on', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        />
                        {errors.occurred_on && <p className="text-sm text-red-600">{errors.occurred_on}</p>}
                    </div>
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
                    <button type="submit" disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white disabled:opacity-50">
                        {isEdit ? 'Update' : 'Create'}
                    </button>
                    <Link href={`/financial-entries?entry_type=${data.entry_type}`} className="rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
