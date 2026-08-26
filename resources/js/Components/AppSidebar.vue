<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const mobileOpen = ref(false);
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const mainNavigation = [{ label: 'Dashboard', href: '/' }];
const adminNavigation = [
  { label: 'Usuários', href: '/admin/users' },
  { label: 'Cursos', href: '/admin/cursos' },
  { label: 'Indicadores', href: '/admin/indicadores' },
];

const isActive = (href) => href === '/' ? page.url === '/' : page.url.startsWith(href);
const logout = () => router.post('/logout');
</script>

<template>
  <button
    type="button"
    class="fixed right-4 top-4 z-40 rounded-xl bg-slate-900 p-3 text-white shadow-lg lg:hidden"
    aria-label="Abrir menu"
    @click="mobileOpen = true"
  >
    <span class="block h-0.5 w-5 bg-current"></span>
    <span class="mt-1 block h-0.5 w-5 bg-current"></span>
    <span class="mt-1 block h-0.5 w-5 bg-current"></span>
  </button>

  <div v-if="mobileOpen" class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="mobileOpen = false"></div>

  <aside :class="[
    'fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 flex-col bg-slate-900 p-5 text-white shadow-xl transition-transform lg:static lg:z-auto lg:w-64 lg:translate-x-0 lg:rounded-2xl lg:shadow-lg',
    mobileOpen ? 'translate-x-0' : '-translate-x-full'
  ]">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-400">Indicadores</p>
        <p class="mt-2 text-lg font-bold text-white">UNIDEAU</p>
      </div>
      <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden" aria-label="Fechar menu" @click="mobileOpen = false">&times;</button>
    </div>

    <nav class="mt-10 space-y-2" aria-label="Navegação principal">
      <Link
        v-for="item in mainNavigation"
        :key="item.href"
        :href="item.href"
        :class="[
          'block rounded-xl px-4 py-3 text-sm transition',
          isActive(item.href) ? 'bg-sky-500 font-semibold text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
        ]"
        @click="mobileOpen = false"
      >
        {{ item.label }}
      </Link>

      <template v-if="isAdmin">
        <p class="px-4 pb-1 pt-6 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Administração</p>
        <Link
          v-for="item in adminNavigation"
          :key="item.href"
          :href="item.href"
          :class="[
            'block rounded-xl px-4 py-3 text-sm transition',
            isActive(item.href) ? 'bg-sky-500 font-semibold text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
          ]"
          @click="mobileOpen = false"
        >
          {{ item.label }}
        </Link>
      </template>
    </nav>

    <div class="mt-auto border-t border-slate-800 pt-5">
      <p class="truncate text-sm font-semibold text-white">{{ user?.name }}</p>
      <p class="mt-1 text-xs uppercase tracking-wide text-slate-400">{{ user?.role }}</p>
      <button type="button" class="mt-4 w-full rounded-xl border border-slate-700 px-4 py-2.5 text-left text-sm text-slate-300 transition hover:border-slate-500 hover:text-white" @click="logout">
        Sair da conta
      </button>
    </div>
  </aside>
</template>