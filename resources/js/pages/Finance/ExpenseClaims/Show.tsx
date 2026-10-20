import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    claim: {
        id: string;
        title: string;
        category: string;
        description: string | null;
        amount: string;
        currency: string;
        status: string;
        incurred_on: string;
        receipt_url: string | null;
        receipt_name: string | null;
        approved_at: string | null;
        rejection_reason: string | null;
        paid_on: string | null;
        claimant?: { id: number; name: string; email: string };
        approver?: { id: number; name: string } | null;
        client?: { id: string; company_name: string } | null;
        client_service?: { id: string; name: string } | null;
        financial_entry?: {
            id: string;
            description: string;
            amount: string;
            status: string;
            occurred_on: string;
        } | null;
    };
    paymentMethods: Record<string, string>;
    can: { approve: boolean; pay: boolean; delete: boolean; isClaimant: boolean };
};

export default function ExpenseClaimShow({ claim, paymentMethods, can }: Props) {
    const [rejecting, setRejecting] = useState(false);
    const reject = useForm({ rejection_reason: '' });
    const pay = useForm({
        method: 'bank_transfer',
        paid_on: new Date().toISOString().slice(0, 10),
    });

    function submitReject(e: FormEvent) {
        e.preventDefault();
        reject.post(`/expense-claims/${claim.id}/reject`);
    }

    function submitPay(e: FormEvent) {
        e.preventDefault();
        pay.post(`/expense-claims/${claim.id}/pay`);
    }

    return (
        <AppLayout title="Expense claim">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-xl font-semibold">{claim.title}</h1>
                    <p className="text-sm text-slate-600">
                        {claim.claimant?.name}
                        {claim.client?.company_name && ` · ${claim.client.company_name}`}
                    </p>
                    <p className="mt-1 text-sm capitalize">Status: {claim.status.replace('_', ' ')}</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {can.approve && (
                        <button
                            type="button"
                            onClick={() => router.post(`/expense-claims/${claim.id}/approve`)}
                            className="rounded-lg bg-emerald-600 px-3 py-2 text-sm text-white"
                        >
                            Approve
                        </button>
                    )}
                    {can.approve && !rejecting && (
                        <button
                            type="button"
                            onClick={() => setRejecting(true)}
                            className="rounded-lg bg-amber-600 px-3 py-2 text-sm text-white"
                        >
                            Reject
                        </button>
                    )}
                    {can.isClaimant && claim.status === 'pending' && (
                        <button
                            type="button"
                            onClick={() => {
                                if (confirm('Cancel this claim?')) {
                                    router.post(`/expense-claims/${claim.id}/cancel`);
                                }
                            }}
                            className="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        >
                            Cancel claim
                        </button>
                    )}
                    {can.delete && (
                        <button
                            type="button"
                            onClick={() => {
                                if (confirm('Delete this claim?')) {
                                    router.delete(`/expense-claims/${claim.id}`);
                                }
                            }}
                            className="rounded-lg bg-red-600 px-3 py-2 text-sm text-white"
                        >
                            Delete
                        </button>
                    )}
                    <Link href="/expense-claims" className="rounded-lg border border-slate-300 px-3 py-2 text-sm">Back</Link>
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Amount</p>
                    <p className="font-medium tabular-nums">${Number(claim.amount).toFixed(2)}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Category</p>
                    <p className="font-medium capitalize">{claim.category.replace('_', ' ')}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Incurred</p>
                    <p className="font-medium">{claim.incurred_on}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Paid on</p>
                    <p className="font-medium">{claim.paid_on ?? '—'}</p>
                </div>
            </div>

            {claim.description && (
                <div className="mb-6 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Description</p>
                    <p className="whitespace-pre-wrap text-sm">{claim.description}</p>
                </div>
            )}

            {claim.status === 'rejected' && claim.rejection_reason && (
                <div className="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40">
                    Rejected{claim.approver?.name ? ` by ${claim.approver.name}` : ''}: {claim.rejection_reason}
                </div>
            )}

            {claim.receipt_url && (
                <p className="mb-6 text-sm">
                    Receipt:{' '}
                    <a href={claim.receipt_url} target="_blank" rel="noreferrer" className="text-blue-600 hover:underline">
                        {claim.receipt_name ?? 'View receipt'}
                    </a>
                </p>
            )}

            {claim.financial_entry && (
                <div className="mb-6 rounded-lg border border-slate-200 p-4 text-sm dark:border-slate-800">
                    <p className="text-xs text-slate-500">Posted cost entry</p>
                    <p>
                        {claim.financial_entry.description} · ${Number(claim.financial_entry.amount).toFixed(2)}
                        <span className="ml-1 capitalize text-slate-500">({claim.financial_entry.status})</span>
                    </p>
                </div>
            )}

            {rejecting && can.approve && (
                <form onSubmit={submitReject} className="mb-6 space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                    <label className="block text-sm font-medium">
                        Reason for rejection
                        <input
                            className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                            value={reject.data.rejection_reason}
                            onChange={(e) => reject.setData('rejection_reason', e.target.value)}
                            required
                        />
                    </label>
                    <div className="flex gap-2">
                        <button type="submit" disabled={reject.processing} className="rounded-lg bg-amber-600 px-3 py-2 text-sm text-white">
                            Confirm reject
                        </button>
                        <button type="button" onClick={() => setRejecting(false)} className="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            Back
                        </button>
                    </div>
                </form>
            )}

            {can.pay && (
                <form onSubmit={submitPay} className="max-w-md space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                    <h2 className="text-sm font-semibold">Pay reimbursement</h2>
                    <div className="grid grid-cols-2 gap-3">
                        <label className="block text-xs font-medium text-slate-500">
                            Method
                            <select
                                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                value={pay.data.method}
                                onChange={(e) => pay.setData('method', e.target.value)}
                            >
                                {Object.entries(paymentMethods).map(([v, l]) => (
                                    <option key={v} value={v}>{l}</option>
                                ))}
                            </select>
                        </label>
                        <label className="block text-xs font-medium text-slate-500">
                            Paid on
                            <input
                                type="date"
                                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                value={pay.data.paid_on}
                                onChange={(e) => pay.setData('paid_on', e.target.value)}
                                required
                            />
                        </label>
                    </div>
                    <button type="submit" disabled={pay.processing} className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white disabled:opacity-50">
                        Mark paid
                    </button>
                </form>
            )}
        </AppLayout>
    );
}
