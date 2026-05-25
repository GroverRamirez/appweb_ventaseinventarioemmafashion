<script setup lang="ts">
import { useForm, Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Plus, Pencil, Shield, ShieldCheck, X } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';

type Role = {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    isSystem: boolean;
    level: number;
    permissions: string[];
};

type Permission = {
    id: number;
    slug: string;
    name: string;
    group: string;
};

type PermissionGroup = {
    name: string;
    permissions: Permission[];
};

type Props = {
    roles: Role[];
    permissions: Permission[];
    permissionGroups: PermissionGroup[];
    availableRoles: Array<{
        value: string;
        label: string;
        permissions: string[];
    }>;
};

const props = defineProps<Props>();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug || '');

const activeTab = ref('negocio');
const activeCreateTab = ref('negocio');
const showPermissionsModal = ref(false);
const showCreateModal = ref(false);
const selectedRole = ref<Role | null>(null);

// Form for create
const createForm = useForm({
    name: '',
    description: '',
    permissions: [] as string[],
});

// Form for edit
const editForm = useForm({
    permissions: [] as string[],
});

function openPermissionsModal(role: Role) {
    selectedRole.value = { ...role, permissions: [...role.permissions] };
    showPermissionsModal.value = true;
}

function openCreateModal() {
    createForm.name = '';
    createForm.description = '';
    createForm.permissions = [];
    activeCreateTab.value = 'negocio';
    showCreateModal.value = true;
}

function isPermissionChecked(permissionSlug: string): boolean {
    if (!selectedRole.value) return false;
    return selectedRole.value.permissions.includes(permissionSlug);
}

function isCreatePermissionChecked(permissionSlug: string): boolean {
    return createForm.permissions.includes(permissionSlug);
}

function togglePermission(permissionSlug: string) {
    if (!selectedRole.value || selectedRole.value.isSystem) return;

    const currentPermissions = [...selectedRole.value.permissions];
    const index = currentPermissions.indexOf(permissionSlug);

    if (index === -1) {
        currentPermissions.push(permissionSlug);
    } else {
        currentPermissions.splice(index, 1);
    }

    selectedRole.value.permissions = currentPermissions;
}

function toggleCreatePermission(permissionSlug: string) {
    const index = createForm.permissions.indexOf(permissionSlug);
    if (index === -1) {
        createForm.permissions.push(permissionSlug);
    } else {
        createForm.permissions.splice(index, 1);
    }
}

function savePermissions() {
    if (!selectedRole.value) return;

    editForm.permissions = selectedRole.value.permissions;

    editForm.patch(`/${teamSlug.value}/admin/roles/${selectedRole.value.id}`, {
        data: {
            permissions: JSON.stringify(selectedRole.value.permissions),
        },
        onSuccess: () => {
            showPermissionsModal.value = false;
            router.reload();
        },
        onError: (errors) => {
            console.error('Error saving permissions:', errors);
            alert('Error al guardar permisos');
        },
    });
}

function submitCreate() {
    createForm.post(`/${teamSlug.value}/admin/roles`, {
        data: {
            permissions: JSON.stringify(createForm.permissions),
        },
        onSuccess: () => {
            showCreateModal.value = false;
            router.reload();
        },
        onError: (errors) => {
            console.error('Error creating role:', errors);
            alert('Error al crear el rol');
        },
    });
}
</script>

<template>
    <Head title="Gestión de Roles" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Gestión de Roles y Permisos"
                description="Administra los roles del sistema y sus permisos"
            />
            <Button class="btn-gradient rounded-full border-0" @click="openCreateModal">
                <Plus class="mr-2 h-4 w-4" />
                Nuevo Rol
            </Button>
        </div>

        <div class="rounded-lg border bg-card">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left text-sm font-medium">Rol</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Descripción</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Estado</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Permisos</th>
                        <th class="px-4 py-3 text-right text-sm font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in roles" :key="role.id" class="border-b">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <ShieldCheck v-if="role.slug === 'owner'" class="h-5 w-5 text-primary" />
                                <Shield v-else class="h-5 w-5 text-muted" />
                                <span class="font-medium">{{ role.name }}</span>
                                <Badge v-if="role.isSystem" variant="secondary" class="text-xs">Sistema</Badge>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted">{{ role.description || '-' }}</td>
                        <td class="px-4 py-3">
                            <Badge variant="outline" class="bg-green-50 text-green-700">Activo</Badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <Badge v-for="perm in role.permissions.slice(0, 3)" :key="perm" variant="outline" class="text-xs">{{ perm }}</Badge>
                                <Badge v-if="role.permissions.length > 3" variant="outline" class="text-xs">+{{ role.permissions.length - 3 }}</Badge>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Button variant="ghost" size="sm" @click="openPermissionsModal(role)">Editar</Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Permissions Modal -->
    <div v-if="showPermissionsModal && selectedRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="mx-4 w-full max-w-2xl rounded-lg bg-background p-6">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Permisos: {{ selectedRole.name }}</h2>
                    <p class="text-sm text-muted">Selecciona los permisos para este rol</p>
                </div>
                <Button variant="ghost" size="sm" @click="showPermissionsModal = false">
                    <X class="h-4 w-4" />
                </Button>
            </div>

            <div v-if="selectedRole.isSystem" class="mb-4 rounded-md bg-muted p-3 text-sm">
                Los roles del sistema no se pueden modificar
            </div>

            <div class="mb-4 flex gap-1 border-b">
                <button
                    v-for="group in permissionGroups"
                    :key="group.name"
                    class="px-4 py-2 text-sm"
                    :class="{ 'border-b-2 border-primary': activeTab === group.name }"
                    @click="activeTab = group.name"
                >
                    {{ group.name }}
                </button>
            </div>

            <div class="max-h-64 space-y-4 overflow-y-auto">
                <div v-for="group in permissionGroups" :key="group.name" v-show="activeTab === group.name">
                    <h3 class="mb-2 font-medium capitalize">{{ group.name }}</h3>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <div v-for="permission in group.permissions" :key="permission.id" class="flex items-center gap-2 rounded border p-2">
                            <Checkbox
                                :checked="isPermissionChecked(permission.slug)"
                                :disabled="selectedRole.isSystem"
                                @update:checked="togglePermission(permission.slug)"
                            />
                            <span class="text-sm">{{ permission.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <Button variant="outline" @click="showPermissionsModal = false">Cancelar</Button>
                <Button v-if="!selectedRole.isSystem" :disabled="editForm.processing" class="btn-gradient" @click="savePermissions">
                    {{ editForm.processing ? 'Guardando...' : 'Guardar' }}
                </Button>
            </div>
        </div>
    </div>

    <!-- Create Role Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="mx-4 w-full max-w-2xl rounded-lg bg-background p-6">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Crear Nuevo Rol</h2>
                    <p class="text-sm text-muted">Ingresa los datos del nuevo rol</p>
                </div>
                <Button variant="ghost" size="sm" @click="showCreateModal = false">
                    <X class="h-4 w-4" />
                </Button>
            </div>

            <form @submit.prevent="submitCreate">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Nombre del Rol</label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            class="mt-1 w-full rounded-md border px-3 py-2"
                            placeholder="Ej: Supervisor"
                            required
                        />
                    </div>
                    <div>
                        <label class="text-sm font-medium">Descripción</label>
                        <textarea
                            v-model="createForm.description"
                            class="mt-1 w-full rounded-md border px-3 py-2"
                            placeholder="Descripción opcional..."
                            rows="2"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-6 mb-4 flex gap-1 border-b">
                    <button
                        type="button"
                        v-for="group in permissionGroups"
                        :key="group.name"
                        class="px-4 py-2 text-sm"
                        :class="{ 'border-b-2 border-primary': activeCreateTab === group.name }"
                        @click="activeCreateTab = group.name"
                    >
                        {{ group.name }}
                    </button>
                </div>

                <div class="max-h-48 space-y-4 overflow-y-auto">
                    <div v-for="group in permissionGroups" :key="group.name" v-show="activeCreateTab === group.name">
                        <h3 class="mb-2 font-medium capitalize">{{ group.name }}</h3>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div v-for="permission in group.permissions" :key="permission.id" class="flex items-center gap-2 rounded border p-2">
                                <Checkbox
                                    :checked="isCreatePermissionChecked(permission.slug)"
                                    @update:checked="toggleCreatePermission(permission.slug)"
                                />
                                <span class="text-sm">{{ permission.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showCreateModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="createForm.processing || !createForm.name" class="btn-gradient">
                        {{ createForm.processing ? 'Creando...' : 'Crear Rol' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>