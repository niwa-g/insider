import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Environment = { id: string; name: string; slug: string; status: string; url?: string | null };

type Props = {
    application: Record<string, unknown> & { id: string; name: string; status: string; environments?: Environment[]; client?: { company_name: string } };
    can: { update: boolean; delete: boolean };
};

export default function ApplicationShow({ application, can }: Props) {
    return (
        <AppLayout title={application.name}>
            <div className="mb-4 flex flex-wrap gap-2">
                <Link href="/applications" className="text-sm text-slate-500">← Applications</Link>
                {can.update && <Link href={`/applications/${application.id}/edit`} className="rounded-lg border px-3 py-1 text-sm">Edit</Link>}
                <Link href={`/applications/${application.id}/relationships`} className="rounded-lg border px-3 py-1 text-sm">Relationships</Link>
                {can.delete && (
                    <button type="button" onClick={() => confirm('Delete application?') && router.delete(`/applications/${application.id}`)} className="rounded-lg border border-red-200 px-3 py-1 text-sm text-red-600">Delete</button>
                )}
            </div>
            <div className="mb-6 rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <div className="flex items-center gap-3">
                    <h2 className="text-xl font-semibold">{application.name}</h2>
                    <StatusBadge status={application.status} />
                </div>
                <p className="mt-2 text-sm text-slate-500">Client: {application.client?.company_name ?? '—'}</p>
            </div>
            <h3 className="mb-2 font-semibold">Environments</h3>
            <div className="overflow-hidden rounded-xl border dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-2 text-left">Name</th><th className="px-4 py-2 text-left">Slug</th><th className="px-4 py-2 text-left">URL</th><th className="px-4 py-2 text-left">Status</th></tr></thead>
                    <tbody>
                        {(application.environments ?? []).map((env) => (
                            <tr key={env.id} className="border-t dark:border-slate-800"><td className="px-4 py-2">{env.name}</td><td className="px-4 py-2">{env.slug}</td><td className="px-4 py-2">{env.url ?? '—'}</td><td className="px-4 py-2"><StatusBadge status={env.status} /></td></tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Link href={`/environments/create?application_id=${application.id}`} className="mt-3 inline-block text-sm text-blue-600">Add environment</Link>
        </AppLayout>
    );
}
