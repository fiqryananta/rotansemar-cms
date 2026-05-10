import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import type { NavItem } from '@/types';

type NavSection = {
    label: string;
    items: NavItem[];
};

export function NavMain({ sections = [] }: { sections: NavSection[] }) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <>
            {sections.map((section) => (
                <SidebarGroup
                    key={section.label}
                    className="rounded-2xl border border-white/65 bg-white/55 px-2.5 py-2 shadow-[0_8px_20px_rgba(15,23,42,0.05)]"
                >
                    <SidebarGroupLabel
                        className={cn(
                            'h-7 px-2 text-[11px] font-semibold tracking-[0.14em] uppercase',
                            section.label === 'Navigation'
                                ? 'text-sky-700/90'
                                : 'text-teal-700/90',
                        )}
                    >
                        {section.label}
                    </SidebarGroupLabel>
                    <SidebarMenu className="gap-1.5">
                        {section.items.map((item) => (
                            <SidebarMenuItem key={item.title}>
                                <SidebarMenuButton
                                    asChild
                                    isActive={isCurrentUrl(item.href)}
                                    tooltip={{ children: item.title }}
                                    className="h-9 rounded-xl px-2.5 text-[13px] font-medium text-slate-600 hover:bg-sky-50/90 hover:text-slate-800 data-[active=true]:bg-linear-to-r data-[active=true]:from-sky-500 data-[active=true]:to-cyan-500 data-[active=true]:text-white data-[active=true]:shadow-[0_10px_18px_rgba(14,116,144,0.34)]"
                                >
                                    <Link href={item.href} prefetch>
                                        {item.icon && (
                                            <item.icon className="size-4" />
                                        )}
                                        <span>{item.title}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        ))}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
