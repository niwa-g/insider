import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Props = {
    article: Record<string, unknown> & { id: string; title: string; body: string; summary?: string; status: string; is_internal?: boolean; category?: { name: string } | null; author?: { name: string } | null };
    can: { update: boolean; delete: boolean };
};

export default function ArticleShow({ article, can }: Props) {
    return (
        <AppLayout title={article.title}>
            <div className="mb-4 flex flex-wrap gap-2">
                <Link href="/knowledge-articles" className="text-sm text-slate-500">← Knowledge Base</Link>
                {can.update && <Link href={`/knowledge-articles/${article.id}/edit`} className="rounded-lg border px-3 py-1 text-sm">Edit</Link>}
                {can.delete && <button type="button" onClick={() => confirm('Delete article?') && router.delete(`/knowledge-articles/${article.id}`)} className="rounded-lg border border-red-200 px-3 py-1 text-sm text-red-600">Delete</button>}
            </div>
            <article className="rounded-xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div className="mb-4 flex flex-wrap items-center gap-3">
                    <h1 className="text-2xl font-semibold">{article.title}</h1>
                    <StatusBadge status={article.status} />
                    {article.is_internal && <span className="text-xs text-amber-600">Internal</span>}
                </div>
                <p className="mb-4 text-sm text-slate-500">{article.category?.name ?? 'Uncategorized'}{article.author ? ` · ${article.author.name}` : ''}</p>
                {article.summary && <p className="mb-4 text-slate-600">{article.summary}</p>}
                <div className="whitespace-pre-wrap text-sm leading-relaxed">{article.body}</div>
            </article>
        </AppLayout>
    );
}
