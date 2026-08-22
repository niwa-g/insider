import { Link, useForm } from '@inertiajs/react';
import { FormEvent, ReactNode } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Option = { id: string | number; name: string };

type Props = {
    client: Record<string, unknown> | null;
    statuses: Record<string, string>;
    groups: Option[];
    managers: Option[];
};

export default function ClientForm({ client, statuses, groups, managers }: Props) {
    const isEdit = Boolean(client?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        company_name: (client?.company_name as string) ?? '',
        trading_name: (client?.trading_name as string) ?? '',
        client_code: (client?.client_code as string) ?? '',
        client_type: (client?.client_type as string) ?? '',
        industry: (client?.industry as string) ?? '',
        website: (client?.website as string) ?? '',
        email: (client?.email as string) ?? '',
        phone: (client?.phone as string) ?? '',
        address: (client?.address as string) ?? '',
        country: (client?.country as string) ?? '',
        billing_information: (client?.billing_information as string) ?? '',
        tax_information: (client?.tax_information as string) ?? '',
        status: (client?.status as string) ?? 'prospect',
        account_manager_id: (client?.account_manager_id as number | string | null) ?? '',
        notes: (client?.notes as string) ?? '',
        group_ids: (client?.group_ids as string[]) ?? [],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/clients/${client?.id}`);
        } else {
            post('/clients');
        }
    }

    function toggleGroup(id: string) {
        setData(
            'group_ids',
            data.group_ids.includes(id)
                ? data.group_ids.filter((g) => g !== id)
                : [...data.group_ids, id],
        );
    }

    return (
        <AppLayout title={isEdit ? 'Edit client' : 'New client'}>
            <form onSubmit={submit} className="mx-auto max-w-3xl space-y-4">
                <div className="grid gap-4 md:grid-cols-2">
                    <Field label="Company name" error={errors.company_name}>
                        <input
                            className="field"
                            value={data.company_name}
                            onChange={(e) => setData('company_name', e.target.value)}
                            required
                        />
                    </Field>
                    <Field label="Trading name" error={errors.trading_name}>
                        <input
                            className="field"
                            value={data.trading_name}
                            onChange={(e) => setData('trading_name', e.target.value)}
                        />
                    </Field>
                    <Field label="Client code" error={errors.client_code}>
                        <input
                            className="field"
                            value={data.client_code}
                            onChange={(e) => setData('client_code', e.target.value)}
                            required
                        />
                    </Field>
                    <Field label="Status" error={errors.status}>
                        <select
                            className="field"
                            value={data.status}
                            onChange={(e) => setData('status', e.target.value)}
                        >
                            {Object.entries(statuses).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                    </Field>
                    <Field label="Type" error={errors.client_type}>
                        <input
                            className="field"
                            value={data.client_type}
                            onChange={(e) => setData('client_type', e.target.value)}
                        />
                    </Field>
                    <Field label="Industry" error={errors.industry}>
                        <input
                            className="field"
                            value={data.industry}
                            onChange={(e) => setData('industry', e.target.value)}
                        />
                    </Field>
                    <Field label="Email" error={errors.email}>
                        <input
                            type="email"
                            className="field"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                    </Field>
                    <Field label="Phone" error={errors.phone}>
                        <input
                            className="field"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                        />
                    </Field>
                    <Field label="Website" error={errors.website}>
                        <input
                            className="field"
                            value={data.website}
                            onChange={(e) => setData('website', e.target.value)}
                        />
                    </Field>
                    <Field label="Country" error={errors.country}>
                        <input
                            className="field"
                            value={data.country}
                            onChange={(e) => setData('country', e.target.value)}
                        />
                    </Field>
                    <Field label="Account manager" error={errors.account_manager_id}>
                        <select
                            className="field"
                            value={data.account_manager_id ?? ''}
                            onChange={(e) =>
                                setData('account_manager_id', e.target.value ? Number(e.target.value) : '')
                            }
                        >
                            <option value="">Unassigned</option>
                            {managers.map((m) => (
                                <option key={m.id} value={m.id}>
                                    {m.name}
                                </option>
                            ))}
                        </select>
                    </Field>
                </div>

                <Field label="Address" error={errors.address}>
                    <textarea
                        className="field"
                        rows={2}
                        value={data.address}
                        onChange={(e) => setData('address', e.target.value)}
                    />
                </Field>
                <Field label="Notes" error={errors.notes}>
                    <textarea
                        className="field"
                        rows={3}
                        value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)}
                    />
                </Field>

                <div>
                    <p className="mb-2 text-sm font-medium">Groups</p>
                    <div className="flex flex-wrap gap-2">
                        {groups.map((group) => (
                            <label
                                key={group.id}
                                className="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-slate-700"
                            >
                                <input
                                    type="checkbox"
                                    checked={data.group_ids.includes(String(group.id))}
                                    onChange={() => toggleGroup(String(group.id))}
                                />
                                {group.name}
                            </label>
                        ))}
                        {groups.length === 0 && (
                            <span className="text-sm text-slate-500">No groups yet.</span>
                        )}
                    </div>
                </div>

                <div className="flex gap-2 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save changes' : 'Create client'}
                    </button>
                    <Link href="/clients" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>

            <style>{`
                .field {
                    margin-top: 0.25rem;
                    width: 100%;
                    border-radius: 0.5rem;
                    border: 1px solid rgb(203 213 225);
                    background: white;
                    padding: 0.5rem 0.75rem;
                    font-size: 0.875rem;
                }
                .dark .field {
                    border-color: rgb(51 65 85);
                    background: rgb(2 6 23);
                }
            `}</style>
        </AppLayout>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <label className="block text-sm font-medium">
            {label}
            {children}
            {error && <span className="mt-1 block text-sm text-red-600">{error}</span>}
        </label>
    );
}
