import { Link } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Resource = { id: string; role: string | null; type: string; name: string };
type Environment = { id: string; name: string; slug: string; status: string; resources: Resource[] };

type Props = {
    application: { id: string; name: string; client?: { company_name: string }; environments: Environment[] };
    domains: Array<{ id: string; name: string; status: string }>;
    servers: Array<{ id: string; name: string; hostname: string | null; status: string }>;
    hostingAccounts: Array<{ id: string; name: string; primary_domain: string | null; status: string }>;
    cloudResources: Array<{ id: string; name: string; resource_type: string; status: string }>;
};

export default function ApplicationRelationships({ application, domains, servers, hostingAccounts, cloudResources }: Props) {
    return (
        <AppLayout title={`${application.name} — Relationships`}>
            <Link href={`/applications/${application.id}`} className="mb-4 inline-block text-sm text-slate-500">← Back to application</Link>
            <h2 className="mb-1 text-xl font-semibold">{application.name}</h2>
            <p className="mb-6 text-sm text-slate-500">{application.client?.company_name}</p>

            <section className="mb-8">
                <h3 className="mb-2 font-semibold">App → Environments → Resources</h3>
                {application.environments.map((env) => (
                    <div key={env.id} className="mb-4 rounded-xl border p-4 dark:border-slate-800">
                        <div className="flex items-center gap-2"><span className="font-medium">{env.name}</span><StatusBadge status={env.status} /><span className="text-xs text-slate-500">({env.slug})</span></div>
                        <ul className="mt-2 space-y-1 text-sm">
                            {env.resources.length === 0 && <li className="text-slate-500">No linked resources</li>}
                            {env.resources.map((r) => (<li key={r.id}>{r.type}: {r.name} {r.role ? `(${r.role})` : ''}</li>))}
                        </ul>
                    </div>
                ))}
            </section>

            <div className="grid gap-6 md:grid-cols-2">
                <section><h3 className="mb-2 font-semibold">Domains</h3><ul className="space-y-1 text-sm">{domains.map((d) => (<li key={d.id}><Link href={`/domains/${d.id}`} className="text-blue-600">{d.name}</Link></li>))}</ul></section>
                <section><h3 className="mb-2 font-semibold">Servers</h3><ul className="space-y-1 text-sm">{servers.map((s) => (<li key={s.id}>{s.name} {s.hostname ? `(${s.hostname})` : ''}</li>))}</ul></section>
                <section><h3 className="mb-2 font-semibold">Hosting</h3><ul className="space-y-1 text-sm">{hostingAccounts.map((h) => (<li key={h.id}>{h.name}</li>))}</ul></section>
                <section><h3 className="mb-2 font-semibold">Cloud</h3><ul className="space-y-1 text-sm">{cloudResources.map((c) => (<li key={c.id}>{c.name} ({c.resource_type})</li>))}</ul></section>
            </div>
        </AppLayout>
    );
}
