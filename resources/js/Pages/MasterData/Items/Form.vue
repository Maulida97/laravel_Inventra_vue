<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    item: {
        type: Object,
        default: () => ({}),
    },
    categories: Array,
    units: Array,
    departments: Array,
});

const isEditing = !!props.item?.id;

const form = useForm({
    category_id: props.item?.category_id || '',
    code: props.item?.code || '',
    sku: props.item?.sku || '',
    barcode: props.item?.barcode || '',
    name: props.item?.name || '',
    description: props.item?.description || '',
    brand: props.item?.brand || '',
    item_type: props.item?.item_type || 'quantity',
    base_unit_id: props.item?.base_unit_id || '',
    minimum_stock: props.item?.minimum_stock || 0,
    status: props.item?.status || 'ACTIVE',
    department_ids: props.item?.department_ids || [],
});

const submit = () => {
    if (isEditing) {
        form.put(route('items.update', props.item.id));
    } else {
        form.post(route('items.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Item' : 'Create Item'" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <Link :href="route('items.index')">Items</Link>
            <span class="sep">/</span>
            <span class="current">{{ isEditing ? 'Edit' : 'Create' }}</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">{{ isEditing ? 'Edit Item' : 'Create Item' }}</h1>
            </div>
        </div>

        <div class="card card-pad max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Column 1 -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Code *</label>
                            <input v-model="form.code" type="text" class="w-full border-border rounded px-3 py-2 text-sm" required />
                            <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Name *</label>
                            <input v-model="form.name" type="text" class="w-full border-border rounded px-3 py-2 text-sm" required />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Category *</label>
                            <select v-model="form.category_id" class="w-full border-border rounded px-3 py-2 text-sm" required>
                                <option value="">Select Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Item Type *</label>
                            <select v-model="form.item_type" class="w-full border-border rounded px-3 py-2 text-sm" required>
                                <option value="quantity">Quantity (Consumable/Stock)</option>
                                <option value="asset">Asset (Fixed Asset/Serialized)</option>
                            </select>
                            <div v-if="form.errors.item_type" class="text-red-500 text-xs mt-1">{{ form.errors.item_type }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Base Unit *</label>
                            <select v-model="form.base_unit_id" class="w-full border-border rounded px-3 py-2 text-sm" required>
                                <option value="">Select Unit</option>
                                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                    {{ unit.name }} ({{ unit.code }})
                                </option>
                            </select>
                            <div v-if="form.errors.base_unit_id" class="text-red-500 text-xs mt-1">{{ form.errors.base_unit_id }}</div>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">SKU</label>
                            <input v-model="form.sku" type="text" class="w-full border-border rounded px-3 py-2 text-sm" />
                            <div v-if="form.errors.sku" class="text-red-500 text-xs mt-1">{{ form.errors.sku }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Barcode</label>
                            <input v-model="form.barcode" type="text" class="w-full border-border rounded px-3 py-2 text-sm" />
                            <div v-if="form.errors.barcode" class="text-red-500 text-xs mt-1">{{ form.errors.barcode }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Brand</label>
                            <input v-model="form.brand" type="text" class="w-full border-border rounded px-3 py-2 text-sm" />
                            <div v-if="form.errors.brand" class="text-red-500 text-xs mt-1">{{ form.errors.brand }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Minimum Stock *</label>
                            <input v-model="form.minimum_stock" type="number" min="0" class="w-full border-border rounded px-3 py-2 text-sm" required />
                            <div v-if="form.errors.minimum_stock" class="text-red-500 text-xs mt-1">{{ form.errors.minimum_stock }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Status *</label>
                            <select v-model="form.status" class="w-full border-border rounded px-3 py-2 text-sm" required>
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea v-model="form.description" class="w-full border-border rounded px-3 py-2 text-sm" rows="3"></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department Allowed Items</label>
                    <p class="text-xs text-text-muted mb-2">Pilih departemen mana saja yang diizinkan menggunakan/me-request item ini. Kosongkan jika item bisa digunakan oleh semua departemen (perlu disesuaikan dengan business logic di level transaksi nanti, secara default relasi akan dikosongkan).</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mt-2 border border-border rounded p-3 max-h-48 overflow-y-auto bg-surface">
                        <label v-for="dept in departments" :key="dept.id" class="flex items-center gap-2 cursor-pointer hover:bg-background p-2 rounded transition-colors">
                            <input 
                                type="checkbox" 
                                :value="dept.id" 
                                v-model="form.department_ids"
                                class="rounded border-border text-primary focus:ring-primary"
                            />
                            <span class="text-sm select-none">{{ dept.name }} ({{ dept.code }})</span>
                        </label>
                    </div>

                    <div v-if="form.errors.department_ids" class="text-red-500 text-xs mt-1">{{ form.errors.department_ids }}</div>
                </div>

                <div class="flex gap-2 pt-4 border-t border-border">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ isEditing ? 'Update' : 'Save' }}
                    </button>
                    <Link :href="route('items.index')" class="btn btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
