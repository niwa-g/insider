import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type TemplateItem = {
    title: string;
    description: string;
    status: string;
    priority: string;
    estimate_hours: number | string;
    sort_order: number | string;
};

type Props = {
    template: (Record<string, unknown> & { items?: TemplateItem[] }) | null;
    statuses: Record<string, string>;
    taskStatuses: Record<string, string>;
    priorities: Record<string, string>;
};

const emptyItem = (): TemplateItem => ({
    title: '',
    description: '',
    status: 'todo',
    priority: 'medium',
    estimate_hours: '',
    sort_order: 0,
});

export default function ProjectTemplateForm({ template, statuses, taskStatuses, priorities }: Props) {
    const isEdit = Boolean(template?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        name: (template?.name as string) ?? '',
        code: (template?.code as string) ?? '',
        description: (template?.description as string) ?? '',
        default_status: (template?.default_status as string) ?? 'planned',
        is_active: (template?.is_active as boolean) ?? true,
        items: (template?.items as TemplateItem[] | undefined)?.map((item) => ({
            title: item.title ?? '',
            description: item.description ?? '',
            status: item.status ?? 'todo',
            priority: item.priority ?? 'medium',
            estimate_hours: item.estimate_hours ?? '',
            sort_order: item.sort_order ?? 0,
        })) ?? [emptyItem()],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/project-templates/${template?.id}`);
        } else {
            post('/project-templates');
        }
    }

    function updateItem(index: number, key: keyof TemplateItem, value: string | number) {
        setData(
            'items',
            data.items.map((item, i) => (i === index ? { ...item, [key]: value } : item)),
        );
    }

    function addItem() {
        setData('items', [...data.items, emptyItem()]);
    }

    function removeItem(index: number) {
        setData(
            'items',
            data.items.filter((_, i) => i !== index),
        );
    }

    return (
        <AppLayout title={isEdit ? 'Edit template' : 'New template'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="block text-sm font-medium md:col-span-2">
                    Name
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && <span className="text-sm text-red-600">{errors.name}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Code
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.code}
                        onChange={(e) => setData('code', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Default status
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.default_status}
                        onChange={(e) => setData('default_status', e.target.value)}
                    >
                        {Object.entries(statuses).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="flex items-center gap-2 text-sm font-medium md:col-span-2">
                    <input
                        type="checkbox"
                        checked={data.is_active}
                        onChange={(e) => setData('is_active', e.target.checked)}
                    />
                    Active
                </label>
                <label className="block text-sm font-medium md:col-span-2">
                    Description
                    <textarea
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        rows={3}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </label>

                <div className="md:col-span-2">
                    <div className="mb-2 flex items-center justify-between">
                        <div className="text-sm font-medium">Template items</div>
                        <button
                            type="button"
                            onClick={addItem}
                            className="rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700"
                        >
                            Add item
                        </button>
                    </div>
                    <div className="space-y-3">
                        {data.items.map((item, index) => (
                            <div
                                key={index}
                                className="grid gap-3 rounded-lg border border-slate-200 p-3 md:grid-cols-2 dark:border-slate-800"
                            >
                                <label className="block text-sm font-medium md:col-span-2">
                                    Title
                                    <input
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                                        value={item.title}
                                        onChange={(e) => updateItem(index, 'title', e.target.value)}
                                        required
                                    />
                                </label>
                                <label className="block text-sm font-medium">
                                    Status
                                    <select
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                                        value={item.status}
                                        onChange={(e) => updateItem(index, 'status', e.target.value)}
                                    >
                                        {Object.entries(taskStatuses).map(([value, label]) => (
                                            <option key={value} value={value}>
                                                {label}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className="block text-sm font-medium">
                                    Priority
                                    <select
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                                        value={item.priority}
                                        onChange={(e) => updateItem(index, 'priority', e.target.value)}
                                    >
                                        {Object.entries(priorities).map(([value, label]) => (
                                            <option key={value} value={value}>
                                                {label}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className="block text-sm font-medium">
                                    Estimate (hours)
                                    <input
                                        type="number"
                                        min={0}
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                                        value={item.estimate_hours}
                                        onChange={(e) => updateItem(index, 'estimate_hours', e.target.value)}
                                    />
                                </label>
                                <label className="block text-sm font-medium">
                                    Sort order
                                    <input
                                        type="number"
                                        min={0}
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                                        value={item.sort_order}
                                        onChange={(e) => updateItem(index, 'sort_order', e.target.value)}
                                    />
                                </label>
                                <label className="block text-sm font-medium md:col-span-2">
                                    Description
                                    <textarea
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                                        rows={2}
                                        value={item.description}
                                        onChange={(e) => updateItem(index, 'description', e.target.value)}
                                    />
                                </label>
                                {data.items.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={() => removeItem(index)}
                                        className="text-left text-sm text-rose-600 md:col-span-2"
                                    >
                                        Remove item
                                    </button>
                                )}
                            </div>
                        ))}
                    </div>
                </div>

                <div className="flex gap-2 md:col-span-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}
                    </button>
                    <Link href="/project-templates" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
