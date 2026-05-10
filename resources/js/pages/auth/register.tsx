import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { UserPlus } from 'lucide-react';

export default function Register() {
    return (
        <>
            <Head title="Register" />

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
                                <UserPlus className="h-8 w-8 text-white" />
                            </div>
                            <h1 className="mt-6 text-3xl font-bold tracking-tight text-gray-900">
                                Buat Akun
                            </h1>
                            <p className="mt-2 text-sm text-gray-600">
                                Daftar untuk mengakses dashboard administrasi
                            </p>
                        </div>

                        {/* Form Card */}
                        <div className="rounded-2xl bg-white p-8 shadow-xl backdrop-blur-sm">
                            <Form
                                action={route('register')}
                                method="post"
                                resetOnSuccess={['password', 'password_confirmation']}
                                disableWhileProcessing
                                className="space-y-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        {/* Name Field */}
                                        <div>
                                            <Label htmlFor="name" className="block text-sm font-semibold text-gray-700">
                                                Nama Lengkap
                                            </Label>
                                            <Input
                                                id="name"
                                                type="text"
                                                required
                                                autoFocus
                                                autoComplete="name"
                                                name="name"
                                                placeholder="John Doe"
                                                className="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                            />
                                            {errors.name && <InputError message={errors.name} />}
                                        </div>

                                        {/* Email Field */}
                                        <div>
                                            <Label htmlFor="email" className="block text-sm font-semibold text-gray-700">
                                                Email
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                required
                                                autoComplete="email"
                                                name="email"
                                                placeholder="user@example.com"
                                                className="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                            />
                                            {errors.email && <InputError message={errors.email} />}
                                        </div>

                                        {/* Password Field */}
                                        <div>
                                            <Label htmlFor="password" className="block text-sm font-semibold text-gray-700">
                                                Password
                                            </Label>
                                            <PasswordInput
                                                id="password"
                                                required
                                                autoComplete="new-password"
                                                name="password"
                                                placeholder="••••••••"
                                                className="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                            />
                                            {errors.password && <InputError message={errors.password} />}
                                        </div>

                                        {/* Confirm Password Field */}
                                        <div>
                                            <Label htmlFor="password_confirmation" className="block text-sm font-semibold text-gray-700">
                                                Konfirmasi Password
                                            </Label>
                                            <PasswordInput
                                                id="password_confirmation"
                                                required
                                                autoComplete="new-password"
                                                name="password_confirmation"
                                                placeholder="••••••••"
                                                className="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                            />
                                            {errors.password_confirmation && (
                                                <InputError message={errors.password_confirmation} />
                                            )}
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
                                                    Sedang membuat akun...
                                                </span>
                                            ) : (
                                                'Daftar'
                                            )}
                                        </Button>

                                        {/* Login Link */}
                                        <div className="text-center text-sm text-gray-600">
                                            Sudah punya akun?{' '}
                                            <TextLink 
                                                href={login()} 
                                                className="font-semibold text-blue-600 hover:text-blue-700"
                                            >
                                                Masuk di sini
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
