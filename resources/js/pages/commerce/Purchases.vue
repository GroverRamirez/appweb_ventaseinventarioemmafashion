<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/purchases';
import type { Team, TeamPermissions } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Plus, ShoppingBag, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

type ProductOption = {
    id: number;
    sku: string;
    name: string;
    stock: number;
    purchase_price: string;
    sale_price: string;
};

type SupplierOption = {
    id: number;
    name: string;
};

type Purchase = {
    id: number;
    purchased_at: string;
    supplier: string | null;
    user: string;
    total: string;
    items: {
        product: string;
        quantity: number;
        unit_cost: string;
        subtotal: string;
    }[];
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    products: ProductOption[];
    suppliers: SupplierOption[];
    purchases: Paginated<Purchase>;
}>();

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team);
const permissions = computed(
    () => (page.props.permissions as TeamPermissions | null) ?? null,
);
const canCreate = computed(() => !!permissions.value?.canCreatePurchase);

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    supplier_id: '',
    purchased_at: today,
    notes: '',
    items: [{ product_id: '', quantity: 1, unit_cost: '0' }],
});

const total = computed(() =>
    form.items.reduce(
        (sum, item) =>
            sum + Number(item.quantity || 0) * Number(item.unit_cost || 0),
        0,
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
                title: 'Compras',
                href: layoutProps.currentTeam
                    ? index(layoutProps.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});

function addItem() {
    form.items.push({ product_id: '', quantity: 1, unit_cost: '0' });
}

function removeItem(index: number) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function submit() {
    form.submit(store(currentTeam.value.slug), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.items = [{ product_id: '', quantity: 1, unit_cost: '0' }];
            form.purchased_at = today;
        },
    });
}
</script>

<template>
    <Head title="Compras" />

    <div class="workspace-page">
        <div class="page-hero">
            <div class="page-kicker">
                <ShoppingBag class="h-3 w-3" />
                Entrada de mercadería
            </div>
            <h1 class="page-title mt-3">
                <span class="page-title-gradient">Compras</span>
            </h1>
            <p class="page-subtitle">
                Ingresos de mercadería con actualización automática de stock.
            </p>
        </div>

        <form
            v-if="canCreate"
            class="surface-panel p-5"
            @submit.prevent="submit"
        >
            <div class="form-grid">
                <div class="grid gap-2">
                    <Label for="supplier_id">Proveedor</Label>
                    <select
                        id="supplier_id"
                        v-model="form.supplier_id"
                        class="field-select"
                    >
                        <option value="">Sin proveedor</option>
                        <option
                            v-for="supplier in suppliers"
                            :key="supplier.id"
                            :value="String(supplier.id)"
                        >
                            {{ supplier.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.supplier_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="purchased_at">Fecha</Label>
                    <Input
                        id="purchased_at"
                        v-model="form.purchased_at"
                        type="date"
                    />
                    <InputError :message="form.errors.purchased_at" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="notes">Notas</Label>
                    <Input id="notes" v-model="form.notes" />
                    <InputError :message="form.errors.notes" />
                </div>
            </div>

            <div class="mt-5 space-y-3">
                <div
                    v-for="(item, itemIndex) in form.items"
                    :key="itemIndex"
                    class="surface-panel-muted grid gap-3 p-3 md:grid-cols-[1fr_120px_150px_44px]"
                >
                    <div class="grid gap-2">
                        <Label :for="`purchase-product-${itemIndex}`"
                            >Producto</Label
                        >
                        <select
                            :id="`purchase-product-${itemIndex}`"
                            v-model="item.product_id"
                            class="field-select"
                        >
                            <option value="">Seleccione</option>
                            <option
                                v-for="product in products"
                                :key="product.id"
                                :value="String(product.id)"
                            >
                                {{ product.sku }} - {{ product.name }} ({{
                                    product.stock
                                }})
                            </option>
                        </select>
                        <InputError
                            :message="
                                form.errors[`items.${itemIndex}.product_id`]
                            "
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`purchase-quantity-${itemIndex}`"
                            >Cantidad</Label
                        >
                        <Input
                            :id="`purchase-quantity-${itemIndex}`"
                            v-model="item.quantity"
                            min="1"
                            type="number"
                        />
                        <InputError
                            :message="
                                form.errors[`items.${itemIndex}.quantity`]
                            "
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`purchase-cost-${itemIndex}`">Costo</Label>
                        <Input
                            :id="`purchase-cost-${itemIndex}`"
                            v-model="item.unit_cost"
                            min="0.01"
                            step="0.01"
                            type="number"
                        />
                        <InputError
                            :message="
                                form.errors[`items.${itemIndex}.unit_cost`]
                            "
                        />
                    </div>

                    <div class="flex items-end justify-end">
                        <Button
                            size="sm"
                            type="button"
                            variant="ghost"
                            @click="removeItem(itemIndex)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <div class="action-strip mt-4">
                <Button type="button" variant="secondary" @click="addItem">
                    <Plus class="h-4 w-4" />
                    Agregar item
                </Button>

                <div class="flex items-center justify-end gap-4">
                    <span
                        class="status-pill border-chart-3/25 bg-chart-3/10 text-chart-3"
                    >
                        Total Bs {{ total.toFixed(2) }}
                    </span>
                    <Button
                        :disabled="form.processing"
                        type="submit"
                        class="btn-gradient rounded-full border-0"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        Registrar compra
                    </Button>
                </div>
            </div>
        </form>

        <div class="surface-panel overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Detalle</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="purchase in purchases.data" :key="purchase.id">
                        <td class="font-medium">{{ purchase.purchased_at }}</td>
                        <td>
                            {{ purchase.supplier ?? '-' }}
                        </td>
                        <td class="text-muted-foreground">
                            <span
                                v-for="item in purchase.items"
                                :key="item.product"
                                class="mr-2"
                            >
                                {{ item.product }} x{{ item.quantity }}
                            </span>
                        </td>
                        <td class="text-right font-medium">
                            Bs {{ purchase.total }}
                        </td>
                    </tr>
                    <tr v-if="purchases.data.length === 0">
                        <td
                            class="px-4 py-8 text-center text-muted-foreground"
                            colspan="4"
                        >
                            Sin compras registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="link in purchases.links"
                :key="link.label"
                :disabled="!link.url"
                :variant="link.active ? 'default' : 'secondary'"
                size="sm"
                as-child
            >
                <Link v-if="link.url" :href="link.url" v-html="link.label" />
                <span v-else v-html="link.label" />
            </Button>
        </div>
    </div>
</template>
