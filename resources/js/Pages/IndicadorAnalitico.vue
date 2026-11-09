<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
} from 'chart.js';
import AppSidebar from '../Components/AppSidebar.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend);

const props = defineProps({
  indicador: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  anos: { type: Array, default: () => [] },
  cursos: { type: Array, default: () => [] },
  labels: { type: Array, default: () => [] },
  datasets: { type: Array, default: () => [] },
  meta: { type: Number, default: 0 },
  operador: { type: String, default: '>=' },
  valor_realizado: { type: Number, default: 0 },
  atingimento: { type: Number, default: 0 },
  status: { type: String, default: '' },
});

const ano = ref(props.filters.ano ?? '');
const cursoId = ref(props.filters.curso_id ?? '');
const acumulado = ref(Boolean(props.filters.acumulado));

const aplicarFiltros = () => {
  const query = {};
  if (ano.value) query.ano = ano.value;
  if (cursoId.value) query.curso_id = cursoId.value;
  if (acumulado.value) query.acumulado = 1;

  router.get(`/indicadores/${props.indicador.id}/analitico`, query, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const definirAcumulado = (valor) => {
  acumulado.value = valor;
  aplicarFiltros();
};

const formatoNumero = (valor) =>
  Number(valor ?? 0).toLocaleString('pt-BR', { maximumFractionDigits: 2 });

const corCurso = (indice) => `hsl(${Math.round((indice * 137.508) % 360)}, 70%, 55%)`;

const tendencia = computed(() => {
  const medias = props.labels.map((_, indice) => {
    const valores = props.datasets
      .map((dataset) => dataset.data[indice])
      .filter((valor) => valor !== null && valor !== undefined);
    return valores.length ? valores.reduce((soma, valor) => soma + valor, 0) / valores.length : null;
  });

  const pontos = medias
    .map((y, x) => ({ x, y }))
    .filter((ponto) => ponto.y !== null);

  if (pontos.length < 2) {
    return props.labels.map(() => null);
  }

  const n = pontos.length;
  const somaX = pontos.reduce((soma, ponto) => soma + ponto.x, 0);
  const somaY = pontos.reduce((soma, ponto) => soma + ponto.y, 0);
  const somaXY = pontos.reduce((soma, ponto) => soma + ponto.x * ponto.y, 0);
  const somaXX = pontos.reduce((soma, ponto) => soma + ponto.x * ponto.x, 0);
  const denominador = n * somaXX - somaX * somaX;

  if (denominador === 0) {
    return props.labels.map(() => null);
  }

  const inclinacao = (n * somaXY - somaX * somaY) / denominador;
  const intercepto = (somaY - inclinacao * somaX) / n;

  return props.labels.map((_, x) => Math.round((intercepto + inclinacao * x) * 100) / 100);
});

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [
    ...props.datasets.map((dataset, indice) => ({
      label: dataset.curso,
      data: dataset.data,
      borderColor: corCurso(indice),
      backgroundColor: corCurso(indice),
      borderWidth: 2,
      pointRadius: 3,
      pointHoverRadius: 6,
      tension: 0.3,
      spanGaps: true,
    })),
    {
      label: `Meta (${props.operador} ${formatoNumero(props.meta)})`,
      data: props.labels.map(() => props.meta),
      borderColor: '#f87171',
      backgroundColor: '#f87171',
      borderDash: [8, 6],
      borderWidth: 2,
      pointRadius: 0,
    },
    {
      label: 'Tendência (média)',
      data: tendencia.value,
      borderColor: '#38bdf8',
      backgroundColor: '#38bdf8',
      borderWidth: 3,
      pointRadius: 4,
      pointStyle: 'rectRot',
      pointHoverRadius: 7,
    },
  ],
}));

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'nearest', axis: 'x', intersect: false },
  plugins: {
    legend: {
      display: props.datasets.length <= 8,
      position: 'bottom',
      labels: { color: '#cbd5e1', boxWidth: 10, font: { size: 11 } },
    },
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
      ticks: { color: '#94a3b8' },
      grid: { color: 'rgba(148, 163, 184, 0.08)' },
    },
    y: {
      beginAtZero: true,
      ticks: { color: '#94a3b8' },
      grid: { color: 'rgba(148, 163, 184, 0.08)' },
    },
  },
}));

const resumoPorCurso = computed(() =>
  props.datasets
    .map((dataset) => {
      const valores = dataset.data.filter((valor) => valor !== null && valor !== undefined);
      const total = valores.reduce((soma, valor) => soma + valor, 0);
      return {
        curso: dataset.curso,
        meses: valores.length,
        total,
        media: valores.length ? total / valores.length : 0,
        ultimo: valores.length ? valores[valores.length - 1] : null,
      };
    })
    .sort((a, b) => b.total - a.total)
);

const statusClasse = computed(
  () =>
    ({
      'Meta atingida': 'bg-emerald-500/15 text-emerald-400 ring-emerald-500/30',
      Alerta: 'bg-amber-500/15 text-amber-400 ring-amber-500/30',
    }[props.status] ?? 'bg-red-500/15 text-red-400 ring-red-500/30')
);
</script>

<template>
  <div class="min-h-screen bg-slate-100 p-[10px]">
    <div class="flex gap-[10px]">
      <AppSidebar />

      <div class="min-w-0 flex-1 space-y-6">
        <header class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <p class="text-sm uppercase tracking-[0.25em] text-slate-300">
                Análise de indicador · {{ indicador.codigo }}
              </p>
              <h1 class="mt-2 text-3xl font-bold">{{ indicador.nome }}</h1>
              <p class="mt-1 text-sm text-slate-400">
                {{ indicador.categoria }} · Unidade de medida: {{ indicador.unidade_medida }}
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <span :class="['rounded-full px-4 py-1.5 text-sm font-semibold ring-1', statusClasse]">
                {{ status }}
              </span>
              <Link
                href="/dashboard-rotativo"
                class="rounded-xl border border-slate-600 px-4 py-2 text-sm text-slate-300 transition hover:border-slate-400 hover:text-white"
              >
                &larr; Painel TV
              </Link>
            </div>
          </div>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Ano</label>
              <select
                v-model="ano"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800"
                @change="aplicarFiltros"
              >
                <option value="">Todos</option>
                <option v-for="anoItem in anos" :key="anoItem" :value="anoItem">{{ anoItem }}</option>
              </select>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Curso</label>
              <select
                v-model="cursoId"
                class="min-w-56 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800"
                @change="aplicarFiltros"
              >
                <option value="">Todos</option>
                <option v-for="curso in cursos" :key="curso.id" :value="curso.id">{{ curso.nome }}</option>
              </select>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Visualização</label>
              <div class="flex overflow-hidden rounded-lg border border-slate-300">
                <button
                  type="button"
                  :class="[
                    'px-4 py-2 text-sm font-medium transition',
                    !acumulado ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50',
                  ]"
                  @click="definirAcumulado(false)"
                >
                  Meta vs Mensal
                </button>
                <button
                  type="button"
                  :class="[
                    'px-4 py-2 text-sm font-medium transition',
                    acumulado ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50',
                  ]"
                  @click="definirAcumulado(true)"
                >
                  Meta vs Acumulado
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Realizado {{ acumulado ? '(acumulado)' : '(período)' }}</p>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ formatoNumero(valor_realizado) }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Meta ({{ operador }})</p>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ formatoNumero(meta) }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Atingimento</p>
            <p class="mt-3 text-2xl font-bold text-sky-600">{{ Number(atingimento).toFixed(1) }}%</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Cursos com lançamentos</p>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ datasets.length }}</p>
          </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-slate-900 p-5 shadow-sm">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Evolução histórica por curso</h2>
            <p class="text-xs text-slate-400">
              {{ labels.length }} períodos · linha tracejada vermelha = meta · linha azul = tendência
            </p>
          </div>

          <div class="relative h-[420px]">
            <Line v-if="labels.length" :data="chartData" :options="chartOptions" />
            <div
              v-else
              class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-700 text-sm text-slate-500"
            >
              Nenhum lançamento encontrado para os filtros selecionados.
            </div>
          </div>
        </section>

        <section v-if="resumoPorCurso.length" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-900">Resumo por curso</h2>
          <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                  <th class="px-3 py-2">Curso</th>
                  <th class="px-3 py-2 text-right">Meses</th>
                  <th class="px-3 py-2 text-right">Total</th>
                  <th class="px-3 py-2 text-right">Média mensal</th>
                  <th class="px-3 py-2 text-right">{{ acumulado ? 'Acumulado final' : 'Último mês' }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="resumo in resumoPorCurso" :key="resumo.curso" class="text-slate-700">
                  <td class="px-3 py-2 font-medium">{{ resumo.curso }}</td>
                  <td class="px-3 py-2 text-right">{{ resumo.meses }}</td>
                  <td class="px-3 py-2 text-right">{{ formatoNumero(resumo.total) }}</td>
                  <td class="px-3 py-2 text-right">{{ formatoNumero(resumo.media) }}</td>
                  <td class="px-3 py-2 text-right">{{ resumo.ultimo === null ? '—' : formatoNumero(resumo.ultimo) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>
