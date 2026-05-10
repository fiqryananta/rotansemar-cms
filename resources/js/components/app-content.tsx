import * as React from 'react';
import { SidebarInset } from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import type { AppVariant } from '@/types';

type Props = React.ComponentProps<'main'> & {
    variant?: AppVariant;
};

export function AppContent({ variant = 'sidebar', children, ...props }: Props) {
    if (variant === 'sidebar') {
        return (
            <SidebarInset
                {...props}
                className={cn(
                    'relative overflow-hidden bg-transparent md:rounded-[28px] md:border md:border-white/55 md:bg-white/35 md:shadow-[0_28px_80px_rgba(15,23,42,0.08)]',
                    props.className,
                )}
            >
                {children}
            </SidebarInset>
        );
    }

    return (
        <main
            className="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-5 rounded-[28px]"
            {...props}
        >
            {children}
        </main>
    );
}
