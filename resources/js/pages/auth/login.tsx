import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

type Props = {
    status?: string;
};

export default function Login({
    status,
}: Props) {
    return (
        <>
            <Head title="Log in" />

            <div className="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-cyan-50 to-sky-100 px-4 py-8">
                <div className="pointer-events-none absolute -top-28 -left-24 h-72 w-72 rounded-full bg-cyan-200/40 blur-3xl" />
                <div className="pointer-events-none absolute -right-24 bottom-0 h-72 w-72 rounded-full bg-sky-300/30 blur-3xl" />

                <div className="relative w-full max-w-md rounded-2xl border border-white/80 bg-white/90 p-6 shadow-[0_18px_44px_rgba(2,132,199,0.18)] backdrop-blur">
                    <div className="mb-6 text-center">
                        <div className="mx-auto flex h-20 items-center justify-center p-2">
                                <img
                                    src="/images/logo-rotansemar.png"
                                    alt="Logo Rotan Semar"
                                    className="h-full w-full object-contain"
                                />
                        </div>

                        <h1 className="mt-4 text-3xl font-bold text-slate-900">
                            Selamat Datang
                        </h1>

                        <p className="mt-1 text-sm text-slate-600">
                            Silahkan Login Menggunakan Akun Anda
                        </p>
                    </div>

                    <Form {...store.form()} resetOnSuccess={['password']} className="space-y-4">
                        {({ processing, errors }) => (
                            <>
                                {status && (
                                    <div className="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                                        {status}
                                    </div>
                                )}

                                <div>
                                    <Label htmlFor="email" className="block text-sm font-semibold text-slate-700">Username</Label>
                                    <Input id="email" type="email" name="email" required autoFocus autoComplete="email" placeholder="Username" className="mt-2 block h-10 w-full rounded-lg border border-slate-200 bg-white/90 px-4 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:border-sky-400 focus-visible:ring-sky-200" />
                                    {errors.email && <InputError message={errors.email} />}
                                </div>

                                <div>
                                    <div className="mb-2 flex items-center justify-between">
                                        <Label htmlFor="password" className="block text-sm font-semibold text-slate-700">Password</Label>
                                    </div>
                                    <PasswordInput id="password" name="password" required autoComplete="current-password" placeholder="••••••••" className="block h-10 w-full rounded-lg border border-slate-200 bg-white/90 px-4 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:border-sky-400 focus-visible:ring-sky-200" />
                                    {errors.password && <InputError message={errors.password} />}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox id="remember" name="remember" className="h-4 w-4 rounded border-slate-300 text-sky-600 focus-visible:ring-sky-300" />
                                    <Label htmlFor="remember" className="cursor-pointer text-sm text-slate-600">Ingat saya</Label>
                                </div>

                                <Button type="submit" disabled={processing} className="h-10 w-full bg-linear-to-r from-cyan-500 to-sky-500 text-sm font-semibold text-white shadow-md shadow-cyan-200 transition hover:from-cyan-600 hover:to-sky-600">
                                    {processing ? (
                                        <span className="flex items-center justify-center gap-2"><Spinner className="h-4 w-4" />Sedang masuk...</span>
                                    ) : (
                                        'Masuk'
                                    )}
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}

Login.layout = null;
