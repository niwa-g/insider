import { Link, router } from '@inertiajs/react';
import { Building2, Plus } from 'lucide-react';
import { FormEvent, useState } from 'react';
import StatusBadge from '@/components/StatusBadge';
import { Button } from '@/components/ui/button';
import { DataTable, PaginationLinks } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { EmptyState, PageHeader } from '@/components/ui/page';
import AppLayout from '@/layouts/AppLayout';

type ClientRow = {
    id: string;
    company_name: string;
    client_code: string;
    email: string | null;
    status: string;
    contacts_count: number;
    account_manager?: { id: number; name: string } | null;
};

type Props = {
    clients: {
        data: ClientRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search: string; status: string };
    statuses: Record<string, string>;
    can: { create: boolean };
};

export default function ClientsIndex({ clients, filters, statuses, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    function applyFilters(e: FormEvent) {
        e.preventDefault();
        router.get('/clients', { search, status }, { preserveState: true });
    }

    return (
        <AppLayout title="Clients">
            <PageHeader
                title="Clients"
                description="Manage client accounts, contacts, and ownership."
                breadcrumbs={[{ label: 'Workspace' }, { label: 'Clients' }]}
                actions={
                    can.create ? (
                        <Button asChild>
                            <Link href="/clients/create">
                                <Plus className="h-4 w-4" />
                                New client
                            </Link>
                        </Button>
                    ) : null
                }
            />

            <DataTable
                toolbar={
                    <form onSubmit={applyFilters} className="flex flex-wrap gap-2">
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search clients…"
                            className="w-full sm:w-64"
                        />
                        <select
                            value={status}
                            onChange={(e) => setStatus(e.target.value)}
                            className="h-9 rounded-lg border border-input bg-card px-3 text-sm"
                        >
                            <option value="">All statuses</option>
                            {Object.entries(statuses).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                        <Button type="submit" variant="secondary">
                            Filter
                        </Button>
                    </form>
                }
                columns={[
                    {
                        key: 'company',
                        header: 'Company',
                        cell: (client) => (
                            <div>
                                <Link href={`/clients/${client.id}`} className="font-medium text-primary hover:underline">
                                    {client.company_name}
                                </Link>
                                <div className="text-xs text-muted-foreground">{client.email}</div>
                            </div>
                        ),
                    },
                    {
                        key: 'code',
                        header: 'Code',
                        cell: (client) => <span className="font-mono text-xs">{client.client_code}</span>,
                    },
                    {
                        key: 'status',
                        header: 'Status',
                        cell: (client) => <StatusBadge status={client.status} />,
                    },
                    {
                        key: 'contacts',
                        header: 'Contacts',
                        hideOnMobile: true,
                        cell: (client) => client.contacts_count,
                    },
                    {
                        key: 'manager',
                        header: 'Account manager',
                        hideOnMobile: true,
                        cell: (client) => client.account_manager?.name ?? '—',
                    },
                ]}
                rows={clients.data}
                rowKey={(client) => client.id}
                empty={
                    <EmptyState
                        icon={Building2}
                        title="No clients found"
                        description="Try adjusting filters or create your first client."
                        action={
                            can.create ? (
                                <Button asChild>
                                    <Link href="/clients/create">New client</Link>
                                </Button>
                            ) : undefined
                        }
                    />
                }
                mobileCard={(client) => (
                    <div className="space-y-2">
                        <div className="flex items-start justify-between gap-3">
                            <Link href={`/clients/${client.id}`} className="font-medium text-primary">
                                {client.company_name}
                            </Link>
                            <StatusBadge status={client.status} />
                        </div>
                        <div className="text-xs text-muted-foreground">{client.client_code}</div>
                        <div className="text-sm text-muted-foreground">{client.account_manager?.name ?? 'No manager'}</div>
                    </div>
                )}
            />

            <PaginationLinks links={clients.links} />
        </AppLayout>
    );
}
