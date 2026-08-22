import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type TemplateRow = {
    id: string;
    name: string;
    code: string | null;
    default_status: string;
    is_active: boolean;
    items_count?: number;
};

type Props = {
    templates: {
        data: TemplateRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search: string };
    can: { create: boolean };
};

export default function ProjectTemplatesIndex({ templates, filters, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/project-templates', { search }, { preserveState: true });
    }

    return (
        <AppLayout title="Project templates">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search templates…"
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    />
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                        Filter
                    </button>
                </form>
                {can.create && (
                    <Link
                        href="/project-templates/create"
                        className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        New template
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Template</th>
                            <th className="px-4 py-3 font-medium">Items</th>
                            <th className="px-4 py-3 font-medium">Default status</th>
                            <th className="px-4 py-3 font-medium">Active</th>
                            <th className="px-4 py-3 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        {templates.data.map((template) => (
                            <tr key={template.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3">
                                    <div className="font-medium">{template.name}</div>
                                    <div className="font-mono text-xs text-slate-500">{template.code ?? '—'}</div>
                                </td>
                                <td className="px-4 py-3">{template.items_count ?? 0}</td>
                                <td className="px-4 py-3">
                                    <StatusBadge status={template.default_status} />
                                </td>
                                <td className="px-4 py-3">{template.is_active ? 'Yes' : 'No'}</td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={`/project-templates/${template.id}/edit`}
                                        className="text-blue-600 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        className="ml-3 text-rose-600"
                                        onClick={() => {
                                            if (confirm(`Delete ${template.name}?`)) {
                                                router.delete(`/project-templates/${template.id}`);
                                            }
                                        }}
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
