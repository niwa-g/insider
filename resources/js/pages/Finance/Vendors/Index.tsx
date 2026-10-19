import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    vendors: {
        data: Array<{
            id: string;
            name: string;
            code: string | null;
            email: string | null;
            status: string;
            bills_count: number;
            client_services_count: number;
        }>;
    };
    can: { create: boolean; update: boolean };
};

export default function VendorsIndex({ vendors, can }: Props) {
    return (
        <AppLayout title="Vendors">
            <div className="mb-4 flex items-center justify-between gap-3">
                <p className="text-sm text-slate-600 dark:text-slate-400">
                    Suppliers you pay for cost of delivery (AWS, licenses, etc.).
                </p>
                {can.create && (
                    <Link href="/vendors/create" className="shrink-0 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white">
                        New vendor
                    </Link>
                )}
            </div>
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Code</th>
                            <th className="px-4 py-3 font-medium">Services</th>
                            <th className="px-4 py-3 font-medium">Bills</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        {vendors.data.map((vendor) => (
                            <tr key={vendor.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3 font-medium">{vendor.name}</td>
                                <td className="px-4 py-3 font-mono text-xs">{vendor.code ?? '—'}</td>
                                <td className="px-4 py-3">{vendor.client_services_count}</td>
                                <td className="px-4 py-3">
                                    <Link href={`/vendor-bills?vendor_id=${vendor.id}`} className="text-blue-600 hover:underline">
                                        {vendor.bills_count}
                                    </Link>
                                </td>
                                <td className="px-4 py-3 capitalize">{vendor.status}</td>
                                <td className="px-4 py-3 text-right">
                                    {can.update && (
                                        <Link href={`/vendors/${vendor.id}/edit`} className="text-blue-600 hover:underline">
                                            Edit
                                        </Link>
                                    )}
                                    {can.update && (
                                        <button
                                            type="button"
                                            className="ml-3 text-rose-600"
                                            onClick={() => {
                                                if (confirm(`Delete ${vendor.name}?`)) {
                                                    router.delete(`/vendors/${vendor.id}`);
                                                }
                                            }}
                                        >
                                            Delete
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
