import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    categories: {
        data: Array<{
            id: string;
            name: string;
            code: string | null;
            status: string;
            service_types_count: number;
        }>;
    };
    can: { create: boolean };
};

export default function ServiceCategoriesIndex({ categories, can }: Props) {
    return (
        <AppLayout title="Service categories">
            <div className="mb-4 flex justify-end">
                {can.create && (
                    <Link
                        href="/service-categories/create"
                        className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        New category
                    </Link>
                )}
            </div>
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Code</th>
                            <th className="px-4 py-3 font-medium">Types</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        {categories.data.map((category) => (
                            <tr key={category.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3 font-medium">{category.name}</td>
                                <td className="px-4 py-3 font-mono text-xs">{category.code ?? '—'}</td>
                                <td className="px-4 py-3">{category.service_types_count}</td>
                                <td className="px-4 py-3 capitalize">{category.status}</td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={`/service-categories/${category.id}/edit`}
                                        className="text-blue-600 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        className="ml-3 text-rose-600"
                                        onClick={() => {
                                            if (confirm(`Delete ${category.name}?`)) {
                                                router.delete(`/service-categories/${category.id}`);
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
