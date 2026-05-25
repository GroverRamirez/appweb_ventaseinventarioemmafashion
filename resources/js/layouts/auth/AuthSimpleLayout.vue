<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-background p-6 md:p-10"
    >
        <!-- Decorative animated blobs -->
        <div
            class="floating-blob"
            style="
                top: -15%;
                left: -10%;
                width: 480px;
                height: 480px;
                background: radial-gradient(circle, hsl(330 90% 65% / 0.5), transparent 60%);
            "
        />
        <div
            class="floating-blob"
            style="
                bottom: -15%;
                right: -10%;
                width: 520px;
                height: 520px;
                background: radial-gradient(circle, hsl(252 90% 65% / 0.45), transparent 60%);
                animation-delay: -5s;
            "
        />
        <div
            class="floating-blob"
            style="
                top: 30%;
                right: 10%;
                width: 380px;
                height: 380px;
                background: radial-gradient(circle, hsl(190 90% 60% / 0.35), transparent 60%);
                animation-delay: -9s;
            "
        />

        <div class="relative z-10 w-full max-w-sm animate-fade-up">
            <div class="glass flex flex-col gap-7 rounded-3xl p-8 shadow-2xl">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-3 font-medium"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-xl animate-pulse-glow"
                            style="background: var(--grad-brand)"
                        >
                            <Sparkles class="h-7 w-7" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-1.5 text-center">
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ title }}
                        </h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
            <p class="mt-6 text-center text-xs text-muted-foreground">
                Emma Fashion · Sistema de Ventas e Inventario
            </p>
        </div>
    </div>
</template>
