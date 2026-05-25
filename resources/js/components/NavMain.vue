<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="text-[10px] font-semibold uppercase tracking-[0.18em] text-sidebar-foreground/50">
            Navegación
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="rounded-xl transition-all hover:bg-gradient-to-r hover:from-primary/10 hover:to-purple-500/10 data-[active=true]:bg-[image:var(--grad-brand)] data-[active=true]:text-white data-[active=true]:shadow-[0_10px_25px_-10px_hsl(330_88%_58%_/_0.55)]"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span class="font-medium">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
