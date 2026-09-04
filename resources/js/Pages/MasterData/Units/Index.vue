<!--
File: Index.vue
Module: Master Data (Units)
Layer: Page
Purpose: Menampilkan daftar data Units beserta fungsionalitas pencarian dan paginasi.

Related Documentation:
- docs/sprints/SPRINT-03-MASTER-DATA.md
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useCan } from '@/Composables/useCan';

const props = defineProps({
    units: Object,
    filters: Object,
});

const { can } = useCan();
const search = ref(props.filters?.search || '');

watch(search, (value) => {
    router.get(route('units.index'), { search: value }, { preserveState: true, replace: true });
});

const destroy = (id) => {
    if (confirm('Are you sure you want to delete this Unit?')) {
        router.delete(route('units.destroy', id));
    }
};
</script>

<template>
    <Head title="Units - Master Data" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <span class="current">Units</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">Units</h1>
            </div>
            <div class="page-header-actions">
                <Link v-if="can('master.create')" :href="route('units.create')" class="btn btn-primary btn-sm">
                    Tambah Unit
                </Link>
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex justify-between items-center mb-4">
                <input v-model="search" type="text" placeholder="Search..." class="border-border rounded px-3 py-1.5 text-sm w-64" />
            </div>

            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-text-secondary">
                        <th class="py-2">Code</th>
                        <th class="py-2">Name</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="unit in units?.data || []" :key="unit.id" class="border-b border-border/50">
                        <td class="py-2">{{ unit.code }}</td>
                        <td class="py-2">{{ unit.name }}</td>
                        <td class="py-2">
                            <span class="badge" :class="unit.status === 'ACTIVE' ? 'badge-success' : 'badge-warning'">
                                <span class="dot"></span>{{ unit.status }}
                            </span>
                        </td>
                        <td class="py-2 text-right space-x-2">
                            <Link v-if="can('master.update')" :href="route('units.edit', unit.id)" class="text-accent hover:underline">Edit</Link>
                            <button v-if="can('master.delete')" @click="destroy(unit.id)" class="text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!units?.data || units.data.length === 0">
                        <td colspan="4" class="py-4 text-center text-text-muted">No Units found.</td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Simple Pagination -->
            <div v-if="units?.links" class="mt-4 flex gap-2">
                <Link v-for="(link, k) in units.links" :key="k" 
                      :href="link.url || '#'" 
                      class="px-3 py-1 border border-border rounded text-sm"
                      :class="{'bg-accent text-white': link.active, 'opacity-50 cursor-not-allowed': !link.url}"
                      v-html="link.label">
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
