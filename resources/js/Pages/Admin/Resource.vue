<script setup>
import { computed, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppSidebar from '../../Components/AppSidebar.vue';

const props = defineProps({
  title: String,
  description: String,
  resource: String,
  items: Array,
  editing: Number,
  coordenadores: { type: Array, default: () => [] },
});

const definitions = {
  users: {
    endpoint: '/admin/users',
    singular: 'usuário',
    columns: [{ key: 'name', label: 'Nome' }, { key: 'email', label: 'E-mail' }, { key: 'role', label: 'Perfil' }],
    fields: [
      { key: 'name', label: 'Nome', type: 'text', required: true },
      { key: 'email', label: 'E-mail', type: 'email', required: true },
      { key: 'role', label: 'Perfil', type: 'select', required: true, options: [{ value: 'admin', label: 'Administrador' }, { value: 'coordenador', label: 'Coordenador' }] },
      { key: 'password', label: 'Senha', type: 'password', required: false, hint: 'Mínimo de 8 caracteres. Deixe em branco para manter a atual.' },
    ],
  },
  cursos: {
    endpoint: '/admin/cursos',
    singular: 'curso',
    columns: [{ key: 'nome', label: 'Nome' }, { key: 'modalidade', label: 'Modalidade' }, { key: 'tipo', label: 'Tipo' }, { key: 'coordenadores', label: 'Coordenadores' }],
    fields: [
      { key: 'nome', label: 'Nome', type: 'text', required: true },
      { key: 'modalidade', label: 'Modalidade', type: 'select', required: true, options: [{ value: 'Presencial', label: 'Presencial' }, { value: 'EAD', label: 'EAD' }, { value: 'Híbrido', label: 'Híbrido' }] },
      { key: 'tipo', label: 'Tipo', type: 'select', required: true, options: [{ value: 'Graduação', label: 'Graduação' }, { value: 'Pós-Graduação', label: 'Pós-Graduação' }, { value: 'Técnico', label: 'Técnico' }] },
      { key: 'coordenador_ids', label: 'Coordenadores', type: 'multiselect', required: false },
    ],
  },
  indicadores: {
    endpoint: '/admin/indicadores',
    singular: 'indicador',
    columns: [{ key: 'codigo', label: 'Código' }, { key: 'nome', label: 'Nome' }, { key: 'categoria', label: 'Categoria' }, { key: 'unidade_medida', label: 'Unidade' }],
    fields: [
      { key: 'codigo', label: 'Código', type: 'text', required: true },
      { key: 'nome', label: 'Nome', type: 'text', required: true },
      { key: 'categoria', label: 'Categoria', type: 'text', required: true },
      { key: 'unidade_medida', label: 'Unidade de medida', type: 'select', required: true, options: [{ value: 'quantidade', label: 'Quantidade' }, { value: 'porcentagem', label: 'Porcentagem' }, { value: 'valor_monetario', label: 'Valor monetário' }] },
    ],
  },
};

const definition = computed(() => definitions[props.resource]);
const editingItem = computed(() => props.items.find((item) => item.id === props.editing));
const form = useForm({
  name: '', email: '', role: '', password: '',
  nome: '', modalidade: '', tipo: '', coordenador_ids: [],
  codigo: '', categoria: '', unidade_medida: '',
});

const resetValues = (item = null) => {
  definition.value.fields.forEach((field) => {
    form[field.key] = field.key === 'coordenador_ids'
      ? (item?.coordenadores?.map((coordenador) => coordenador.id) ?? [])
      : (item?.[field.key] ?? '');
  });
  form.clearErrors();
};

watch(() => [props.resource, props.editing, props.items], () => resetValues(editingItem.value), { immediate: true });

const submit = () => {
  const options = { preserveScroll: true };
  if (editingItem.value) {
    form.put(`${definition.value.endpoint}/${editingItem.value.id}`, options);
  } else {
    form.post(definition.value.endpoint, options);
  }
};

const edit = (item) => router.get(`${definition.value.endpoint}?edit=${item.id}`, {}, { preserveScroll: true });
const cancelEdit = () => router.get(definition.value.endpoint, {}, { preserveScroll: true });
const remove = (item) => {
  if (window.confirm(`Excluir este ${definition.value.singular}?`)) {
    router.delete(`${definition.value.endpoint}/${item.id}`, { preserveScroll: true });
  }
};

const displayValue = (item, key) => {
  if (key === 'coordenadores') return item[key]?.length ? item[key].map((coordenador) => coordenador.name).join(', ') : 'Nenhum vinculado';
  return item[key] || '-';
};
</script>

<template>
  <main class="min-h-screen bg-slate-100 p-6">
    <div class="mx-auto flex max-w-7xl gap-6">
      <AppSidebar />

      <section class="min-w-0 flex-1 space-y-6">
        <header class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg">
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-400">Administração</p>
          <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold">{{ title }}</h1>
              <p class="mt-2 text-sm text-slate-300">{{ description }}</p>
            </div>
            <Link href="/" class="text-sm text-sky-300 transition hover:text-white">Voltar ao dashboard</Link>
          </div>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-slate-900">{{ editingItem ? `Editar ${definition.singular}` : `Novo ${definition.singular}` }}</h2>
              <p class="mt-1 text-sm text-slate-500">Preencha os dados abaixo para salvar.</p>
            </div>
            <button v-if="editingItem" type="button" class="text-sm text-slate-500 hover:text-slate-900" @click="cancelEdit">Cancelar</button>
          </div>

          <form class="mt-5 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
            <label v-for="field in definition.fields" :key="field.key" class="block">
              <span class="text-sm font-medium text-slate-700">{{ field.label }}</span>
              <select v-if="field.type === 'select'" v-model="form[field.key]" :required="field.required" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                <option value="">Selecione</option>
                <option v-for="option in field.options" :key="option.value" :value="option.value">{{ option.label }}</option>
              </select>
              <select v-else-if="field.type === 'multiselect'" v-model="form[field.key]" multiple class="mt-2 min-h-28 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                <option v-for="coordenador in coordenadores" :key="coordenador.id" :value="coordenador.id">{{ coordenador.name }}</option>
              </select>
              <input v-else v-model="form[field.key]" :type="field.type" :required="field.required && !editingItem" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
              <span v-if="field.hint" class="mt-1 block text-xs text-slate-500">{{ field.hint }}</span>
              <span v-if="form.errors[field.key]" class="mt-1 block text-sm text-rose-600">{{ form.errors[field.key] }}</span>
            </label>

            <div class="md:col-span-2">
              <button type="submit" :disabled="form.processing" class="rounded-xl bg-sky-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Salvando...' : editingItem ? 'Atualizar' : 'Cadastrar' }}
              </button>
            </div>
          </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-semibold text-slate-900">Registros cadastrados</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                  <th v-for="column in definition.columns" :key="column.key" class="px-6 py-3 font-semibold">{{ column.label }}</th>
                  <th class="px-6 py-3 text-right font-semibold">Ações</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in items" :key="item.id" class="text-slate-700">
                  <td v-for="column in definition.columns" :key="column.key" class="px-6 py-4">{{ displayValue(item, column.key) }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-right">
                    <button type="button" class="mr-3 font-medium text-sky-600 hover:text-sky-800" @click="edit(item)">Editar</button>
                    <button type="button" class="font-medium text-rose-600 hover:text-rose-800" @click="remove(item)">Excluir</button>
                  </td>
                </tr>
                <tr v-if="!items.length">
                  <td :colspan="definition.columns.length + 1" class="px-6 py-10 text-center text-slate-500">Nenhum registro cadastrado.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>
    </div>
  </main>
</template>
