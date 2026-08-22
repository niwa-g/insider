import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Task = {
    id: string;
    title: string;
    status: string;
    priority: string;
    due_date: string | null;
    assignee?: { id: number; name: string } | null;
};

type Props = {
    project: {
        id: string;
        name: string;
        code: string | null;
        status: string;
        priority: string;
        health_status: string;
        health_score: number;
        due_date: string | null;
        description: string | null;
        notes: string | null;
        budget: string | null;
        client?: { id: string; company_name: string; client_code: string };
        manager?: { id: number; name: string; email: string } | null;
        template?: { id: string; name: string } | null;
        services?: Array<{ id: string; client_service?: { id: string; name: string; selling_price: string } }>;
        tasks?: Task[];
    };
    stats: { tasks: number; open_tasks: number; overdue_tasks: number; services: number };
    can: { update: boolean; delete: boolean; manageTasks: boolean };
};

export default function ProjectShow({ project, stats, can }: Props) {
    function destroy() {
        if (confirm(`Delete project ${project.name}?`)) {
            router.delete(`/projects/${project.id}`);
        }
    }

    return (
        <AppLayout title={project.name}>
            <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div className="flex flex-wrap items-center gap-3">
                        <h2 className="text-xl font-semibold">{project.name}</h2>
                        <StatusBadge status={project.status} />
                        <StatusBadge status={project.health_status} label={`Health ${project.health_score}`} />
                    </div>
                    <p className="mt-1 text-sm text-slate-500">
                        {project.code ?? 'No code'}
                        {project.client ? ` · ${project.client.company_name}` : ''}
                        {project.due_date ? ` · Due ${project.due_date}` : ''}
                    </p>
                </div>
                <div className="flex gap-2">
                    {can.manageTasks && (
                        <Link
                            href={`/tasks/create?project_id=${project.id}`}
                            className="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700"
                        >
                            Add task
                        </Link>
                    )}
                    {can.update && (
                        <Link
                            href={`/projects/${project.id}/edit`}
                            className="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700"
                        >
                            Edit
                        </Link>
                    )}
                    {can.delete && (
                        <button
                            type="button"
                            onClick={destroy}
                            className="rounded-lg border border-rose-300 px-3 py-2 text-sm text-rose-700"
                        >
                            Delete
                        </button>
                    )}
                </div>
            </div>

            <div className="mb-4 grid gap-3 sm:grid-cols-4">
                <Stat label="Tasks" value={stats.tasks} />
                <Stat label="Open" value={stats.open_tasks} />
                <Stat label="Overdue" value={stats.overdue_tasks} />
                <Stat label="Services" value={stats.services} />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <section className="space-y-3 rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex items-center justify-between">
                        <h3 className="font-semibold">Tasks</h3>
                        <Link href={`/tasks?project_id=${project.id}&view=board`} className="text-sm text-blue-600">
                            Board view
                        </Link>
                    </div>
                    {(project.tasks ?? []).length === 0 ? (
                        <p className="text-sm text-slate-500">No tasks yet.</p>
                    ) : (
                        <ul className="divide-y divide-slate-100 dark:divide-slate-800">
                            {(project.tasks ?? []).map((task) => (
                                <li key={task.id} className="flex items-center justify-between gap-3 py-3 text-sm">
                                    <div>
                                        <Link href={`/tasks/${task.id}/edit`} className="font-medium hover:text-blue-600">
                                            {task.title}
                                        </Link>
                                        <div className="text-slate-500">
                                            {task.assignee?.name ?? 'Unassigned'}
                                            {task.due_date ? ` · ${task.due_date}` : ''}
                                        </div>
                                    </div>
                                    <StatusBadge status={task.status} />
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <div className="space-y-4">
                    <section className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 className="font-semibold">Details</h3>
                        <dl className="mt-3 space-y-2 text-sm">
                            <Item label="Manager" value={project.manager?.name} />
                            <Item label="Priority" value={project.priority} />
                            <Item label="Budget" value={project.budget ? `$${project.budget}` : null} />
                            <Item label="Template" value={project.template?.name} />
                            <Item label="Description" value={project.description} />
                        </dl>
                    </section>
                    <section className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 className="font-semibold">Services</h3>
                        {(project.services ?? []).length === 0 ? (
                            <p className="mt-2 text-sm text-slate-500">No linked services.</p>
                        ) : (
                            <ul className="mt-2 space-y-2 text-sm">
                                {(project.services ?? []).map((row) => (
                                    <li key={row.id} className="flex justify-between gap-2">
                                        <span>{row.client_service?.name ?? 'Service'}</span>
                                        <span className="text-slate-500">
                                            {row.client_service?.selling_price
                                                ? `$${row.client_service.selling_price}`
                                                : ''}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}

function Stat({ label, value }: { label: string; value: number }) {
    return (
        <div className="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
            <div className="text-xs uppercase tracking-wide text-slate-500">{label}</div>
            <div className="mt-1 text-2xl font-semibold">{value}</div>
        </div>
    );
}

function Item({ label, value }: { label: string; value?: string | null }) {
    return (
        <div>
            <dt className="text-slate-500">{label}</dt>
            <dd className="mt-0.5 font-medium capitalize">{value || '—'}</dd>
        </div>
    );
}
