import AppLayout from '../../../layouts/AppLayout';

type Activity = { id: number; description: string; created_at?: string | null; causer?: { name: string } | null; subject_type?: string | null };

type Props = {
    activities: { data: Activity[] };
};

export default function TimelineIndex({ activities }: Props) {
    return (
        <AppLayout title="Timeline">
            <div className="space-y-3">
                {activities.data.map((a) => (
                    <div key={a.id} className="rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm">{a.description}</p>
                        <p className="mt-2 text-xs text-slate-500">
                            {a.causer?.name ?? 'System'}
                            {a.subject_type ? ` · ${a.subject_type}` : ''}
                            {a.created_at ? ` · ${new Date(a.created_at).toLocaleString()}` : ''}
                        </p>
                    </div>
                ))}
                {activities.data.length === 0 && <p className="text-sm text-slate-500">No activity yet.</p>}
            </div>
        </AppLayout>
    );
}
