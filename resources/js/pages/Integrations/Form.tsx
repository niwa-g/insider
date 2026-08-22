import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../layouts/AppLayout';

type Props = {
    integration: Record<string, unknown> | null;
    providers: Record<string, string>;
    categories: Record<string, string>;
    hasCredentials: boolean;
};

export default function IntegrationForm({ integration, providers, categories, hasCredentials }: Props) {
    const isEdit = Boolean(integration?.id);
    const config = (integration?.config as Record<string, string>) ?? {};

    const { data, setData, post, put, processing } = useForm({
        name: (integration?.name as string) ?? '',
        provider: (integration?.provider as string) ?? 'aws',
        category: (integration?.category as string) ?? 'cloud',
        is_enabled: integration?.is_enabled !== false,
        config: {
            region: config.region ?? '',
            account_id: config.account_id ?? '',
            base_url: config.base_url ?? '',
            org: config.org ?? '',
            login: config.login ?? '',
            zone_id: config.zone_id ?? '',
        },
        credentials: {
            access_key: '',
            secret_key: '',
            token: '',
            api_key: '',
            api_secret: '',
        },
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/integrations/${integration?.id}`);
        else post('/integrations');
    }

    return (
        <AppLayout title={isEdit ? 'Edit integration' : 'New integration'}>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4 md:grid-cols-2">
                <label className="text-sm md:col-span-2">
                    Name
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                </label>
                <label className="text-sm">
                    Provider
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.provider} onChange={(e) => setData('provider', e.target.value)}>
                        {Object.entries(providers).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="text-sm">
                    Category
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.category} onChange={(e) => setData('category', e.target.value)}>
                        {Object.entries(categories).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>

                <fieldset className="md:col-span-2 rounded-xl border p-4 dark:border-slate-800">
                    <legend className="px-1 text-sm font-medium">Config (non-secret)</legend>
                    <div className="mt-2 grid gap-3 md:grid-cols-2">
                        <label className="text-sm">Region<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.config.region} onChange={(e) => setData('config', { ...data.config, region: e.target.value })} /></label>
                        <label className="text-sm">Account ID<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.config.account_id} onChange={(e) => setData('config', { ...data.config, account_id: e.target.value })} /></label>
                        <label className="text-sm md:col-span-2">Base URL<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.config.base_url} onChange={(e) => setData('config', { ...data.config, base_url: e.target.value })} /></label>
                        <label className="text-sm">Org / Login<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.config.org || data.config.login} onChange={(e) => setData('config', { ...data.config, org: e.target.value, login: e.target.value })} /></label>
                        <label className="text-sm">Zone ID<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.config.zone_id} onChange={(e) => setData('config', { ...data.config, zone_id: e.target.value })} /></label>
                    </div>
                </fieldset>

                <fieldset className="md:col-span-2 rounded-xl border p-4 dark:border-slate-800">
                    <legend className="px-1 text-sm font-medium">Credentials (write-only)</legend>
                    {hasCredentials && <p className="mb-2 text-xs text-slate-500">Credentials on file. Leave blank to keep existing values.</p>}
                    <div className="mt-2 grid gap-3 md:grid-cols-2">
                        <label className="text-sm">Access key<input type="password" autoComplete="new-password" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.credentials.access_key} onChange={(e) => setData('credentials', { ...data.credentials, access_key: e.target.value })} /></label>
                        <label className="text-sm">Secret key<input type="password" autoComplete="new-password" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.credentials.secret_key} onChange={(e) => setData('credentials', { ...data.credentials, secret_key: e.target.value })} /></label>
                        <label className="text-sm md:col-span-2">Token / API key<input type="password" autoComplete="new-password" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.credentials.token || data.credentials.api_key} onChange={(e) => setData('credentials', { ...data.credentials, token: e.target.value, api_key: e.target.value })} /></label>
                    </div>
                </fieldset>

                <label className="flex items-center gap-2 text-sm md:col-span-2">
                    <input type="checkbox" checked={data.is_enabled} onChange={(e) => setData('is_enabled', e.target.checked)} />
                    Enabled
                </label>

                <div className="flex gap-2 md:col-span-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button>
                    <Link href={isEdit ? `/integrations/${integration?.id}` : '/integrations'} className="px-4 py-2 text-sm">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
