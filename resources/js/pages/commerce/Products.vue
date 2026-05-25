<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { destroy, index, store, update } from '@/routes/products';
import type { Team, TeamPermissions } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Package, Pencil, Search, Sparkles, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Category = {
    id: number;
    name: string;
};

type Product = {
    id: number;
    category_id: number | null;
    category: string | null;
    sku: string;
    name: string;
    model: string | null;
    size: string | null;
    color: string | null;
    location: string | null;
    purchase_price: string;
    sale_price: string;
    stock: number;
    minimum_stock: number;
    is_active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    filters: {
        search: string;
    };
    categories: Category[];
    products: Paginated<Product>;
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team);
const permissions = computed(
    () => (page.props.permissions as TeamPermissions | null) ?? null,
);
const canManage = computed(() => !!permissions.value?.canManageProduct);
const editing = ref<Product | null>(null);
const search = ref(props.filters.search ?? '');

const form = useForm({
    category_id: '',
    category_name: '',
    sku: '',
    name: '',
    model: '',
    size: '',
    color: '',
    location: '',
    purchase_price: '0',
    sale_price: '0',
    stock: 0,
    minimum_stock: 1,
    is_active: true,
});

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
                title: 'Productos',
                href: layoutProps.currentTeam
                    ? index(layoutProps.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});

function submit() {
    const route = editing.value
        ? update({
              current_team: currentTeam.value.slug,
              product: editing.value.id,
          })
        : store(currentTeam.value.slug);

    form.submit(route, {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    });
}

function editProduct(product: Product) {
    editing.value = product;
    form.category_id = product.category_id ? String(product.category_id) : '';
    form.category_name = '';
    form.sku = product.sku;
    form.name = product.name;
    form.model = product.model ?? '';
    form.size = product.size ?? '';
    form.color = product.color ?? '';
    form.location = product.location ?? '';
    form.purchase_price = product.purchase_price;
    form.sale_price = product.sale_price;
    form.stock = product.stock;
    form.minimum_stock = product.minimum_stock;
    form.is_active = product.is_active;
}

function resetForm() {
    editing.value = null;
    form.reset();
    form.clearErrors();
}

function applySearch() {
    router.get(
        index(currentTeam.value.slug).url,
        { search: search.value },
        { preserveState: true, preserveScroll: true },
    );
}

function deleteProduct(product: Product) {
    router.delete(
        destroy({ current_team: currentTeam.value.slug, product: product.id })
            .url,
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Productos" />

    <div class="workspace-page">
        <div class="page-hero">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <div class="page-kicker">
                        <Package class="h-3 w-3" />
                        Inventario
                    </div>
                    <h1 class="page-title mt-3">
                        <span class="page-title-gradient">Productos</span>
                    </h1>
                    <p class="page-subtitle">
                        Inventario por código, talla, color y ubicación.
                    </p>
                </div>

                <form class="flex gap-2" @submit.prevent="applySearch">
                    <Input
                        v-model="search"
                        class="w-64 rounded-full"
                        placeholder="Buscar producto..."
                    />
                    <Button
                        type="submit"
                        class="btn-gradient rounded-full border-0"
                    >
                        <Search class="h-4 w-4" />
                        Buscar
                    </Button>
                </form>
            </div>
        </div>

        <form
            v-if="canManage"
            class="surface-panel p-5"
            @submit.prevent="submit"
        >
            <div class="form-grid">
                <div class="grid gap-2">
                    <Label for="sku">Codigo</Label>
                    <Input id="sku" v-model="form.sku" required />
                    <InputError :message="form.errors.sku" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="name">Producto</Label>
                    <Input id="name" v-model="form.name" required />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="category_id">Categoria</Label>
                    <select
                        id="category_id"
                        v-model="form.category_id"
                        class="field-select"
                    >
                        <option value="">Sin categoria</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="String(category.id)"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.category_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="category_name">Nueva categoria</Label>
                    <Input id="category_name" v-model="form.category_name" />
                    <InputError :message="form.errors.category_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="model">Modelo</Label>
                    <Input id="model" v-model="form.model" />
                </div>

                <div class="grid gap-2">
                    <Label for="size">Talla</Label>
                    <Input id="size" v-model="form.size" />
                </div>

                <div class="grid gap-2">
                    <Label for="color">Color</Label>
                    <Input id="color" v-model="form.color" />
                </div>

                <div class="grid gap-2">
                    <Label for="location">Ubicacion</Label>
                    <Input id="location" v-model="form.location" />
                </div>

                <div class="grid gap-2">
                    <Label for="purchase_price">Costo</Label>
                    <Input
                        id="purchase_price"
                        v-model="form.purchase_price"
                        min="0"
                        step="0.01"
                        type="number"
                    />
                    <InputError :message="form.errors.purchase_price" />
                </div>

                <div class="grid gap-2">
                    <Label for="sale_price">Precio venta</Label>
                    <Input
                        id="sale_price"
                        v-model="form.sale_price"
                        min="0"
                        step="0.01"
                        type="number"
                    />
                    <InputError :message="form.errors.sale_price" />
                </div>

                <div class="grid gap-2">
                    <Label for="stock">Stock</Label>
                    <Input
                        id="stock"
                        v-model="form.stock"
                        min="0"
                        type="number"
                    />
                    <InputError :message="form.errors.stock" />
                </div>

                <div class="grid gap-2">
                    <Label for="minimum_stock">Stock minimo</Label>
                    <Input
                        id="minimum_stock"
                        v-model="form.minimum_stock"
                        min="0"
                        type="number"
                    />
                    <InputError :message="form.errors.minimum_stock" />
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2">
                <Button
                    v-if="editing"
                    type="button"
                    variant="secondary"
                    @click="resetForm"
                >
                    Cancelar
                </Button>
                <Button
                    :disabled="form.processing"
                    type="submit"
                    class="btn-gradient rounded-full border-0"
                >
                    <Sparkles class="h-4 w-4" />
                    {{ editing ? 'Actualizar' : 'Registrar' }}
                </Button>
            </div>
        </form>

        <div class="surface-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <th>Categoria</th>
                            <th>Detalle</th>
                            <th class="text-right">Stock</th>
                            <th class="text-right">Venta</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in products.data" :key="product.id">
                            <td class="font-medium">
                                {{ product.sku }}
                            </td>
                            <td>{{ product.name }}</td>
                            <td>
                                {{ product.category ?? '-' }}
                            </td>
                            <td class="text-muted-foreground">
                                {{ product.size ?? '-' }} /
                                {{ product.color ?? '-' }}
                            </td>
                            <td class="text-right">
                                <span
                                    class="status-pill"
                                    :class="
                                        product.stock <= product.minimum_stock
                                            ? 'border-destructive/25 bg-destructive/10 text-destructive'
                                            : 'border-emerald-500/25 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                    "
                                >
                                    {{ product.stock }}
                                </span>
                            </td>
                            <td class="text-right font-medium">
                                Bs {{ product.sale_price }}
                            </td>
                            <td>
                                <div
                                    v-if="canManage"
                                    class="flex justify-end gap-2"
                                >
                                    <Button
                                        size="sm"
                                        type="button"
                                        variant="ghost"
                                        @click="editProduct(product)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        type="button"
                                        variant="ghost"
                                        @click="deleteProduct(product)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td
                                class="px-4 py-8 text-center text-muted-foreground"
                                colspan="7"
                            >
                                Sin productos registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="link in products.links"
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
