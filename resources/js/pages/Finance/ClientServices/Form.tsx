import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    service: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    serviceTypes: Array<{
        id: string;
        name: string;
        code: string;
        billing_type?: string;
        billing_cycle?: string | null;
        default_cost?: string | number;
        default_selling_price?: string | number;
    }>;
    billingTypes: Record<string, string>;
    billingCycles: Record<string, string>;
    statuses: Record<string, string>;
    users: Array<{ id: number; name: string }>;
    vendors: Array<{ id: string; name: string }>;
    preselectedClientId?: string | null;
};

export default function ClientServiceForm({
    service,
    clients,
    serviceTypes,
    billingTypes,
    billingCycles,
    statuses,
    users,
    vendors = [],
    preselectedClientId,
}: Props) {
    const isEdit = Boolean(service?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (service?.client_id as string) ?? preselectedClientId ?? '',
        service_type_id: (service?.service_type_id as string) ?? '',
        name: (service?.name as string) ?? '',
        start_date: (service?.start_date as string) ?? '',
        end_date: (service?.end_date as string) ?? '',
        renewal_date: (service?.renewal_date as string) ?? '',
        billing_type: (service?.billing_type as string) ?? 'recurring',
        billing_cycle: (service?.billing_cycle as string) ?? 'monthly',
        cost: (service?.cost as string | number) ?? 0,
        selling_price: (service?.selling_price as string | number) ?? 0,
        provider: (service?.provider as string) ?? '',
        vendor_id: (service?.vendor_id as string) ?? '',
        account_manager_id: (service?.account_manager_id as number | string | null) ?? '',
        technical_owner_id: (service?.technical_owner_id as number | string | null) ?? '',
        status: (service?.status as string) ?? 'active',
        notes: (service?.notes as string) ?? '',
    });

    function onServiceTypeChange(id: string) {
        const type = serviceTypes.find((t) => t.id === id);
        setData('service_type_id', id);
        if (!type || isEdit) {
            return;
        }
        if (!data.name) {
            setData('name', type.name);
        }
        if (type.billing_type) {
            setData('billing_type', type.billing_type);
        }
        if (type.billing_cycle) {
            setData('billing_cycle', type.billing_cycle);
        }
        if (type.default_cost !== undefined) {
            setData('cost', type.default_cost);
        }
        if (type.default_selling_price !== undefined) {
            setData('selling_price', type.default_selling_price);
        }
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/client-services/${service?.id}`);
        } else {
            post('/client-services');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit client service' : 'Assign service'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="block text-sm font-medium">
                    Client
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.client_id}
                        onChange={(e) => setData('client_id', e.target.value)}
                        required
                        disabled={isEdit}
                    >
                        <option value="">Select…</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.company_name}
                            </option>
                        ))}
                    </select>
                    {errors.client_id && <span className="text-sm text-red-600">{errors.client_id}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Service type
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.service_type_id}
                        onChange={(e) => onServiceTypeChange(e.target.value)}
                        required
                    >
                        <option value="">Select…</option>
                        {serviceTypes.map((t) => (
                            <option key={t.id} value={t.id}>
                                {t.name} ({t.code})
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium md:col-span-2">
                    Display name
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
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
                    Cost
                    <input
                        type="number"
                        step="0.01"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.cost}
                        onChange={(e) => setData('cost', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Selling price
                    <input
                        type="number"
                        step="0.01"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.selling_price}
                        onChange={(e) => setData('selling_price', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Start date
                    <input
                        type="date"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.start_date}
                        onChange={(e) => setData('start_date', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Renewal date
                    <input
                        type="date"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.renewal_date}
                        onChange={(e) => setData('renewal_date', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Vendor (e.g. AWS)
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.vendor_id}
                        onChange={(e) => {
                            const id = e.target.value;
                            const vendor = vendors.find((v) => v.id === id);
                            setData({
                                ...data,
                                vendor_id: id,
                                provider: vendor?.name ?? data.provider,
                            });
                        }}
                    >
                        <option value="">None</option>
                        {vendors.map((v) => (
                            <option key={v.id} value={v.id}>{v.name}</option>
                        ))}
                    </select>
                    <span className="mt-1 block text-xs text-slate-500">
                        Used for vendor bills & cost when this service renews.{' '}
                        <Link href="/vendors" className="text-blue-600 hover:underline">Manage vendors</Link>
                    </span>
                    {errors.vendor_id && <span className="mt-1 block text-sm text-red-600">{errors.vendor_id}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Account manager
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.account_manager_id ?? ''}
                        onChange={(e) => setData('account_manager_id', e.target.value ? Number(e.target.value) : '')}
                    >
                        <option value="">Unassigned</option>
                        {users.map((u) => (
                            <option key={u.id} value={u.id}>
                                {u.name}
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
                    Notes
                    <textarea
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        rows={3}
                        value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)}
                    />
                </label>
                <div className="flex gap-2 md:col-span-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save' : 'Assign'}
                    </button>
                    <Link href="/client-services" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
