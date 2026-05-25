<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { destroy, index, store, update } from '@/routes/suppliers';
import type { Team, TeamPermissions } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Pencil, Sparkles, Trash2, Truck } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Supplier = {
    id: number;
    name: string;
    phone: string | null;
    address: string | null;
    notes: string | null;
    purchases_count: number;
};

defineProps<{
    suppliers: Supplier[];
}>();

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team);
const permissions = computed(
    () => (page.props.permissions as TeamPermissions | null) ?? null,
);
const canManage = computed(() => !!permissions.value?.canManageSupplier);
const editing = ref<Supplier | null>(null);

const form = useForm({
    name: '',
    phone: '',
    address: '',
    notes: '',
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
                title: 'Proveedores',
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
              supplier: editing.value.id,
          })
        : store(currentTeam.value.slug);

    form.submit(route, {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    });
}

function editSupplier(supplier: Supplier) {
    editing.value = supplier;
    form.name = supplier.name;
    form.phone = supplier.phone ?? '';
    form.address = supplier.address ?? '';
    form.notes = supplier.notes ?? '';
}

function resetForm() {
    editing.value = null;
    form.reset();
    form.clearErrors();
}

function deleteSupplier(supplier: Supplier) {
    router.delete(
        destroy({
            current_team: currentTeam.value.slug,
            supplier: supplier.id,
        }).url,
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Proveedores" />

    <div class="workspace-page">
        <div class="page-hero">
            <div class="page-kicker">
                <Truck class="h-3 w-3" />
                Abastecimiento
            </div>
            <h1 class="page-title mt-3">
                <span class="page-title-gradient">Proveedores</span>
            </h1>
            <p class="page-subtitle">
                Contactos usados en el registro de compras.
            </p>
        </div>

        <form
            v-if="canManage"
            class="surface-panel p-5"
            @submit.prevent="submit"
        >
            <div class="form-grid">
                <div class="grid gap-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" v-model="form.name" required />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Telefono</Label>
                    <Input id="phone" v-model="form.phone" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="address">Direccion</Label>
                    <Input id="address" v-model="form.address" />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="grid gap-2">
                    <Label for="notes">Notas</Label>
                    <Input id="notes" v-model="form.notes" />
                    <InputError :message="form.errors.notes" />
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
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
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Telefono</th>
                        <th>Direccion</th>
                        <th class="text-right">Compras</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="supplier in suppliers" :key="supplier.id">
                        <td class="font-medium">
                            {{ supplier.name }}
                        </td>
                        <td>{{ supplier.phone ?? '-' }}</td>
                        <td>{{ supplier.address ?? '-' }}</td>
                        <td class="text-right">
                            <span
                                class="status-pill border-chart-2/25 bg-chart-2/10 text-chart-2"
                            >
                                {{ supplier.purchases_count }}
                            </span>
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
                                    @click="editSupplier(supplier)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="sm"
                                    type="button"
                                    variant="ghost"
                                    @click="deleteSupplier(supplier)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="suppliers.length === 0">
                        <td
                            class="px-4 py-8 text-center text-muted-foreground"
                            colspan="5"
                        >
                            Sin proveedores registrados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
