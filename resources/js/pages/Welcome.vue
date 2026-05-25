<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';
import {
    ArrowRight,
    BarChart3,
    Layers,
    PackageCheck,
    ShieldCheck,
    Sparkles,
    TrendingUp,
    Users,
    Zap,
} from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();
const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/',
);

const features = [
    {
        icon: PackageCheck,
        title: 'Inventario inteligente',
        text: 'Control total de prendas por código, talla, color y ubicación. Alertas automáticas de stock bajo.',
        gradient: 'from-pink-500 to-rose-500',
    },
    {
        icon: TrendingUp,
        title: 'Ventas en tiempo real',
        text: 'Registra ventas al instante con cálculo automático de totales, descuentos e impuestos.',
        gradient: 'from-violet-500 to-purple-500',
    },
    {
        icon: BarChart3,
        title: 'Reportes visuales',
        text: 'Dashboards con métricas claves: ventas del día, productos top y proyecciones del mes.',
        gradient: 'from-cyan-500 to-blue-500',
    },
    {
        icon: Users,
        title: 'Multiusuario por equipos',
        text: 'Roles, permisos y equipos. Cada miembro con acceso a lo que necesita.',
        gradient: 'from-amber-500 to-orange-500',
    },
    {
        icon: ShieldCheck,
        title: 'Seguridad de élite',
        text: 'Autenticación 2FA, cifrado de datos y respaldos automáticos. Tu negocio, protegido.',
        gradient: 'from-emerald-500 to-teal-500',
    },
    {
        icon: Zap,
        title: 'Velocidad SaaS',
        text: 'Interfaz moderna, instantánea y responsive. Diseñada para que vendas más, más rápido.',
        gradient: 'from-fuchsia-500 to-pink-500',
    },
];

const stats = [
    { value: '+1,200', label: 'Prendas gestionadas' },
    { value: '99.9%', label: 'Disponibilidad' },
    { value: '<200ms', label: 'Tiempo de respuesta' },
    { value: '24/7', label: 'Soporte' },
];
</script>

<template>
    <Head title="Emma Fashion · Sistema de Ventas e Inventario">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="relative min-h-screen overflow-hidden bg-background text-foreground">
        <!-- Decorative blobs -->
        <div
            class="floating-blob"
            style="
                top: -10%;
                left: -8%;
                width: 480px;
                height: 480px;
                background: radial-gradient(circle, hsl(330 90% 65% / 0.55), transparent 60%);
            "
        />
        <div
            class="floating-blob"
            style="
                top: 10%;
                right: -10%;
                width: 520px;
                height: 520px;
                background: radial-gradient(circle, hsl(252 90% 65% / 0.5), transparent 60%);
                animation-delay: -4s;
            "
        />
        <div
            class="floating-blob"
            style="
                bottom: -10%;
                left: 30%;
                width: 480px;
                height: 480px;
                background: radial-gradient(circle, hsl(190 90% 60% / 0.4), transparent 60%);
                animation-delay: -8s;
            "
        />

        <!-- NAVBAR -->
        <header class="relative z-20">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-lg animate-pulse-glow"
                        style="background: var(--grad-brand)"
                    >
                        <Sparkles class="h-5 w-5" />
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="text-lg font-bold tracking-tight">Emma Fashion</span>
                        <span class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground">
                            Sales · Stock · Style
                        </span>
                    </div>
                </div>

                <nav class="hidden items-center gap-8 text-sm font-medium md:flex">
                    <a href="#features" class="text-muted-foreground transition-colors hover:text-foreground">Features</a>
                    <a href="#stats" class="text-muted-foreground transition-colors hover:text-foreground">Métricas</a>
                    <a href="#cta" class="text-muted-foreground transition-colors hover:text-foreground">Empezar</a>
                </nav>

                <div class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboardUrl"
                        class="btn-gradient inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold"
                    >
                        Ir al Dashboard
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden rounded-full px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted sm:inline-block"
                        >
                            Iniciar sesión
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="btn-gradient inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold"
                        >
                            Crear cuenta
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- HERO -->
        <section class="relative z-10 px-4 pt-12 pb-20 sm:px-6 lg:px-8 lg:pt-20">
            <div class="mx-auto max-w-6xl text-center">
                <div
                    class="animate-fade-up mx-auto inline-flex items-center gap-2 rounded-full border border-primary/20 bg-gradient-to-r from-primary/10 to-purple-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary backdrop-blur"
                >
                    <Sparkles class="h-3.5 w-3.5" />
                    Plataforma SaaS de moda — nueva generación
                </div>

                <h1
                    class="animate-fade-up-delay-1 mx-auto mt-6 max-w-4xl text-5xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl"
                >
                    Tu boutique digital,
                    <span class="text-gradient">brillando</span>
                    como nunca.
                </h1>

                <p
                    class="animate-fade-up-delay-2 mx-auto mt-6 max-w-2xl text-lg text-muted-foreground sm:text-xl"
                >
                    Emma Fashion centraliza tu
                    <span class="font-semibold text-foreground">inventario</span>,
                    <span class="font-semibold text-foreground">ventas</span>,
                    <span class="font-semibold text-foreground">compras</span> y
                    <span class="font-semibold text-foreground">reportes</span> en una
                    interfaz espectacular pensada para vender más.
                </p>

                <div class="animate-fade-up-delay-3 mt-10 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        :href="$page.props.auth.user ? dashboardUrl : (canRegister ? register().url : login().url)"
                        class="btn-gradient group inline-flex items-center gap-2 rounded-full px-7 py-3.5 text-base font-semibold"
                    >
                        {{ $page.props.auth.user ? 'Abrir Dashboard' : 'Empezar gratis' }}
                        <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </Link>
                    <a
                        href="#features"
                        class="glass inline-flex items-center gap-2 rounded-full px-7 py-3.5 text-base font-semibold text-foreground transition-transform hover:-translate-y-0.5"
                    >
                        Ver características
                    </a>
                </div>

                <!-- Floating preview card -->
                <div
                    class="animate-fade-up-delay-3 relative mx-auto mt-16 max-w-5xl"
                    style="animation-delay: 0.5s"
                >
                    <div
                        class="absolute inset-x-0 -top-10 mx-auto h-72 w-3/4 rounded-full opacity-60 blur-3xl"
                        style="background: var(--grad-brand)"
                    />

                    <div
                        class="glass relative overflow-hidden rounded-3xl p-2 shadow-2xl"
                    >
                        <div
                            class="rounded-2xl border border-border/50 bg-gradient-to-br from-card to-muted/40 p-5"
                        >
                            <div class="flex items-center justify-between border-b border-border/40 pb-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex gap-1.5">
                                        <span class="h-3 w-3 rounded-full bg-red-400/70" />
                                        <span class="h-3 w-3 rounded-full bg-amber-400/70" />
                                        <span class="h-3 w-3 rounded-full bg-emerald-400/70" />
                                    </div>
                                    <span class="ml-4 text-xs font-medium text-muted-foreground">
                                        emmafashion.app/dashboard
                                    </span>
                                </div>
                                <div
                                    class="rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-white"
                                    style="background: var(--grad-brand)"
                                >
                                    Live
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-5 sm:grid-cols-4">
                                <div
                                    v-for="(stat, i) in stats"
                                    :key="i"
                                    class="rounded-xl border border-border/40 bg-card/60 p-4 backdrop-blur"
                                >
                                    <div
                                        class="text-2xl font-bold sm:text-3xl"
                                        :class="i === 0 ? 'text-gradient' : i === 1 ? 'text-gradient-cool' : ''"
                                    >
                                        {{ stat.value }}
                                    </div>
                                    <div class="mt-1 text-[11px] uppercase tracking-wider text-muted-foreground">
                                        {{ stat.label }}
                                    </div>
                                </div>
                            </div>

                            <!-- Fake bars chart -->
                            <div class="mt-6 flex items-end justify-between gap-2 px-2">
                                <div
                                    v-for="n in 24"
                                    :key="n"
                                    class="flex-1 rounded-t-md transition-all"
                                    :style="{
                                        height: 20 + Math.abs(Math.sin(n * 0.7)) * 80 + 'px',
                                        background: `linear-gradient(180deg, hsl(${330 - n * 3} 85% 65%), hsl(${252 - n * 2} 85% 60%))`,
                                        opacity: 0.85,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- STATS BAND -->
        <section id="stats" class="relative z-10 px-4 py-12 sm:px-6 lg:px-8">
            <div
                class="mx-auto max-w-6xl rounded-3xl p-8 sm:p-10"
                style="background: var(--grad-brand)"
            >
                <div class="grid grid-cols-2 gap-8 text-center text-white sm:grid-cols-4">
                    <div v-for="(stat, i) in stats" :key="i">
                        <div class="text-3xl font-extrabold sm:text-4xl">{{ stat.value }}</div>
                        <div class="mt-2 text-xs font-medium uppercase tracking-wider text-white/80">
                            {{ stat.label }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section id="features" class="relative z-10 px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="mx-auto max-w-2xl text-center">
                    <div class="page-kicker mx-auto">Funcionalidades</div>
                    <h2 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">
                        Todo lo que tu boutique
                        <span class="text-gradient">necesita</span>
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Desde inventario hasta reportes, una experiencia diseñada para
                        vendedoras modernas.
                    </p>
                </div>

                <div class="mt-16 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feature, i) in features"
                        :key="i"
                        class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card/70 p-6 backdrop-blur-xl transition-all hover:-translate-y-1 hover:shadow-2xl"
                    >
                        <div
                            class="absolute -top-12 -right-12 h-40 w-40 rounded-full opacity-20 blur-2xl transition-opacity group-hover:opacity-40"
                            :class="`bg-gradient-to-br ${feature.gradient}`"
                        />
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-lg"
                            :class="`bg-gradient-to-br ${feature.gradient}`"
                        >
                            <component :is="feature.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-5 text-xl font-bold">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            {{ feature.text }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section id="cta" class="relative z-10 px-4 py-20 sm:px-6 lg:px-8">
            <div
                class="relative mx-auto max-w-5xl overflow-hidden rounded-3xl p-10 text-center sm:p-16"
                style="background: var(--grad-brand)"
            >
                <div
                    class="absolute inset-0 opacity-30"
                    style="
                        background-image:
                            radial-gradient(circle at 20% 30%, white 0%, transparent 35%),
                            radial-gradient(circle at 80% 70%, white 0%, transparent 35%);
                    "
                />
                <div class="relative">
                    <Layers class="mx-auto h-12 w-12 text-white" />
                    <h2 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                        Lleva tu boutique al siguiente nivel
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-white/90">
                        Únete a Emma Fashion y empieza a vender más con menos esfuerzo.
                        Configuración en minutos.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboardUrl"
                            class="inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-base font-semibold text-primary shadow-xl transition-transform hover:-translate-y-0.5"
                        >
                            Ir al Dashboard
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                        <template v-else>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-base font-semibold text-primary shadow-xl transition-transform hover:-translate-y-0.5"
                            >
                                Empezar ahora
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex items-center gap-2 rounded-full border-2 border-white/40 px-7 py-3.5 text-base font-semibold text-white transition-all hover:bg-white/10"
                            >
                                Iniciar sesión
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="relative z-10 border-t border-border/40 px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 text-sm text-muted-foreground sm:flex-row">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-md text-white"
                        style="background: var(--grad-brand)"
                    >
                        <Sparkles class="h-3.5 w-3.5" />
                    </div>
                    <span class="font-semibold text-foreground">Emma Fashion</span>
                    <span>© {{ new Date().getFullYear() }}</span>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="transition-colors hover:text-foreground">Privacidad</a>
                    <a href="#" class="transition-colors hover:text-foreground">Términos</a>
                    <a href="#" class="transition-colors hover:text-foreground">Contacto</a>
                </div>
            </div>
        </footer>
    </div>
</template>
