<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useCan } from '@/Composables/useCan';

const props = defineProps({
    items: Object,
    filters: Object,
});

const { can } = useCan();
const search = ref(props.filters?.search || '');
const itemType = ref(props.filters?.item_type || '');

watch([search, itemType], ([newSearch, newType]) => {
    router.get(route('items.index'), { search: newSearch, item_type: newType }, { preserveState: true, replace: true });
});

const destroy = (id) => {
    if (confirm('Are you sure you want to delete this Item?')) {
        router.delete(route('items.destroy', id));
    }
};
</script>

<template>
    <Head title="Items - Master Data" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <span class="current">Items</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">Items</h1>
            </div>
            <div class="page-header-actions">
                <Link v-if="can('master.create')" :href="route('items.create')" class="btn btn-primary btn-sm">
                    Tambah Item
                </Link>
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex justify-between items-center mb-4 gap-4">
                <input v-model="search" type="text" placeholder="Search code, name, sku..." class="border-border rounded px-3 py-1.5 text-sm w-64" />
                <select v-model="itemType" class="border-border rounded px-3 py-1.5 text-sm w-48">
                    <option value="">All Types</option>
                    <option value="quantity">Quantity</option>
                    <option value="asset">Asset</option>
                </select>
            </div>

            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-text-secondary">
                        <th class="py-2">Code</th>
                        <th class="py-2">Name</th>
                        <th class="py-2">Category</th>
                        <th class="py-2">Type</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items?.data || []" :key="item.id" class="border-b border-border/50">
                        <td class="py-2">{{ item.code }}</td>
                        <td class="py-2">{{ item.name }}</td>
                        <td class="py-2">{{ item.category?.name }}</td>
                        <td class="py-2 capitalize">{{ item.item_type }}</td>
                        <td class="py-2">
                            <span class="badge" :class="item.status === 'ACTIVE' ? 'badge-success' : 'badge-warning'">
                                <span class="dot"></span>{{ item.status }}
                            </span>
                        </td>
                        <td class="py-2 text-right space-x-2">
                            <Link v-if="can('master.update')" :href="route('items.edit', item.id)" class="text-accent hover:underline">Edit</Link>
                            <button v-if="can('master.delete')" @click="destroy(item.id)" class="text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!items?.data || items.data.length === 0">
                        <td colspan="6" class="py-4 text-center text-text-muted">No Items found.</td>
                    </tr>
                </tbody>
            </table>
            
            <div v-if="items?.links" class="mt-4 flex gap-2">
                <Link v-for="(link, k) in items.links" :key="k" 
                      :href="link.url || '#'" 
                      class="px-3 py-1 border border-border rounded text-sm"
                      :class="{'bg-accent text-white': link.active, 'opacity-50 cursor-not-allowed': !link.url}"
                      v-html="link.label">
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
