import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Props = {
    articles: { data: Array<Record<string, unknown> & { id: string; title?: string; status?: string; category?: { name: string } | null }> };
    filters: Record<string, string>;
    can: { create: boolean };
};

export default function Index({ articles, filters, can }: Props) {
    const [localFilters, setLocalFilters] = useState(filters ?? {});

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/knowledge-articles', localFilters, { preserveState: true });
    }

    return (
        <AppLayout title="Knowledge Base">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex gap-2">
                    <input value={localFilters.search ?? ''} onChange={(e) => setLocalFilters({ ...localFilters, search: e.target.value })} placeholder="Search…" className="rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
                </form>
                {can.create && <Link href="/knowledge-articles/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">New article</Link>}
            </div>
            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-3 text-left">Title</th><th className="px-4 py-3 text-left">Category</th><th className="px-4 py-3 text-left">Status</th></tr></thead>
                    <tbody>
                        {articles.data.map((a) => (
                            <tr key={a.id} className="border-t dark:border-slate-800">
                                <td className="px-4 py-3"><Link href={`/knowledge-articles/${a.id}`} className="font-medium text-blue-600 hover:underline">{String(a.title)}</Link></td>
                                <td className="px-4 py-3">{a.category?.name ?? '—'}</td>
                                <td className="px-4 py-3">{a.status ? <StatusBadge status={String(a.status)} /> : '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
