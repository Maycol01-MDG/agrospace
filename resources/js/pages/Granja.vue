<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Home, LogOut, User } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';

type CropKey = 'papa' | 'alfalfa' | 'maiz' | 'zanahoria';
type SeasonName = 'Primavera' | 'Verano' | 'Otoño' | 'Invierno';
type FertilizerType = 'natural' | 'chemical';

type Tool =
    | 'build'
    | 'plant'
    | 'water'
    | 'harvest'
    | 'fertilizer-natural'
    | 'fertilizer-chemical';

type PestType = 'Gusanos' | 'Hongos' | 'Pájaros';

interface CropDefinition {
    label: string;
    description: string;
    baseTime: number;
    baseYield: number;
    baseCoins: number;
    baseXp: number;
    seasonBoost?: Partial<Record<SeasonName, number>>;
}

interface SeasonDefinition {
    name: SeasonName;
    description: string;
    drain: number;
    bonuses: Partial<Record<CropKey, number>>;
    color: string;
}

interface FertilizerState {
    type: FertilizerType;
    remaining: number;
}

interface CropInstance {
    type: CropKey;
    growth: number;
    required: number;
}

interface TileState {
    id: number;
    x: number;
    y: number;
    type: 'wild' | 'soil';
    moisture: number;
    crop: CropInstance | null;
    pest: PestType | null;
    fertilizer: FertilizerState | null;
    chemicalResidue: boolean;
}

interface Mission {
    id: string;
    level: number;
    title: string;
    description: string;
    check: () => boolean;
    xpReward: number;
    coinReward: number;
    isComplete: boolean;
}

const logout = () => {
    router.post('/logout');
};

const GRID_SIZE = 5;
const TICK_INTERVAL = 1000;
const SEASON_DURATION = 45000;

const crops: Record<CropKey, CropDefinition> = {
    papa: {
        label: 'Papa',
        description: 'Tubérculo noble y rendidor, ideal para el otoño.',
        baseTime: 60,
        baseYield: 3,
        baseCoins: 35,
        baseXp: 28,
        seasonBoost: {
            Otoño: 1.25,
        },
    },
    alfalfa: {
        label: 'Alfalfa',
        description: 'Forraje nutritivo que ama la primavera.',
        baseTime: 55,
        baseYield: 4,
        baseCoins: 28,
        baseXp: 22,
        seasonBoost: {
            Primavera: 1.3,
        },
    },
    maiz: {
        label: 'Maíz',
        description: 'Crece alto en verano, pero exige mucha agua.',
        baseTime: 70,
        baseYield: 5,
        baseCoins: 40,
        baseXp: 30,
        seasonBoost: {
            Verano: 1.25,
        },
    },
    zanahoria: {
        label: 'Zamaroria',
        description: 'Zanahoria resistente al frío, perfecta para el invierno.',
        baseTime: 65,
        baseYield: 4,
        baseCoins: 34,
        baseXp: 26,
        seasonBoost: {
            Invierno: 1.2,
        },
    },
};

const seasons: SeasonDefinition[] = [
    {
        name: 'Primavera',
        description: 'Mejores cosechas de alfalfa y clima templado.',
        drain: 4,
        bonuses: { alfalfa: 1.2 },
        color: 'bg-green-100 text-green-800',
    },
    {
        name: 'Verano',
        description: 'El maíz crece más rápido pero necesita más agua.',
        drain: 6,
        bonuses: { maiz: 1.25 },
        color: 'bg-yellow-100 text-yellow-800',
    },
    {
        name: 'Otoño',
        description: 'Tiempo de papas con mejor rendimiento.',
        drain: 4,
        bonuses: { papa: 1.2 },
        color: 'bg-orange-100 text-orange-800',
    },
    {
        name: 'Invierno',
        description: 'La zamaroria resiste mejor al frío.',
        drain: 5,
        bonuses: { zanahoria: 1.15 },
        color: 'bg-blue-100 text-blue-800',
    },
];

const irrigationSystems = [
    {
        level: 1,
        name: 'Riego por gravedad',
        detail: 'Baldes y canales manuales. ¡A ejercitar esos brazos!',
        multiplier: 1,
    },
    {
        level: 2,
        name: 'Riego básico con canales',
        detail: 'Zanjas y tuberías sencillas para humedecer varias parcelas.',
        multiplier: 1.15,
    },
    {
        level: 3,
        name: 'Riego por aspersión',
        detail: 'Aspersores automáticos que mantienen la humedad.',
        multiplier: 1.3,
    },
    {
        level: 4,
        name: 'Riego automatizado inteligente',
        detail: 'Sensores de humedad que riegan solos.',
        multiplier: 1.45,
    },
];

const pestTypes: PestType[] = ['Gusanos', 'Hongos', 'Pájaros'];

const createTiles = () =>
    Array.from({ length: GRID_SIZE * GRID_SIZE }, (_, index): TileState => ({
        id: index,
        x: Math.floor(index / GRID_SIZE),
        y: index % GRID_SIZE,
        type: 'wild',
        moisture: 50,
        crop: null,
        pest: null,
        fertilizer: null,
        chemicalResidue: false,
    }));

const tiles = reactive<TileState[]>(createTiles());

const player = reactive({
    level: 1,
    xp: 0,
    coins: 120,
});

const hasChickens = ref(false);
const hasBees = ref(false);

const availableCrops = computed(() => {
    if (player.level >= 4) {
        return ['papa', 'alfalfa', 'maiz', 'zanahoria'] satisfies CropKey[];
    }
    if (player.level === 3) {
        return ['papa', 'alfalfa', 'maiz', 'zanahoria'] satisfies CropKey[];
    }
    if (player.level === 2) {
        return ['papa', 'alfalfa', 'maiz'] satisfies CropKey[];
    }
    return ['papa', 'alfalfa'] satisfies CropKey[];
});

const selectedCrop = ref<CropKey>('papa');

watch(availableCrops, (list) => {
    if (!list.includes(selectedCrop.value)) {
        selectedCrop.value = list[0];
    }
});

const selectedTool = ref<Tool>('build');
const eventLog = ref<string[]>([]);

const currentSeasonIndex = ref(0);
const seasonTimer = ref<number>();
const gameTimer = ref<number>();

const progress = reactive({
    builtPlots: 0,
    harvestedCrops: 0,
    wateringActions: 0,
    pestsCleared: 0,
    automationOnline: false,
});

const missions = reactive<Mission[]>([
    {
        id: 'level-1',
        level: 1,
        title: 'Nivel 1: aprender a sembrar y cosechar',
        description: 'Construye un bloque de cultivo y cosecha tu primera planta.',
        xpReward: 30,
        coinReward: 25,
        isComplete: false,
        check: () => progress.builtPlots >= 1 && progress.harvestedCrops >= 1,
    },
    {
        id: 'level-2',
        level: 2,
        title: 'Nivel 2: dominar riego básico',
        description: 'Usa el riego para mantener al menos tres parcelas saludables.',
        xpReward: 45,
        coinReward: 40,
        isComplete: false,
        check: () => player.level >= 2 && progress.wateringActions >= 3,
    },
    {
        id: 'level-3',
        level: 3,
        title: 'Nivel 3: proteger cultivos de plagas',
        description: 'Controla plagas en dos parcelas usando tus fertilizantes.',
        xpReward: 60,
        coinReward: 55,
        isComplete: false,
        check: () => player.level >= 3 && progress.pestsCleared >= 2,
    },
    {
        id: 'level-4',
        level: 4,
        title: 'Nivel 4: automatizar el riego y mejorar la productividad',
        description: 'Activa el sistema inteligente y cosecha con sensores en marcha.',
        xpReward: 80,
        coinReward: 70,
        isComplete: false,
        check: () => player.level >= 4 && progress.automationOnline,
    },
]);

const xpGoals = [0, 120, 280, 520, 0];

const irrigationMultiplier = computed(() => {
    const system = irrigationSystems[player.level - 1] ?? irrigationSystems[0];
    return system.multiplier;
});

const xpProgress = computed(() => {
    const goal = xpGoals[player.level] ?? xpGoals[xpGoals.length - 1];
    const currentGoal = xpGoals[player.level - 1] ?? 0;
    const span = goal - currentGoal || 1;
    return Math.min(100, ((player.xp - currentGoal) / span) * 100);
});

const currentSeason = computed(() => seasons[currentSeasonIndex.value]);

const pushLog = (message: string) => {
    const timestamp = new Date().toLocaleTimeString();
    eventLog.value.unshift(`[${timestamp}] ${message}`);
    if (eventLog.value.length > 8) {
        eventLog.value.pop();
    }
};

const adjustCoins = (amount: number) => {
    player.coins = Math.max(0, player.coins + amount);
};

const awardXp = (amount: number) => {
    player.xp += amount;
    checkLevelUp();
};

const buildSoil = (tile: TileState) => {
    if (tile.type === 'soil') {
        pushLog('Esta parcela ya está preparada para cultivar.');
        return;
    }
    const cost = 12;
    if (player.coins < cost) {
        pushLog('Necesitas más monedas para preparar este terreno.');
        return;
    }
    adjustCoins(-cost);
    tile.type = 'soil';
    tile.moisture = 60;
    pushLog('Has preparado un nuevo bloque de tierra fértil.');
    progress.builtPlots += 1;
    updateMissions();
};

const plantCrop = (tile: TileState) => {
    if (tile.type !== 'soil') {
        pushLog('Primero prepara la parcela antes de sembrar.');
        return;
    }
    if (tile.crop) {
        pushLog('Ya hay un cultivo creciendo aquí.');
        return;
    }
    const cropKey = selectedCrop.value;
    if (!availableCrops.value.includes(cropKey)) {
        pushLog('Aún no has desbloqueado esta semilla.');
        return;
    }
    const seedCost = 8;
    if (player.coins < seedCost) {
        pushLog('Necesitas más monedas para comprar estas semillas.');
        return;
    }
    adjustCoins(-seedCost);
    const cropDefinition = crops[cropKey];
    tile.crop = {
        type: cropKey,
        growth: 0,
        required: cropDefinition.baseTime,
    };
    tile.moisture = Math.max(tile.moisture, 60);
    tile.pest = null;
    tile.chemicalResidue = false;
    pushLog(`Sembraste ${cropDefinition.label}. ¡Cuídala bien!`);
};

const waterTile = (tile: TileState) => {
    if (tile.type !== 'soil') {
        pushLog('Primero convierte esta parcela en tierra cultivable.');
        return;
    }
    const tilesToWater = player.level >= 2 ? tiles.filter((t) => t.x === tile.x) : [tile];
    const hydration = player.level >= 3 ? 50 : 35;
    tilesToWater.forEach((plot) => {
        plot.moisture = Math.min(100, plot.moisture + hydration);
    });
    progress.wateringActions += 1;
    pushLog(
        player.level >= 2
            ? 'El agua fluye por los canales y humedece toda la hilera.'
            : 'Has regado la parcela manualmente.',
    );
    updateMissions();
};

const applyFertilizer = (tile: TileState, type: FertilizerType) => {
    if (tile.type !== 'soil') {
        pushLog('Necesitas un campo preparado para aplicar fertilizantes.');
        return;
    }
    if (!tile.crop) {
        pushLog('No hay plantas para nutrir en esta parcela.');
        return;
    }

    const cost = type === 'natural' ? (hasChickens.value ? 0 : 6) : 12;
    if (player.coins < cost) {
        pushLog('No tienes suficientes monedas para este fertilizante.');
        return;
    }
    adjustCoins(-cost);

    if (type === 'natural') {
        tile.fertilizer = { type: 'natural', remaining: 45 };
        tile.moisture = Math.min(100, tile.moisture + 20);
        if (tile.pest) {
            tile.pest = null;
            pushLog('El fertilizante natural controló la plaga de forma sostenible.');
            progress.pestsCleared += 1;
        } else {
            pushLog('Aplicaste compost y abonos naturales. La tierra revive.');
        }
    } else {
        tile.fertilizer = { type: 'chemical', remaining: 60 };
        tile.moisture = Math.min(100, tile.moisture + 10);
        tile.pest = null;
        tile.chemicalResidue = true;
        progress.pestsCleared += 1;
        pushLog('El fertilizante químico eliminó la plaga y acelerará el crecimiento.');
    }
    updateMissions();
};

const harvestTile = (tile: TileState) => {
    if (!tile.crop) {
        pushLog('No hay cultivos listos para cosechar aquí.');
        return;
    }
    if (tile.crop.growth < tile.crop.required) {
        pushLog('Este cultivo aún necesita más tiempo.');
        return;
    }

    const cropDefinition = crops[tile.crop.type];
    const beesBonus = hasBees.value ? 1.2 : 1;
    const fertilizerBonus =
        tile.fertilizer?.type === 'natural'
            ? 1.1
            : tile.fertilizer?.type === 'chemical'
            ? 1.25
            : 1;
    const seasonBonus = currentSeason.value.bonuses[tile.crop.type] ?? 1;
    const yieldAmount = Math.round(
        cropDefinition.baseYield * beesBonus * fertilizerBonus * seasonBonus,
    );
    const coinReward = Math.round(
        cropDefinition.baseCoins * yieldAmount * (tile.chemicalResidue ? 0.95 : 1),
    );
    const xpReward = Math.round(
        cropDefinition.baseXp * (tile.chemicalResidue ? 0.9 : 1) * fertilizerBonus,
    );

    adjustCoins(coinReward);
    awardXp(xpReward);
    progress.harvestedCrops += 1;

    pushLog(
        `Cosechaste ${cropDefinition.label} y obtuviste ${coinReward} monedas y ${xpReward} XP.`,
    );

    if (hasChickens.value) {
        pushLog('Tus gallinas generaron abono natural adicional.');
        tile.fertilizer = { type: 'natural', remaining: 30 };
    } else {
        tile.fertilizer = null;
    }

    tile.crop = null;
    tile.pest = null;
    tile.chemicalResidue = false;

    updateMissions();
};

const handleTileClick = (tile: TileState) => {
    switch (selectedTool.value) {
        case 'build':
            buildSoil(tile);
            break;
        case 'plant':
            plantCrop(tile);
            break;
        case 'water':
            waterTile(tile);
            break;
        case 'harvest':
            harvestTile(tile);
            break;
        case 'fertilizer-natural':
            applyFertilizer(tile, 'natural');
            break;
        case 'fertilizer-chemical':
            applyFertilizer(tile, 'chemical');
            break;
    }
};

const pestChance = () => {
    let base = 0.02;
    if (currentSeason.value.name === 'Primavera') {
        base += 0.01;
    }
    if (currentSeason.value.name === 'Verano') {
        base += 0.015;
    }
    return base;
};

const spawnPests = (tile: TileState) => {
    if (!tile.crop || tile.pest) {
        return;
    }
    if (tile.fertilizer?.type === 'natural') {
        return;
    }
    const chance = pestChance();
    if (Math.random() < chance) {
        tile.pest = pestTypes[Math.floor(Math.random() * pestTypes.length)];
        pushLog(`Han aparecido ${tile.pest?.toLowerCase()} en una parcela.`);
    }
};

const irrigationMultiplierValue = computed(() => irrigationMultiplier.value);

const handleTick = () => {
    tiles.forEach((tile) => {
        if (tile.type !== 'soil') {
            tile.moisture = Math.max(0, tile.moisture - 2);
            return;
        }

        const seasonalDrain = seasons[currentSeasonIndex.value].drain;
        let moistureDrain = seasonalDrain;
        if (tile.crop?.type === 'maiz' && currentSeason.value.name === 'Verano') {
            moistureDrain += 2;
        }
        if (tile.crop?.type === 'zanahoria' && currentSeason.value.name === 'Invierno') {
            moistureDrain = Math.max(1, moistureDrain - 2);
        }

        const irrigationHelp = player.level >= 4 ? moistureDrain : player.level;
        const finalDrain = player.level >= 4 ? 0 : Math.max(1, moistureDrain - irrigationHelp);
        tile.moisture = Math.max(0, tile.moisture - finalDrain);

        if (tile.crop) {
            spawnPests(tile);
            if (tile.fertilizer) {
                tile.fertilizer.remaining -= 1;
                if (tile.fertilizer.remaining <= 0) {
                    tile.fertilizer = null;
                }
            }

            const moistureFactor = tile.moisture >= 60 ? 1 : tile.moisture >= 30 ? 0.6 : 0.2;
            const irrigationBonus = irrigationMultiplierValue.value;
            const fertilizerBonus =
                tile.fertilizer?.type === 'natural'
                    ? 1.15
                    : tile.fertilizer?.type === 'chemical'
                    ? 1.35
                    : 1;
            const pestPenalty = tile.pest ? 0 : 1;
            const seasonBonus =
                seasons[currentSeasonIndex.value].bonuses[tile.crop.type] ??
                (crops[tile.crop.type].seasonBoost?.[currentSeason.value.name] ?? 1);
            const beesBonus = hasBees.value ? 1.1 : 1;

            const growthIncrease =
                1 * moistureFactor * irrigationBonus * fertilizerBonus * pestPenalty * seasonBonus * beesBonus;

            tile.crop.growth = Math.min(tile.crop.required, tile.crop.growth + growthIncrease);
        }

        if (player.level >= 4 && tile.type === 'soil') {
            tile.moisture = Math.max(tile.moisture, 70);
            progress.automationOnline = true;
        }
    });
};

const advanceSeason = () => {
    currentSeasonIndex.value = (currentSeasonIndex.value + 1) % seasons.length;
    pushLog(`Ha llegado ${currentSeason.value.name}. ${currentSeason.value.description}`);
};

const xpGoalsComputed = computed(() => xpGoals);

const checkLevelUp = () => {
    const goals = xpGoalsComputed.value;
    while (player.level < 4 && player.xp >= goals[player.level]) {
        player.level += 1;
        const system = irrigationSystems[player.level - 1];
        adjustCoins(50);
        pushLog(
            `¡Ascendiste al nivel ${player.level}! Desbloqueaste ${system.name} y nuevas recompensas.`,
        );
        updateMissions();
    }
};

const updateMissions = () => {
    missions.forEach((mission) => {
        if (!mission.isComplete && mission.check()) {
            mission.isComplete = true;
            adjustCoins(mission.coinReward);
            awardXp(mission.xpReward);
            pushLog(`Completaste "${mission.title}". Recompensa entregada.`);
        }
    });
};

const resetGame = () => {
    tiles.splice(0, tiles.length, ...createTiles());
    player.level = 1;
    player.xp = 0;
    player.coins = 120;
    hasBees.value = false;
    hasChickens.value = false;
    progress.builtPlots = 0;
    progress.harvestedCrops = 0;
    progress.pestsCleared = 0;
    progress.wateringActions = 0;
    progress.automationOnline = false;
    missions.forEach((mission) => {
        mission.isComplete = false;
    });
    currentSeasonIndex.value = 0;
    eventLog.value = [];
    pushLog('La granja ha sido reiniciada. ¡Hora de comenzar de nuevo!');
};

const buyChickens = () => {
    if (hasChickens.value) {
        pushLog('Ya tienes gallinas felices en tu granja.');
        return;
    }
    const cost = 80;
    if (player.coins < cost) {
        pushLog('Necesitas más monedas para construir el gallinero.');
        return;
    }
    adjustCoins(-cost);
    hasChickens.value = true;
    pushLog('Compraste gallinas. Ahora producirán abono natural para tus cultivos.');
};

const buyBees = () => {
    if (hasBees.value) {
        pushLog('Tus abejas ya están polinizando a toda máquina.');
        return;
    }
    const cost = 90;
    if (player.coins < cost) {
        pushLog('Necesitas más monedas para instalar las colmenas.');
        return;
    }
    adjustCoins(-cost);
    hasBees.value = true;
    pushLog('Las abejas han llegado. La polinización mejorará tus cosechas.');
};

onMounted(() => {
    pushLog('Bienvenido a AgroSpace. ¡Construye tu granja sostenible!');
    gameTimer.value = window.setInterval(handleTick, TICK_INTERVAL);
    seasonTimer.value = window.setInterval(advanceSeason, SEASON_DURATION);
});

onBeforeUnmount(() => {
    if (gameTimer.value) {
        clearInterval(gameTimer.value);
    }
    if (seasonTimer.value) {
        clearInterval(seasonTimer.value);
    }
});
</script>

<template>
    <Head title="Granja - AgroSpace" />

    <div class="min-h-screen bg-gradient-to-br from-lime-50 via-sky-50 to-amber-50">
        <header class="border-b border-emerald-200 bg-white/80 shadow-sm backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h1 class="font-fredoka text-2xl font-bold text-emerald-700">
                            AgroSpace - Mi Granja Sostenible
                        </h1>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600">Temporada:</span>
                            <span :class="`rounded-full px-3 py-1 text-sm font-semibold ${currentSeason.color}`">
                                {{ currentSeason.name }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link
                            href="/dashboard"
                            class="font-fredoka flex items-center space-x-2 rounded-lg bg-blue-100 px-4 py-2 font-semibold text-blue-800 transition-colors hover:bg-blue-200"
                        >
                            <Home class="h-5 w-5" />
                            <span>Dashboard</span>
                        </Link>
                        <Link
                            href="/profile"
                            class="font-fredoka flex items-center space-x-2 rounded-lg bg-emerald-100 px-4 py-2 font-semibold text-emerald-800 transition-colors hover:bg-emerald-200"
                        >
                            <User class="h-5 w-5" />
                            <span>Perfil</span>
                        </Link>
                        <button
                            @click="logout"
                            class="font-fredoka flex items-center space-x-2 rounded-lg bg-rose-100 px-4 py-2 font-semibold text-rose-800 transition-colors hover:bg-rose-200"
                        >
                            <LogOut class="h-5 w-5" />
                            <span>Salir</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
                <section class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg bg-white/85 p-4 shadow-sm">
                            <h2 class="text-lg font-semibold text-gray-800">Progreso del agricultor</h2>
                            <div class="mt-3 space-y-3 text-sm text-gray-600">
                                <p>
                                    Nivel actual:
                                    <span class="font-semibold text-emerald-600">{{ player.level }}</span>
                                </p>
                                <div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <span>Experiencia</span>
                                        <span class="font-semibold text-emerald-600">{{ player.xp }} XP</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-emerald-100">
                                        <div
                                            class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 transition-all"
                                            :style="{ width: `${xpProgress}%` }"
                                        />
                                    </div>
                                </div>
                                <p>
                                    Monedas disponibles:
                                    <span class="font-semibold text-amber-600">{{ player.coins }}</span>
                                </p>
                                <p>
                                    Sistema de riego:
                                    <span class="font-semibold text-sky-600">
                                        {{ irrigationSystems[player.level - 1]?.name }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ irrigationSystems[player.level - 1]?.detail }}
                                </p>
                            </div>
                        </div>
                        <div class="rounded-lg bg-white/85 p-4 shadow-sm">
                            <h2 class="text-lg font-semibold text-gray-800">Calendario agrícola</h2>
                            <p class="mt-2 text-sm text-gray-600">{{ currentSeason.description }}</p>
                            <div class="mt-4 grid grid-cols-4 gap-2 text-xs font-semibold">
                                <div
                                    v-for="(season, index) in seasons"
                                    :key="season.name"
                                    class="rounded-md border p-2 text-center"
                                    :class="[
                                        index === currentSeasonIndex
                                            ? 'border-emerald-400 bg-emerald-50 text-emerald-700'
                                            : 'border-gray-200 bg-gray-50 text-gray-500',
                                    ]"
                                >
                                    <p>{{ season.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white/90 p-4 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800">
                                    Herramientas y semillas disponibles
                                </h2>
                                <p class="text-sm text-gray-600">
                                    Selecciona una herramienta y luego haz clic en una parcela para usarla.
                                </p>
                            </div>
                            <button
                                @click="resetGame"
                                class="rounded-lg bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-600 transition-colors hover:bg-rose-200"
                            >
                                Reiniciar granja
                            </button>
                        </div>

                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold text-gray-700">Herramientas</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="tool in [
                                            { id: 'build', label: 'Construir bloque' },
                                            { id: 'plant', label: 'Sembrar' },
                                            { id: 'water', label: 'Regar' },
                                            { id: 'fertilizer-natural', label: 'Fert. natural' },
                                            { id: 'fertilizer-chemical', label: 'Fert. químico' },
                                            { id: 'harvest', label: 'Cosechar' },
                                        ]"
                                        :key="tool.id"
                                        @click="selectedTool = tool.id as Tool"
                                        :class="[
                                            'rounded-lg px-3 py-2 text-xs font-semibold transition-colors',
                                            selectedTool === (tool.id as Tool)
                                                ? 'bg-emerald-500 text-white shadow'
                                                : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200',
                                        ]"
                                    >
                                        {{ tool.label }}
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold text-gray-700">Semillas</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="cropKey in availableCrops"
                                        :key="cropKey"
                                        @click="selectedCrop = cropKey"
                                        :class="[
                                            'rounded-lg px-3 py-2 text-xs font-semibold transition-colors',
                                            selectedCrop === cropKey
                                                ? 'bg-amber-500 text-white shadow'
                                                : 'bg-amber-100 text-amber-700 hover:bg-amber-200',
                                        ]"
                                    >
                                        {{ crops[cropKey].label }}
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ crops[selectedCrop].description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white/90 p-4 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-800">Campo de cultivo</h2>
                        <p class="text-sm text-gray-600">
                            Construye bloques de tierra, siembra y usa el riego adecuado para avanzar de nivel.
                        </p>
                        <div class="mt-4 grid gap-2 sm:grid-cols-5">
                            <button
                                v-for="tile in tiles"
                                :key="tile.id"
                                @click="handleTileClick(tile)"
                                class="relative flex h-28 flex-col justify-between rounded-lg border p-2 text-left transition-transform hover:-translate-y-1"
                                :class="[
                                    tile.type === 'soil'
                                        ? 'border-amber-200 bg-amber-50'
                                        : 'border-lime-200 bg-lime-50',
                                    tile.crop && tile.crop.growth >= tile.crop.required
                                        ? 'ring-2 ring-emerald-400'
                                        : '',
                                    tile.pest ? 'animate-pulse border-rose-300' : '',
                                ]"
                            >
                                <div class="flex items-center justify-between text-xs font-semibold">
                                    <span>
                                        {{ tile.type === 'soil' ? 'Parcela' : 'Terreno' }}
                                    </span>
                                    <span class="text-emerald-600">
                                        {{ tile.moisture }}%
                                    </span>
                                </div>
                                <div class="flex flex-1 items-center justify-center text-center text-sm font-bold">
                                    <span v-if="tile.crop">
                                        {{ crops[tile.crop.type].label }}
                                    </span>
                                    <span v-else class="text-emerald-500">Disponible</span>
                                </div>
                                <div class="space-y-1 text-[10px] text-gray-600">
                                    <div v-if="tile.crop" class="h-1.5 w-full overflow-hidden rounded-full bg-emerald-100">
                                        <div
                                            class="h-full rounded-full bg-emerald-500"
                                            :style="{
                                                width: `${Math.min(
                                                    100,
                                                    (tile.crop.growth / tile.crop.required) * 100,
                                                )}%`,
                                            }"
                                        />
                                    </div>
                                    <p v-if="tile.pest" class="text-rose-500">
                                        {{ tile.pest }} atacan la parcela
                                    </p>
                                    <p v-else-if="tile.crop && tile.crop.growth >= tile.crop.required" class="text-emerald-500">
                                        ¡Listo para cosechar!
                                    </p>
                                    <p v-else-if="tile.crop" class="text-gray-500">
                                        Crecimiento al {{ Math.round((tile.crop.growth / tile.crop.required) * 100) }}%
                                    </p>
                                    <p v-else class="text-gray-400">Usa "Construir" para preparar este suelo.</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-lg bg-white/85 p-4 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-800">Animales aliados</h2>
                        <p class="text-sm text-gray-600">
                            Mejora la productividad con apoyo animal y agricultura sostenible.
                        </p>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-amber-700">Gallinas ({{ hasChickens ? 'activa' : 'disponible' }})</p>
                                        <p class="text-xs text-amber-600">
                                            Aportan abono natural tras cada cosecha.
                                        </p>
                                    </div>
                                    <button
                                        @click="buyChickens"
                                        class="rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white disabled:opacity-50"
                                        :disabled="hasChickens"
                                    >
                                        {{ hasChickens ? 'Comprado' : '80 monedas' }}
                                    </button>
                                </div>
                            </div>
                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-yellow-700">Abejas ({{ hasBees ? 'activas' : 'disponibles' }})</p>
                                        <p class="text-xs text-yellow-600">
                                            Mejoran la polinización y aumentan la producción.
                                        </p>
                                    </div>
                                    <button
                                        @click="buyBees"
                                        class="rounded-lg bg-yellow-500 px-3 py-1 text-xs font-semibold text-white disabled:opacity-50"
                                        :disabled="hasBees"
                                    >
                                        {{ hasBees ? 'Compradas' : '90 monedas' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white/85 p-4 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-800">Misiones por nivel</h2>
                        <p class="text-sm text-gray-600">
                            Completa objetivos educativos para subir de nivel y recibir recompensas.
                        </p>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li
                                v-for="mission in missions"
                                :key="mission.id"
                                class="rounded-lg border p-3"
                                :class="mission.isComplete ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-gray-50'"
                            >
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-gray-700">{{ mission.title }}</p>
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="mission.isComplete ? 'bg-emerald-200 text-emerald-700' : 'bg-gray-200 text-gray-600'"
                                    >
                                        {{ mission.isComplete ? 'Completada' : 'Pendiente' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ mission.description }}</p>
                                <p class="mt-2 text-xs text-emerald-600">
                                    Recompensa: {{ mission.coinReward }} monedas, {{ mission.xpReward }} XP
                                </p>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-lg bg-white/85 p-4 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-800">Registro de eventos</h2>
                        <p class="text-sm text-gray-600">
                            Sigue los cambios de estaciones, plagas y recompensas en tu granja.
                        </p>
                        <ul class="mt-4 space-y-2 text-xs text-gray-600">
                            <li v-for="log in eventLog" :key="log" class="rounded bg-gray-50 px-2 py-1">
                                {{ log }}
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </main>
    </div>
</template>

<style scoped>
.animate-pulse {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        opacity: 1;
    }
}
</style>

