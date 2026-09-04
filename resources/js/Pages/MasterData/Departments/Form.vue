<!--
File: Form.vue
Module: Master Data (Departments)
Layer: Page
Purpose: Menyediakan formulir untuk menambah (Create) atau mengubah (Edit) entitas Departments.

Related Documentation:
- docs/sprints/SPRINT-03-MASTER-DATA.md
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    department: {
        type: Object,
        default: () => ({}),
    }
});

const isEditing = !!props.department?.id;

const form = useForm({
    code: props.department?.code || '',
    name: props.department?.name || '',
    description: props.department?.description || '',
    status: props.department?.status || 'ACTIVE',
});

const submit = () => {
    if (isEditing) {
        form.put(route('departments.update', props.department.id));
    } else {
        form.post(route('departments.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Department' : 'Create Department'" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <Link :href="route('departments.index')">Departments</Link>
            <span class="sep">/</span>
            <span class="current">{{ isEditing ? 'Edit' : 'Create' }}</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">{{ isEditing ? 'Edit Department' : 'Create Department' }}</h1>
            </div>
        </div>

        <div class="card card-pad max-w-2xl">
            <form @submit.prevent="submit" class="space-y-4">
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

                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea v-model="form.description" class="w-full border-border rounded px-3 py-2 text-sm" rows="3"></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select v-model="form.status" class="w-full border-border rounded px-3 py-2 text-sm" required>
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="INACTIVE">INACTIVE</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ isEditing ? 'Update' : 'Save' }}
                    </button>
                    <Link :href="route('departments.index')" class="btn btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
