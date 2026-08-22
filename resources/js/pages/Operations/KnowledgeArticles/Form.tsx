import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    article: Record<string, unknown> | null;
    categories: Array<{ id: string; name: string }>;
    statuses: Record<string, string>;
    authors: Array<{ id: number; name: string }>;
};

export default function ArticleForm({ article, categories, statuses }: Props) {
    const isEdit = Boolean(article?.id);
    const { data, setData, post, put, processing } = useForm({
        knowledge_category_id: (article?.knowledge_category_id as string) ?? '',
        title: (article?.title as string) ?? '',
        slug: (article?.slug as string) ?? '',
        summary: (article?.summary as string) ?? '',
        body: (article?.body as string) ?? '',
        status: (article?.status as string) ?? 'draft',
        is_internal: (article?.is_internal as boolean) ?? true,
        author_id: (article?.author_id as number | string | null) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/knowledge-articles/${article?.id}`);
        else post('/knowledge-articles');
    }

    return (
        <AppLayout title={isEdit ? 'Edit article' : 'New article'}>
            <form onSubmit={submit} className="mx-auto max-w-3xl space-y-4">
                <label className="block text-sm font-medium">Title<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.title} onChange={(e) => setData('title', e.target.value)} required /></label>
                <label className="block text-sm font-medium">Slug<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.slug} onChange={(e) => setData('slug', e.target.value)} required /></label>
                <label className="block text-sm font-medium">Category<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.knowledge_category_id} onChange={(e) => setData('knowledge_category_id', e.target.value)}><option value="">None</option>{categories.map((c) => (<option key={c.id} value={c.id}>{c.name}</option>))}</select></label>
                <label className="block text-sm font-medium">Summary<textarea className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" rows={2} value={data.summary} onChange={(e) => setData('summary', e.target.value)} /></label>
                <label className="block text-sm font-medium">Body<textarea className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" rows={10} value={data.body} onChange={(e) => setData('body', e.target.value)} required /></label>
                <label className="block text-sm font-medium">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={data.is_internal} onChange={(e) => setData('is_internal', e.target.checked)} /> Internal only</label>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : 'Save'}</button>
                    <Link href="/knowledge-articles" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
