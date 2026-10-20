import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Claim = {
    id: string;
    title: string;
    category: string;
    amount: string;
    status: string;
    incurred_on: string;
    claimant?: { id: number; name: string };
    client?: { id: string; company_name: string } | null;
};

type Props = {
    claims: { data: Claim[] };
    filters: { scope: string; status: string };
    statuses: Record<string, string>;
    can: { create: boolean; approve: boolean; pay: boolean; currentUserId: number };
};

const SCOPES = [
    { value: 'mine', label: 'My claims' },
    { value: 'pending', label: 'Pending approval' },
    { value: 'all', label: 'All claims' },
] as const;

export default function ExpenseClaimsIndex({ claims, filters, can }: Props) {
    return (
        <AppLayout title="Expense claims">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h1 className="text-xl font-semibold">Expense claims</h1>
                {can.create && (
                    <Link href="/expense-claims/create" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                        Submit claim
                    </Link>
                )}
            </div>

            <div className="mb-4 flex flex-wrap gap-2">
                {SCOPES.map((scope) => (
                    <button
                        key={scope.value}
                        type="button"
                        onClick={() => router.get('/expense-claims', { scope: scope.value }, { preserveState: true })}
                        className={`rounded-lg px-3 py-1.5 text-sm ${filters.scope === scope.value ? 'bg-slate-900 text-white' : 'border border-slate-300 dark:border-slate-700'}`}
                    >
                        {scope.label}
                    </button>
                ))}
            </div>

            <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Title</th>
                            <th className="px-4 py-2 text-left">Claimant</th>
                            <th className="px-4 py-2 text-left">Category</th>
                            <th className="px-4 py-2 text-left">Incurred</th>
                            <th className="px-4 py-2 text-right">Amount</th>
                            <th className="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {claims.data.map((claim) => (
                            <tr key={claim.id} className="border-t border-slate-200 dark:border-slate-800">
                                <td className="px-4 py-2">
                                    <Link href={`/expense-claims/${claim.id}`} className="text-blue-600 hover:underline">
                                        {claim.title}
                                    </Link>
                                    {claim.client?.company_name && (
                                        <div className="text-xs text-slate-500">{claim.client.company_name}</div>
                                    )}
                                </td>
                                <td className="px-4 py-2">{claim.claimant?.name}</td>
                                <td className="px-4 py-2 capitalize">{claim.category.replace('_', ' ')}</td>
                                <td className="px-4 py-2">{claim.incurred_on}</td>
                                <td className="px-4 py-2 text-right tabular-nums">${Number(claim.amount).toFixed(2)}</td>
                                <td className="px-4 py-2 capitalize">{claim.status.replace('_', ' ')}</td>
                            </tr>
                        ))}
                        {claims.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-6 text-center text-slate-500">
                                    No expense claims found.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
