import { Link } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';

type Section = {
    name: string;
    description: string;
    href: string;
    permission: string;
};

type Props = {
    sections: Section[];
    can: { users: boolean; teams: boolean; roles: boolean; audit: boolean };
};

export default function SettingsIndex({ sections, can }: Props) {
    const access: Record<string, boolean> = can;

    return (
        <AppLayout title="Settings">
            <p className="mb-6 text-sm text-slate-500">
                Organization administration — users, teams, roles, and audit history.
            </p>

            <div className="grid gap-4 sm:grid-cols-2">
                {sections.map((section) => {
                    const allowed = access[section.permission.split('.')[0] as keyof typeof can] ?? false;
                    if (!allowed) {
                        return null;
                    }

                    return (
                        <Link
                            key={section.href}
                            href={section.href}
                            className="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700"
                        >
                            <h2 className="font-semibold">{section.name}</h2>
                            <p className="mt-1 text-sm text-slate-500">{section.description}</p>
                        </Link>
                    );
                })}
            </div>
        </AppLayout>
    );
}
