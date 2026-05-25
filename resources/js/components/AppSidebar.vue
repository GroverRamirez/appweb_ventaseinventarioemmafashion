<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    ChartColumn,
    LayoutGrid,
    Layers,
    Package,
    Shield,
    ShoppingBag,
    ShoppingCart,
    Truck,
    UsersRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
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
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as categoriesIndex } from '@/routes/categories';
import { index as productsIndex } from '@/routes/products';
import { index as purchasesIndex } from '@/routes/purchases';
import { index as reportsIndex } from '@/routes/reports';
import { index as salesIndex } from '@/routes/sales';
import { index as suppliersIndex } from '@/routes/suppliers';
import { edit as teamEdit } from '@/routes/teams';
import type { NavItem, TeamPermissions } from '@/types';

const page = usePage();

const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/',
);

const permissions = computed(
    () => (page.props.permissions as TeamPermissions | null) ?? null,
);

function can(flag: keyof TeamPermissions): boolean {
    return permissions.value ? !!permissions.value[flag] : false;
}

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (can('canViewDashboard')) {
        items.push({
            title: 'Dashboard',
            href: dashboardUrl.value,
            icon: LayoutGrid,
        });
    }

    if (can('canViewProduct')) {
        items.push({
            title: 'Productos',
            href: page.props.currentTeam
                ? productsIndex(page.props.currentTeam.slug).url
                : '/',
            icon: Package,
        });
    }

    if (can('canViewCategory')) {
        items.push({
            title: 'Categorías',
            href: page.props.currentTeam
                ? categoriesIndex(page.props.currentTeam.slug).url
                : '/',
            icon: Layers,
        });
    }

    if (can('canViewSupplier')) {
        items.push({
            title: 'Proveedores',
            href: page.props.currentTeam
                ? suppliersIndex(page.props.currentTeam.slug).url
                : '/',
            icon: Truck,
        });
    }

    if (can('canViewPurchase')) {
        items.push({
            title: 'Compras',
            href: page.props.currentTeam
                ? purchasesIndex(page.props.currentTeam.slug).url
                : '/',
            icon: ShoppingBag,
        });
    }

    if (can('canViewSale')) {
        items.push({
            title: 'Ventas',
            href: page.props.currentTeam
                ? salesIndex(page.props.currentTeam.slug).url
                : '/',
            icon: ShoppingCart,
        });
    }

    if (can('canViewReport')) {
        items.push({
            title: 'Reportes',
            href: page.props.currentTeam
                ? reportsIndex(page.props.currentTeam.slug).url
                : '/',
            icon: ChartColumn,
        });
    }

    // Gestión de usuarios — apunta a la página oficial /settings/teams/{slug}/edit
    // que tiene invitar, cambiar rol, quitar miembros y cancelar invitaciones.
    if (can('canUpdateTeam') && page.props.currentTeam) {
        items.push({
            title: 'Usuarios',
            href: teamEdit(page.props.currentTeam.slug).url,
            icon: UsersRound,
        });
    }

    // Gestión de roles — solo Propietaria, gestiona catálogo de roles y permisos
    if (can('canUpdateTeam') && page.props.currentTeam) {
        items.push({
            title: 'Roles',
            href: rolesIndex(page.props.currentTeam.slug).url,
            icon: Shield,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repositorio',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentación',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-sidebar-border/70"
    >
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
