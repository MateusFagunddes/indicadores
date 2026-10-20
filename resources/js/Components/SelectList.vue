<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  modelValue: { type: [Number, String, null], default: null },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Selecione' },
  disabled: Boolean,
  clearable: Boolean,
  searchable: { type: Boolean, default: true },
});
const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const search = ref('');
const root = ref(null);

const selected = computed(() => props.options.find((option) => option.value === props.modelValue));
const filtered = computed(() => {
  const term = search.value.trim().toLowerCase();
  return term ? props.options.filter((option) => option.label.toLowerCase().includes(term)) : props.options;
});

const choose = (value) => {
  emit('update:modelValue', value);
  open.value = false;
  search.value = '';
};
const toggle = () => {
  if (!props.disabled) open.value = !open.value;
};
const closeOnOutsideClick = (event) => {
  if (root.value && !root.value.contains(event.target)) open.value = false;
};

onMounted(() => document.addEventListener('click', closeOnOutsideClick));
onBeforeUnmount(() => document.removeEventListener('click', closeOnOutsideClick));
</script>

<template>
  <div ref="root" class="relative">
    <button type="button" :disabled="disabled" :aria-expanded="open" class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-left text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500" @click="toggle">
      <span class="truncate" :class="selected ? '' : 'text-slate-500'">{{ selected ? selected.label : placeholder }}</span>
      <svg class="ml-2 h-4 w-4 shrink-0 text-slate-500 transition" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" /></svg>
    </button>
    <div v-show="open" class="absolute z-20 mt-1 w-full rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
      <input v-if="searchable" v-model="search" type="text" placeholder="Buscar..." class="mx-2 my-1 w-[calc(100%-1rem)] rounded-lg border border-slate-200 px-2 py-1.5 text-sm outline-none focus:border-sky-500">
      <ul class="max-h-60 overflow-auto">
        <li v-if="clearable">
          <button type="button" class="block w-full px-3 py-2 text-left text-sm text-slate-500 hover:bg-slate-100" @click="choose(null)">{{ placeholder }}</button>
        </li>
        <li v-for="option in filtered" :key="option.value">
          <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-100" :class="option.value === modelValue ? 'bg-sky-50 font-semibold text-sky-700' : 'text-slate-800'" @click="choose(option.value)">{{ option.label }}</button>
        </li>
        <li v-if="!filtered.length" class="px-3 py-2 text-sm text-slate-500">Nenhuma opção encontrada</li>
      </ul>
    </div>
  </div>
</template>
