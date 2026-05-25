<script setup lang="ts">
import { Sparkles } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-xl text-white shadow-lg"
        style="
            background: linear-gradient(
                135deg,
                hsl(330 90% 60%) 0%,
                hsl(290 85% 60%) 50%,
                hsl(252 90% 65%) 100%
            );
            box-shadow: 0 10px 30px -10px hsl(330 81% 56% / 0.6);
        "
    >
        <Sparkles class="size-4.5" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-bold tracking-tight">Emma Fashion</span>
        <span class="truncate text-[10px] uppercase tracking-[0.18em] text-sidebar-foreground/60"
            >Sales · Stock</span
        >
    </div>
</template>
