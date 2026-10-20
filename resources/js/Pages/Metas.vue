<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppSidebar from '../Components/AppSidebar.vue';
import SelectList from '../Components/SelectList.vue';

const props = defineProps({
  items: { type: Array, default: () => [] },
  indicadores: { type: Array, default: () => [] },
});

const page = usePage();
const success = computed(() => page.props.flash?.success);

const form = useForm({ indicador_id: null, operador: '>=', valor_meta: '' });
const editingId = ref(null);
const valorDisplay = ref('');

const operadorOptions = ['>', '<', '=', '<=', '>='].map((op) => ({ value: op, label: op }));
const indicadorOptions = computed(() => props.indicadores.map((indicador) => ({ value: indicador.id, label: indicador.nome })));
const unidade = computed(() => props.indicadores.find((indicador) => indicador.id === form.indicador_id)?.unidade_medida ?? null);

const group = (digits) => digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

const applyDigits = (rawDigits, unit = unidade.value) => {
  const digits = rawDigits.slice(0, unit === 'quantidade' ? 10 : 15);
  if (!digits) {
    form.valor_meta = '';
    valorDisplay.value = '';
    return;
  }
  if (unit === 'quantidade') {
    const integer = digits.replace(/^0+(?=\d)/, '');
    form.valor_meta = integer;
    valorDisplay.value = group(integer);
    return;
  }
  const padded = digits.padStart(3, '0');
  const integer = padded.slice(0, -2).replace(/^0+(?=\d)/, '');
  const decimals = padded.slice(-2);
  form.valor_meta = `${integer}.${decimals}`;
  valorDisplay.value = `${group(integer)},${decimals}`;
};

const onValorInput = (event) => {
  applyDigits(event.target.value.replace(/\D/g, ''));
  event.target.value = valorDisplay.value;
};

let skipReset = false;
watch(() => form.indicador_id, () => {
  if (skipReset) {
    skipReset = false;
    return;
  }
  applyDigits('');
});

const reset = () => {
  editingId.value = null;
  form.defaults({ indicador_id: null, operador: '>=', valor_meta: '' });
  form.reset();
  form.clearErrors();
  valorDisplay.value = '';
};

const submit = () => {
  const options = { preserveScroll: true, onSuccess: reset };
  if (editingId.value) form.put(`/admin/metas/${editingId.value}`, options);
  else form.post('/admin/metas', options);
};

const edit = (item) => {
  editingId.value = item.id;
  form.clearErrors();
  skipReset = form.indicador_id !== item.indicador.id;
  form.indicador_id = item.indicador.id;
  form.operador = item.operador;
  const number = Number(item.valor_meta);
  applyDigits(item.indicador.unidade_medida === 'quantidade' ? String(Math.trunc(number)) : number.toFixed(2).replace('.', ''), item.indicador.unidade_medida);
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const remove = (item) => {
  if (window.confirm('Excluir esta meta?')) {
    router.delete(`/admin/metas/${item.id}`, { preserveScroll: true, onSuccess: () => { if (editingId.value === item.id) reset(); } });
  }
};

const formatValor = (item) => {
  const number = Number(item.valor_meta);
  switch (item.indicador.unidade_medida) {
    case 'quantidade': return number.toLocaleString('pt-BR', { maximumFractionDigits: 0 });
    case 'porcentagem': return `${number.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} %`;
    default: return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  }
};
</script>

<template>
  <main class="min-h-screen bg-slate-100 p-[10px]">
    <div class="flex gap-[10px]">
      <AppSidebar />

      <section class="min-w-0 flex-1 space-y-6">
        <header class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg">
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-400">Administração</p>
          <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold">Metas</h1>
              <p class="mt-2 text-sm text-slate-300">Defina o valor de meta considerado para cada indicador.</p>
            </div>
            <Link href="/" class="text-sm text-sky-300 transition hover:text-white">Voltar ao dashboard</Link>
          </div>
        </header>

        <p v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ success }}</p>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-slate-900">{{ editingId ? 'Editar meta' : 'Nova meta' }}</h2>
              <p class="mt-1 text-sm text-slate-500">Preencha os dados abaixo para salvar.</p>
            </div>
            <button v-if="editingId" type="button" class="text-sm text-slate-500 hover:text-slate-900" @click="reset">Cancelar</button>
          </div>

          <form class="mt-5 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
            <div>
              <span class="text-sm font-medium text-slate-700">Indicador</span>
              <div class="mt-2"><SelectList v-model="form.indicador_id" :options="indicadorOptions" placeholder="Selecione o indicador" clearable /></div>
              <span v-if="form.errors.indicador_id" class="mt-1 block text-sm text-rose-600">{{ form.errors.indicador_id }}</span>
            </div>

            <div>
              <span class="text-sm font-medium text-slate-700">Operador</span>
              <div class="mt-2"><SelectList v-model="form.operador" :options="operadorOptions" placeholder="Selecione o operador" /></div>
              <span v-if="form.errors.operador" class="mt-1 block text-sm text-rose-600">{{ form.errors.operador }}</span>
            </div>

            <div v-if="form.indicador_id !== null">
              <label class="block">
                <span class="text-sm font-medium text-slate-700">Valor da meta</span>
                <div class="relative mt-2">
                  <span v-if="unidade === 'valor_monetario'" class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">R$</span>
                  <input :value="valorDisplay" type="text" inputmode="numeric" autocomplete="off" :placeholder="unidade === 'quantidade' ? '0' : '0,00'" class="w-full rounded-xl border border-slate-300 py-2.5 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" :class="[unidade === 'valor_monetario' ? 'pl-10 pr-3' : unidade === 'porcentagem' ? 'pl-3 pr-9' : 'px-3']" @input="onValorInput">
                  <span v-if="unidade === 'porcentagem'" class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-500">%</span>
                </div>
              </label>
              <span v-if="form.errors.valor_meta" class="mt-1 block text-sm text-rose-600">{{ form.errors.valor_meta }}</span>
            </div>

            <div class="md:col-span-2">
              <button type="submit" :disabled="form.processing" class="rounded-xl bg-sky-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Salvando...' : editingId ? 'Atualizar' : 'Salvar' }}
              </button>
            </div>
          </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-6 py-4"><h2 class="font-semibold text-slate-900">Metas cadastradas</h2></div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[480px] text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="px-6 py-3 font-semibold">Indicador</th>
                  <th class="px-6 py-3 font-semibold">Operador</th>
                  <th class="px-6 py-3 font-semibold">Meta</th>
                  <th class="px-6 py-3 text-right font-semibold">Ações</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in items" :key="item.id" class="text-slate-700">
                  <td class="px-6 py-4">{{ item.indicador.nome }}</td>
                  <td class="px-6 py-4 font-mono">{{ item.operador }}</td>
                  <td class="px-6 py-4">{{ formatValor(item) }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-right">
                    <button type="button" class="mr-3 font-medium text-sky-600 hover:text-sky-800" @click="edit(item)">Editar</button>
                    <button type="button" class="font-medium text-rose-600 hover:text-rose-800" @click="remove(item)">Excluir</button>
                  </td>
                </tr>
                <tr v-if="!items.length">
                  <td colspan="4" class="px-6 py-10 text-center text-slate-500">Nenhuma meta cadastrada.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>
    </div>
  </main>
</template>
