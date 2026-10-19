import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Option = { id: number | string; name: string; email?: string };

type Props = {
    team: {
        id: string;
        name: string;
        code: string | null;
        department_id: string | null;
        status: string;
        user_ids: number[];
    } | null;
    departments: Option[];
    users: Option[];
    statuses: Record<string, string>;
};

export default function TeamForm({ team, departments, users, statuses }: Props) {
    const isEdit = Boolean(team?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        name: team?.name ?? '',
        code: team?.code ?? '',
        department_id: team?.department_id ?? '',
        status: team?.status ?? 'active',
        user_ids: team?.user_ids ?? [],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/settings/teams/${team?.id}`);
        } else {
            post('/settings/teams');
        }
    }

    function toggleUser(id: number) {
        setData(
            'user_ids',
            data.user_ids.includes(id)
                ? data.user_ids.filter((u) => u !== id)
                : [...data.user_ids, id],
        );
    }

    return (
        <AppLayout title={isEdit ? 'Edit team' : 'New team'}>
            <div className="mb-4">
                <Link href="/settings/teams" className="text-sm text-slate-500 hover:text-slate-800">
                    ← Back to teams
                </Link>
            </div>

            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <Field label="Name" error={errors.name}>
                    <input className="field" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                </Field>
                <Field label="Code" error={errors.code}>
                    <input className="field" value={data.code} onChange={(e) => setData('code', e.target.value)} />
                </Field>
                <Field label="Department" error={errors.department_id}>
                    <select className="field" value={data.department_id} onChange={(e) => setData('department_id', e.target.value)}>
                        <option value="">No department</option>
                        {departments.map((d) => (
                            <option key={d.id} value={String(d.id)}>
                                {d.name}
                            </option>
                        ))}
                    </select>
                </Field>
                <Field label="Status" error={errors.status}>
                    <select className="field" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        {Object.entries(statuses).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </Field>

                <div>
                    <p className="mb-2 text-sm font-medium">Members</p>
                    <div className="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                        {users.map((user) => (
                            <label key={user.id} className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={data.user_ids.includes(Number(user.id))}
                                    onChange={() => toggleUser(Number(user.id))}
                                />
                                <span>
                                    {user.name}
                                    {user.email ? <span className="text-slate-500"> · {user.email}</span> : null}
                                </span>
                            </label>
                        ))}
                    </div>
                    {errors.user_ids && <p className="mt-1 text-sm text-rose-600">{errors.user_ids}</p>}
                </div>

                <div className="flex justify-end gap-2 pt-2">
                    <Link href="/settings/teams" className="rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700">
                        Cancel
                    </Link>
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        {isEdit ? 'Save team' : 'Create team'}
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <label className="block text-sm">
            <span className="mb-1 block font-medium">{label}</span>
            {children}
            {error && <span className="mt-1 block text-rose-600">{error}</span>}
        </label>
    );
}
