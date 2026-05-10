import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Mail } from 'lucide-react';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Forgot password" />

            <div className="relative min-h-screen w-full bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 overflow-hidden">
                {/* Animated background elements */}
                <div className="absolute inset-0 overflow-hidden">
                    <div className="absolute -top-40 -right-40 h-80 w-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                    <div className="absolute -bottom-40 -left-40 h-80 w-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                    <div className="absolute top-1/2 left-1/2 h-80 w-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
                </div>

                <div className="relative z-10 flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
                    <div className="w-full max-w-md space-y-8">
                        {/* Logo & Header */}
                        <div className="text-center">
                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg">
                                <Mail className="h-8 w-8 text-white" />
                            </div>
                            <h1 className="mt-6 text-3xl font-bold tracking-tight text-gray-900">
                                Lupa Password?
                            </h1>
                            <p className="mt-2 text-sm text-gray-600">
                                Tidak masalah. Beritahu kami alamat email Anda dan kami akan mengirimkan link untuk mengatur ulang password.
                            </p>
                        </div>

                        {/* Form Card */}
                        <div className="rounded-2xl bg-white p-8 shadow-xl backdrop-blur-sm">
                            {status && (
                                <div className="mb-6 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 border border-emerald-200">
                                    {status}
                                </div>
                            )}

                            <Form {...email.form()} className="space-y-6">
                                {({ processing, errors }) => (
                                    <>
                                        {/* Email Field */}
                                        <div>
                                            <Label htmlFor="email" className="block text-sm font-semibold text-gray-700">
                                                Email
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                autoComplete="email"
                                                autoFocus
                                                required
                                                placeholder="user@example.com"
                                                className="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                            />
                                            {errors.email && <InputError message={errors.email} />}
                                        </div>

                                        {/* Submit Button */}
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 py-3 font-semibold text-white shadow-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200"
                                        >
                                            {processing ? (
                                                <span className="flex items-center justify-center gap-2">
                                                    <Spinner className="h-4 w-4" />
                                                    Mengirim link...
                                                </span>
                                            ) : (
                                                'Kirim Link Reset Password'
                                            )}
                                        </Button>

                                        {/* Login Link */}
                                        <div className="text-center text-sm text-gray-600">
                                            Ingat password Anda?{' '}
                                            <TextLink 
                                                href={login()} 
                                                className="font-semibold text-blue-600 hover:text-blue-700"
                                            >
                                                Kembali ke login
                                            </TextLink>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </div>

                        {/* Footer Info */}
                        <p className="text-center text-xs text-gray-500">
                            © 2024 Rotansemar Admin. Semua hak dilindungi.
                        </p>
                    </div>
                </div>
            </div>

            <style>{`
                @keyframes blob {
                    0%, 100% {
                        transform: translate(0, 0) scale(1);
                    }
                    33% {
                        transform: translate(30px, -50px) scale(1.1);
                    }
                    66% {
                        transform: translate(-20px, 20px) scale(0.9);
                    }
                }
                .animate-blob {
                    animation: blob 7s infinite;
                }
                .animation-delay-2000 {
                    animation-delay: 2s;
                }
                .animation-delay-4000 {
                    animation-delay: 4s;
                }
            `}</style>
        </>
    );
}
