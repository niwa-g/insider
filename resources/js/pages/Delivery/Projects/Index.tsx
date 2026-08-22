import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type ProjectRow = {
    id: string;
    name: string;
    code: string | null;
    status: string;
    priority: string;
    health_status: string;
    health_score: number;
    due_date: string | null;
    tasks_count?: number;
    open_tasks_count?: number;
    client?: { id: string; company_name: string };
    manager?: { id: number; name: string } | null;
};

type Props = {
    projects: {
        data: ProjectRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search: string; status: string; client_id: string; health_status: string };
    statuses: Record<string, string>;
    healthStatuses: Record<string, string>;
    clients: Array<{ id: string; company_name: string }>;
    can: { create: boolean };
};

export default function ProjectsIndex({ projects, filters, statuses, healthStatuses, clients, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [clientId, setClientId] = useState(filters.client_id ?? '');
    const [health, setHealth] = useState(filters.health_status ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/projects', { search, status, client_id: clientId, health_status: health }, { preserveState: true });
    }

    return (
        <AppLayout title="Projects">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search projects…"
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    />
                    <select
                        value={status}
                        onChange={(e) => setStatus(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All statuses</option>
                        {Object.entries(statuses).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                    <select
                        value={health}
                        onChange={(e) => setHealth(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All health</option>
                        {Object.entries(healthStatuses).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                    <select
                        value={clientId}
                        onChange={(e) => setClientId(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All clients</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.company_name}
                            </option>
                        ))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                        Filter
                    </button>
                </form>
                {can.create && (
                    <Link
                        href="/projects/create"
                        className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        New project
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Project</th>
                            <th className="px-4 py-3 font-medium">Client</th>
                            <th className="px-4 py-3 font-medium">Health</th>
                            <th className="px-4 py-3 font-medium">Tasks</th>
                            <th className="px-4 py-3 font-medium">Due</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {projects.data.map((project) => (
                            <tr key={project.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3">
                                    <Link href={`/projects/${project.id}`} className="font-medium text-blue-600 hover:underline">
                                        {project.name}
                                    </Link>
                                    <div className="font-mono text-xs text-slate-500">{project.code ?? '—'}</div>
                                </td>
                                <td className="px-4 py-3">{project.client?.company_name ?? '—'}</td>
                                <td className="px-4 py-3">
                                    <StatusBadge status={project.health_status} />
                                    <span className="ml-2 text-xs text-slate-500">{project.health_score}</span>
                                </td>
                                <td className="px-4 py-3">
                                    {project.open_tasks_count ?? 0}/{project.tasks_count ?? 0} open
                                </td>
                                <td className="px-4 py-3">{project.due_date ?? '—'}</td>
                                <td className="px-4 py-3">
                                    <StatusBadge status={project.status} />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
