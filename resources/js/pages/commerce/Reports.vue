<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index } from '@/routes/reports';
import type { Team } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ChartColumn,
    PackageCheck,
    ShoppingBag,
    ShoppingCart,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Summary = {
    salesTotal: string;
    salesCount: number;
    purchasesTotal: string;
    purchasesCount: number;
    inventoryValue: string;
    lowStockCount: number;
};

type DailySale = {
    date: string;
    total: string;
    count: number;
};

type TopProduct = {
    product: string;
    sku: string;
    quantity: number;
    total: string;
};

type LowStockProduct = {
    id: number;
    sku: string;
    name: string;
    category: string | null;
    stock: number;
    minimum_stock: number;
};

type SupplierPurchase = {
    supplier: string;
    purchases: number;
    total: string;
};

const props = defineProps<{
    filters: {
        from: string;
        to: string;
    };
    summary: Summary;
    dailySales: DailySale[];
    topProducts: TopProduct[];
    lowStockProducts: LowStockProduct[];
    supplierPurchases: SupplierPurchase[];
}>();

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team);
const from = ref(props.filters.from);
const to = ref(props.filters.to);

const maxDailySales = computed(() =>
    Math.max(...props.dailySales.map((day) => Number(day.total)), 1),
);
const maxTopProducts = computed(() =>
    Math.max(...props.topProducts.map((product) => product.quantity), 1),
);
const maxSupplierPurchases = computed(() =>
    Math.max(
        ...props.supplierPurchases.map((supplier) => Number(supplier.total)),
        1,
    ),
);

defineOptions({
    inheritAttrs: false,
    layout: (layoutProps: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: layoutProps.currentTeam
                    ? dashboard(layoutProps.currentTeam.slug)
                    : '/',
            },
            {
                title: 'Reportes',
                href: layoutProps.currentTeam
                    ? index(layoutProps.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});

function applyFilters() {
    router.get(
        index(currentTeam.value.slug).url,
        { from: from.value, to: to.value },
        { preserveScroll: true, preserveState: true },
    );
}

function money(value: string | number) {
    return `Bs ${Number(value || 0).toFixed(2)}`;
}
</script>

<template>
    <div class="workspace-page">
        <Head title="Reportes" />

        <div class="page-hero">
            <div
                class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <div class="page-kicker">
                        <ChartColumn class="h-3 w-3" />
                        Analítica comercial
                    </div>
                    <h1 class="page-title mt-3">
                        <span class="page-title-gradient">Reportes</span>
                    </h1>
                    <p class="page-subtitle">
                        Lectura rápida de ventas, compras, inventario crítico y
                        productos con mayor rotación.
                    </p>
                </div>

                <form
                    class="grid gap-3 sm:grid-cols-[150px_150px_auto]"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-2">
                        <Label for="from">Desde</Label>
                        <Input id="from" v-model="from" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="to">Hasta</Label>
                        <Input id="to" v-model="to" type="date" />
                    </div>
                    <div class="flex items-end">
                        <Button
                            class="btn-gradient w-full rounded-full border-0"
                            type="submit"
                        >
                            <ChartColumn class="h-4 w-4" />
                            Aplicar
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="metric-card metric-card-success">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Ventas
                    </span>
                    <div class="metric-icon metric-icon-success">
                        <ShoppingCart class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-bold">
                    {{ money(summary.salesTotal) }}
                </div>
                <div class="mt-1 text-sm text-muted-foreground">
                    {{ summary.salesCount }} operaciones
                </div>
            </div>

            <div class="metric-card metric-card-accent">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Compras
                    </span>
                    <div class="metric-icon metric-icon-accent">
                        <ShoppingBag class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-bold">
                    {{ money(summary.purchasesTotal) }}
                </div>
                <div class="mt-1 text-sm text-muted-foreground">
                    {{ summary.purchasesCount }} ingresos
                </div>
            </div>

            <div class="metric-card">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Valor inventario
                    </span>
                    <div class="metric-icon">
                        <PackageCheck class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-semibold">
                    {{ money(summary.inventoryValue) }}
                </div>
                <div class="mt-1 text-sm text-muted-foreground">
                    Costo estimado en stock
                </div>
            </div>

            <div class="metric-card metric-card-warm">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Stock bajo
                    </span>
                    <div class="metric-icon metric-icon-danger">
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-bold text-destructive">
                    {{ summary.lowStockCount }}
                </div>
                <div class="mt-1 text-sm text-muted-foreground">
                    Prendas requieren reposición
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="surface-panel overflow-hidden">
                <div class="border-b border-border/70 px-5 py-4">
                    <h2 class="font-medium">Ventas por dia</h2>
                    <p class="text-sm text-muted-foreground">
                        Evolucion diaria en el periodo seleccionado.
                    </p>
                </div>

                <div class="space-y-3 p-5">
                    <div
                        v-for="day in dailySales"
                        :key="day.date"
                        class="grid gap-2 md:grid-cols-[120px_1fr_120px]"
                    >
                        <div class="text-sm font-medium">{{ day.date }}</div>
                        <div class="h-8 overflow-hidden rounded-full bg-muted/60">
                            <div
                                class="h-full rounded-full transition-all"
                                :style="{
                                    width: `${Math.max((Number(day.total) / maxDailySales) * 100, 4)}%`,
                                    background: 'var(--grad-brand)',
                                }"
                            ></div>
                        </div>
                        <div class="text-right text-sm font-medium">
                            {{ money(day.total) }}
                        </div>
                    </div>

                    <p
                        v-if="dailySales.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        Sin ventas en el periodo.
                    </p>
                </div>
            </section>

            <section class="surface-panel overflow-hidden">
                <div class="border-b border-border/70 px-5 py-4">
                    <h2 class="font-medium">Productos mas vendidos</h2>
                    <p class="text-sm text-muted-foreground">
                        Ranking por cantidad vendida.
                    </p>
                </div>

                <div class="space-y-4 p-5">
                    <div
                        v-for="product in topProducts"
                        :key="product.sku"
                        class="space-y-2"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-medium">
                                    {{ product.product }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ product.sku }}
                                </div>
                            </div>
                            <span
                                class="status-pill border-chart-2/25 bg-chart-2/10 text-chart-2"
                            >
                                {{ product.quantity }} u.
                            </span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-chart-2"
                                :style="{
                                    width: `${Math.max((product.quantity / maxTopProducts) * 100, 5)}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <p
                        v-if="topProducts.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        Sin productos vendidos en el periodo.
                    </p>
                </div>
            </section>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="surface-panel overflow-hidden">
                <div
                    class="flex items-center gap-2 border-b border-border/70 px-5 py-4"
                >
                    <Truck class="h-4 w-4 text-muted-foreground" />
                    <h2 class="font-medium">Compras por proveedor</h2>
                </div>

                <div class="space-y-4 p-5">
                    <div
                        v-for="supplier in supplierPurchases"
                        :key="supplier.supplier"
                        class="space-y-2"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-medium">
                                    {{ supplier.supplier }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ supplier.purchases }} compras
                                </div>
                            </div>
                            <span class="text-sm font-medium">
                                {{ money(supplier.total) }}
                            </span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-chart-3"
                                :style="{
                                    width: `${Math.max((Number(supplier.total) / maxSupplierPurchases) * 100, 5)}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <p
                        v-if="supplierPurchases.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        Sin compras en el periodo.
                    </p>
                </div>
            </section>

            <section class="surface-panel overflow-hidden">
                <div class="border-b border-border/70 px-5 py-4">
                    <h2 class="font-medium">Inventario critico</h2>
                    <p class="text-sm text-muted-foreground">
                        Productos cuyo stock llego al minimo.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Producto</th>
                                <th class="text-right">Stock</th>
                                <th class="text-right">Minimo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="product in lowStockProducts"
                                :key="product.id"
                            >
                                <td class="font-medium">{{ product.sku }}</td>
                                <td>
                                    <div>{{ product.name }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{
                                            product.category ?? 'Sin categoria'
                                        }}
                                    </div>
                                </td>
                                <td class="text-right">
                                    <span
                                        class="status-pill border-destructive/25 bg-destructive/10 text-destructive"
                                    >
                                        {{ product.stock }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    {{ product.minimum_stock }}
                                </td>
                            </tr>

                            <tr v-if="lowStockProducts.length === 0">
                                <td
                                    class="px-4 py-8 text-center text-muted-foreground"
                                    colspan="4"
                                >
                                    Sin productos en stock bajo.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
