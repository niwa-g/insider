import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Organization = {
    id: string;
    name: string;
    legal_name: string | null;
    slug: string;
    website: string | null;
    email: string | null;
    phone: string | null;
    address_line1: string | null;
    address_line2: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    country: string | null;
    tax_id: string | null;
    primary_color: string | null;
    timezone: string;
    currency: string;
    status: string;
    logo_url: string | null;
};

type Props = {
    organization: Organization;
    can: { update: boolean };
};

export default function OrganizationEdit({ organization, can }: Props) {
    const { flash } = usePage<{ flash?: { success?: string } }>().props;
    const [preview, setPreview] = useState<string | null>(organization.logo_url);

    const { data, setData, post, processing, errors } = useForm({
        name: organization.name ?? '',
        legal_name: organization.legal_name ?? '',
        slug: organization.slug ?? '',
        website: organization.website ?? '',
        email: organization.email ?? '',
        phone: organization.phone ?? '',
        address_line1: organization.address_line1 ?? '',
        address_line2: organization.address_line2 ?? '',
        city: organization.city ?? '',
        state: organization.state ?? '',
        postal_code: organization.postal_code ?? '',
        country: organization.country ?? '',
        tax_id: organization.tax_id ?? '',
        primary_color: organization.primary_color ?? '#1a5cff',
        timezone: organization.timezone ?? 'UTC',
        currency: organization.currency ?? 'USD',
        logo: null as File | null,
        remove_logo: false as boolean,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/settings/organization', { forceFormData: true });
    }

    return (
        <AppLayout title="Company profile">
            <div className="mb-6 flex items-center justify-between gap-3">
                <div>
                    <h2 className="text-lg font-semibold">Company profile</h2>
                    <p className="text-sm text-slate-500">Logo, branding, contact details, and locale.</p>
                </div>
                <Link href="/settings" className="text-sm text-slate-500 hover:text-slate-800">
                    Back to settings
                </Link>
            </div>

            {flash?.success && (
                <div className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                    {flash.success}
                </div>
            )}

            <form onSubmit={submit} className="mx-auto max-w-3xl space-y-6">
                <section className="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Branding</h3>
                    <div className="flex flex-wrap items-start gap-6">
                        <div className="flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-950">
                            {preview ? (
                                <img src={preview} alt="Logo" className="h-full w-full object-contain" />
                            ) : (
                                <span className="text-xs text-slate-400">No logo</span>
                            )}
                        </div>
                        <div className="space-y-2">
                            <input
                                type="file"
                                accept="image/*"
                                disabled={!can.update}
                                onChange={(e) => {
                                    const file = e.target.files?.[0] ?? null;
                                    setData('logo', file);
                                    setData('remove_logo', false);
                                    setPreview(file ? URL.createObjectURL(file) : organization.logo_url);
                                }}
                            />
                            {organization.logo_url && (
                                <button
                                    type="button"
                                    disabled={!can.update}
                                    className="block text-sm text-rose-600"
                                    onClick={() => {
                                        setData('logo', null);
                                        setData('remove_logo', true);
                                        setPreview(null);
                                    }}
                                >
                                    Remove logo
                                </button>
                            )}
                            {errors.logo && <p className="text-sm text-rose-600">{errors.logo}</p>}
                        </div>
                    </div>
                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                        <Field label="Primary color" error={errors.primary_color}>
                            <input
                                type="color"
                                className="field h-10"
                                value={data.primary_color || '#1a5cff'}
                                disabled={!can.update}
                                onChange={(e) => setData('primary_color', e.target.value)}
                            />
                        </Field>
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Identity</h3>
                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Company name" error={errors.name}>
                            <input className="field" value={data.name} disabled={!can.update} onChange={(e) => setData('name', e.target.value)} required />
                        </Field>
                        <Field label="Legal name" error={errors.legal_name}>
                            <input className="field" value={data.legal_name} disabled={!can.update} onChange={(e) => setData('legal_name', e.target.value)} />
                        </Field>
                        <Field label="Slug" error={errors.slug}>
                            <input className="field" value={data.slug} disabled={!can.update} onChange={(e) => setData('slug', e.target.value)} required />
                        </Field>
                        <Field label="Tax / registration ID" error={errors.tax_id}>
                            <input className="field" value={data.tax_id} disabled={!can.update} onChange={(e) => setData('tax_id', e.target.value)} />
                        </Field>
                        <Field label="Website" error={errors.website}>
                            <input className="field" value={data.website} disabled={!can.update} onChange={(e) => setData('website', e.target.value)} />
                        </Field>
                        <Field label="Email" error={errors.email}>
                            <input type="email" className="field" value={data.email} disabled={!can.update} onChange={(e) => setData('email', e.target.value)} />
                        </Field>
                        <Field label="Phone" error={errors.phone}>
                            <input className="field" value={data.phone} disabled={!can.update} onChange={(e) => setData('phone', e.target.value)} />
                        </Field>
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Address</h3>
                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Address line 1" error={errors.address_line1}>
                            <input className="field" value={data.address_line1} disabled={!can.update} onChange={(e) => setData('address_line1', e.target.value)} />
                        </Field>
                        <Field label="Address line 2" error={errors.address_line2}>
                            <input className="field" value={data.address_line2} disabled={!can.update} onChange={(e) => setData('address_line2', e.target.value)} />
                        </Field>
                        <Field label="City" error={errors.city}>
                            <input className="field" value={data.city} disabled={!can.update} onChange={(e) => setData('city', e.target.value)} />
                        </Field>
                        <Field label="State / region" error={errors.state}>
                            <input className="field" value={data.state} disabled={!can.update} onChange={(e) => setData('state', e.target.value)} />
                        </Field>
                        <Field label="Postal code" error={errors.postal_code}>
                            <input className="field" value={data.postal_code} disabled={!can.update} onChange={(e) => setData('postal_code', e.target.value)} />
                        </Field>
                        <Field label="Country (ISO-2)" error={errors.country}>
                            <input className="field" value={data.country} disabled={!can.update} maxLength={2} onChange={(e) => setData('country', e.target.value.toUpperCase())} />
                        </Field>
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Locale</h3>
                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Timezone" error={errors.timezone}>
                            <input className="field" value={data.timezone} disabled={!can.update} onChange={(e) => setData('timezone', e.target.value)} required />
                        </Field>
                        <Field label="Currency" error={errors.currency}>
                            <input className="field" value={data.currency} disabled={!can.update} maxLength={3} onChange={(e) => setData('currency', e.target.value.toUpperCase())} required />
                        </Field>
                    </div>
                </section>

                {can.update && (
                    <div className="flex justify-end gap-2">
                        <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            Save company profile
                        </button>
                    </div>
                )}
            </form>
        </AppLayout>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <label className="block text-sm">
            <span className="mb-1 block font-medium text-slate-700 dark:text-slate-300">{label}</span>
            {children}
            {error && <span className="mt-1 block text-rose-600">{error}</span>}
        </label>
    );
}
