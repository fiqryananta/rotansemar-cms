import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { PaginationLink } from '@/types/pagination';

type Props = {
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

function normalizePaginationUrl(url: string): string {
    const fallbackOrigin = 'https://localhost';
    const currentOrigin =
        typeof window !== 'undefined' ? window.location.origin : fallbackOrigin;

    try {
        const parsed = new URL(url, currentOrigin);

        // Always use relative URLs so Inertia follows current page scheme/origin.
        return `${parsed.pathname}${parsed.search}${parsed.hash}`;
    } catch {
        return url;
    }
}

export default function TablePagination({ links, from, to, total }: Props) {
    if (!links || links.length <= 3) {
        return null;
    }

    return (
        <div className="mt-4 flex flex-col gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm text-gray-600">
                Menampilkan <span className="font-medium">{from ?? 0}</span> -{' '}
                <span className="font-medium">{to ?? 0}</span> dari{' '}
                <span className="font-medium">{total}</span> data
            </p>

            <nav className="flex flex-wrap gap-1">
                {links.map((link, index) => {
                    const normalizedUrl = link.url
                        ? normalizePaginationUrl(link.url)
                        : null;
                    const isDisabled = !normalizedUrl;

                    if (isDisabled) {
                        return (
                            <span
                                key={`${link.label}-${index}`}
                                className="rounded-md border border-gray-200 px-3 py-1.5 text-sm text-gray-400"
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        );
                    }

                    return (
                        <Link
                            key={`${link.label}-${index}`}
                            href={normalizedUrl!}
                            preserveScroll
                            preserveState
                            className={cn(
                                'rounded-md border px-3 py-1.5 text-sm transition-colors',
                                link.active
                                    ? 'border-sky-500 bg-sky-50 font-medium text-sky-700'
                                    : 'border-gray-200 text-gray-600 hover:bg-gray-50',
                            )}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    );
                })}
            </nav>
        </div>
    );
}
