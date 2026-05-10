import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="sticky top-0 z-20 px-3 pt-3 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:pt-2 md:px-4">
            <div className="flex h-16 items-center gap-3 rounded-[22px] border border-white/60 bg-white/76 px-4 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur-xl transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-14">
                <SidebarTrigger className="rounded-xl border border-sky-200/70 bg-white/85 text-slate-700 shadow-sm hover:bg-sky-50 hover:text-sky-700" />
            </div>
        </header>
    );
}
