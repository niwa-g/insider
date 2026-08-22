import { Link, router } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Contact = {
    id: string;
    name: string;
    position: string | null;
    email: string | null;
    phone: string | null;
    is_primary: boolean;
    is_billing: boolean;
    is_technical: boolean;
    status: string;
};

type Client = {
    id: string;
    company_name: string;
    trading_name: string | null;
    client_code: string;
    client_type: string | null;
    industry: string | null;
    website: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    country: string | null;
    status: string;
    notes: string | null;
    account_manager?: { id: number; name: string; email: string } | null;
    groups?: Array<{ id: string; name: string; color: string | null }>;
    contacts: Contact[];
};

type Related = {
    projects: Array<{ id: string; name: string; code: string | null; status: string; health_status: string }>;
    services: Array<{
        id: string;
        name: string;
        status: string;
        billing_cycle: string | null;
        selling_price: string;
        service_type?: { id: string; name: string };
    }>;
    domains: Array<{ id: string; name: string; status: string; expires_at: string | null }>;
    hostingAccounts: Array<{ id: string; name: string; plan: string | null; status: string; primary_domain: string | null }>;
    applications: Array<{ id: string; name: string; status: string; stack: string | null }>;
    servers: Array<{ id: string; name: string; hostname: string | null; status: string; role: string | null }>;
    cloudResources: Array<{ id: string; name: string; resource_type: string; status: string; region: string | null }>;
    tickets: Array<{ id: string; number: string; subject: string; status: string; priority: string }>;
    documents: Array<{ id: string; title: string; status: string; original_name: string | null; created_at: string }>;
};

type Props = {
    client: Client;
    activities: Array<{ id: number; description: string; created_at: string }>;
    related: Related;
    summary: Record<string, number>;
    can: { update: boolean; delete: boolean; createContact: boolean };
};

const tabs = [
    'Overview',
    'Contacts',
    'Projects',
    'Services',
    'Domains',
    'Hosting',
    'Applications',
    'Infrastructure',
    'Tickets',
    'Documents',
    'Activity',
] as const;

function EmptyState({ message, href, linkLabel }: { message: string; href?: string; linkLabel?: string }) {
    return (
        <div className="py-8 text-center text-sm text-slate-500">
            {message}
            {href && linkLabel && (
                <>
                    {' '}
                    <Link href={href} className="text-blue-600 hover:underline">
                        {linkLabel}
                    </Link>
                </>
            )}
        </div>
    );
}

export default function ClientShow({ client, activities, related, summary, can }: Props) {
    const [tab, setTab] = useState<(typeof tabs)[number]>('Overview');

    function destroyClient() {
        if (confirm(`Delete ${client.company_name}?`)) {
            router.delete(`/clients/${client.id}`);
        }
    }

    return (
        <AppLayout title={client.company_name}>
            <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div className="flex items-center gap-3">
                        <h2 className="text-xl font-semibold">{client.company_name}</h2>
                        <StatusBadge status={client.status} />
                    </div>
                    <p className="mt-1 text-sm text-slate-500">
                        {client.client_code}
                        {client.industry ? ` · ${client.industry}` : ''}
                        {client.country ? ` · ${client.country}` : ''}
                    </p>
                </div>
                <div className="flex gap-2">
                    {can.update && (
                        <Link
                            href={`/clients/${client.id}/edit`}
                            className="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700"
                        >
                            Edit
                        </Link>
                    )}
                    {can.delete && (
                        <button
                            type="button"
                            onClick={destroyClient}
                            className="rounded-lg border border-rose-300 px-3 py-2 text-sm text-rose-700 dark:border-rose-800"
                        >
                            Delete
                        </button>
                    )}
                </div>
            </div>

            <div className="mb-4 flex gap-1 overflow-x-auto border-b border-slate-200 dark:border-slate-800">
                {tabs.map((name) => (
                    <button
                        key={name}
                        type="button"
                        onClick={() => setTab(name)}
                        className={`whitespace-nowrap px-3 py-2 text-sm ${
                            tab === name
                                ? 'border-b-2 border-blue-600 font-medium text-blue-600'
                                : 'text-slate-500'
                        }`}
                    >
                        {name}
                    </button>
                ))}
            </div>

            {tab === 'Overview' && (
                <div className="grid gap-4 lg:grid-cols-3">
                    <section className="space-y-3 rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                        <h3 className="font-semibold">Profile</h3>
                        <dl className="grid gap-3 sm:grid-cols-2 text-sm">
                            <Item label="Trading name" value={client.trading_name} />
                            <Item label="Type" value={client.client_type} />
                            <Item label="Email" value={client.email} />
                            <Item label="Phone" value={client.phone} />
                            <Item label="Website" value={client.website} />
                            <Item label="Account manager" value={client.account_manager?.name} />
                            <Item label="Address" value={client.address} />
                            <Item label="Notes" value={client.notes} />
                        </dl>
                        {client.groups && client.groups.length > 0 && (
                            <div className="flex flex-wrap gap-2 pt-2">
                                {client.groups.map((g) => (
                                    <span
                                        key={g.id}
                                        className="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs dark:bg-slate-800"
                                    >
                                        {g.name}
                                    </span>
                                ))}
                            </div>
                        )}
                    </section>
                    <section className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 className="font-semibold">Linked summary</h3>
                        <ul className="mt-3 space-y-2 text-sm">
                            {Object.entries(summary).map(([key, value]) => (
                                <li key={key} className="flex justify-between">
                                    <span className="capitalize text-slate-500">{key.replace(/([A-Z])/g, ' $1')}</span>
                                    <span className="font-medium">{value}</span>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>
            )}

            {tab === 'Contacts' && (
                <section className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div className="mb-3 flex items-center justify-between">
                        <h3 className="font-semibold">Contacts</h3>
                        {can.createContact && (
                            <Link
                                href={`/contacts/create?client_id=${client.id}`}
                                className="text-sm text-blue-600 hover:underline"
                            >
                                Add contact
                            </Link>
                        )}
                    </div>
                    {client.contacts.length === 0 ? (
                        <p className="text-sm text-slate-500">No contacts yet.</p>
                    ) : (
                        <ul className="divide-y divide-slate-100 dark:divide-slate-800">
                            {client.contacts.map((contact) => (
                                <li
                                    key={contact.id}
                                    className="flex flex-wrap items-center justify-between gap-2 py-3 text-sm"
                                >
                                    <div>
                                        <div className="font-medium">
                                            {contact.name}
                                            {contact.is_primary && (
                                                <span className="ml-2 text-xs text-blue-600">Primary</span>
                                            )}
                                        </div>
                                        <div className="text-slate-500">
                                            {[contact.position, contact.email, contact.phone]
                                                .filter(Boolean)
                                                .join(' · ')}
                                        </div>
                                    </div>
                                    <Link
                                        href={`/contacts/${contact.id}/edit`}
                                        className="text-slate-500 hover:text-blue-600"
                                    >
                                        Edit
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            )}

            {tab === 'Projects' && (
                <ResourceList
                    title="Projects"
                    rows={related.projects}
                    empty="No projects linked to this client."
                    createHref={`/projects/create?client_id=${client.id}`}
                    renderRow={(p) => (
                        <Link href={`/projects/${p.id}`} className="block py-3 hover:text-blue-600">
                            <div className="font-medium">{p.name}</div>
                            <div className="text-xs text-slate-500">
                                {[p.code, p.status, p.health_status].filter(Boolean).join(' · ')}
                            </div>
                        </Link>
                    )}
                />
            )}

            {tab === 'Services' && (
                <ResourceList
                    title="Services"
                    rows={related.services}
                    empty="No client services yet."
                    createHref={`/client-services/create?client_id=${client.id}`}
                    renderRow={(s) => (
                        <Link href={`/client-services/${s.id}/edit`} className="block py-3 hover:text-blue-600">
                            <div className="font-medium">{s.name}</div>
                            <div className="text-xs text-slate-500">
                                {[s.service_type?.name, s.status, s.billing_cycle, s.selling_price && `$${s.selling_price}`]
                                    .filter(Boolean)
                                    .join(' · ')}
                            </div>
                        </Link>
                    )}
                />
            )}

            {tab === 'Domains' && (
                <ResourceList
                    title="Domains"
                    rows={related.domains}
                    empty="No domains linked."
                    createHref={`/domains/create?client_id=${client.id}`}
                    renderRow={(d) => (
                        <Link href={`/domains/${d.id}`} className="block py-3 hover:text-blue-600">
                            <div className="font-medium">{d.name}</div>
                            <div className="text-xs text-slate-500">
                                {[d.status, d.expires_at].filter(Boolean).join(' · ')}
                            </div>
                        </Link>
                    )}
                />
            )}

            {tab === 'Hosting' && (
                <ResourceList
                    title="Hosting accounts"
                    rows={related.hostingAccounts}
                    empty="No hosting accounts linked."
                    createHref={`/hosting-accounts/create?client_id=${client.id}`}
                    renderRow={(h) => (
                        <Link href={`/hosting-accounts/${h.id}/edit`} className="block py-3 hover:text-blue-600">
                            <div className="font-medium">{h.name}</div>
                            <div className="text-xs text-slate-500">
                                {[h.plan, h.primary_domain, h.status].filter(Boolean).join(' · ')}
                            </div>
                        </Link>
                    )}
                />
            )}

            {tab === 'Applications' && (
                <ResourceList
                    title="Applications"
                    rows={related.applications}
                    empty="No applications linked."
                    createHref={`/applications/create?client_id=${client.id}`}
                    renderRow={(a) => (
                        <Link href={`/applications/${a.id}`} className="block py-3 hover:text-blue-600">
                            <div className="font-medium">{a.name}</div>
                            <div className="text-xs text-slate-500">
                                {[a.stack, a.status].filter(Boolean).join(' · ')}
                            </div>
                        </Link>
                    )}
                />
            )}

            {tab === 'Infrastructure' && (
                <section className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="font-semibold">Infrastructure</h3>
                    {related.servers.length === 0 && related.cloudResources.length === 0 ? (
                        <EmptyState
                            message="No servers or cloud resources linked."
                            href={`/servers/create?client_id=${client.id}`}
                            linkLabel="Add server"
                        />
                    ) : (
                        <ul className="divide-y divide-slate-100 dark:divide-slate-800">
                            {related.servers.map((s) => (
                                <li key={s.id}>
                                    <Link href={`/servers/${s.id}/edit`} className="block py-3 hover:text-blue-600">
                                        <div className="font-medium">{s.name} <span className="text-xs text-slate-500">(server)</span></div>
                                        <div className="text-xs text-slate-500">
                                            {[s.hostname, s.role, s.status].filter(Boolean).join(' · ')}
                                        </div>
                                    </Link>
                                </li>
                            ))}
                            {related.cloudResources.map((c) => (
                                <li key={c.id}>
                                    <Link href={`/cloud-resources/${c.id}/edit`} className="block py-3 hover:text-blue-600">
                                        <div className="font-medium">{c.name} <span className="text-xs text-slate-500">(cloud)</span></div>
                                        <div className="text-xs text-slate-500">
                                            {[c.resource_type, c.region, c.status].filter(Boolean).join(' · ')}
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            )}

            {tab === 'Tickets' && (
                <ResourceList
                    title="Tickets"
                    rows={related.tickets}
                    empty="No support tickets."
                    createHref={`/tickets/create?client_id=${client.id}`}
                    renderRow={(t) => (
                        <Link href={`/tickets/${t.id}`} className="block py-3 hover:text-blue-600">
                            <div className="font-medium">{t.number}: {t.subject}</div>
                            <div className="text-xs text-slate-500">
                                {[t.status, t.priority].filter(Boolean).join(' · ')}
                            </div>
                        </Link>
                    )}
                />
            )}

            {tab === 'Documents' && (
                <ResourceList
                    title="Documents"
                    rows={related.documents}
                    empty="No documents uploaded."
                    createHref={`/documents/create?client_id=${client.id}`}
                    renderRow={(d) => (
                        <Link href={`/documents/${d.id}/edit`} className="block py-3 hover:text-blue-600">
                            <div className="font-medium">{d.title}</div>
                            <div className="text-xs text-slate-500">
                                {[d.original_name, d.status, d.created_at].filter(Boolean).join(' · ')}
                            </div>
                        </Link>
                    )}
                />
            )}

            {tab === 'Activity' && (
                <section className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="font-semibold">Activity</h3>
                    {activities.length === 0 ? (
                        <p className="mt-2 text-sm text-slate-500">No activity yet.</p>
                    ) : (
                        <ul className="mt-3 space-y-2 text-sm">
                            {activities.map((item) => (
                                <li key={item.id} className="flex justify-between gap-4">
                                    <span>{item.description}</span>
                                    <span className="shrink-0 text-slate-500">{item.created_at}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            )}
        </AppLayout>
    );
}

function ResourceList<T extends { id: string }>({
    title,
    rows,
    empty,
    createHref,
    renderRow,
}: {
    title: string;
    rows: T[];
    empty: string;
    createHref: string;
    renderRow: (row: T) => ReactNode;
}) {
    return (
        <section className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <div className="mb-3 flex items-center justify-between">
                <h3 className="font-semibold">{title}</h3>
                <Link href={createHref} className="text-sm text-blue-600 hover:underline">
                    Add
                </Link>
            </div>
            {rows.length === 0 ? (
                <EmptyState message={empty} href={createHref} linkLabel="Create one" />
            ) : (
                <ul className="divide-y divide-slate-100 dark:divide-slate-800">{rows.map((row) => (
                    <li key={row.id}>{renderRow(row)}</li>
                ))}</ul>
            )}
        </section>
    );
}

function Item({ label, value }: { label: string; value?: string | null }) {
    return (
        <div>
            <dt className="text-slate-500">{label}</dt>
            <dd className="mt-0.5 font-medium">{value || '—'}</dd>
        </div>
    );
}
