import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type TaskRow = {
    id: string;
    title: string;
    status: string;
    priority: string;
    due_date: string | null;
    client?: { id: string; company_name: string } | null;
    project?: { id: string; name: string; code: string | null } | null;
    assignee?: { id: number; name: string } | null;
};

type Props = {
    tasks: {
        data: TaskRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    } | null;
    columns: Record<string, TaskRow[]> | null;
    view: string;
    filters: {
        search: string;
        status: string;
        project_id: string;
        client_id: string;
        assignee_id: string;
        view: string;
    };
    statuses: Record<string, string>;
    priorities: Record<string, string>;
    projects: Array<{ id: string; name: string; code: string | null }>;
    clients: Array<{ id: string; company_name: string }>;
    assignees: Array<{ id: number; name: string }>;
    can: { create: boolean };
};

export default function TasksIndex({
    tasks,
    columns,
    view,
    filters,
    statuses,
    projects,
    clients,
    assignees,
    can,
}: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [projectId, setProjectId] = useState(filters.project_id ?? '');
    const [clientId, setClientId] = useState(filters.client_id ?? '');
    const [assigneeId, setAssigneeId] = useState(filters.assignee_id ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get(
            '/tasks',
            {
                search,
                status,
                project_id: projectId,
                client_id: clientId,
                assignee_id: assigneeId,
                view,
            },
            { preserveState: true },
        );
    }

    function setView(next: string) {
        router.get(
            '/tasks',
            {
                search: filters.search,
                status: filters.status,
                project_id: filters.project_id,
                client_id: filters.client_id,
                assignee_id: filters.assignee_id,
                view: next,
            },
            { preserveState: true },
        );
    }

    function moveStatus(taskId: string, nextStatus: string) {
        router.patch(`/tasks/${taskId}/status`, { status: nextStatus }, { preserveScroll: true });
    }

    return (
        <AppLayout title="Tasks">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search tasks…"
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
                        value={projectId}
                        onChange={(e) => setProjectId(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All projects</option>
                        {projects.map((p) => (
                            <option key={p.id} value={p.id}>
                                {p.name}
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
                    <select
                        value={assigneeId}
                        onChange={(e) => setAssigneeId(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All assignees</option>
                        {assignees.map((a) => (
                            <option key={a.id} value={a.id}>
                                {a.name}
                            </option>
                        ))}
                    </select>
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                        Filter
                    </button>
                </form>
                <div className="flex gap-2">
                    <div className="flex overflow-hidden rounded-lg border border-slate-300 dark:border-slate-700">
                        <button
                            type="button"
                            onClick={() => setView('list')}
                            className={`px-3 py-2 text-sm ${view === 'list' ? 'bg-slate-900 text-white' : 'bg-white dark:bg-slate-950'}`}
                        >
                            List
                        </button>
                        <button
                            type="button"
                            onClick={() => setView('board')}
                            className={`px-3 py-2 text-sm ${view === 'board' ? 'bg-slate-900 text-white' : 'bg-white dark:bg-slate-950'}`}
                        >
                            Board
                        </button>
                    </div>
                    {can.create && (
                        <Link
                            href="/tasks/create"
                            className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                        >
                            New task
                        </Link>
                    )}
                </div>
            </div>

            {view === 'board' && columns ? (
                <div className="grid gap-3 overflow-x-auto md:grid-cols-3 xl:grid-cols-6">
                    {Object.entries(columns).map(([columnStatus, columnTasks]) => (
                        <div
                            key={columnStatus}
                            className="min-w-[220px] rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div className="mb-3 flex items-center justify-between">
                                <StatusBadge status={columnStatus} label={statuses[columnStatus] ?? columnStatus} />
                                <span className="text-xs text-slate-500">{columnTasks.length}</span>
                            </div>
                            <div className="space-y-2">
                                {columnTasks.map((task) => (
                                    <div
                                        key={task.id}
                                        className="rounded-lg border border-slate-200 bg-white p-3 text-sm dark:border-slate-800 dark:bg-slate-900"
                                    >
                                        <Link
                                            href={`/tasks/${task.id}/edit`}
                                            className="font-medium text-blue-600 hover:underline"
                                        >
                                            {task.title}
                                        </Link>
                                        <div className="mt-1 text-xs text-slate-500">
                                            {task.project?.name ?? 'No project'}
                                            {task.assignee ? ` · ${task.assignee.name}` : ''}
                                        </div>
                                        <div className="mt-2 flex items-center justify-between gap-2">
                                            <StatusBadge status={task.priority} />
                                            <select
                                                value={task.status}
                                                onChange={(e) => moveStatus(task.id, e.target.value)}
                                                className="rounded border border-slate-300 bg-white px-1 py-0.5 text-xs dark:border-slate-700 dark:bg-slate-950"
                                            >
                                                {Object.entries(statuses).map(([value, label]) => (
                                                    <option key={value} value={value}>
                                                        {label}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <table className="min-w-full text-left text-sm">
                        <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                            <tr>
                                <th className="px-4 py-3 font-medium">Task</th>
                                <th className="px-4 py-3 font-medium">Project</th>
                                <th className="px-4 py-3 font-medium">Assignee</th>
                                <th className="px-4 py-3 font-medium">Due</th>
                                <th className="px-4 py-3 font-medium">Priority</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {(tasks?.data ?? []).map((task) => (
                                <tr key={task.id} className="border-b border-slate-100 dark:border-slate-800">
                                    <td className="px-4 py-3">
                                        <Link
                                            href={`/tasks/${task.id}/edit`}
                                            className="font-medium text-blue-600 hover:underline"
                                        >
                                            {task.title}
                                        </Link>
                                        <div className="text-xs text-slate-500">{task.client?.company_name ?? '—'}</div>
                                    </td>
                                    <td className="px-4 py-3">{task.project?.name ?? '—'}</td>
                                    <td className="px-4 py-3">{task.assignee?.name ?? '—'}</td>
                                    <td className="px-4 py-3">{task.due_date ?? '—'}</td>
                                    <td className="px-4 py-3">
                                        <StatusBadge status={task.priority} />
                                    </td>
                                    <td className="px-4 py-3">
                                        <select
                                            value={task.status}
                                            onChange={(e) => moveStatus(task.id, e.target.value)}
                                            className="rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950"
                                        >
                                            {Object.entries(statuses).map(([value, label]) => (
                                                <option key={value} value={value}>
                                                    {label}
                                                </option>
                                            ))}
                                        </select>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </AppLayout>
    );
}
