import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type TeamRow = {
    id: string;
    name: string;
    code: string | null;
    status: string;
    users_count: number;
    department?: { id: string; name: string } | null;
    users?: Array<{ id: number; name: string }>;
};

type Props = {
    teams: {
        data: TeamRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    can: { create: boolean; update: boolean; delete: boolean };
};

export default function TeamsIndex({ teams, can }: Props) {
    function destroyTeam(team: TeamRow) {
        if (!confirm(`Delete team "${team.name}"?`)) {
            return;
        }
        router.delete(`/settings/teams/${team.id}`);
    }

    return (
        <AppLayout title="Teams">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 className="text-lg font-semibold">Teams</h2>
                    <p className="text-sm text-slate-500">Organize people into departments and teams.</p>
                </div>
                {can.create && (
                    <Link href="/settings/teams/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        New team
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Team</th>
                            <th className="px-4 py-3 font-medium">Code</th>
                            <th className="px-4 py-3 font-medium">Department</th>
                            <th className="px-4 py-3 font-medium">Members</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            {(can.update || can.delete) && <th className="px-4 py-3 font-medium">Actions</th>}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                        {teams.data.length === 0 ? (
                            <tr>
                                <td colSpan={6} className="px-4 py-8 text-center text-slate-500">
                                    No teams yet.
                                </td>
                            </tr>
                        ) : (
                            teams.data.map((team) => (
                                <tr key={team.id}>
                                    <td className="px-4 py-3 font-medium">{team.name}</td>
                                    <td className="px-4 py-3 text-slate-500">{team.code || '—'}</td>
                                    <td className="px-4 py-3">{team.department?.name ?? '—'}</td>
                                    <td className="px-4 py-3">{team.users_count}</td>
                                    <td className="px-4 py-3 capitalize">{team.status}</td>
                                    {(can.update || can.delete) && (
                                        <td className="px-4 py-3">
                                            <div className="flex gap-3">
                                                {can.update && (
                                                    <Link href={`/settings/teams/${team.id}/edit`} className="text-sm text-blue-600 hover:underline">
                                                        Edit
                                                    </Link>
                                                )}
                                                {can.delete && (
                                                    <button type="button" onClick={() => destroyTeam(team)} className="text-sm text-rose-600 hover:underline">
                                                        Delete
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    )}
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
