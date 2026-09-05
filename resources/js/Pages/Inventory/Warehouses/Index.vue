<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    warehouses: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('warehouses.index'), { search: value }, { preserveState: true, replace: true });
});

const destroy = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus gudang ini?')) {
        router.delete(route('warehouses.destroy', id));
    }
};
</script>

<template>
    <Head title="Warehouses - Inventory" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <span class="current">Warehouses</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">Gudang & Scope Akses</h1>
                <p class="text-sm text-text-secondary mt-1">Kelola data fisik gudang dan hak akses user ke gudang spesifik.</p>
            </div>
            <div class="page-header-actions">
                <Link :href="route('warehouses.create')" class="btn btn-primary btn-sm">
                    Tambah Gudang
                </Link>
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex justify-between items-center mb-4">
                <input v-model="search" type="text" placeholder="Cari Kode / Nama Gudang..." class="border-border rounded px-3 py-1.5 text-sm w-64" />
            </div>

            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-text-secondary">
                        <th class="py-2">Kode</th>
                        <th class="py-2">Nama Gudang</th>
                        <th class="py-2">Alamat</th>
                        <th class="py-2">Jumlah Rak/Bin</th>
                        <th class="py-2">User Scope</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="warehouse in warehouses.data" :key="warehouse.id" class="border-b border-border/50">
                        <td class="py-2 font-semibold">{{ warehouse.code }}</td>
                        <td class="py-2">{{ warehouse.name }}</td>
                        <td class="py-2 text-text-muted">{{ warehouse.address || '-' }}</td>
                        <td class="py-2">
                            <span class="px-2 py-0.5 rounded bg-surface-alt text-xs font-mono">
                                {{ warehouse.locations_count }} lokasi
                            </span>
                        </td>
                        <td class="py-2">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="u in warehouse.users" :key="u.id" class="badge badge-info text-xs font-medium">
                                    {{ u.name }}
                                </span>
                                <span v-if="!warehouse.users || warehouse.users.length === 0" class="text-xs text-text-muted italic">
                                    Semua (Super Admin Only / Open)
                                </span>
                            </div>
                        </td>
                        <td class="py-2">
                            <span class="badge" :class="warehouse.status === 'ACTIVE' ? 'badge-success' : 'badge-warning'">
                                <span class="dot"></span>{{ warehouse.status }}
                            </span>
                        </td>
                        <td class="py-2 text-right space-x-2">
                            <Link :href="route('warehouses.edit', warehouse.id)" class="text-accent hover:underline">Edit</Link>
                            <button @click="destroy(warehouse.id)" class="text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="warehouses.data.length === 0">
                        <td colspan="7" class="py-4 text-center text-text-muted">Gudang tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mt-4 flex gap-2">
                <Link v-for="(link, k) in warehouses.links" :key="k" 
                      :href="link.url || '#'" 
                      class="px-3 py-1 border border-border rounded text-sm"
                      :class="{'bg-accent text-white': link.active, 'opacity-50 cursor-not-allowed': !link.url}"
                      v-html="link.label">
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
