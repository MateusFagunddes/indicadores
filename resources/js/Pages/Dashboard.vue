<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppSidebar from '../Components/AppSidebar.vue';

const props = defineProps({
  filters: Object,
  unidades: Array,
  cursos: Array,
  coordenadores: Array,
  periodos: Array,
  indicadores: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const totalRealizado = computed(() => props.indicadores.reduce((sum, item) => sum + Number(item.valor_realizado || 0), 0));
const totalMeta = computed(() => props.indicadores.reduce((sum, item) => sum + Number(item.meta || 0), 0));
const taxaAtingimento = computed(() => totalMeta.value ? (totalRealizado.value / totalMeta.value) * 100 : 0);

const applyFilters = () => {
  const query = {};

  if (props.filters.unidade_id) query.unidade_id = props.filters.unidade_id;
  if (props.filters.curso_id) query.curso_id = props.filters.curso_id;
  if (props.filters.coordenador_id) query.coordenador_id = props.filters.coordenador_id;
  if (props.filters.periodo_letivo_id) query.periodo_letivo_id = props.filters.periodo_letivo_id;
  if (props.filters.categoria) query.categoria = props.filters.categoria;

  router.get('/', query, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};
</script>

<template>
  <div class="min-h-screen bg-slate-100 p-6">
    <div class="mx-auto flex max-w-7xl gap-6">
      <AppSidebar />

      <div class="min-w-0 flex-1 space-y-6">
      <header class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-300">Dashboard UNIDEAU</p>
            <h1 class="mt-2 text-3xl font-bold">Indicadores acadêmicos</h1>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-300">Unidade</label>
              <select v-model="filters.unidade_id" @change="applyFilters" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white" :title="filters.unidade_id ? 'Filtro ativo: unidade' : 'Todos'">
                <option value="">Todos</option>
                <option v-for="unidade in unidades" :key="unidade.id" :value="unidade.id">{{ unidade.nome }}</option>
              </select>
            </div>

            <div v-if="user?.role === 'admin'" class="flex flex-col gap-1">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-300">Coordenador</label>
              <select v-model="filters.coordenador_id" @change="applyFilters" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white" :title="filters.coordenador_id ? 'Filtro ativo: coordenador' : 'Todos'">
                <option value="">Todos</option>
                <option v-for="coordenador in coordenadores" :key="coordenador.id" :value="coordenador.id">{{ coordenador.name }}</option>
              </select>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-300">Curso</label>
              <select v-model="filters.curso_id" @change="applyFilters" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white" :title="filters.curso_id ? 'Filtro ativo: curso' : 'Todos'">
                <option value="">Todos</option>
                <option v-for="curso in cursos" :key="curso.id" :value="curso.id">{{ curso.nome }}</option>
              </select>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-300">Período</label>
              <select v-model="filters.periodo_letivo_id" @change="applyFilters" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white" :title="filters.periodo_letivo_id ? 'Filtro ativo: período' : 'Todos'">
                <option value="">Todos</option>
                <option v-for="periodo in periodos" :key="periodo.id" :value="periodo.id">{{ periodo.rotulo }}</option>
              </select>
            </div>

            <div class="flex items-center gap-3 border-l border-slate-700 pl-3">
              <div class="hidden text-right sm:block">
                <p class="text-sm font-semibold text-white">{{ user?.name }}</p>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ user?.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Realizado</p>
          <p class="mt-3 text-3xl font-bold text-slate-900">{{ totalRealizado.toFixed(2) }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Meta</p>
          <p class="mt-3 text-3xl font-bold text-slate-900">{{ totalMeta.toFixed(2) }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Atingimento</p>
          <p class="mt-3 text-3xl font-bold text-emerald-600">{{ taxaAtingimento.toFixed(1) }}%</p>
        </div>
      </section>

      <section class="grid gap-4 lg:grid-cols-2">
        <div v-for="item in indicadores" :key="item.indicador.id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ item.indicador.categoria }}</p>
              <h2 class="mt-2 text-xl font-semibold">{{ item.indicador.nome }}</h2>
            </div>
            <span :class="[
              item.status === 'Meta atingida' ? 'bg-emerald-100 text-emerald-700' :
              item.status === 'Alerta' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700',
              'rounded-full px-3 py-1 text-xs font-semibold'
            ]">
              {{ item.status }}
            </span>
          </div>

          <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div>
              <p class="text-xs uppercase tracking-wide text-slate-400">Realizado</p>
              <p class="mt-2 text-lg font-bold">{{ Number(item.valor_realizado).toFixed(2) }}</p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-wide text-slate-400">Meta</p>
              <p class="mt-2 text-lg font-bold">{{ Number(item.meta).toFixed(2) }}</p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-wide text-slate-400">Atingimento</p>
              <p class="mt-2 text-lg font-bold text-sky-600">{{ Number(item.atingimento).toFixed(1) }}%</p>
            </div>
          </div>
        </div>
      </section>
      </div>
    </div>
  </div>
</template>
