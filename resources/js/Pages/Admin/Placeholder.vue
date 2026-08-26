<script setup>
import AppSidebar from '../../Components/AppSidebar.vue';

defineProps({
  title: String,
  description: String,
});

</script>

<template>
  <main class="min-h-screen bg-slate-100 p-6">
    <div class="mx-auto flex max-w-7xl gap-6">
      <AppSidebar />

      <section class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-600">Cadastro</p>
        <h1 class="mt-3 text-3xl font-bold text-slate-900">{{ title }}</h1>
        <p class="mt-3 max-w-xl text-slate-500">{{ description }}</p>
        <div class="mt-10 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
          Esta tela será implementada na próxima etapa.
        </div>
      </section>
    </div>
  </main>
</template>
