import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    groups: {
        data: Array<{
            id: string;
            name: string;
            code: string | null;
            color: string | null;
            clients_count: number;
        }>;
    };
    can: { create: boolean };
};

export default function ClientGroupsIndex({ groups, can }: Props) {
    return (
        <AppLayout title="Client groups">
            <div className="mb-4 flex justify-end">
                {can.create && (
                    <Link
                        href="/client-groups/create"
                        className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        New group
                    </Link>
                )}
            </div>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {groups.data.map((group) => (
                    <div
                        key={group.id}
                        className="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <h3 className="font-semibold">{group.name}</h3>
                                <p className="text-xs text-slate-500">{group.code ?? 'No code'}</p>
                            </div>
                            <span className="text-sm text-slate-500">{group.clients_count} clients</span>
                        </div>
                        <div className="mt-3 flex gap-2 text-sm">
                            <Link href={`/client-groups/${group.id}/edit`} className="text-blue-600">
                                Edit
                            </Link>
                            <button
                                type="button"
                                className="text-rose-600"
                                onClick={() => {
                                    if (confirm(`Delete ${group.name}?`)) {
                                        router.delete(`/client-groups/${group.id}`);
                                    }
                                }}
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </AppLayout>
    );
}
