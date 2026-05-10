import { Link } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { BriefcaseBusiness, Building, Building2, ClipboardCheck, ClipboardList, FileBarChart2, HeartPulse, Hospital, KeyRound, LayoutGrid, MapPinned, Megaphone, Pill, ShieldCheck, UserRound, Users, Wrench } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { Auth, NavItem } from '@/types';

type SidebarNavItem = NavItem & {
    permission: string;
};

const navigationItems: SidebarNavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        permission: 'dashboard.view',
    },
    {
        title: 'Pasien',
        href: '/pasiens',
        icon: UserRound,
        permission: 'pasiens.view',
    },
    {
        title: 'Tindak Lanjut',
        href: '/tindak-lanjut',
        icon: ClipboardCheck,
        permission: 'tindak-lanjut.view',
    },
    {
        title: 'Pengambilan Obat',
        href: '/medication-pickups',
        icon: Pill,
        permission: 'medication-pickups.view',
    },
];

const managementItems: SidebarNavItem[] = [
    {
        title: 'Users',
        href: '/users',
        icon: Users,
        permission: 'users.view',
    },
    {
        title: 'Role',
        href: '/roles',
        icon: ShieldCheck,
        permission: 'roles.view',
    },
    {
        title: 'Permission',
        href: '/permissions',
        icon: KeyRound,
        permission: 'permissions.view',
    },
];

const laporanItems: SidebarNavItem[] = [
    {
        title: 'Laporan',
        href: '/laporan',
        icon: FileBarChart2,
        permission: 'laporan.view',
    },
];

const kegiatanItems: SidebarNavItem[] = [
    {
        title: 'Kegiatan Penyuluhan',
        href: '/kegiatan-penyuluhan',
        icon: Megaphone,
        permission: 'kegiatan-penyuluhan.view',
    },
];

const masterDataItems: SidebarNavItem[] = [
    {
        title: 'OPD',
        href: '/opds',
        icon: Building2,
        permission: 'opds.view',
    },
    {
        title: 'Puskesmas',
        href: '/puskesmas',
        icon: HeartPulse,
        permission: 'puskesmas.view',
    },
    {
        title: 'Faskes',
        href: '/faskes',
        icon: Hospital,
        permission: 'faskes.view',
    },
    {
        title: 'Kecamatan',
        href: '/kecamatans',
        icon: Building,
        permission: 'kecamatans.view',
    },
    {
        title: 'Kelurahan',
        href: '/kelurahans',
        icon: MapPinned,
        permission: 'kelurahans.view',
    },
    {
        title: 'Pekerjaan',
        href: '/pekerjaan',
        icon: BriefcaseBusiness,
        permission: 'pekerjaan.view',
    },
    {
        title: 'Jenis Penanganan',
        href: '/jenis-penanganans',
        icon: ClipboardList,
        permission: 'jenis-penanganans.view',
    },
    {
        title: 'Jenis Kebutuhan',
        href: '/jenis-kebutuhans',
        icon: Wrench,
        permission: 'jenis-kebutuhans.view',
    },
];

export function AppSidebar() {
    const { props } = usePage<{ auth: Auth }>();
    const permissions = props.auth?.permissions ?? [];

    const canAccess = (permission: string) => permissions.includes(permission);

    const navSections = [
        {
            label: 'Navigation',
            items: navigationItems.filter((item) => canAccess(item.permission)),
        },
        {
            label: 'Laporan',
            items: laporanItems.filter((item) => canAccess(item.permission)),
        },
        {
            label: 'Kegiatan',
            items: kegiatanItems.filter((item) => canAccess(item.permission)),
        },
        {
            label: 'Manajemen',
            items: managementItems.filter((item) => canAccess(item.permission)),
        },
        {
            label: 'Data Master',
            items: masterDataItems.filter((item) => canAccess(item.permission)),
        },
    ].filter((section) => section.items.length > 0)
        .map((section) => ({
            ...section,
            items: section.items.map(({ permission, ...item }) => item),
        }));

    return (
        <Sidebar
            collapsible="icon"
            variant="inset"
            className="group-data-[variant=inset]:p-3"
        >
            <SidebarHeader className="pb-0">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="h-14 rounded-2xl border border-sky-100/80 bg-white/82 px-3 shadow-[0_14px_30px_rgba(14,116,144,0.12)] ring-0 backdrop-blur"
                        >
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="gap-3 px-1 pb-2 pt-1">
                <NavMain sections={navSections} />
            </SidebarContent>

            <SidebarFooter className="pt-0">
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

