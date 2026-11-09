<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip);

const props = defineProps({
  indicadores: { type: Array, default: () => [] },
  intervaloRotacao: { type: Number, default: 30 },
});

const POR_PAGINA = 4;
const paginaAtual = ref(0);
const pausado = ref(false);
const progresso = ref(0);
const agora = ref(new Date());

const corCurso = (indice) => `hsl(${Math.round((indice * 137.508) % 360)}, 70%, 55%)`;

const formatoNumero = (valor) =>
  Number(valor ?? 0).toLocaleString('pt-BR', { maximumFractionDigits: 2 });

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  interaction: { mode: 'nearest', axis: 'x', intersect: false },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      titleColor: '#e2e8f0',
      bodyColor: '#cbd5e1',
      borderColor: 'rgba(148, 163, 184, 0.25)',
      borderWidth: 1,
      callbacks: {
        label: (context) => {
          const valor = context.parsed.y;
          return `${context.dataset.label}: ${valor === null || valor === undefined ? '—' : formatoNumero(valor)}`;
        },
      },
    },
  },
  scales: {
    x: {
      ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true },
      grid: { color: 'rgba(148, 163, 184, 0.08)' },
    },
    y: {
      beginAtZero: true,
      ticks: { color: '#94a3b8' },
      grid: { color: 'rgba(148, 163, 184, 0.08)' },
    },
  },
};

const montarGrafico = (item) => ({
  labels: item.labels,
  datasets: [
    ...item.datasets.map((dataset, indice) => ({
      label: dataset.curso,
      data: dataset.data,
      borderColor: corCurso(indice),
      backgroundColor: corCurso(indice),
      borderWidth: 1.5,
      pointRadius: 0,
      pointHitRadius: 8,
      tension: 0.3,
      spanGaps: true,
    })),
    {
      label: 'Meta',
      data: item.labels.map(() => item.meta),
      borderColor: '#f87171',
      backgroundColor: '#f87171',
      borderDash: [6, 6],
      borderWidth: 2,
      pointRadius: 0,
    },
  ],
});

const itensComGrafico = computed(() =>
  props.indicadores.map((item) => ({ ...item, chart: montarGrafico(item) }))
);

const paginas = computed(() => {
  const grupos = [];
  for (let i = 0; i < itensComGrafico.value.length; i += POR_PAGINA) {
    grupos.push(itensComGrafico.value.slice(i, i + POR_PAGINA));
  }
  return grupos;
});

const visiveis = computed(() => paginas.value[paginaAtual.value] ?? []);

let timer = null;
let relogio = null;
let acumuladoMs = 0;
let ultimoTick = 0;

onMounted(() => {
  const intervaloMs = props.intervaloRotacao * 1000;
  ultimoTick = Date.now();

  timer = setInterval(() => {
    const tick = Date.now();
    if (!pausado.value && paginas.value.length > 1) {
      acumuladoMs += tick - ultimoTick;
      if (acumuladoMs >= intervaloMs) {
        acumuladoMs = 0;
        paginaAtual.value = (paginaAtual.value + 1) % paginas.value.length;
      }
      progresso.value = Math.min(100, (acumuladoMs / intervaloMs) * 100);
    }
    ultimoTick = tick;
  }, 100);

  relogio = setInterval(() => {
    agora.value = new Date();
  }, 1000);
});

onBeforeUnmount(() => {
  clearInterval(timer);
  clearInterval(relogio);
});

const horaAtual = computed(() =>
  agora.value.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
);
const dataAtual = computed(() =>
  agora.value.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
);

const statusClasse = (status) =>
  ({
    'Meta atingida': 'bg-emerald-500/15 text-emerald-400 ring-emerald-500/30',
    Alerta: 'bg-amber-500/15 text-amber-400 ring-amber-500/30',
  }[status] ?? 'bg-red-500/15 text-red-400 ring-red-500/30');

const irPara = (indice) => {
  if (paginas.value.length <= 1) return;
  paginaAtual.value = indice;
  acumuladoMs = 0;
  progresso.value = 0;
};

const abrirAnalitico = (item) => {
  router.get(`/indicadores/${item.indicador.id}/analitico`);
};
</script>

<template>
  <div class="flex min-h-screen flex-col bg-slate-950 p-4 text-white lg:p-6">
    <header class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-slate-900 px-6 py-4 shadow-lg">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-400">Painel de Indicadores</p>
        <h1 class="mt-1 text-2xl font-bold">UNIDEAU — Acompanhamento de Metas</h1>
      </div>

      <div class="flex items-center gap-6">
        <div v-if="paginas.length > 1" class="flex items-center gap-2">
          <button
            v-for="(_, indice) in paginas"
            :key="indice"
            type="button"
            :aria-label="`Ir para página ${indice + 1}`"
            :class="[
              'h-2.5 rounded-full transition-all',
              indice === paginaAtual ? 'w-8 bg-sky-400' : 'w-2.5 bg-slate-600 hover:bg-slate-500',
            ]"
            @click="irPara(indice)"
          ></button>
        </div>

        <div class="text-right">
          <p class="text-2xl font-bold tabular-nums text-white">{{ horaAtual }}</p>
          <p class="text-xs capitalize text-slate-400">{{ dataAtual }}</p>
        </div>
      </div>
    </header>

    <div v-if="paginas.length > 1" class="mt-3 h-1 overflow-hidden rounded-full bg-slate-800">
      <div
        class="h-full rounded-full bg-sky-400 transition-[width] duration-100 ease-linear"
        :style="{ width: `${progresso}%` }"
      ></div>
    </div>

    <main
      class="mt-4 grid flex-1 grid-cols-1 gap-4 md:grid-cols-2"
      @mouseenter="pausado = true"
      @mouseleave="pausado = false"
    >
      <div
        v-for="item in visiveis"
        :key="item.indicador.id"
        class="flex min-h-[300px] cursor-pointer flex-col rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg transition hover:border-sky-500/60 hover:ring-2 hover:ring-sky-500/20"
        @click="abrirAnalitico(item)"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <p class="truncate text-[11px] uppercase tracking-[0.2em] text-slate-500">
              {{ item.indicador.categoria || 'Indicador' }} · {{ item.indicador.codigo }}
            </p>
            <h2 class="mt-1 truncate text-lg font-semibold text-white">{{ item.indicador.nome }}</h2>
          </div>
          <span :class="['shrink-0 rounded-full px-3 py-1 text-xs font-semibold ring-1', statusClasse(item.status)]">
            {{ item.status }}
          </span>
        </div>

        <div class="mt-3 grid grid-cols-3 gap-3 text-sm">
          <div>
            <p class="text-[10px] uppercase tracking-wide text-slate-500">Realizado</p>
            <p class="mt-0.5 font-bold text-slate-100">{{ formatoNumero(item.valor_realizado) }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wide text-slate-500">Meta ({{ item.operador }})</p>
            <p class="mt-0.5 font-bold text-slate-100">{{ formatoNumero(item.meta) }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wide text-slate-500">Atingimento</p>
            <p class="mt-0.5 font-bold text-sky-400">{{ Number(item.atingimento).toFixed(1) }}%</p>
          </div>
        </div>

        <div class="relative mt-4 min-h-0 flex-1">
          <Line v-if="item.labels.length" :data="item.chart" :options="chartOptions" />
          <div v-else class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-700 text-sm text-slate-500">
            Sem lançamentos registrados
          </div>
        </div>
      </div>

      <div
        v-if="!visiveis.length"
        class="col-span-full flex items-center justify-center rounded-2xl border border-dashed border-slate-700 p-16 text-slate-500"
      >
        Nenhum indicador cadastrado.
      </div>
    </main>

    <footer class="mt-4 flex items-center justify-between text-xs text-slate-500">
      <p>
        Página {{ paginaAtual + 1 }} de {{ Math.max(paginas.length, 1) }} ·
        {{ indicadores.length }} indicadores · rotação a cada {{ intervaloRotacao }}s
        <span v-if="pausado" class="ml-2 rounded-full bg-amber-500/15 px-2 py-0.5 font-semibold text-amber-400">Pausado</span>
      </p>
      <p>Clique em um gráfico para abrir a análise detalhada</p>
    </footer>
  </div>
</template>
