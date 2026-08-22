import { Link } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Props = {
    run: Record<string, unknown> & { id: string; trigger: string; status: string; message?: string; meta?: Record<string, unknown> };
};

export default function RunShow({ run }: Props) {
    return (
        <AppLayout title="Automation run">
            <div className="mb-4">
                <Link href="/automation/runs" className="text-sm text-blue-600">← Back to runs</Link>
            </div>
            <div className="rounded-xl border p-6 dark:border-slate-800">
                <div className="mb-4"><StatusBadge status={run.status} /></div>
                <dl className="space-y-2 text-sm">
                    <div><dt className="font-medium text-slate-500">Trigger</dt><dd className="capitalize">{String(run.trigger).replace(/_/g, ' ')}</dd></div>
                    {run.message && <div><dt className="font-medium text-slate-500">Message</dt><dd>{String(run.message)}</dd></div>}
                    {run.meta && (
                        <div>
                            <dt className="font-medium text-slate-500">Meta</dt>
                            <dd><pre className="mt-1 overflow-auto rounded bg-slate-50 p-2 text-xs dark:bg-slate-950">{JSON.stringify(run.meta, null, 2)}</pre></dd>
                        </div>
                    )}
                </dl>
            </div>
        </AppLayout>
    );
}
