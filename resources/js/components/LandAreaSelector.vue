<template>
    <div class="land-area-selector">
        <!-- Mapa -->
        <div
            ref="mapContainer"
            class="h-96 w-full rounded-xl border border-green-300 shadow-lg"
            style="min-height: 400px"
        ></div>

        <!-- Información del área calculada -->
        <div
            v-if="areaData.size > 0"
            class="mt-4 rounded-xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4 shadow-sm"
        >
            <h4
                class="font-fredoka mb-2 flex items-center gap-2 font-semibold text-green-800"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                </svg>
                Información del área
            </h4>
            <p class="font-fredoka text-sm text-green-700">
                <strong>Área calculada:</strong>
                {{ areaData.size.toFixed(2) }} hectáreas
            </p>
            <p class="font-fredoka mt-1 text-sm text-green-700">
                <strong>Puntos definidos:</strong>
                {{ areaData.coordinates.length }}
            </p>
        </div>

        <!-- Coordenadas detalladas -->
        <div
            v-if="areaData.coordinates.length > 0"
            class="mt-4 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-sky-50 p-4 shadow-sm"
        >
            <h4
                class="font-fredoka mb-2 flex items-center gap-2 font-semibold text-blue-800"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    ></path>
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    ></path>
                </svg>
                Coordenadas
            </h4>
            <div class="max-h-32 overflow-y-auto">
                <div
                    v-for="(coord, index) in areaData.coordinates"
                    :key="index"
                    class="font-fredoka text-xs text-blue-700"
                >
                    Punto {{ index + 1 }}: {{ coord.lat.toFixed(6) }},
                    {{ coord.lng.toFixed(6) }}
                </div>
            </div>
        </div>

        <!-- Campos ocultos para el formulario -->
        <input
            type="hidden"
            name="land_area_coordinates"
            :value="JSON.stringify(areaData.coordinates)"
        />
        <input type="hidden" name="land_area_size" :value="areaData.size" />
        <input type="hidden" name="land_area_name" :value="areaData.name" />
        <input
            type="hidden"
            name="land_area_description"
            :value="areaData.description"
        />
    </div>
</template>

<script setup lang="ts">
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { nextTick, onMounted, reactive, ref } from 'vue';
// @ts-ignore
import 'leaflet-geometryutil';

// Fix para los iconos de Leaflet
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

const mapContainer = ref<HTMLElement>();
let map: L.Map | null = null;
let polygon: L.Polygon | null = null;
let markers: L.Marker[] = [];

const areaData = reactive({
    coordinates: [] as Array<{ lat: number; lng: number }>,
    size: 0,
    name: '',
    description: '',
});

const initMap = async () => {
    await nextTick();

    if (!mapContainer.value) {
        console.error('Map container not found');
        return;
    }

    try {
        // Agregar capa satelital como opción
        const satelliteLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                attribution: 'Tiles © Esri',
            },
        );

        // Inicializar el mapa centrado en Cajamarca, Perú con vista satelital por defecto
        map = L.map(mapContainer.value).setView([-7.156801, -78.516861], 15);
        
        // Agregar la capa satelital por defecto
        satelliteLayer.addTo(map);

        // Crear capa de OpenStreetMap para el control de capas
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
        });

        const baseMaps = {
            Satélite: satelliteLayer,
            Mapa: osmLayer,
        };

        L.control.layers(baseMaps).addTo(map);

        // Evento de clic en el mapa
        map.on('click', onMapClick);

        console.log('Map initialized successfully');
    } catch (error) {
        console.error('Error initializing map:', error);
    }
};

const onMapClick = (e: L.LeafletMouseEvent) => {
    const { lat, lng } = e.latlng;

    // Agregar coordenada
    areaData.coordinates.push({ lat, lng });

    // Agregar marcador
    const marker = L.marker([lat, lng]).addTo(map!);
    markers.push(marker);

    // Actualizar polígono
    updatePolygon();
};

const updatePolygon = () => {
    if (!map) return;

    // Remover polígono anterior
    if (polygon) {
        map.removeLayer(polygon);
    }

    // Crear nuevo polígono si hay al menos 3 puntos
    if (areaData.coordinates.length >= 3) {
        const latLngs = areaData.coordinates.map(
            (coord) => [coord.lat, coord.lng] as [number, number],
        );

        polygon = L.polygon(latLngs, {
            color: '#3b82f6',
            fillColor: '#3b82f6',
            fillOpacity: 0.2,
            weight: 2,
        }).addTo(map);

        // Ajustar vista al polígono
        map.fitBounds(polygon.getBounds(), { padding: [20, 20] });
    }
};

const calculateArea = () => {
    if (!polygon) {
        alert('Debes definir al menos 3 puntos para calcular el área');
        return;
    }

    // Calcular área en metros cuadrados y convertir a hectáreas
    const areaInSquareMeters = L.GeometryUtil.geodesicArea(
        polygon.getLatLngs()[0] as L.LatLng[],
    );
    areaData.size = areaInSquareMeters / 10000; // Convertir a hectáreas
};

const clearArea = () => {
    if (!map) return;

    // Limpiar coordenadas
    areaData.coordinates = [];
    areaData.size = 0;

    // Remover marcadores
    markers.forEach((marker) => map!.removeLayer(marker));
    markers = [];

    // Remover polígono
    if (polygon) {
        map.removeLayer(polygon);
        polygon = null;
    }
};

onMounted(async () => {
    await initMap();
});

// Exponer métodos para que puedan ser llamados desde el componente padre
defineExpose({
    clearArea,
    calculateArea
});
</script>

<style scoped>
.land-area-selector {
    @apply w-full h-full flex flex-col;
}

/* Estilos optimizados para Leaflet */
:deep(.leaflet-container) {
    font-family: 'Fredoka', sans-serif;
    border-radius: 0.75rem;
}

:deep(.leaflet-popup-content-wrapper) {
    border-radius: 0.5rem;
    font-family: 'Fredoka', sans-serif;
}

:deep(.leaflet-control-layers) {
    border-radius: 0.5rem;
    font-family: 'Fredoka', sans-serif;
}

:deep(.leaflet-control-zoom a) {
    border-radius: 0.25rem;
}
</style>
