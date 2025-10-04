<template>
  <div class="farm-simulator">
    <div class="game-header">
      <h1>Simulador de Granja AgroSpace</h1>
      <div class="game-stats">
        <div class="stat">Nivel: {{ playerLevel }}</div>
        <div class="stat">Monedas: {{ coins }}</div>
        <div class="stat">Experiencia: {{ experience }}/{{ experienceToNextLevel }}</div>
        <div class="stat">Estación: {{ currentSeason }}</div>
      </div>
    </div>

    <div class="game-controls">
      <button @click="selectTool('plow')" :class="{ active: selectedTool === 'plow' }">Arar Tierra</button>
      <button @click="selectTool('plant')" :class="{ active: selectedTool === 'plant' }">Sembrar</button>
      <button @click="selectTool('water')" :class="{ active: selectedTool === 'water' }">Regar</button>
      <button @click="selectTool('harvest')" :class="{ active: selectedTool === 'harvest' }">Cosechar</button>
    </div>

    <div class="crop-selection" v-if="selectedTool === 'plant'">
      <div 
        v-for="crop in availableCrops" 
        :key="crop.id" 
        @click="selectCrop(crop)"
        :class="['crop-option', { active: selectedCrop?.id === crop.id }]"
      >
        <img :src="crop.icon" :alt="crop.name">
        <span>{{ crop.name }}</span>
      </div>
    </div>

    <div class="irrigation-system">
      <h3>Sistema de Riego (Nivel {{ playerLevel }})</h3>
      <p>{{ irrigationSystems[playerLevel - 1].description }}</p>
    </div>

    <div class="game-grid" ref="gameGrid">
      <!-- Aquí se renderizarán los bloques de tierra dinámicamente -->
      <div 
        v-for="(block, index) in farmBlocks" 
        :key="index" 
        class="farm-block"
        :class="[
          block.state, 
          { 'has-crop': block.crop, 'watered': block.watered }
        ]"
        @click="handleBlockClick(index)"
      >
        <div v-if="block.crop" class="crop" :class="[block.crop.type, 'stage-' + block.growthStage]"></div>
      </div>
    </div>

    <div class="game-info">
      <div class="info-panel">
        <h3>Guía de Cultivos</h3>
        <div v-for="crop in availableCrops" :key="crop.id" class="crop-info">
          <img :src="crop.icon" :alt="crop.name">
          <div>
            <h4>{{ crop.name }}</h4>
            <p>Tiempo de crecimiento: {{ crop.growthTime }} días</p>
            <p>Mejor estación: {{ crop.bestSeason }}</p>
            <p>Recompensa: {{ crop.reward }} monedas</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';

// Estado del juego
const playerLevel = ref(1);
const coins = ref(100);
const experience = ref(0);
const experienceToNextLevel = computed(() => playerLevel.value * 100);
const currentSeason = ref('Primavera');
const gameDay = ref(1);
const selectedTool = ref('plow');
const selectedCrop = ref(null);

// Sistemas de riego por nivel
const irrigationSystems = [
  { level: 1, name: 'Riego por Gravedad', description: 'Riego manual básico. Debes regar cada planta individualmente.' },
  { level: 2, name: 'Riego con Canales', description: 'Canales básicos que permiten regar varias plantas a la vez.' },
  { level: 3, name: 'Riego por Aspersión', description: 'Sistema de aspersores que riegan automáticamente cada día.' },
  { level: 4, name: 'Riego Automatizado', description: 'Sistema inteligente con sensores de humedad que optimiza el uso del agua.' }
];

// Cultivos disponibles
const availableCrops = ref([
  { 
    id: 1, 
    name: 'Papa', 
    type: 'potato',
    icon: '/img/crops/potato.svg', 
    growthTime: 4, 
    bestSeason: 'Otoño',
    reward: 20,
    experience: 15,
    unlockLevel: 1
  },
  { 
    id: 2, 
    name: 'Maíz', 
    type: 'corn',
    icon: '/img/crops/corn.svg', 
    growthTime: 6, 
    bestSeason: 'Verano',
    reward: 30,
    experience: 25,
    unlockLevel: 1
  },
  { 
    id: 3, 
    name: 'Zanahoria', 
    type: 'carrot',
    icon: '/img/crops/carrot.svg', 
    growthTime: 3, 
    bestSeason: 'Primavera',
    reward: 15,
    experience: 10,
    unlockLevel: 1
  },
  { 
    id: 4, 
    name: 'Alfalfa', 
    type: 'alfalfa',
    icon: '/img/crops/alfalfa.svg', 
    growthTime: 5, 
    bestSeason: 'Primavera',
    reward: 25,
    experience: 20,
    unlockLevel: 2
  }
]);

// Bloques de granja
const farmBlocks = ref(Array(36).fill(null).map(() => ({
  state: 'empty',
  crop: null,
  growthStage: 0,
  watered: false,
  daysGrowing: 0
})));

// Funciones del juego
function selectTool(tool) {
  selectedTool.value = tool;
  if (tool !== 'plant') {
    selectedCrop.value = null;
  }
}

function selectCrop(crop) {
  if (crop.unlockLevel <= playerLevel.value) {
    selectedCrop.value = crop;
  } else {
    alert(`Este cultivo se desbloquea en el nivel ${crop.unlockLevel}`);
  }
}

function handleBlockClick(index) {
  const block = farmBlocks.value[index];
  
  switch (selectedTool.value) {
    case 'plow':
      if (block.state === 'empty') {
        block.state = 'plowed';
      }
      break;
    
    case 'plant':
      if (block.state === 'plowed' && !block.crop && selectedCrop.value) {
        if (coins.value >= 5) { // Costo de sembrar
          block.crop = { ...selectedCrop.value };
          block.growthStage = 1;
          block.daysGrowing = 0;
          coins.value -= 5;
        } else {
          alert('No tienes suficientes monedas para sembrar');
        }
      }
      break;
    
    case 'water':
      if (block.crop && !block.watered) {
        block.watered = true;
        
        // Aplicar efectos según el nivel de riego
        if (playerLevel.value >= 2) {
          // Regar bloques adyacentes si tiene canales (nivel 2+)
          const adjacentBlocks = getAdjacentBlocks(index);
          adjacentBlocks.forEach(adjIndex => {
            const adjBlock = farmBlocks.value[adjIndex];
            if (adjBlock.crop && !adjBlock.watered) {
              adjBlock.watered = true;
            }
          });
        }
      }
      break;
    
    case 'harvest':
      if (block.crop && block.growthStage >= 4) { // Planta madura
        // Calcular recompensa
        let reward = block.crop.reward;
        let expReward = block.crop.experience;
        
        // Bonificación por estación correcta
        if (block.crop.bestSeason === currentSeason.value) {
          reward = Math.floor(reward * 1.5);
          expReward = Math.floor(expReward * 1.5);
        }
        
        coins.value += reward;
        addExperience(expReward);
        
        // Resetear el bloque
        block.crop = null;
        block.growthStage = 0;
        block.watered = false;
        block.daysGrowing = 0;
      }
      break;
  }
}

function getAdjacentBlocks(index) {
  const gridSize = 6; // 6x6 grid
  const adjacentIndices = [];
  
  // Arriba
  if (index >= gridSize) {
    adjacentIndices.push(index - gridSize);
  }
  
  // Abajo
  if (index < farmBlocks.value.length - gridSize) {
    adjacentIndices.push(index + gridSize);
  }
  
  // Izquierda
  if (index % gridSize !== 0) {
    adjacentIndices.push(index - 1);
  }
  
  // Derecha
  if (index % gridSize !== gridSize - 1) {
    adjacentIndices.push(index + 1);
  }
  
  return adjacentIndices;
}

function addExperience(amount) {
  experience.value += amount;
  
  // Subir de nivel si alcanza la experiencia necesaria
  if (experience.value >= experienceToNextLevel.value) {
    playerLevel.value++;
    experience.value -= experienceToNextLevel.value;
    alert(`¡Has subido al nivel ${playerLevel.value}! Desbloqueaste: ${irrigationSystems[playerLevel.value - 1].name}`);
    
    // Desbloquear nuevos cultivos si corresponde
    const newCrops = availableCrops.value.filter(crop => crop.unlockLevel === playerLevel.value);
    if (newCrops.length > 0) {
      alert(`¡Has desbloqueado nuevos cultivos: ${newCrops.map(c => c.name).join(', ')}!`);
    }
  }
}

function advanceDay() {
  gameDay.value++;
  
  // Cambiar estación cada 28 días
  if (gameDay.value % 28 === 0) {
    const seasons = ['Primavera', 'Verano', 'Otoño', 'Invierno'];
    const currentIndex = seasons.indexOf(currentSeason.value);
    currentSeason.value = seasons[(currentIndex + 1) % 4];
    alert(`¡Ha comenzado una nueva estación: ${currentSeason.value}!`);
  }
  
  // Procesar crecimiento de cultivos
  farmBlocks.value.forEach(block => {
    if (block.crop && block.watered) {
      block.daysGrowing++;
      
      // Avanzar etapa de crecimiento
      const growthRate = block.crop.bestSeason === currentSeason.value ? 1.5 : 1;
      if (block.daysGrowing >= (block.crop.growthTime / 4) * (block.growthStage) / growthRate) {
        if (block.growthStage < 4) {
          block.growthStage++;
        }
      }
      
      // Resetear estado de riego para el siguiente día
      // Excepto en niveles avanzados de riego
      if (playerLevel.value < 3) {
        block.watered = false;
      }
    }
  });
  
  // Riego automático para niveles avanzados
  if (playerLevel.value >= 3) {
    // Nivel 3: Riego por aspersión automático diario
    farmBlocks.value.forEach(block => {
      if (block.crop) {
        block.watered = true;
      }
    });
  }
  
  if (playerLevel.value >= 4) {
    // Nivel 4: Optimización con sensores (bonificación de crecimiento)
    farmBlocks.value.forEach(block => {
      if (block.crop && block.watered) {
        // Probabilidad de crecimiento acelerado
        if (Math.random() > 0.7) {
          block.daysGrowing++;
        }
      }
    });
  }
}

// Iniciar el ciclo del juego
onMounted(() => {
  // Avanzar un día cada 30 segundos (para demostración)
  setInterval(advanceDay, 30000);
});
</script>

<style scoped>
.farm-simulator {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Instrument Sans', sans-serif;
}

.game-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.game-stats {
  display: flex;
  gap: 20px;
}

.stat {
  background-color: #f0f9ff;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 500;
}

.game-controls {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.game-controls button {
  padding: 10px 20px;
  background-color: #e0f2fe;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.game-controls button:hover {
  background-color: #bae6fd;
}

.game-controls button.active {
  background-color: #0ea5e9;
  color: white;
}

.crop-selection {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f0f9ff;
  border-radius: 8px;
}

.crop-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.crop-option:hover {
  background-color: #bae6fd;
}

.crop-option.active {
  background-color: #0ea5e9;
  color: white;
}

.crop-option img {
  width: 40px;
  height: 40px;
  margin-bottom: 5px;
}

.irrigation-system {
  background-color: #f0f9ff;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.game-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 10px;
  margin-bottom: 30px;
}

.farm-block {
  aspect-ratio: 1/1;
  background-color: #f5f5f4;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  transition: all 0.2s;
}

.farm-block.empty {
  background-color: #f5f5f4;
}

.farm-block.plowed {
  background-color: #a16207;
}

.farm-block.watered {
  background-color: #854d0e;
}

.farm-block .crop {
  width: 80%;
  height: 80%;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
}

/* Estilos para las etapas de crecimiento */
.crop.potato.stage-1 { background-image: url('/img/crops/potato_stage1.svg'); }
.crop.potato.stage-2 { background-image: url('/img/crops/potato_stage2.svg'); }
.crop.potato.stage-3 { background-image: url('/img/crops/potato_stage3.svg'); }
.crop.potato.stage-4 { background-image: url('/img/crops/potato_stage4.svg'); }

.crop.corn.stage-1 { background-image: url('/img/crops/corn_stage1.svg'); }
.crop.corn.stage-2 { background-image: url('/img/crops/corn_stage2.svg'); }
.crop.corn.stage-3 { background-image: url('/img/crops/corn_stage3.svg'); }
.crop.corn.stage-4 { background-image: url('/img/crops/corn_stage4.svg'); }

.crop.carrot.stage-1 { background-image: url('/img/crops/carrot_stage1.svg'); }
.crop.carrot.stage-2 { background-image: url('/img/crops/carrot_stage2.svg'); }
.crop.carrot.stage-3 { background-image: url('/img/crops/carrot_stage3.svg'); }
.crop.carrot.stage-4 { background-image: url('/img/crops/carrot_stage4.svg'); }

.crop.alfalfa.stage-1 { background-image: url('/img/crops/alfalfa_stage1.svg'); }
.crop.alfalfa.stage-2 { background-image: url('/img/crops/alfalfa_stage2.svg'); }
.crop.alfalfa.stage-3 { background-image: url('/img/crops/alfalfa_stage3.svg'); }
.crop.alfalfa.stage-4 { background-image: url('/img/crops/alfalfa_stage4.svg'); }

.game-info {
  margin-top: 30px;
}

.info-panel {
  background-color: #f0f9ff;
  padding: 20px;
  border-radius: 8px;
}

.crop-info {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.crop-info:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.crop-info img {
  width: 50px;
  height: 50px;
  margin-right: 15px;
}

.crop-info h4 {
  margin: 0 0 5px 0;
}

.crop-info p {
  margin: 0 0 3px 0;
  font-size: 14px;
}
</style>