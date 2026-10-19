import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Option = { id: number | string; name: string };

type Props = {
    user: {
        id: number;
        name: string;
        email: string;
        status: string;
        role_names: string[];
        team_ids: string[];
    } | null;
    roles: Option[];
    teams: Option[];
    statuses: Record<string, string>;
};

export default function UserForm({ user, roles, teams, statuses }: Props) {
    const isEdit = Boolean(user?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        password_confirmation: '',
        status: user?.status ?? 'active',
        role_names: user?.role_names ?? [],
        team_ids: user?.team_ids ?? [],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/settings/users/${user?.id}`);
        } else {
            post('/settings/users');
        }
    }

    function toggleRole(name: string) {
        setData(
            'role_names',
            data.role_names.includes(name)
                ? data.role_names.filter((r) => r !== name)
                : [...data.role_names, name],
        );
    }

    function toggleTeam(id: string) {
        setData(
            'team_ids',
            data.team_ids.includes(id)
                ? data.team_ids.filter((t) => t !== id)
                : [...data.team_ids, id],
        );
    }

    return (
        <AppLayout title={isEdit ? 'Edit user' : 'New user'}>
            <div className="mb-4">
                <Link href="/settings/users" className="text-sm text-slate-500 hover:text-slate-800">
                    ← Back to users
                </Link>
            </div>

            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <Field label="Name" error={errors.name}>
                    <input className="field" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                </Field>
                <Field label="Email" error={errors.email}>
                    <input type="email" className="field" value={data.email} onChange={(e) => setData('email', e.target.value)} required />
                </Field>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field label={isEdit ? 'New password (optional)' : 'Password'} error={errors.password}>
                        <input
                            type="password"
                            className="field"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required={!isEdit}
                        />
                    </Field>
                    <Field label="Confirm password" error={errors.password_confirmation}>
                        <input
                            type="password"
                            className="field"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            required={!isEdit || data.password.length > 0}
                        />
                    </Field>
                </div>
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
                    <p className="mb-2 text-sm font-medium">Roles</p>
                    <div className="flex flex-wrap gap-2">
                        {roles.map((role) => (
                            <label key={role.id} className="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-slate-700">
                                <input
                                    type="checkbox"
                                    checked={data.role_names.includes(String(role.name))}
                                    onChange={() => toggleRole(String(role.name))}
                                />
                                {role.name}
                            </label>
                        ))}
                    </div>
                    {errors.role_names && <p className="mt-1 text-sm text-rose-600">{errors.role_names}</p>}
                </div>

                <div>
                    <p className="mb-2 text-sm font-medium">Teams</p>
                    <div className="flex flex-wrap gap-2">
                        {teams.length === 0 && <p className="text-sm text-slate-500">No teams yet.</p>}
                        {teams.map((team) => (
                            <label key={team.id} className="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-slate-700">
                                <input
                                    type="checkbox"
                                    checked={data.team_ids.includes(String(team.id))}
                                    onChange={() => toggleTeam(String(team.id))}
                                />
                                {team.name}
                            </label>
                        ))}
                    </div>
                    {errors.team_ids && <p className="mt-1 text-sm text-rose-600">{errors.team_ids}</p>}
                </div>

                <div className="flex justify-end gap-2 pt-2">
                    <Link href="/settings/users" className="rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700">
                        Cancel
                    </Link>
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        {isEdit ? 'Save user' : 'Create user'}
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
