<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/sales';
import type { Team, TeamPermissions } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Plus, ShoppingCart, Sparkles, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

type ProductOption = {
    id: number;
    sku: string;
    name: string;
    stock: number;
    sale_price: string;
};

type Sale = {
    id: number;
    sold_at: string;
    user: string;
    total: string;
    items: {
        product: string;
        quantity: number;
        unit_price: string;
        subtotal: string;
    }[];
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    products: ProductOption[];
    dailyClose: {
        date: string;
        total: string;
        count: number;
    };
    sales: Paginated<Sale>;
}>();

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team);
const permissions = computed(
    () => (page.props.permissions as TeamPermissions | null) ?? null,
);
const canCreate = computed(() => !!permissions.value?.canCreateSale);

const nowForInput = () => new Date().toISOString().slice(0, 16);

const form = useForm({
    sold_at: nowForInput(),
    notes: '',
    items: [{ product_id: '', quantity: 1, unit_price: '0' }],
});

const total = computed(() =>
    form.items.reduce(
        (sum, item) =>
            sum + Number(item.quantity || 0) * Number(item.unit_price || 0),
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
                title: 'Ventas',
                href: layoutProps.currentTeam
                    ? index(layoutProps.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});

function addItem() {
    form.items.push({ product_id: '', quantity: 1, unit_price: '0' });
}

function removeItem(index: number) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function setProductPrice(itemIndex: number, products: ProductOption[]) {
    const product = products.find(
        (option) =>
            String(option.id) === String(form.items[itemIndex].product_id),
    );

    if (product) {
        form.items[itemIndex].unit_price = product.sale_price;
    }
}

function submit() {
    form.submit(store(currentTeam.value.slug), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.items = [{ product_id: '', quantity: 1, unit_price: '0' }];
            form.sold_at = nowForInput();
        },
    });
}
</script>

<template>
    <Head title="Ventas" />

    <div class="workspace-page">
        <div class="page-hero">
            <div
                class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <div class="page-kicker">
                        <Sparkles class="h-3 w-3" />
                        Caja y cierre diario
                    </div>
                    <h1 class="page-title mt-3">
                        <span class="page-title-gradient">Ventas</span>
                    </h1>
                    <p class="page-subtitle">
                        Registro de salida de prendas y cierre diario.
                    </p>
                </div>

                <div
                    class="glass rounded-2xl px-5 py-4 text-right"
                >
                    <span class="text-xs uppercase tracking-wider text-muted-foreground">{{
                        dailyClose.date
                    }}</span>
                    <div class="text-2xl font-bold text-gradient">
                        Bs {{ dailyClose.total }}
                    </div>
                    <span class="text-xs text-muted-foreground"
                        >{{ dailyClose.count }} ventas</span
                    >
                </div>
            </div>
        </div>

        <form
            v-if="canCreate"
            class="surface-panel p-5"
            @submit.prevent="submit"
        >
            <div class="grid gap-4 md:grid-cols-3">
                <div class="grid gap-2">
                    <Label for="sold_at">Fecha y hora</Label>
                    <Input
                        id="sold_at"
                        v-model="form.sold_at"
                        type="datetime-local"
                    />
                    <InputError :message="form.errors.sold_at" />
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
                        <Label :for="`sale-product-${itemIndex}`"
                            >Producto</Label
                        >
                        <select
                            :id="`sale-product-${itemIndex}`"
                            v-model="item.product_id"
                            class="field-select"
                            @change="setProductPrice(itemIndex, products)"
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
                        <Label :for="`sale-quantity-${itemIndex}`"
                            >Cantidad</Label
                        >
                        <Input
                            :id="`sale-quantity-${itemIndex}`"
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
                        <Label :for="`sale-price-${itemIndex}`">Precio</Label>
                        <Input
                            :id="`sale-price-${itemIndex}`"
                            v-model="item.unit_price"
                            min="0.01"
                            step="0.01"
                            type="number"
                        />
                        <InputError
                            :message="
                                form.errors[`items.${itemIndex}.unit_price`]
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
                        class="status-pill status-pill-primary text-base"
                    >
                        Total Bs {{ total.toFixed(2) }}
                    </span>
                    <Button
                        :disabled="form.processing"
                        type="submit"
                        class="btn-gradient rounded-full border-0"
                    >
                        <ShoppingCart class="h-4 w-4" />
                        Registrar venta
                    </Button>
                </div>
            </div>
        </form>

        <div class="surface-panel overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Detalle</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in sales.data" :key="sale.id">
                        <td class="font-medium">{{ sale.sold_at }}</td>
                        <td>{{ sale.user }}</td>
                        <td class="text-muted-foreground">
                            <span
                                v-for="item in sale.items"
                                :key="item.product"
                                class="mr-2"
                            >
                                {{ item.product }} x{{ item.quantity }}
                            </span>
                        </td>
                        <td class="text-right font-medium">
                            Bs {{ sale.total }}
                        </td>
                    </tr>
                    <tr v-if="sales.data.length === 0">
                        <td
                            class="px-4 py-8 text-center text-muted-foreground"
                            colspan="4"
                        >
                            Sin ventas registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="link in sales.links"
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
