import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Props = {
    categories: { data: Array<Record<string, unknown> & { id: string; name?: string; slug?: string; status?: string; parent?: { name: string } | null }> };
    filters: Record<string, string>;
    can: { create: boolean };
};

export default function Index({ categories, filters, can }: Props) {
    const [localFilters, setLocalFilters] = useState(filters ?? {});

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/knowledge-categories', localFilters, { preserveState: true });
    }

    return (
        <AppLayout title="Knowledge Categories">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex gap-2">
                    <input value={localFilters.search ?? ''} onChange={(e) => setLocalFilters({ ...localFilters, search: e.target.value })} placeholder="Search…" className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                {can.create && <Link href="/knowledge-categories/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">New category</Link>}
            </div>
            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-3 text-left">Name</th><th className="px-4 py-3 text-left">Slug</th><th className="px-4 py-3 text-left">Parent</th><th className="px-4 py-3 text-left">Status</th><th className="px-4 py-3 text-left"></th></tr></thead>
                    <tbody>
                        {categories.data.map((c) => (
                            <tr key={c.id} className="border-t dark:border-slate-800">
                                <td className="px-4 py-3">{String(c.name)}</td>
                                <td className="px-4 py-3">{String(c.slug)}</td>
                                <td className="px-4 py-3">{c.parent?.name ?? '—'}</td>
                                <td className="px-4 py-3">{c.status ? <StatusBadge status={String(c.status)} /> : '—'}</td>
                                <td className="px-4 py-3"><Link href={`/knowledge-categories/${c.id}/edit`} className="text-blue-600">Edit</Link></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
