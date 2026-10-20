<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppSidebar from '../Components/AppSidebar.vue';
import MonthPicker from '../Components/MonthPicker.vue';
import SelectList from '../Components/SelectList.vue';

const props = defineProps({
  items: { type: Array, default: () => [] },
  cursos: { type: Array, default: () => [] },
  indicadores: { type: Array, default: () => [] },
  usuarios: { type: Array, default: () => [] },
  mesAnoAtual: String,
  filtroMesAno: { type: String, default: null },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isAdmin = computed(() => currentUser.value?.role === 'admin');
const success = computed(() => page.props.flash?.success);

const emptyForm = () => ({ curso_id: null, mes_ano: '', indicador_id: null, valor_meta: '', user_id: currentUser.value?.id ?? null });
const form = useForm(emptyForm());
const editingId = ref(null);
const valorDisplay = ref('');
const filtro = ref(props.filtroMesAno ?? '');

const cursoOptions = computed(() => props.cursos.map((curso) => ({ value: curso.id, label: curso.nome })));
const indicadorOptions = computed(() => props.indicadores.map((indicador) => ({ value: indicador.id, label: indicador.nome })));
const usuarioOptions = computed(() => props.usuarios.map((usuario) => ({ value: usuario.id, label: usuario.name })));
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
  form.defaults(emptyForm());
  form.reset();
  form.clearErrors();
  valorDisplay.value = '';
};

const submit = () => {
  const options = { preserveScroll: true, onSuccess: reset };
  if (editingId.value) form.put(`/lancamentos/${editingId.value}`, options);
  else form.post('/lancamentos', options);
};

const edit = (item) => {
  editingId.value = item.id;
  form.clearErrors();
  form.curso_id = item.curso.id;
  form.mes_ano = item.mes_ano;
  skipReset = form.indicador_id !== item.indicador.id;
  form.indicador_id = item.indicador.id;
  form.user_id = item.user?.id ?? currentUser.value?.id ?? null;
  const number = Number(item.valor_meta);
  applyDigits(item.indicador.unidade_medida === 'quantidade' ? String(Math.trunc(number)) : number.toFixed(2).replace('.', ''), item.indicador.unidade_medida);
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const remove = (item) => {
  if (window.confirm('Excluir este lançamento?')) {
    router.delete(`/lancamentos/${item.id}`, { preserveScroll: true, onSuccess: () => { if (editingId.value === item.id) reset(); } });
  }
};

watch(filtro, (value) => {
  router.get('/lancamentos', value ? { mes_ano: value } : {}, { preserveState: true, preserveScroll: true, replace: true });
});

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
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-400">Metas</p>
          <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold">Lançamentos</h1>
              <p class="mt-2 text-sm text-slate-300">Lance as metas dos indicadores por curso e mês/ano.</p>
            </div>
            <Link href="/" class="text-sm text-sky-300 transition hover:text-white">Voltar ao dashboard</Link>
          </div>
        </header>

        <p v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ success }}</p>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-slate-900">{{ editingId ? 'Editar lançamento' : 'Novo lançamento' }}</h2>
              <p class="mt-1 text-sm text-slate-500">Preencha os dados abaixo para salvar.</p>
            </div>
            <button v-if="editingId" type="button" class="text-sm text-slate-500 hover:text-slate-900" @click="reset">Cancelar</button>
          </div>

          <form class="mt-5 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
            <div>
              <span class="text-sm font-medium text-slate-700">Curso</span>
              <div class="mt-2"><SelectList v-model="form.curso_id" :options="cursoOptions" placeholder="Selecione o curso" /></div>
              <span v-if="form.errors.curso_id" class="mt-1 block text-sm text-rose-600">{{ form.errors.curso_id }}</span>
            </div>

            <div>
              <span class="text-sm font-medium text-slate-700">Mês/Ano</span>
              <div class="mt-2"><MonthPicker v-model="form.mes_ano" :min="mesAnoAtual" /></div>
              <span v-if="form.errors.mes_ano" class="mt-1 block text-sm text-rose-600">{{ form.errors.mes_ano }}</span>
            </div>

            <div>
              <span class="text-sm font-medium text-slate-700">Indicador</span>
              <div class="mt-2"><SelectList v-model="form.indicador_id" :options="indicadorOptions" placeholder="Selecione o indicador" clearable /></div>
              <span v-if="form.errors.indicador_id" class="mt-1 block text-sm text-rose-600">{{ form.errors.indicador_id }}</span>
            </div>

            <div v-if="form.indicador_id !== null">
              <label class="block">
                <span class="text-sm font-medium text-slate-700">Valor</span>
                <div class="relative mt-2">
                  <span v-if="unidade === 'valor_monetario'" class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">R$</span>
                  <input :value="valorDisplay" type="text" inputmode="numeric" autocomplete="off" :placeholder="unidade === 'quantidade' ? '0' : '0,00'" class="w-full rounded-xl border border-slate-300 py-2.5 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" :class="[unidade === 'valor_monetario' ? 'pl-10 pr-3' : unidade === 'porcentagem' ? 'pl-3 pr-9' : 'px-3']" @input="onValorInput">
                  <span v-if="unidade === 'porcentagem'" class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-500">%</span>
                </div>
              </label>
              <span v-if="form.errors.valor_meta" class="mt-1 block text-sm text-rose-600">{{ form.errors.valor_meta }}</span>
            </div>

            <div>
              <span class="text-sm font-medium text-slate-700">Usuário</span>
              <div class="mt-2"><SelectList v-model="form.user_id" :options="usuarioOptions" :disabled="!isAdmin" placeholder="Selecione o usuário" /></div>
              <span v-if="form.errors.user_id" class="mt-1 block text-sm text-rose-600">{{ form.errors.user_id }}</span>
            </div>

            <div class="md:col-span-2">
              <button type="submit" :disabled="form.processing" class="rounded-xl bg-sky-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Salvando...' : editingId ? 'Atualizar' : 'Salvar' }}
              </button>
            </div>
          </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-slate-900">Lançamentos</h2>
            <div class="w-full sm:w-56"><MonthPicker v-model="filtro" placeholder="Filtrar por mês/ano" clearable /></div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="px-6 py-3 font-semibold">Mês/Ano</th>
                  <th class="px-6 py-3 font-semibold">Curso</th>
                  <th class="px-6 py-3 font-semibold">Indicador</th>
                  <th class="px-6 py-3 font-semibold">Valor</th>
                  <th class="px-6 py-3 font-semibold">Usuário</th>
                  <th class="px-6 py-3 text-right font-semibold">Ações</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in items" :key="item.id" class="text-slate-700">
                  <td class="px-6 py-4">{{ item.mes_ano }}</td>
                  <td class="px-6 py-4">{{ item.curso.nome }}</td>
                  <td class="px-6 py-4">{{ item.indicador.nome }}</td>
                  <td class="px-6 py-4">{{ formatValor(item) }}</td>
                  <td class="px-6 py-4">{{ item.user?.name ?? '-' }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-right">
                    <template v-if="item.editable">
                      <button type="button" class="mr-3 font-medium text-sky-600 hover:text-sky-800" @click="edit(item)">Editar</button>
                      <button type="button" class="font-medium text-rose-600 hover:text-rose-800" @click="remove(item)">Excluir</button>
                    </template>
                    <span v-else class="text-xs text-slate-400">Período encerrado</span>
                  </td>
                </tr>
                <tr v-if="!items.length">
                  <td colspan="6" class="px-6 py-10 text-center text-slate-500">Nenhum lançamento encontrado.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>
    </div>
  </main>
</template>
