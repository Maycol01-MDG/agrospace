<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Home, LogOut, User } from 'lucide-vue-next';
import * as THREE from 'three';
import { onMounted, ref } from 'vue';

// Función para cerrar sesión
const logout = () => {
    router.post('/logout');
};

// Referencias para el canvas y el juego
const canvasRef = ref<HTMLCanvasElement>();
const gameMode = ref<'explore' | 'plant'>('explore');

// Variables de Three.js
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let terrainTiles: THREE.Mesh[] = []; // Array de cuadros del terreno
let raycaster: THREE.Raycaster;
let mouse: THREE.Vector2;
let textureLoader: THREE.TextureLoader;
let originalMaterial: THREE.MeshStandardMaterial;
let plantMaterial: THREE.MeshStandardMaterial;

// Variables para el zoom
let zoomLevel = 1;
const MIN_ZOOM = 0.5;
const MAX_ZOOM = 3;
const ZOOM_SPEED = 0.1;

// Manejar zoom con rueda del mouse
const onCanvasWheel = (event: WheelEvent) => {
    event.preventDefault();

    // Calcular el nuevo nivel de zoom
    const delta = event.deltaY > 0 ? -ZOOM_SPEED : ZOOM_SPEED;
    const newZoomLevel = Math.max(
        MIN_ZOOM,
        Math.min(MAX_ZOOM, zoomLevel + delta),
    );

    if (newZoomLevel !== zoomLevel) {
        zoomLevel = newZoomLevel;

        // Aplicar zoom ajustando la posición de la cámara
        const baseDistance = 20; // Distancia base de la cámara
        const newDistance = baseDistance / zoomLevel;

        // Mantener la dirección de la cámara pero cambiar la distancia
        const direction = new THREE.Vector3();
        camera.getWorldDirection(direction);
        direction.negate(); // Invertir para obtener la dirección hacia la cámara

        // Calcular nueva posición manteniendo el ángulo
        const targetPosition = new THREE.Vector3(0, 0, 0); // Centro del terreno
        const newPosition = targetPosition
            .clone()
            .add(direction.multiplyScalar(newDistance));
        newPosition.y = Math.max(5, newPosition.y); // Mantener altura mínima

        camera.position.copy(newPosition);
        camera.lookAt(targetPosition);
    }
};

// Configuración del terreno
const GRID_SIZE = 10; // 6x6 cuadros
const TILE_SIZE = 2; // Cada cuadro de 3x3 unidades
const TOTAL_SIZE = GRID_SIZE * TILE_SIZE; // 18x18 unidades totales

// Inicializar el juego 3D
const initGame = () => {
    if (!canvasRef.value) return;

    // Crear la escena
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x87ceeb); // Color cielo azul

    // Crear el texture loader
    textureLoader = new THREE.TextureLoader();

    // Crear la cámara
    camera = new THREE.PerspectiveCamera(
        40,
        canvasRef.value.clientWidth / canvasRef.value.clientHeight,
        0.1,
        1000,
    );
    camera.position.set(0, 10, 20);
    camera.lookAt(0, 0, 0);

    // Crear el renderer
    renderer = new THREE.WebGLRenderer({
        canvas: canvasRef.value,
        antialias: true,
    });
    renderer.setSize(canvasRef.value.clientWidth, canvasRef.value.clientHeight);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    // Cargar texturas de Poliigon
    const baseColorTexture = textureLoader.load(
        '/textures/Poliigon_GrassPatchyGround_4585_BaseColor.jpg',
    );
    const normalTexture = textureLoader.load(
        '/textures/Poliigon_GrassPatchyGround_4585_Normal.png',
    );
    const roughnessTexture = textureLoader.load(
        '/textures/Poliigon_GrassPatchyGround_4585_Roughness.jpg',
    );
    const metallicTexture = textureLoader.load(
        '/textures/Poliigon_GrassPatchyGround_4585_Metallic.jpg',
    );

    // ✅ Hacer que la textura se repita
    baseColorTexture.wrapS = baseColorTexture.wrapT = THREE.RepeatWrapping;
    normalTexture.wrapS = normalTexture.wrapT = THREE.RepeatWrapping;
    roughnessTexture.wrapS = roughnessTexture.wrapT = THREE.RepeatWrapping;
    metallicTexture.wrapS = metallicTexture.wrapT = THREE.RepeatWrapping;

    // Aumentar la repetición para ver más detalle de la hierba
    const repeatTimes = 1; // Esto hará que la textura se repita más veces
    baseColorTexture.repeat.set(repeatTimes, repeatTimes);
    normalTexture.repeat.set(repeatTimes, repeatTimes);
    roughnessTexture.repeat.set(repeatTimes, repeatTimes);
    metallicTexture.repeat.set(repeatTimes, repeatTimes);

    // Configurar filtros para mejor calidad
    baseColorTexture.anisotropy = renderer.capabilities.getMaxAnisotropy();
    normalTexture.anisotropy = renderer.capabilities.getMaxAnisotropy();
    roughnessTexture.anisotropy = renderer.capabilities.getMaxAnisotropy();
    metallicTexture.anisotropy = renderer.capabilities.getMaxAnisotropy();

    // Material original con texturas de hierba (para cada cuadro individual)
    originalMaterial = new THREE.MeshStandardMaterial({
        map: baseColorTexture,
        normalMap: normalTexture,
        roughnessMap: roughnessTexture,
        metalnessMap: metallicTexture,
        metalness: 0.0,
        roughness: 1.0,
        normalScale: new THREE.Vector2(0, 0), // Aumentar efecto del normal map
    });

    // Material para modo siembra (tierra)
    plantMaterial = new THREE.MeshStandardMaterial({
        color: 0x8b4513,
        roughness: 0.9,
        metalness: 0.0,
    });

    // Crear cuadros individuales del terreno
    createTerrainTiles();

    // Agregar iluminación
    const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
    scene.add(ambientLight);

    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(10, 10, 5);
    directionalLight.castShadow = true;
    directionalLight.shadow.mapSize.width = 2048;
    directionalLight.shadow.mapSize.height = 2048;
    scene.add(directionalLight);

    // Configurar raycaster para detectar clicks
    raycaster = new THREE.Raycaster();
    mouse = new THREE.Vector2();

    // Event listener para clicks en el canvas
    canvasRef.value.addEventListener('click', onCanvasClick);

    // Event listener para zoom con rueda del mouse
    canvasRef.value.addEventListener('wheel', onCanvasWheel, {
        passive: false,
    });

    // Iniciar el loop de renderizado
    animate();
};

// Crear los cuadros individuales del terreno
const createTerrainTiles = () => {
    const tileGeometry = new THREE.PlaneGeometry(TILE_SIZE, TILE_SIZE, 16, 16);

    for (let x = 0; x < GRID_SIZE; x++) {
        for (let z = 0; z < GRID_SIZE; z++) {
            // Crear material único para cada cuadro
            const tileMaterial = originalMaterial.clone();

            // Crear el cuadro
            const tile = new THREE.Mesh(tileGeometry, tileMaterial);

            // Posicionar el cuadro
            const posX = (x - GRID_SIZE / 2 + 0.5) * TILE_SIZE;
            const posZ = (z - GRID_SIZE / 2 + 0.5) * TILE_SIZE;

            tile.position.set(posX, 0, posZ);
            tile.rotation.x = -Math.PI / 2;
            tile.receiveShadow = true;

            // Agregar propiedades personalizadas para identificar el cuadro
            tile.userData = {
                gridX: x,
                gridZ: z,
                isPlanted: false,
                originalMaterial: tileMaterial,
                plantMaterial: plantMaterial.clone(),
            };

            terrainTiles.push(tile);
            scene.add(tile);
        }
    }
};

// Manejar clicks en el canvas
const onCanvasClick = (event: MouseEvent) => {
    if (!canvasRef.value) return;

    const rect = canvasRef.value.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    raycaster.setFromCamera(mouse, camera);
    const intersects = raycaster.intersectObjects(terrainTiles);

    if (intersects.length > 0) {
        const clickedTile = intersects[0].object as THREE.Mesh;

        // Alternar entre modo exploración y siembra para el cuadro clickeado
        if (clickedTile.userData.isPlanted) {
            // Volver a hierba
            clickedTile.material = clickedTile.userData.originalMaterial;
            clickedTile.userData.isPlanted = false;
            gameMode.value = 'explore';
        } else {
            // Cambiar a tierra para siembra
            clickedTile.material = clickedTile.userData.plantMaterial;
            clickedTile.userData.isPlanted = true;
            gameMode.value = 'plant';
        }
    }
};

// Loop de animación
const animate = () => {
    requestAnimationFrame(animate);
    renderer.render(scene, camera);
};

// Manejar redimensionamiento de ventana
const handleResize = () => {
    if (!canvasRef.value) return;

    camera.aspect = canvasRef.value.clientWidth / canvasRef.value.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(canvasRef.value.clientWidth, canvasRef.value.clientHeight);
};

// Resetear el modo de juego
const resetGameMode = () => {
    gameMode.value = 'explore';
    terrainTiles.forEach((tile) => {
        tile.material = tile.userData.originalMaterial;
        tile.userData.isPlanted = false;
    });
};

onMounted(() => {
    initGame();
    window.addEventListener('resize', handleResize);
});
</script>

<template>
    <Head title="Granja - AgroSpace" />

    <div
        class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-amber-50"
    >
        <!-- Top Bar -->
        <header
            class="border-b border-green-200 bg-white/80 shadow-sm backdrop-blur-sm"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo/Título -->
                    <div class="flex items-center space-x-4">
                        <h1
                            class="font-fredoka text-2xl font-bold text-green-800"
                        >
                            AgroSpace - Mi Granja
                        </h1>

                        <!-- Indicador de modo -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600"
                                >Modo:</span
                            >
                            <span
                                :class="
                                    gameMode === 'explore'
                                        ? 'bg-blue-100 text-blue-800'
                                        : 'bg-orange-100 text-orange-800'
                                "
                                class="rounded-full px-3 py-1 text-sm font-semibold"
                            >
                                {{
                                    gameMode === 'explore'
                                        ? 'Explorar'
                                        : 'Listo para Sembrar'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Opciones de usuario -->
                    <div class="flex items-center space-x-4">
                        <!-- Volver al Dashboard -->
                        <Link
                            href="/dashboard"
                            class="font-fredoka flex items-center space-x-2 rounded-lg bg-blue-100 px-4 py-2 font-semibold text-blue-800 transition-colors hover:bg-blue-200"
                        >
                            <Home class="h-5 w-5" />
                            <span>Dashboard</span>
                        </Link>

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

        <!-- Contenido Principal -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Panel de Control -->
            <div
                class="mb-6 rounded-lg bg-white/80 p-4 shadow-sm backdrop-blur-sm"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">
                            Control de la Granja
                        </h2>
                        <p class="text-sm text-gray-600">
                            {{
                                gameMode === 'explore'
                                    ? 'Haz click en el terreno para prepararlo para la siembra'
                                    : 'El terreno está listo para sembrar. Haz click en "Resetear" para volver al modo exploración'
                            }}
                        </p>
                    </div>

                    <button
                        v-if="gameMode === 'plant'"
                        @click="resetGameMode"
                        class="rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-800 transition-colors hover:bg-gray-200"
                    >
                        Resetear Modo
                    </button>
                </div>
            </div>

            <!-- Canvas del Juego -->
            <div class="rounded-lg bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                <div class="aspect-video w-full overflow-hidden rounded-lg">
                    <canvas
                        ref="canvasRef"
                        class="h-full w-full cursor-pointer"
                        style="display: block"
                    />
                </div>
            </div>

            <!-- Instrucciones -->
            <div
                class="mt-6 rounded-lg bg-white/80 p-4 shadow-sm backdrop-blur-sm"
            >
                <h3 class="mb-2 text-lg font-semibold text-gray-800">
                    Instrucciones
                </h3>
                <ul class="space-y-1 text-sm text-gray-600">
                    <li>
                        • Haz click en cualquier parte del terreno verde para
                        prepararlo para la siembra
                    </li>
                    <li>
                        • El terreno cambiará de color a marrón cuando esté
                        listo para sembrar
                    </li>
                    <li>
                        • Usa la rueda del mouse para hacer zoom dentro del
                        canvas (acercar/alejar)
                    </li>
                    <li>
                        • Usa el botón "Resetear Modo" para volver al modo
                        exploración
                    </li>
                    <li>
                        • El juego usa Three.js para renderizar el terreno 3D
                    </li>
                </ul>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
