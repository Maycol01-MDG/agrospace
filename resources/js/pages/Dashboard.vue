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
            resolve(existingGoogle);
            return;
        }

        const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

        if (!apiKey) {
            reject(
                new Error(
                    'Falta configurar la variable VITE_GOOGLE_MAPS_API_KEY para cargar el mapa.',
                ),
            );
            return;
        }
        const handleLoad = () => {
            const loadedGoogle = window.google;

            if (loadedGoogle?.maps) {
                resolve(loadedGoogle);
            } else {
                reject(new Error('Google Maps no está disponible en este momento.'));
            }
        };

        const handleError = () => {
            reject(new Error('No fue posible cargar Google Maps.'));
        };

        const existingScript = document.getElementById(
            'google-maps-script',
        ) as HTMLScriptElement | null;

        if (existingScript) {
            existingScript.addEventListener('load', handleLoad, { once: true });
            existingScript.addEventListener('error', handleError, { once: true });
            return;
        }

        const script = document.createElement('script');
        script.id = 'google-maps-script';
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}`;
        script.async = true;
        script.defer = true;
        script.addEventListener('load', handleLoad, { once: true });
        script.addEventListener('error', handleError, { once: true });

        document.head.appendChild(script);
    });

    return googleMapsPromise;
};

const initialiseFarmMap = async () => {
    if (!mapContainerRef.value) {
        return;
    }

    mapError.value = null;
    isMapLoading.value = true;

    try {
        const google = await loadGoogleMaps();
        const center = computePolygonCenter();

        if (!mapInstance.value) {
            mapInstance.value = new google.maps.Map(mapContainerRef.value, {
                center,
                zoom: 16,
                mapTypeId: 'satellite',
            });
        } else {
            mapInstance.value.setCenter(center);
            mapInstance.value.setZoom(16);
        }

        if (polygonInstance.value) {
            polygonInstance.value.setMap(null);
        }

        polygonInstance.value = new google.maps.Polygon({
            paths: farmlandPolygon,
            strokeColor: '#16a34a',
            strokeOpacity: 0.9,
            strokeWeight: 2,
            fillColor: '#22c55e',
            fillOpacity: 0.35,
        });

        polygonInstance.value.setMap(mapInstance.value);

        const infoWindow = new google.maps.InfoWindow({
            content: '<strong>Campo Principal</strong><br/>Área delimitada lista para sembrar.',
            position: farmlandPolygon[0],
        });

        infoWindow.open({ map: mapInstance.value });
    } catch (error) {
        mapError.value =
            error instanceof Error
                ? error.message
                : 'Ocurrió un error inesperado al cargar el mapa.';
    } finally {
        isMapLoading.value = false;
    }
};

const openFarmMap = async () => {
    showFarmMap.value = true;
    await nextTick();
    await initialiseFarmMap();
};

const closeFarmMap = () => {
    showFarmMap.value = false;
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

                        <button
                            type="button"
                            @click.stop="openFarmMap"
                            class="rounded-xl bg-white/70 p-4 text-center font-fredoka text-sm font-semibold text-green-700 shadow-sm transition hover:bg-white"
                        >
                            🌱 ¡Comienza tu aventura agrícola ahora!
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <transition name="fade">
        <div
            v-if="showFarmMap"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-10"
            @click="closeFarmMap"
        >
            <div
                class="relative w-full max-w-4xl rounded-2xl bg-white p-6 shadow-2xl"
                @click.stop
            >
                <button
                    type="button"
                    class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-gray-200"
                    @click="closeFarmMap"
                    aria-label="Cerrar mapa de la granja"
                >
                    <span class="text-xl">×</span>
                </button>

                <h3 class="font-fredoka text-2xl font-semibold text-green-800">
                    Mapa de tu Modo Granja
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Visualiza la parcela delimitada en Google Maps para planificar tus cultivos con precisión.
                </p>

                <div
                    class="relative mt-6 h-[420px] overflow-hidden rounded-xl border border-green-200"
                >
                    <div
                        ref="mapContainerRef"
                        class="h-full w-full"
                    />

                    <div
                        v-if="isMapLoading"
                        class="pointer-events-none absolute inset-0 flex items-center justify-center bg-white/70 text-green-700"
                    >
                        <svg
                            class="h-8 w-8 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            ></path>
                        </svg>
                    </div>

                    <div
                        v-if="mapError"
                        class="absolute inset-0 flex items-center justify-center bg-white/90 p-6 text-center text-sm text-red-600"
                    >
                        {{ mapError }}
                    </div>
                </div>

                <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-semibold">Indicaciones iniciales:</p>
                    <ol class="mt-2 list-decimal space-y-1 pl-5">
                        <li>Selecciona el área de tu terreno directamente en el mapa.</li>
                        <li>
                            Crea un mapa para el modo <strong>Jugar Modo Granja</strong>. Construye
                            y administra tu propia granja: siembra cultivos, cuida animales y expande tu
                            imperio agrícola en tiempo real.
                        </li>
                        <li>
                            Planifica tus cultivos considerando la humedad del aire, el tipo de suelo y otros
                            factores ambientales clave.
                        </li>
                    </ol>
                </div>

                <div class="mt-4 rounded-xl bg-green-50 p-4 text-sm text-green-800">
                    <p class="font-semibold">Sugerencias de uso:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>
                            Utiliza el mapa satelital para identificar las áreas óptimas de siembra dentro del polígono.
                        </li>
                        <li>
                            Ajusta la figura desde Google Maps para personalizar tus parcelas y compartirlas con tu equipo.
                        </li>
                        <li>
                            Configura la clave <code class="rounded bg-white px-1">VITE_GOOGLE_MAPS_API_KEY</code> para habilitar la edición en vivo del mapa.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap');

.font-fredoka {
    font-family: 'Fredoka', sans-serif;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
