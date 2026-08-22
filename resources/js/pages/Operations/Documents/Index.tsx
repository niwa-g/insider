import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Props = {
    documents: { data: Array<Record<string, unknown> & { id: string; title?: string; status?: string; client?: { company_name: string }; original_name?: string }> };
    filters: Record<string, string>;
    can: { create: boolean };
};

export default function Index({ documents, filters, can }: Props) {
    const [localFilters, setLocalFilters] = useState(filters ?? {});

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/documents', localFilters, { preserveState: true });
    }

    return (
        <AppLayout title="Documents">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex gap-2">
                    <input value={localFilters.search ?? ''} onChange={(e) => setLocalFilters({ ...localFilters, search: e.target.value })} placeholder="Search…" className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                {can.create && <Link href="/documents/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">Upload document</Link>}
            </div>
            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-3 text-left">Title</th><th className="px-4 py-3 text-left">Client</th><th className="px-4 py-3 text-left">File</th><th className="px-4 py-3 text-left">Status</th><th className="px-4 py-3 text-left"></th></tr></thead>
                    <tbody>
                        {documents.data.map((d) => (
                            <tr key={d.id} className="border-t dark:border-slate-800">
                                <td className="px-4 py-3">{String(d.title)}</td>
                                <td className="px-4 py-3">{d.client?.company_name ?? '—'}</td>
                                <td className="px-4 py-3">{String(d.original_name ?? '—')}</td>
                                <td className="px-4 py-3">{d.status ? <StatusBadge status={String(d.status)} /> : '—'}</td>
                                <td className="px-4 py-3"><Link href={`/documents/${d.id}/edit`} className="text-blue-600">Edit</Link></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
