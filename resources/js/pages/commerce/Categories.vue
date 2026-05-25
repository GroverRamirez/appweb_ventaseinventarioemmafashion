<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { destroy, index, store, update } from '@/routes/categories';
import type { Team, TeamPermissions } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Layers, Package, Pencil, Sparkles, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Category = {
    id: number;
    name: string;
    description: string | null;
    products_count: number;
};

defineProps<{
    categories: Category[];
}>();

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team);
const permissions = computed(
    () => (page.props.permissions as TeamPermissions | null) ?? null,
);
const canManage = computed(() => !!permissions.value?.canManageCategory);
const editing = ref<Category | null>(null);

const form = useForm({
    name: '',
    description: '',
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
                title: 'Categorías',
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
              category: editing.value.id,
          })
        : store(currentTeam.value.slug);

    form.submit(route, {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    });
}

function editCategory(category: Category) {
    editing.value = category;
    form.name = category.name;
    form.description = category.description ?? '';
}

function resetForm() {
    editing.value = null;
    form.reset();
    form.clearErrors();
}

function deleteCategory(category: Category) {
    if (
        category.products_count > 0 &&
        !confirm(
            `La categoría "${category.name}" tiene ${category.products_count} producto(s) asociados. ¿Eliminar de todas formas?`,
        )
    ) {
        return;
    }

    router.delete(
        destroy({
            current_team: currentTeam.value.slug,
            category: category.id,
        }).url,
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Categorías" />

    <div class="workspace-page">
        <!-- HERO -->
        <div class="page-hero animate-fade-up">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <div class="page-kicker">
                        <Layers class="h-3 w-3" />
                        Organización
                    </div>
                    <h1 class="page-title mt-3">
                        <span class="page-title-gradient">Categorías</span>
                    </h1>
                    <p class="page-subtitle">
                        Clasifica tus prendas para agilizar búsquedas y reportes.
                    </p>
                </div>

                <div class="glass rounded-2xl px-5 py-4 text-right">
                    <span
                        class="text-xs uppercase tracking-wider text-muted-foreground"
                    >
                        Total
                    </span>
                    <div class="text-2xl font-bold text-gradient">
                        {{ categories.length }}
                    </div>
                    <span class="text-xs text-muted-foreground"
                        >categoría(s)</span
                    >
                </div>
            </div>
        </div>

        <!-- FORM -->
        <form
            v-if="canManage"
            class="surface-panel p-5 animate-fade-up-delay-1"
            @submit.prevent="submit"
        >
            <div class="grid gap-4 md:grid-cols-3">
                <div class="grid gap-2">
                    <Label for="name">Nombre</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Ej. Vestidos"
                        required
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="description">Descripción</Label>
                    <Input
                        id="description"
                        v-model="form.description"
                        placeholder="Opcional"
                    />
                    <InputError :message="form.errors.description" />
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <Button
                    v-if="editing"
                    type="button"
                    variant="secondary"
                    class="rounded-full"
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

        <!-- TABLE -->
        <div class="surface-panel overflow-hidden animate-fade-up-delay-2">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th class="text-right">Productos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id">
                        <td>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white shadow-md"
                                    style="background: var(--grad-brand)"
                                >
                                    <Layers class="h-4 w-4" />
                                </div>
                                <span class="font-semibold">
                                    {{ category.name }}
                                </span>
                            </div>
                        </td>
                        <td class="text-muted-foreground">
                            {{ category.description ?? '-' }}
                        </td>
                        <td class="text-right">
                            <span
                                class="status-pill"
                                :class="
                                    category.products_count > 0
                                        ? 'status-pill-primary'
                                        : 'border-border bg-muted/40 text-muted-foreground'
                                "
                            >
                                <Package class="h-3 w-3" />
                                {{ category.products_count }}
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
                                    @click="editCategory(category)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="sm"
                                    type="button"
                                    variant="ghost"
                                    @click="deleteCategory(category)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td
                            class="px-4 py-12 text-center text-muted-foreground"
                            colspan="4"
                        >
                            Sin categorías registradas. Crea la primera con el
                            formulario.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
