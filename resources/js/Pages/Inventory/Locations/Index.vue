<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    locations: Object,
    warehouses: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedWarehouse = ref(props.filters.warehouse_id || '');

const applyFilters = () => {
    router.get(route('locations.index'), { 
        search: search.value, 
        warehouse_id: selectedWarehouse.value 
    }, { preserveState: true, replace: true });
};

watch(search, () => applyFilters());
watch(selectedWarehouse, () => applyFilters());

const destroy = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus lokasi rak/bin ini?')) {
        router.delete(route('locations.destroy', id));
    }
};
</script>

<template>
    <Head title="Lokasi Rak & Bin - Inventory" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <span class="current">Lokasi Rak & Bin</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">Hierarki Lokasi Rak & Bin</h1>
                <p class="text-sm text-text-secondary mt-1">Kelola hierarki fisik tempat penyimpanan barang di dalam gudang (Aisle -> Rack -> Bin).</p>
            </div>
            <div class="page-header-actions">
                <Link :href="route('locations.create')" class="btn btn-primary btn-sm">
                    Tambah Lokasi Rak/Bin
                </Link>
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex flex-col md:flex-row justify-between gap-4 mb-4">
                <div class="flex gap-2">
                    <input v-model="search" type="text" placeholder="Cari Kode / Nama Lokasi..." class="border-border rounded px-3 py-1.5 text-sm w-64" />
                    <select v-model="selectedWarehouse" class="border-border rounded px-3 py-1.5 text-sm">
                        <option value="">-- Semua Gudang --</option>
                        <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                            {{ wh.code }} - {{ wh.name }}
                        </option>
                    </select>
                </div>
            </div>

            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-text-secondary">
                        <th class="py-2">Kode Lokasi</th>
                        <th class="py-2">Nama Lokasi</th>
                        <th class="py-2">Gudang</th>
                        <th class="py-2">Parent Location</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="location in locations.data" :key="location.id" class="border-b border-border/50">
                        <td class="py-2 font-mono font-semibold text-accent">{{ location.code }}</td>
                        <td class="py-2 font-medium">{{ location.name }}</td>
                        <td class="py-2">
                            <span class="badge badge-secondary">
                                {{ location.warehouse?.code }}
                            </span>
                        </td>
                        <td class="py-2 text-text-muted">
                            <span v-if="location.parent" class="text-xs font-mono bg-surface-alt px-2 py-0.5 rounded">
                                {{ location.parent.code }} ({{ location.parent.name }})
                            </span>
                            <span v-else class="text-xs text-text-muted italic">Root (Aisle Utama)</span>
                        </td>
                        <td class="py-2">
                            <span class="badge" :class="location.status === 'ACTIVE' ? 'badge-success' : 'badge-warning'">
                                <span class="dot"></span>{{ location.status }}
                            </span>
                        </td>
                        <td class="py-2 text-right space-x-2">
                            <Link :href="route('locations.edit', location.id)" class="text-accent hover:underline">Edit</Link>
                            <button @click="destroy(location.id)" class="text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="locations.data.length === 0">
                        <td colspan="6" class="py-4 text-center text-text-muted">Lokasi rak/bin tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mt-4 flex gap-2">
                <Link v-for="(link, k) in locations.links" :key="k" 
                      :href="link.url || '#'" 
                      class="px-3 py-1 border border-border rounded text-sm"
                      :class="{'bg-accent text-white': link.active, 'opacity-50 cursor-not-allowed': !link.url}"
                      v-html="link.label">
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
