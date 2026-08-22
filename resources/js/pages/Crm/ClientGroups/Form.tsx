import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    group: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
};

export default function ClientGroupForm({ group, clients }: Props) {
    const isEdit = Boolean(group?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        name: (group?.name as string) ?? '',
        code: (group?.code as string) ?? '',
        color: (group?.color as string) ?? '#2563eb',
        description: (group?.description as string) ?? '',
        client_ids: (group?.client_ids as string[]) ?? [],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) {
            put(`/client-groups/${group?.id}`);
        } else {
            post('/client-groups');
        }
    }

    function toggleClient(id: string) {
        setData(
            'client_ids',
            data.client_ids.includes(id)
                ? data.client_ids.filter((c) => c !== id)
                : [...data.client_ids, id],
        );
    }

    return (
        <AppLayout title={isEdit ? 'Edit group' : 'New group'}>
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4">
                <label className="block text-sm font-medium">
                    Name
                    <input
                        className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && <span className="mt-1 block text-sm text-red-600">{errors.name}</span>}
                </label>
                <div className="grid gap-4 sm:grid-cols-2">
                    <label className="block text-sm font-medium">
                        Code
                        <input
                            className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                        />
                    </label>
                    <label className="block text-sm font-medium">
                        Color
                        <input
                            type="color"
                            className="mt-1 h-10 w-full rounded-lg border border-slate-300 bg-white px-1 dark:border-slate-700 dark:bg-slate-950"
                            value={data.color}
                            onChange={(e) => setData('color', e.target.value)}
                        />
                    </label>
                </div>
                <label className="block text-sm font-medium">
                    Description
                    <textarea
                        className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        rows={3}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </label>

                <div>
                    <p className="mb-2 text-sm font-medium">Clients</p>
                    <div className="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-2 dark:border-slate-800">
                        {clients.map((client) => (
                            <label key={client.id} className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={data.client_ids.includes(client.id)}
                                    onChange={() => toggleClient(client.id)}
                                />
                                {client.company_name}
                            </label>
                        ))}
                    </div>
                </div>

                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        {processing ? 'Saving…' : isEdit ? 'Save changes' : 'Create group'}
                    </button>
                    <Link href="/client-groups" className="rounded-lg px-4 py-2 text-sm text-slate-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
