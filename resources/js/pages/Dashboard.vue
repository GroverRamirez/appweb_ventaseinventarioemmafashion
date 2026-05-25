<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import { index as salesIndex } from '@/routes/sales';
import type { Team, TeamPermissions } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUpRight,
    Package,
    ShoppingBag,
    ShoppingCart,
    Sparkles,
    TrendingUp,
} from 'lucide-vue-next';
import { computed } from 'vue';

type Metrics = {
    products: number;
    lowStock: number;
    todaySales: string;
    monthSales: string;
};

type LowStockProduct = {
    id: number;
    sku: string;
    name: string;
    category: string | null;
    stock: number;
    minimum_stock: number;
};

type RecentSale = {
    id: number;
    sold_at: string;
    total: string;
    user: string;
    items: {
        product: string;
        quantity: number;
    }[];
};

defineProps<{
    metrics: Metrics;
    lowStockProducts: LowStockProduct[];
    recentSales: RecentSale[];
}>();

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team | null);
const permissions = computed(
    () => (page.props.permissions as TeamPermissions | null) ?? null,
);
const canViewProduct = computed(() => !!permissions.value?.canViewProduct);
const canCreateSale = computed(() => !!permissions.value?.canCreateSale);

defineOptions({
    inheritAttrs: false,
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam
                    ? dashboard(props.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="workspace-page">
        <!-- HERO -->
        <div class="page-hero animate-fade-up">
            <div
                class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <div class="page-kicker">
                        <Sparkles class="h-3 w-3" />
                        Panel operativo
                    </div>
                    <h1 class="page-title mt-3">
                        Bienvenida a
                        <span class="page-title-gradient">Emma Fashion</span>
                    </h1>
                    <p class="page-subtitle">
                        Control centralizado de prendas, compras, ventas,
                        alertas de inventario y cierre diario — todo en un
                        vistazo.
                    </p>
                </div>

                <div v-if="currentTeam" class="flex flex-wrap gap-2">
                    <Button
                        v-if="canViewProduct"
                        as-child
                        variant="secondary"
                        class="rounded-full"
                    >
                        <Link :href="productsIndex(currentTeam.slug)">
                            <Package class="h-4 w-4" />
                            Productos
                        </Link>
                    </Button>
                    <Button
                        v-if="canCreateSale"
                        as-child
                        class="btn-gradient rounded-full border-0"
                    >
                        <Link :href="salesIndex(currentTeam.slug)">
                            <ShoppingCart class="h-4 w-4" />
                            Nueva venta
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <!-- METRICS -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="metric-card animate-fade-up-delay-1">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            Productos
                        </span>
                        <div class="mt-3 text-4xl font-bold tracking-tight">
                            {{ metrics.products }}
                        </div>
                        <div class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            <TrendingUp class="h-3.5 w-3.5" />
                            En inventario
                        </div>
                    </div>
                    <div class="metric-icon">
                        <Package class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="metric-card metric-card-warm animate-fade-up-delay-1">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            Stock bajo
                        </span>
                        <div class="mt-3 text-4xl font-bold tracking-tight text-destructive">
                            {{ metrics.lowStock }}
                        </div>
                        <div class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-amber-600 dark:text-amber-400">
                            <AlertTriangle class="h-3.5 w-3.5" />
                            Requieren reposición
                        </div>
                    </div>
                    <div class="metric-icon metric-icon-danger">
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="metric-card metric-card-success animate-fade-up-delay-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            Ventas hoy
                        </span>
                        <div class="mt-3 text-4xl font-bold tracking-tight">
                            <span class="text-base font-semibold text-muted-foreground">Bs </span>{{ metrics.todaySales }}
                        </div>
                        <div class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            <ArrowUpRight class="h-3.5 w-3.5" />
                            Cierre del día
                        </div>
                    </div>
                    <div class="metric-icon metric-icon-success">
                        <ShoppingCart class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="metric-card metric-card-accent animate-fade-up-delay-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            Ventas mes
                        </span>
                        <div class="mt-3 text-4xl font-bold tracking-tight">
                            <span class="text-base font-semibold text-muted-foreground">Bs </span>{{ metrics.monthSales }}
                        </div>
                        <div class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-cyan-600 dark:text-cyan-400">
                            <TrendingUp class="h-3.5 w-3.5" />
                            Acumulado mensual
                        </div>
                    </div>
                    <div class="metric-icon metric-icon-accent">
                        <ShoppingBag class="h-5 w-5" />
                    </div>
                </div>
            </div>
        </div>

        <!-- TWO PANELS -->
        <div class="grid gap-5 lg:grid-cols-2">
            <!-- STOCK ALERTS -->
            <section class="surface-panel overflow-hidden animate-fade-up-delay-3">
                <div
                    class="flex items-center justify-between border-b border-border/50 px-6 py-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-white"
                            style="background: linear-gradient(135deg, hsl(354 84% 60%), hsl(15 90% 60%))"
                        >
                            <AlertTriangle class="h-4 w-4" />
                        </div>
                        <h2 class="text-base font-semibold">Alertas de stock</h2>
                    </div>
                    <Button
                        v-if="currentTeam"
                        as-child
                        size="sm"
                        variant="ghost"
                        class="rounded-full"
                    >
                        <Link :href="productsIndex(currentTeam.slug)">
                            Ver inventario
                            <ArrowUpRight class="ml-1 h-3.5 w-3.5" />
                        </Link>
                    </Button>
                </div>

                <div class="divide-y divide-border/40">
                    <div
                        v-for="product in lowStockProducts"
                        :key="product.id"
                        class="flex items-center justify-between gap-4 px-6 py-4 transition-all hover:bg-gradient-to-r hover:from-destructive/5 hover:to-amber-500/5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-destructive/10 text-destructive"
                            >
                                <Package class="h-4 w-4" />
                            </div>
                            <div>
                                <div class="font-semibold">{{ product.name }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ product.sku }} ·
                                    {{ product.category ?? 'Sin categoría' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-destructive">
                                {{ product.stock }}
                            </div>
                            <div class="text-[10px] uppercase tracking-wider text-muted-foreground">
                                Min. {{ product.minimum_stock }}
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="lowStockProducts.length === 0"
                        class="px-6 py-12 text-center text-sm text-muted-foreground"
                    >
                        Sin productos en stock bajo.
                    </p>
                </div>
            </section>

            <!-- RECENT SALES -->
            <section class="surface-panel overflow-hidden animate-fade-up-delay-3">
                <div
                    class="flex items-center justify-between border-b border-border/50 px-6 py-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-white"
                            style="background: var(--grad-success)"
                        >
                            <ShoppingCart class="h-4 w-4" />
                        </div>
                        <h2 class="text-base font-semibold">Ventas recientes</h2>
                    </div>
                    <Button
                        v-if="currentTeam"
                        as-child
                        size="sm"
                        variant="ghost"
                        class="rounded-full"
                    >
                        <Link :href="salesIndex(currentTeam.slug)">
                            Ver ventas
                            <ArrowUpRight class="ml-1 h-3.5 w-3.5" />
                        </Link>
                    </Button>
                </div>

                <div class="divide-y divide-border/40">
                    <div
                        v-for="sale in recentSales"
                        :key="sale.id"
                        class="flex items-start justify-between gap-4 px-6 py-4 transition-all hover:bg-gradient-to-r hover:from-emerald-500/5 hover:to-cyan-500/5"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <div class="text-lg font-bold text-gradient">
                                    Bs {{ sale.total }}
                                </div>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ sale.sold_at }} · {{ sale.user }}
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="item in sale.items"
                                    :key="item.product"
                                    class="status-pill status-pill-primary"
                                >
                                    {{ item.product }} ×{{ item.quantity }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="recentSales.length === 0"
                        class="px-6 py-12 text-center text-sm text-muted-foreground"
                    >
                        Sin ventas registradas.
                    </p>
                </div>
            </section>
        </div>
    </div>
</template>
