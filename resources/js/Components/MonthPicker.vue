<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  // Meses anteriores a este valor (mm/aaaa) ficam desabilitados
  min: { type: String, default: '' },
  placeholder: { type: String, default: 'mm/aaaa' },
  clearable: Boolean,
});
const emit = defineEmits(['update:modelValue']);

const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
const parse = (value) => {
  const match = /^(\d{2})\/(\d{4})$/.exec(value || '');
  return match ? { month: Number(match[1]), year: Number(match[2]) } : null;
};

const open = ref(false);
const root = ref(null);
const minParsed = computed(() => parse(props.min));
const year = ref((parse(props.modelValue) ?? minParsed.value ?? { year: new Date().getFullYear() }).year);

watch(() => props.modelValue, (value) => {
  const parsed = parse(value);
  if (parsed) year.value = parsed.year;
});

const isDisabled = (month) => {
  const min = minParsed.value;
  return !!min && (year.value < min.year || (year.value === min.year && month < min.month));
};
const canGoBack = computed(() => !minParsed.value || year.value > minParsed.value.year);
const isSelected = (month) => props.modelValue === `${String(month).padStart(2, '0')}/${year.value}`;

const pick = (month) => {
  if (isDisabled(month)) return;
  emit('update:modelValue', `${String(month).padStart(2, '0')}/${year.value}`);
  open.value = false;
};
const clear = () => {
  emit('update:modelValue', '');
  open.value = false;
};
const closeOnOutsideClick = (event) => {
  if (root.value && !root.value.contains(event.target)) open.value = false;
};

onMounted(() => document.addEventListener('click', closeOnOutsideClick));
onBeforeUnmount(() => document.removeEventListener('click', closeOnOutsideClick));
</script>

<template>
  <div ref="root" class="relative">
    <button type="button" :aria-expanded="open" class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-left text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" @click="open = !open">
      <span :class="modelValue ? '' : 'text-slate-500'">{{ modelValue || placeholder }}</span>
      <svg class="ml-2 h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 20 20" fill="currentColor"><path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm-2 6h12v8H4V8z" /></svg>
    </button>
    <div v-show="open" class="absolute z-20 mt-1 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
      <div class="mb-2 flex items-center justify-between">
        <button type="button" :disabled="!canGoBack" class="rounded-lg px-2 py-1 text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Ano anterior" @click="year--">&lsaquo;</button>
        <span class="text-sm font-semibold text-slate-900">{{ year }}</span>
        <button type="button" class="rounded-lg px-2 py-1 text-slate-600 hover:bg-slate-100" aria-label="Próximo ano" @click="year++">&rsaquo;</button>
      </div>
      <div class="grid grid-cols-3 gap-1">
        <button v-for="(label, index) in months" :key="label" type="button" :disabled="isDisabled(index + 1)" class="rounded-lg px-2 py-2 text-sm transition disabled:cursor-not-allowed disabled:text-slate-300" :class="isSelected(index + 1) ? 'bg-sky-500 font-semibold text-slate-950' : 'text-slate-700 hover:bg-slate-100'" @click="pick(index + 1)">{{ label }}</button>
      </div>
      <button v-if="clearable && modelValue" type="button" class="mt-2 w-full rounded-lg px-2 py-1.5 text-sm text-slate-500 hover:bg-slate-100" @click="clear">Limpar</button>
    </div>
  </div>
</template>
