import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    category: Record<string, unknown> | null;
    statuses: Record<string, string>;
};

export default function ServiceCategoryForm({ category, statuses }: Props) {
    const isEdit = Boolean(category?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        name: (category?.name as string) ?? '',
        code: (category?.code as string) ?? '',
        description: (category?.description as string) ?? '',
        sort_order: (category?.sort_order as number) ?? 0,
        status: (category?.status as string) ?? 'active',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/service-categories/${category?.id}`);
        } else {
            post('/service-categories');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit category' : 'New category'}>
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4">
                <Field label="Name" error={errors.name}>
                    <input
                        className="field"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                </Field>
                <Field label="Code" error={errors.code}>
                    <input className="field" value={data.code} onChange={(e) => setData('code', e.target.value)} />
                </Field>
                <Field label="Description" error={errors.description}>
                    <textarea
                        className="field"
                        rows={3}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </Field>
                <Field label="Sort order" error={errors.sort_order}>
                    <input
                        type="number"
                        className="field"
                        value={data.sort_order}
                        onChange={(e) => setData('sort_order', Number(e.target.value))}
                    />
                </Field>
                <Field label="Status" error={errors.status}>
                    <select className="field" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        {Object.entries(statuses).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </Field>
                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}
                    </button>
                    <Link href="/service-categories" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
            <style>{`.field{margin-top:.25rem;width:100%;border-radius:.5rem;border:1px solid #cbd5e1;padding:.5rem .75rem;font-size:.875rem}.dark .field{border-color:#334155;background:#020617}`}</style>
        </AppLayout>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="block text-sm font-medium">
            {label}
            {children}
            {error && <span className="mt-1 block text-sm text-red-600">{error}</span>}
        </label>
    );
}
