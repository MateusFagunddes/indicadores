<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => form.post('/login');
</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-slate-950 px-6 py-12">
    <section class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">
      <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-400">Indicadores UNIDEAU</p>
      <h1 class="mt-5 text-3xl font-bold text-white">Acesse seu painel</h1>
      <p class="mt-2 text-sm text-slate-400">Entre para visualizar os indicadores vinculados ao seu perfil.</p>

      <form class="mt-8 space-y-5" @submit.prevent="submit">
        <label class="block">
          <span class="text-sm font-medium text-slate-300">E-mail</span>
          <input v-model="form.email" type="email" autocomplete="email" required class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20">
          <span v-if="form.errors.email" class="mt-1 block text-sm text-rose-400">{{ form.errors.email }}</span>
        </label>

        <label class="block">
          <span class="text-sm font-medium text-slate-300">Senha</span>
          <input v-model="form.password" type="password" autocomplete="current-password" required class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20">
        </label>

        <label class="flex items-center gap-2 text-sm text-slate-400">
          <input v-model="form.remember" type="checkbox" class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-400">
          Manter sessão ativa
        </label>

        <button type="submit" :disabled="form.processing" class="w-full rounded-xl bg-sky-500 px-4 py-3 font-semibold text-slate-950 transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-60">
          {{ form.processing ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>
    </section>
  </main>
</template>
