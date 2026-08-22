import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    serviceType: Record<string, unknown> | null;
    categories: Array<{ id: string; name: string }>;
    billingTypes: Record<string, string>;
    billingCycles: Record<string, string>;
    statuses: Record<string, string>;
    owners: Array<{ id: number; name: string }>;
};

export default function ServiceTypeForm({
    serviceType,
    categories,
    billingTypes,
    billingCycles,
    statuses,
    owners,
}: Props) {
    const isEdit = Boolean(serviceType?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        service_category_id: (serviceType?.service_category_id as string) ?? '',
        name: (serviceType?.name as string) ?? '',
        code: (serviceType?.code as string) ?? '',
        description: (serviceType?.description as string) ?? '',
        billing_type: (serviceType?.billing_type as string) ?? 'recurring',
        billing_cycle: (serviceType?.billing_cycle as string) ?? 'monthly',
        default_cost: (serviceType?.default_cost as string | number) ?? 0,
        default_selling_price: (serviceType?.default_selling_price as string | number) ?? 0,
        sla: (serviceType?.sla as string) ?? '',
        owner_id: (serviceType?.owner_id as number | string | null) ?? '',
        status: (serviceType?.status as string) ?? 'active',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/service-types/${serviceType?.id}`);
        } else {
            post('/service-types');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit service type' : 'New service type'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="block text-sm font-medium md:col-span-2">
                    Name
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && <span className="text-sm text-red-600">{errors.name}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Code
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.code}
                        onChange={(e) => setData('code', e.target.value)}
                        required
                    />
                </label>
                <label className="block text-sm font-medium">
                    Category
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.service_category_id}
                        onChange={(e) => setData('service_category_id', e.target.value)}
                        required
                    >
                        <option value="">Select…</option>
                        {categories.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Billing type
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.billing_type}
                        onChange={(e) => setData('billing_type', e.target.value)}
                    >
                        {Object.entries(billingTypes).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Billing cycle
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.billing_cycle}
                        onChange={(e) => setData('billing_cycle', e.target.value)}
                    >
                        {Object.entries(billingCycles).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Default cost
                    <input
                        type="number"
                        step="0.01"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.default_cost}
                        onChange={(e) => setData('default_cost', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Default selling price
                    <input
                        type="number"
                        step="0.01"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.default_selling_price}
                        onChange={(e) => setData('default_selling_price', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Owner
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.owner_id ?? ''}
                        onChange={(e) => setData('owner_id', e.target.value ? Number(e.target.value) : '')}
                    >
                        <option value="">Unassigned</option>
                        {owners.map((o) => (
                            <option key={o.id} value={o.id}>
                                {o.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Status
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.status}
                        onChange={(e) => setData('status', e.target.value)}
                    >
                        {Object.entries(statuses).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium md:col-span-2">
                    Description
                    <textarea
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        rows={3}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </label>
                <div className="flex gap-2 md:col-span-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}
                    </button>
                    <Link href="/service-types" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
