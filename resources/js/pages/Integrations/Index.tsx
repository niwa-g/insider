import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../layouts/AppLayout';
import StatusBadge from '../../components/StatusBadge';

type Integration = {
    id: string;
    name: string;
    provider: string;
    category: string;
    status: string;
    last_synced_at?: string | null;
    is_enabled: boolean;
};

type Props = {
    integrations: Integration[];
    filters: Record<string, string>;
    categories: Record<string, string>;
    can: { create: boolean };
};

export default function IntegrationsIndex({ integrations, filters, categories, can }: Props) {
    const [localFilters, setLocalFilters] = useState(filters);

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/integrations', localFilters, { preserveState: true });
    }

    const grouped = integrations.reduce<Record<string, Integration[]>>((acc, item) => {
        const key = item.category ?? 'other';
        acc[key] = acc[key] ?? [];
        acc[key].push(item);
        return acc;
    }, {});

    return (
        <AppLayout title="Integrations">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <select
                        className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        value={localFilters.category ?? ''}
                        onChange={(e) => setLocalFilters({ ...localFilters, category: e.target.value })}
                    >
                        <option value="">All categories</option>
                        {Object.entries(categories).map(([value, label]) => (
                            <option key={value} value={value}>{label}</option>
                        ))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                <div className="flex gap-2">
                    <Link href="/integration-sync-logs" className="rounded-lg border px-3 py-2 text-sm">Sync logs</Link>
                    {can.create && (
                        <Link href="/integrations/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white">New integration</Link>
                    )}
                </div>
            </div>

            <div className="space-y-6">
                {Object.entries(grouped).map(([category, items]) => (
                    <section key={category}>
                        <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{categories[category] ?? category}</h2>
                        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            {items.map((integration) => (
                                <Link
                                    key={integration.id}
                                    href={`/integrations/${integration.id}`}
                                    className="rounded-xl border bg-white p-4 transition hover:border-blue-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700"
                                >
                                    <div className="mb-2 flex items-center justify-between gap-2">
                                        <h3 className="font-semibold">{integration.name}</h3>
                                        <StatusBadge status={integration.status} />
                                    </div>
                                    <p className="text-sm text-slate-500 capitalize">{integration.provider.replace('_', ' ')}</p>
                                    <p className="mt-2 text-xs text-slate-400">
                                        {integration.last_synced_at
                                            ? `Last sync: ${new Date(integration.last_synced_at).toLocaleString()}`
                                            : 'Never synced'}
                                    </p>
                                </Link>
                            ))}
                        </div>
                    </section>
                ))}
                {integrations.length === 0 && (
                    <p className="text-sm text-slate-500">No integrations yet.</p>
                )}
            </div>
        </AppLayout>
    );
}
