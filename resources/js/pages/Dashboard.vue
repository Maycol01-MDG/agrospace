<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { LogOut, User } from 'lucide-vue-next';

// Función para cerrar sesión
const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Dashboard - AgroSpace" />

    <div
        class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-amber-50"
    >
        <!-- Top Bar Simple -->
        <header
            class="border-b border-green-200 bg-white/80 shadow-sm backdrop-blur-sm"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo/Título -->
                    <div class="flex items-center">
                        <h1
                            class="font-fredoka text-2xl font-bold text-green-800"
                        >
                            AgroSpace
                        </h1>
                    </div>

                    <!-- Opciones de usuario -->
                    <div class="flex items-center space-x-4">
                        <!-- Ver Perfil -->
                        <Link
                            href="/profile"
                            class="font-fredoka flex items-center space-x-2 rounded-lg bg-green-100 px-4 py-2 font-semibold text-green-800 transition-colors hover:bg-green-200"
                        >
                            <User class="h-5 w-5" />
                            <span>Perfil</span>
                        </Link>

                        <!-- Salir -->
                        <button
                            @click="logout"
                            class="font-fredoka flex items-center space-x-2 rounded-lg bg-red-100 px-4 py-2 font-semibold text-red-800 transition-colors hover:bg-red-200"
                        >
                            <LogOut class="h-5 w-5" />
                            <span>Salir</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido Principal del Dashboard -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div
                class="grid h-[calc(100vh-12rem)] grid-cols-1 gap-8 lg:grid-cols-2"
            >
                <!-- Sección Izquierda: Modo Historia -->
                <div
                    class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl bg-gradient-to-br from-purple-100 to-indigo-100 p-8 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                >
                    <div class="text-center">
                        <!-- Icono/Ilustración para Modo Historia -->
                        <div class="mb-6">
                            <div
                                class="mx-auto flex h-32 w-32 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg transition-shadow group-hover:shadow-xl"
                            >
                                <svg
                                    class="h-16 w-16 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"
                                    ></path>
                                </svg>
                            </div>
                        </div>

                        <h2
                            class="font-fredoka mb-4 text-4xl font-bold text-purple-800 transition-colors group-hover:text-purple-900"
                        >
                            Jugar Modo Historia
                        </h2>

                        <p
                            class="font-fredoka mb-8 max-w-md text-lg text-purple-700"
                        >
                            Embárcate en una aventura épica a través de
                            diferentes eras agrícolas. Descubre secretos,
                            completa misiones y desbloquea nuevas tecnologías.
                        </p>

                        <div
                            class="rounded-xl bg-white/50 p-4 backdrop-blur-sm"
                        >
                            <p
                                class="font-fredoka text-sm font-semibold text-purple-600"
                            >
                                ✨ Próximamente: Nuevas aventuras te esperan
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sección Derecha: Modo Granja -->
                <div
                    class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl bg-gradient-to-br from-green-100 to-emerald-100 p-8 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                >
                    <div class="text-center">
                        <!-- Icono/Ilustración para Modo Granja -->
                        <div class="mb-6">
                            <div
                                class="mx-auto flex h-32 w-32 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg transition-shadow group-hover:shadow-xl"
                            >
                                <svg
                                    class="h-16 w-16 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                        </div>

                        <h2
                            class="font-fredoka mb-4 text-4xl font-bold text-green-800 transition-colors group-hover:text-green-900"
                        >
                            Jugar Modo Granja
                        </h2>

                        <p
                            class="font-fredoka mb-8 max-w-md text-lg text-green-700"
                        >
                            Construye y administra tu propia granja. Siembra
                            cultivos, cuida animales y expande tu imperio
                            agrícola en tiempo real.
                        </p>

                        <div
                            class="rounded-xl bg-white/50 p-4 backdrop-blur-sm"
                        >
                            <p
                                class="font-fredoka text-sm font-semibold text-green-600"
                            >
                                🌱 ¡Comienza tu aventura agrícola ahora!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap');

.font-fredoka {
    font-family: 'Fredoka', sans-serif;
}
</style>
