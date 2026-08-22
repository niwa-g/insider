import { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/user/confirm-password');
    }

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4 dark:bg-slate-950">
            <form
                onSubmit={submit}
                className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <h1 className="text-2xl font-semibold tracking-tight">Confirm password</h1>
                <p className="mt-2 text-sm text-slate-500">This is a secure area. Please confirm your password.</p>

                <label className="mt-6 block text-sm font-medium">
                    Password
                    <input
                        type="password"
                        className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        autoComplete="current-password"
                    />
                    {errors.password && (
                        <span className="mt-1 block text-sm text-red-600">{errors.password}</span>
                    )}
                </label>

                <button
                    type="submit"
                    disabled={processing}
                    className="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                >
                    {processing ? 'Confirming…' : 'Confirm'}
                </button>
            </form>
        </div>
    );
}
