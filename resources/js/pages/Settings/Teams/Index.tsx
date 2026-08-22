import AppLayout from '../../../layouts/AppLayout';

type TeamRow = {
    id: string;
    name: string;
    code: string;
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
};

export default function TeamsIndex({ teams }: Props) {
    return (
        <AppLayout title="Teams">
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Team</th>
                            <th className="px-4 py-3 font-medium">Code</th>
                            <th className="px-4 py-3 font-medium">Department</th>
                            <th className="px-4 py-3 font-medium">Members</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                        {teams.data.length === 0 ? (
                            <tr>
                                <td colSpan={5} className="px-4 py-8 text-center text-slate-500">
                                    No teams yet.
                                </td>
                            </tr>
                        ) : (
                            teams.data.map((team) => (
                                <tr key={team.id}>
                                    <td className="px-4 py-3 font-medium">{team.name}</td>
                                    <td className="px-4 py-3 text-slate-500">{team.code}</td>
                                    <td className="px-4 py-3">{team.department?.name ?? '—'}</td>
                                    <td className="px-4 py-3">{team.users_count}</td>
                                    <td className="px-4 py-3 capitalize">{team.status}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
