<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Inventra — Inventory & Asset Management" />
    <div class="min-h-screen bg-bg flex flex-col justify-between text-text font-sans selection:bg-accent selection:text-white">
        
        <!-- Header -->
        <header class="bg-surface border-b border-border shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md bg-secondary flex items-center justify-center text-white font-extrabold text-xl shadow-sm">
                        I
                    </div>
                    <span class="text-xl font-bold tracking-tight text-secondary">Inventra</span>
                </div>
                
                <nav v-if="canLogin" class="flex gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-4 h-9 inline-flex items-center rounded-md bg-accent text-white font-semibold text-sm hover:bg-accent-hover transition shadow-sm"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-4 h-9 inline-flex items-center rounded-md border border-border-strong bg-surface text-text-secondary font-semibold text-sm hover:bg-surface-alt transition"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-4 h-9 inline-flex items-center rounded-md bg-accent text-white font-semibold text-sm hover:bg-accent-hover transition shadow-sm"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col items-center justify-center">
            <div class="w-full max-w-3xl text-center mb-10">
                <h1 class="text-4xl sm:text-5xl font-extrabold text-secondary tracking-tight mb-4">
                    Inventory & Asset Management System
                </h1>
                <p class="text-lg text-text-secondary max-w-2xl mx-auto">
                    Kelola barang, stok, aset, gudang, transaksi, persetujuan, dan audit secara terpadu dan presisi menggunakan **Inventra**.
                </p>
            </div>

            <!-- Dashboard Card Structure (Tokens Demonstration) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl">
                
                <!-- Card 1 -->
                <div class="bg-surface border border-border p-6 rounded-lg shadow-sm">
                    <div class="w-12 h-12 rounded-md bg-accent-100 text-accent flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Immutable Ledger</h3>
                    <p class="text-sm text-text-secondary">
                        Setiap pergerakan stok dicatat permanen di ledger. Saldo stok tidak dapat diubah bebas, menjamin auditabilitas tinggi.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-surface border border-border p-6 rounded-lg shadow-sm">
                    <div class="w-12 h-12 rounded-md bg-secondary-100 text-secondary-500 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Scoped RBAC</h3>
                    <p class="text-sm text-text-secondary">
                        Kontrol akses berdasarkan Role, Permission, dan Scope departemen atau gudang secara rinci dan aman.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-surface border border-border p-6 rounded-lg shadow-sm">
                    <div class="w-12 h-12 rounded-md bg-yellow-100 text-yellow-700 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.656 48.656 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7C4.747 9.75 4.7 10.972 4.7 12s.048 2.25.138 3.41c.09 1.157.9 2.052 2.015 2.148A49.117 49.117 0 0012 18c1.724 0 3.413-.087 5.016-.255a2.25 2.25 0 002.015-2.148c.09-1.16.138-2.31.138-3.41c0-1.16-.048-2.312-.138-3.41z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Workflow Approval</h3>
                    <p class="text-sm text-text-secondary">
                        Setiap pengeluaran barang dan mutasi penting melewati persetujuan manajer dengan validasi pencegahan *self-approval*.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-surface border-t border-border py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-text-muted">
                <div>
                    &copy; 2026 Inventra. All rights reserved.
                </div>
                <div class="flex gap-4">
                    <span>Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</span>
                </div>
            </div>
        </footer>
    </div>
</template>
