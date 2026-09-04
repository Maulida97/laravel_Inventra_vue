<!--
File: Form.vue
Module: Master Data (Suppliers)
Layer: Page
Purpose: Menyediakan formulir untuk menambah (Create) atau mengubah (Edit) entitas Suppliers.

Related Documentation:
- docs/sprints/SPRINT-03-MASTER-DATA.md
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    supplier: {
        type: Object,
        default: () => ({}),
    }
});

const isEditing = !!props.supplier.id;

const form = useForm({
    code: props.supplier.code || '',
    name: props.supplier.name || '',
    phone: props.supplier.phone || '',
    email: props.supplier.email || '',
    address: props.supplier.address || '',
    contact_person: props.supplier.contact_person || '',
    status: props.supplier.status || 'ACTIVE',
});

const submit = () => {
    if (isEditing) {
        form.put(route('suppliers.update', props.supplier.id));
    } else {
        form.post(route('suppliers.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Supplier' : 'Create Supplier'" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <Link :href="route('suppliers.index')">Suppliers</Link>
            <span class="sep">/</span>
            <span class="current">{{ isEditing ? 'Edit' : 'Create' }}</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">{{ isEditing ? 'Edit Supplier' : 'Create Supplier' }}</h1>
            </div>
        </div>

        <div class="card card-pad max-w-2xl">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Code</label>
                        <input v-model="form.code" type="text" class="w-full border-border rounded px-3 py-2 text-sm" required />
                        <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full border-border rounded px-3 py-2 text-sm" required />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone</label>
                        <input v-model="form.phone" type="text" class="w-full border-border rounded px-3 py-2 text-sm" />
                        <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full border-border rounded px-3 py-2 text-sm" />
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Person</label>
                        <input v-model="form.contact_person" type="text" class="w-full border-border rounded px-3 py-2 text-sm" />
                        <div v-if="form.errors.contact_person" class="text-red-500 text-xs mt-1">{{ form.errors.contact_person }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select v-model="form.status" class="w-full border-border rounded px-3 py-2 text-sm" required>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                        <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Address</label>
                    <textarea v-model="form.address" class="w-full border-border rounded px-3 py-2 text-sm" rows="3"></textarea>
                    <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ isEditing ? 'Update' : 'Save' }}
                    </button>
                    <Link :href="route('suppliers.index')" class="btn btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

