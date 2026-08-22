import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Row = Record<string, unknown> & { id: string; status?: string };

type Props = {
    environments: { data: Row[] };
    filters: Record<string, string>;
    can: { create: boolean };
    [key: string]: unknown;
};

export default function Index(props: Props) {
    const rows = props.environments.data;
    const filters = props.filters ?? {};
    const [localFilters] = useState(filters);

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/environments', localFilters, { preserveState: true });
    }

    return (
        <AppLayout title="Environments">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                {props.can.create && (
                    <Link href="/environments/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white">New environment</Link>
                )}
            </div>
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr><th className="px-4 py-3 font-medium">Name</th><th className="px-4 py-3 font-medium">Status</th></tr>
                    </thead>
                    <tbody>
                        {rows.map((row) => (
                            <tr key={row.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3"><Link href={`/environments/${row.id}/edit`} className="font-medium text-blue-600 hover:underline">{String(row.name ?? row.common_name ?? row.id)}</Link></td>
                                <td className="px-4 py-3">{row.status ? <StatusBadge status={String(row.status)} /> : '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
