import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    reasons: {
        data: Array<{
            id: string;
            name: string;
            code: string;
            description: string | null;
            is_system: boolean;
            status: string;
            invoices_count: number;
        }>;
    };
    can: { create: boolean; update: boolean; delete: boolean };
};

export default function DiscountReasonsIndex({ reasons, can }: Props) {
    return (
        <AppLayout title="Discount reasons">
            <div className="mb-4 flex items-center justify-between gap-3">
                <p className="text-sm text-slate-600 dark:text-slate-400">
                    Standard reasons are included for every organization. Add custom types as needed.
                </p>
                {can.create && (
                    <Link
                        href="/discount-reasons/create"
                        className="shrink-0 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        New reason
                    </Link>
                )}
            </div>
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Code</th>
                            <th className="px-4 py-3 font-medium">Type</th>
                            <th className="px-4 py-3 font-medium">Invoices</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        {reasons.data.map((reason) => (
                            <tr key={reason.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3 font-medium">{reason.name}</td>
                                <td className="px-4 py-3 font-mono text-xs">{reason.code}</td>
                                <td className="px-4 py-3">{reason.is_system ? 'Standard' : 'Custom'}</td>
                                <td className="px-4 py-3">{reason.invoices_count}</td>
                                <td className="px-4 py-3 capitalize">{reason.status}</td>
                                <td className="px-4 py-3 text-right">
                                    {can.update && (
                                        <Link
                                            href={`/discount-reasons/${reason.id}/edit`}
                                            className="text-blue-600 hover:underline"
                                        >
                                            Edit
                                        </Link>
                                    )}
                                    {can.delete && !reason.is_system && (
                                        <button
                                            type="button"
                                            className="ml-3 text-rose-600"
                                            onClick={() => {
                                                if (confirm(`Delete ${reason.name}?`)) {
                                                    router.delete(`/discount-reasons/${reason.id}`);
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
