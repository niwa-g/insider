import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Line = {
    id: string;
    description: string;
    quantity: string;
    unit_price: string;
    line_total: string;
};

type Props = {
    invoice: {
        id: string;
        number: string;
        status: string;
        issue_date: string;
        due_date: string | null;
        paid_at: string | null;
        subtotal: string;
        tax_amount: string;
        total: string;
        notes: string | null;
        client?: { id: string; company_name: string; client_code: string };
        lines: Line[];
    };
    statuses: Record<string, string>;
    can: { update: boolean; delete: boolean };
};

export default function InvoiceShow({ invoice, can }: Props) {
    return (
        <AppLayout title={`Invoice ${invoice.number}`}>
            <div className="mb-6 flex items-start justify-between">
                <div>
                    <h1 className="text-xl font-semibold">{invoice.number}</h1>
                    <p className="text-sm text-slate-600">{invoice.client?.company_name}</p>
                    <p className="mt-1 text-sm capitalize">Status: {invoice.status}</p>
                </div>
                <div className="flex gap-2">
                    {invoice.status === 'draft' && can.update && (
                        <Link href={`/invoices/${invoice.id}/edit`} className="rounded-lg border border-slate-300 px-3 py-2 text-sm">Edit</Link>
                    )}
                    {invoice.status === 'draft' && can.update && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/send`)} className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">Mark sent</button>
                    )}
                    {(invoice.status === 'sent' || invoice.status === 'overdue') && can.update && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/pay`)} className="rounded-lg bg-green-600 px-3 py-2 text-sm text-white">Mark paid</button>
                    )}
                    {invoice.status !== 'void' && invoice.status !== 'paid' && can.update && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/void`)} className="rounded-lg bg-amber-600 px-3 py-2 text-sm text-white">Void</button>
                    )}
                    {invoice.status === 'draft' && can.delete && (
                        <button type="button" onClick={() => router.delete(`/invoices/${invoice.id}`)} className="rounded-lg bg-red-600 px-3 py-2 text-sm text-white">Delete</button>
                    )}
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Issue date</p>
                    <p className="font-medium">{invoice.issue_date}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Due date</p>
                    <p className="font-medium">{invoice.due_date ?? '—'}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Total</p>
                    <p className="font-medium">${Number(invoice.total).toFixed(2)}</p>
                </div>
            </div>

            <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Description</th>
                            <th className="px-4 py-2 text-right">Qty</th>
                            <th className="px-4 py-2 text-right">Unit price</th>
                            <th className="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {invoice.lines.map((line) => (
                            <tr key={line.id} className="border-t border-slate-200 dark:border-slate-800">
                                <td className="px-4 py-2">{line.description}</td>
                                <td className="px-4 py-2 text-right">{line.quantity}</td>
                                <td className="px-4 py-2 text-right">${Number(line.unit_price).toFixed(2)}</td>
                                <td className="px-4 py-2 text-right">${Number(line.line_total).toFixed(2)}</td>
                            </tr>
                        ))}
                    </tbody>
                    <tfoot>
                        <tr className="border-t border-slate-200 dark:border-slate-800">
                            <td colSpan={3} className="px-4 py-2 text-right font-medium">Subtotal</td>
                            <td className="px-4 py-2 text-right">${Number(invoice.subtotal).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td colSpan={3} className="px-4 py-2 text-right font-medium">Tax</td>
                            <td className="px-4 py-2 text-right">${Number(invoice.tax_amount).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td colSpan={3} className="px-4 py-2 text-right font-semibold">Total</td>
                            <td className="px-4 py-2 text-right font-semibold">${Number(invoice.total).toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {invoice.notes && (
                <div className="mt-4 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Notes</p>
                    <p className="whitespace-pre-wrap text-sm">{invoice.notes}</p>
                </div>
            )}

            <Link href="/invoices" className="mt-4 inline-block text-sm text-blue-600 hover:underline">← Back to invoices</Link>
        </AppLayout>
    );
}
