import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    project: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    statuses: Record<string, string>;
    priorities: Record<string, string>;
    managers: Array<{ id: number; name: string }>;
    templates: Array<{ id: string; name: string; code: string | null }>;
    clientServices: Array<{ id: string; name: string; status: string }>;
    preselectedClientId?: string | null;
};

export default function ProjectForm({
    project,
    clients,
    statuses,
    priorities,
    managers,
    templates,
    clientServices,
    preselectedClientId,
}: Props) {
    const isEdit = Boolean(project?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (project?.client_id as string) ?? preselectedClientId ?? '',
        name: (project?.name as string) ?? '',
        code: (project?.code as string) ?? '',
        description: (project?.description as string) ?? '',
        status: (project?.status as string) ?? 'planned',
        priority: (project?.priority as string) ?? 'medium',
        start_date: (project?.start_date as string) ?? '',
        due_date: (project?.due_date as string) ?? '',
        budget: (project?.budget as string | number) ?? '',
        project_manager_id: (project?.project_manager_id as number | string | null) ?? '',
        notes: (project?.notes as string) ?? '',
        project_template_id: (project?.project_template_id as string) ?? '',
        apply_template: false,
        client_service_ids: (project?.client_service_ids as string[]) ?? [],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/projects/${project?.id}`);
        } else {
            post('/projects');
        }
    }

    function toggleService(id: string) {
        setData(
            'client_service_ids',
            data.client_service_ids.includes(id)
                ? data.client_service_ids.filter((s) => s !== id)
                : [...data.client_service_ids, id],
        );
    }

    return (
        <AppLayout title={isEdit ? 'Edit project' : 'New project'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="block text-sm font-medium md:col-span-2">
                    Name
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && <span className="text-sm text-red-600">{errors.name}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Code
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.code}
                        onChange={(e) => setData('code', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Client
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.client_id}
                        onChange={(e) => setData('client_id', e.target.value)}
                        required
                        disabled={isEdit}
                    >
                        <option value="">Select…</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.company_name}
                            </option>
                        ))}
                    </select>
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
                    Start date
                    <input
                        type="date"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.start_date}
                        onChange={(e) => setData('start_date', e.target.value)}
                    />
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
                    Budget
                    <input
                        type="number"
                        step="0.01"
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.budget}
                        onChange={(e) => setData('budget', e.target.value)}
                    />
                </label>
                <label className="block text-sm font-medium">
                    Project manager
                    <select
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.project_manager_id ?? ''}
                        onChange={(e) => setData('project_manager_id', e.target.value ? Number(e.target.value) : '')}
                    >
                        <option value="">Unassigned</option>
                        {managers.map((m) => (
                            <option key={m.id} value={m.id}>
                                {m.name}
                            </option>
                        ))}
                    </select>
                </label>
                {!isEdit && (
                    <>
                        <label className="block text-sm font-medium">
                            Template
                            <select
                                className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                                value={data.project_template_id}
                                onChange={(e) => setData('project_template_id', e.target.value)}
                            >
                                <option value="">None</option>
                                {templates.map((t) => (
                                    <option key={t.id} value={t.id}>
                                        {t.name}
                                    </option>
                                ))}
                            </select>
                        </label>
                        <label className="flex items-center gap-2 text-sm font-medium">
                            <input
                                type="checkbox"
                                checked={data.apply_template}
                                onChange={(e) => setData('apply_template', e.target.checked)}
                            />
                            Apply template tasks on create
                        </label>
                    </>
                )}
                <label className="block text-sm font-medium md:col-span-2">
                    Description
                    <textarea
                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        rows={3}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </label>
                {clientServices.length > 0 && (
                    <div className="md:col-span-2">
                        <div className="mb-2 text-sm font-medium">Linked client services</div>
                        <div className="space-y-2 rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                            {clientServices.map((service) => (
                                <label key={service.id} className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={data.client_service_ids.includes(service.id)}
                                        onChange={() => toggleService(service.id)}
                                    />
                                    {service.name}
                                    <span className="text-slate-500">({service.status})</span>
                                </label>
                            ))}
                        </div>
                    </div>
                )}
                <div className="flex gap-2 md:col-span-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}
                    </button>
                    <Link href="/projects" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
