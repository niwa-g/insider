import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type ContactRow = {
    id: string;
    name: string;
    email: string | null;
    phone: string | null;
    is_primary: boolean;
    client?: { id: string; company_name: string; client_code: string };
};

type Props = {
    contacts: { data: ContactRow[] };
    filters: { search: string; client_id: string };
    clients: Array<{ id: string; company_name: string }>;
    can: { create: boolean };
};

export default function ContactsIndex({ contacts, filters, clients, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [clientId, setClientId] = useState(filters.client_id ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/contacts', { search, client_id: clientId }, { preserveState: true });
    }

    return (
        <AppLayout title="Contacts">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search contacts…"
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    />
                    <select
                        value={clientId}
                        onChange={(e) => setClientId(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All clients</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.company_name}
                            </option>
                        ))}
                    </select>
                    <button
                        type="submit"
                        className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white dark:bg-slate-100 dark:text-slate-900"
                    >
                        Filter
                    </button>
                </form>
                {can.create && (
                    <Link
                        href="/contacts/create"
                        className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        New contact
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Client</th>
                            <th className="px-4 py-3 font-medium">Email</th>
                            <th className="px-4 py-3 font-medium">Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        {contacts.data.map((contact) => (
                            <tr
                                key={contact.id}
                                className="border-b border-slate-100 last:border-0 dark:border-slate-800"
                            >
                                <td className="px-4 py-3">
                                    <Link
                                        href={`/contacts/${contact.id}/edit`}
                                        className="font-medium text-blue-600 hover:underline"
                                    >
                                        {contact.name}
                                    </Link>
                                    {contact.is_primary && (
                                        <span className="ml-2 text-xs text-slate-500">Primary</span>
                                    )}
                                </td>
                                <td className="px-4 py-3">
                                    {contact.client ? (
                                        <Link
                                            href={`/clients/${contact.client.id}`}
                                            className="hover:underline"
                                        >
                                            {contact.client.company_name}
                                        </Link>
                                    ) : (
                                        '—'
                                    )}
                                </td>
                                <td className="px-4 py-3">{contact.email ?? '—'}</td>
                                <td className="px-4 py-3">{contact.phone ?? '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
