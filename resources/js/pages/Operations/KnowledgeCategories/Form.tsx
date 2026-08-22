import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    category: Record<string, unknown> | null;
    parents: Array<{ id: string; name: string }>;
    statuses: Record<string, string>;
};

export default function CategoryForm({ category, parents, statuses }: Props) {
    const isEdit = Boolean(category?.id);
    const { data, setData, post, put, processing } = useForm({
        name: (category?.name as string) ?? '',
        slug: (category?.slug as string) ?? '',
        description: (category?.description as string) ?? '',
        parent_id: (category?.parent_id as string) ?? '',
        sort_order: (category?.sort_order as number) ?? 0,
        status: (category?.status as string) ?? 'active',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/knowledge-categories/${category?.id}`);
        else post('/knowledge-categories');
    }

    return (
        <AppLayout title={isEdit ? 'Edit category' : 'New category'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <label className="block text-sm font-medium">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="block text-sm font-medium">Slug<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.slug} onChange={(e) => setData('slug', e.target.value)} required /></label>
                <label className="block text-sm font-medium">Parent<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.parent_id} onChange={(e) => setData('parent_id', e.target.value)}><option value="">None</option>{parents.map((p) => (<option key={p.id} value={p.id}>{p.name}</option>))}</select></label>
                <label className="block text-sm font-medium">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <div className="flex gap-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : 'Save'}</button>
                    <Link href="/knowledge-categories" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
