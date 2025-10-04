<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { LogOut, User } from 'lucide-vue-next';
import { nextTick, ref, shallowRef } from 'vue';

// Función para cerrar sesión
const logout = () => {
    router.post('/logout');
};

const showFarmMap = ref(false);
const isMapLoading = ref(false);
const mapError = ref<string | null>(null);
const mapContainerRef = ref<HTMLDivElement | null>(null);
const mapInstance = shallowRef<any>(null);
const polygonInstance = shallowRef<any>(null);
let googleMapsPromise: Promise<any> | null = null;

const farmlandPolygon = [
    { lat: 20.706658, lng: -103.411528 },
    { lat: 20.706447, lng: -103.4059 },
    { lat: 20.702294, lng: -103.405838 },
    { lat: 20.701998, lng: -103.411454 },
];

const computePolygonCenter = () => {
    const pointsCount = farmlandPolygon.length;

    if (pointsCount === 0) {
        return { lat: 0, lng: 0 };
    }

    return farmlandPolygon.reduce(
        (accumulator, point) => ({
            lat: accumulator.lat + point.lat / pointsCount,
            lng: accumulator.lng + point.lng / pointsCount,
        }),
        { lat: 0, lng: 0 },
    );
};

const loadGoogleMaps = () => {
    if (googleMapsPromise) {
        return googleMapsPromise;
    }

    googleMapsPromise = new Promise((resolve, reject) => {
        const existingGoogle = window.google;

        if (existingGoogle?.maps) {
            resolve(window.google);
        }
    });

    return googleMapsPromise;
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dashboard
                </h2>
                <div class="flex items-center space-x-4">
                    <Link
                        href="/settings/profile"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        <User class="h-5 w-5" />
                    </Link>
                    <button
                        @click="logout"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        <LogOut class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </header>

        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tarjeta de Bienvenida -->
                            <div class="bg-gradient-to-r from-blue-500 to-teal-400 rounded-lg p-6 text-white shadow-md">
                                <h3 class="text-xl font-bold mb-2">¡Bienvenido a AgroSpace!</h3>
                                <p class="mb-4">
                                    Tu plataforma para gestionar y optimizar tus cultivos de manera inteligente.
                                </p>
                                <div class="mt-4">
                                    <Link
                                        href="/granja"
                                        class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Ir al Simulador
                                    </Link>
                                </div>
                            </div>

                            <!-- Tarjeta de Estadísticas -->
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow-md">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                                    Estadísticas de tu Terreno
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Área Total</p>
                                        <p class="text-2xl font-bold text-gray-800 dark:text-white">5.2 ha</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Cultivos Activos</p>
                                        <p class="text-2xl font-bold text-gray-800 dark:text-white">3</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Rendimiento</p>
                                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">+12%</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Eficiencia Hídrica</p>
                                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">85%</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Mapa de la Parcela -->
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow-md md:col-span-2">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                        Mapa de tu Parcela
                                    </h3>
                                    <button
                                        @click="showFarmMap = true"
                                        class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition"
                                    >
                                        Ver Mapa
                                    </button>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">
                                    Visualiza tu terreno y planifica tus cultivos de manera eficiente.
                                </p>
                                <div class="bg-gray-100 dark:bg-gray-800 h-48 rounded-lg flex items-center justify-center">
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Haz clic en "Ver Mapa" para visualizar tu parcela
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal del Mapa -->
    <transition name="fade">
        <div
            v-if="showFarmMap"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-xl">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Mapa de tu Parcela</h3>
                    <button
                        @click="showFarmMap = false"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <span class="sr-only">Cerrar</span>
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <div
                        ref="mapContainerRef"
                        class="w-full h-[60vh] bg-gray-100 dark:bg-gray-700 rounded-lg relative"
                    >
                        <div
                            v-if="isMapLoading"
                            class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 dark:bg-gray-800 dark:bg-opacity-75 z-10"
                        >
                            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                        </div>
                        <div
                            v-if="mapError"
                            class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 dark:bg-gray-800 dark:bg-opacity-90 z-10"
                        >
                            <div class="text-red-500 text-center p-4">
                                <p class="font-semibold mb-2">Error al cargar el mapa</p>
                                <p>{{ mapError }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
