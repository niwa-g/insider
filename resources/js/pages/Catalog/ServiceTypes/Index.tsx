import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    serviceTypes: {
        data: Array<{
            id: string;
            name: string;
            code: string;
            billing_type: string;
            billing_cycle: string | null;
            default_selling_price: string;
            status: string;
            category?: { id: string; name: string };
        }>;
    };
    filters: { search: string; category_id: string };
    categories: Array<{ id: string; name: string }>;
    can: { create: boolean };
};

export default function ServiceTypesIndex({ serviceTypes, filters, categories, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [categoryId, setCategoryId] = useState(filters.category_id ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/service-types', { search, category_id: categoryId }, { preserveState: true });
    }

    return (
        <AppLayout title="Service catalog">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search services…"
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    />
                    <select
                        value={categoryId}
                        onChange={(e) => setCategoryId(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All categories</option>
                        {categories.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                        Filter
                    </button>
                </form>
                {can.create && (
                    <Link
                        href="/service-types/create"
                        className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        New service type
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Service</th>
                            <th className="px-4 py-3 font-medium">Category</th>
                            <th className="px-4 py-3 font-medium">Billing</th>
                            <th className="px-4 py-3 font-medium">Price</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {serviceTypes.data.map((type) => (
                            <tr key={type.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3">
                                    <Link
                                        href={`/service-types/${type.id}/edit`}
                                        className="font-medium text-blue-600 hover:underline"
                                    >
                                        {type.name}
                                    </Link>
                                    <div className="font-mono text-xs text-slate-500">{type.code}</div>
                                </td>
                                <td className="px-4 py-3">{type.category?.name ?? '—'}</td>
                                <td className="px-4 py-3 capitalize">
                                    {type.billing_type.replace('_', ' ')}
                                    {type.billing_cycle ? ` / ${type.billing_cycle}` : ''}
                                </td>
                                <td className="px-4 py-3">${type.default_selling_price}</td>
                                <td className="px-4 py-3 capitalize">{type.status}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
