import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    contact: Record<string, unknown> | null;
    statuses: Record<string, string>;
    clients: Array<{ id: string; company_name: string }>;
    preselectedClientId: string | null;
};

export default function ContactForm({ contact, statuses, clients, preselectedClientId }: Props) {
    const isEdit = Boolean(contact?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (contact?.client_id as string) ?? preselectedClientId ?? '',
        name: (contact?.name as string) ?? '',
        position: (contact?.position as string) ?? '',
        email: (contact?.email as string) ?? '',
        phone: (contact?.phone as string) ?? '',
        department: (contact?.department as string) ?? '',
        is_primary: Boolean(contact?.is_primary),
        is_billing: Boolean(contact?.is_billing),
        is_technical: Boolean(contact?.is_technical),
        status: (contact?.status as string) ?? 'active',
        notes: (contact?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/contacts/${contact?.id}`);
        } else {
            post('/contacts');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit contact' : 'New contact'}>
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4">
                {!isEdit && (
                    <label className="block text-sm font-medium">
                        Client
                        <select
                            className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                            value={data.client_id}
                            onChange={(e) => setData('client_id', e.target.value)}
                            required
                        >
                            <option value="">Select client</option>
                            {clients.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.company_name}
                                </option>
                            ))}
                        </select>
                        {errors.client_id && (
                            <span className="mt-1 block text-sm text-red-600">{errors.client_id}</span>
                        )}
                    </label>
                )}

                <label className="block text-sm font-medium">
                    Name
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && <span className="mt-1 block text-sm text-red-600">{errors.name}</span>}
                </label>

                <div className="grid gap-4 sm:grid-cols-2">
                    <label className="block text-sm font-medium">
                        Position
                        <input
                            className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                            value={data.position}
                            onChange={(e) => setData('position', e.target.value)}
                        />
                    </label>
                    <label className="block text-sm font-medium">
                        Department
                        <input
                            className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                            value={data.department}
                            onChange={(e) => setData('department', e.target.value)}
                        />
                    </label>
                    <label className="block text-sm font-medium">
                        Email
                        <input
                            type="email"
                            className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                    </label>
                    <label className="block text-sm font-medium">
                        Phone
                        <input
                            className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                        />
                    </label>
                </div>

                <label className="block text-sm font-medium">
                    Status
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
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

                <div className="flex flex-wrap gap-4 text-sm">
                    <label className="inline-flex items-center gap-2">
                        <input
                            type="checkbox"
                            checked={data.is_primary}
                            onChange={(e) => setData('is_primary', e.target.checked)}
                        />
                        Primary
                    </label>
                    <label className="inline-flex items-center gap-2">
                        <input
                            type="checkbox"
                            checked={data.is_billing}
                            onChange={(e) => setData('is_billing', e.target.checked)}
                        />
                        Billing
                    </label>
                    <label className="inline-flex items-center gap-2">
                        <input
                            type="checkbox"
                            checked={data.is_technical}
                            onChange={(e) => setData('is_technical', e.target.checked)}
                        />
                        Technical
                    </label>
                </div>

                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save changes' : 'Create contact'}
                    </button>
                    <Link href="/contacts" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
