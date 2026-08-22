import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    task: Record<string, unknown> | null;
    statuses: Record<string, string>;
    priorities: Record<string, string>;
    projects: Array<{ id: string; name: string; code: string | null; client_id: string }>;
    clients: Array<{ id: string; company_name: string }>;
    assignees: Array<{ id: number; name: string }>;
    preselectedProjectId?: string | null;
    preselectedClientId?: string | null;
};

export default function TaskForm({
    task,
    statuses,
    priorities,
    projects,
    clients,
    assignees,
    preselectedProjectId,
    preselectedClientId,
}: Props) {
    const isEdit = Boolean(task?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        project_id: (task?.project_id as string) ?? preselectedProjectId ?? '',
        client_id: (task?.client_id as string) ?? preselectedClientId ?? '',
        title: (task?.title as string) ?? '',
        description: (task?.description as string) ?? '',
        status: (task?.status as string) ?? 'todo',
        priority: (task?.priority as string) ?? 'medium',
        assignee_id: (task?.assignee_id as number | string | null) ?? '',
        due_date: (task?.due_date as string) ?? '',
        estimate_hours: (task?.estimate_hours as number | string | null) ?? '',
        sort_order: (task?.sort_order as number | string | null) ?? 0,
    });

    function onProjectChange(projectId: string) {
        const project = projects.find((p) => p.id === projectId);
        setData((current) => ({
            ...current,
            project_id: projectId,
            client_id: project?.client_id ?? current.client_id,
        }));
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/tasks/${task?.id}`);
        } else {
            post('/tasks');
        }
    }

    return (
        <AppLayout title={isEdit ? 'Edit task' : 'New task'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="block text-sm font-medium md:col-span-2">
                    Title
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        required
                    />
                    {errors.title && <span className="text-sm text-red-600">{errors.title}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Project
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.project_id}
                        onChange={(e) => onProjectChange(e.target.value)}
                    >
                        <option value="">None</option>
                        {projects.map((p) => (
                            <option key={p.id} value={p.id}>
                                {p.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Client
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.client_id}
                        onChange={(e) => setData('client_id', e.target.value)}
                        required={!data.project_id}
                        disabled={Boolean(data.project_id)}
                    >
                        <option value="">Select…</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.company_name}
                            </option>
                        ))}
                    </select>
                    {errors.client_id && <span className="text-sm text-red-600">{errors.client_id}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Status
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.status}
                        onChange={(e) => setData('status', e.target.value)}
                    >
                        {Object.entries(statuses).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Priority
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.priority}
                        onChange={(e) => setData('priority', e.target.value)}
                    >
                        {Object.entries(priorities).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Assignee
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.assignee_id ?? ''}
                        onChange={(e) => setData('assignee_id', e.target.value ? Number(e.target.value) : '')}
                    >
                        <option value="">Unassigned</option>
                        {assignees.map((a) => (
                            <option key={a.id} value={a.id}>
                                {a.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Due date
                    <input
                        type="date"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.due_date}
                        onChange={(e) => setData('due_date', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Estimate (hours)
                    <input
                        type="number"
                        min={0}
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.estimate_hours}
                        onChange={(e) => setData('estimate_hours', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium md:col-span-2">
                    Description
                    <textarea
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        rows={3}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </label>
                <div className="flex gap-2 md:col-span-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}
                    </button>
                    <Link href="/tasks" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
