<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    warehouse: Object,
    users: Array,
});

const isEdit = !!props.warehouse.id;

const form = useForm({
    code: props.warehouse.code || '',
    name: props.warehouse.name || '',
    address: props.warehouse.address || '',
    status: props.warehouse.status || 'ACTIVE',
    user_ids: props.warehouse.user_ids || [],
});

const submit = () => {
    if (isEdit) {
        form.put(route('warehouses.update', props.warehouse.id));
    } else {
        form.post(route('warehouses.store'));
    }
};

const toggleUser = (userId) => {
    const index = form.user_ids.indexOf(userId);
    if (index === -1) {
        form.user_ids.push(userId);
    } else {
        form.user_ids.splice(index, 1);
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Gudang' : 'Tambah Gudang'" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <Link href="/warehouses">Warehouses</Link>
            <span class="sep">/</span>
            <span class="current">{{ isEdit ? 'Edit' : 'Tambah' }}</span>
        </div>

        <div class="page-header">
            <h1 class="text-page-title">{{ isEdit ? 'Edit Gudang' : 'Tambah Gudang Baru' }}</h1>
        </div>

        <div class="card card-pad max-w-2xl">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Kode Gudang <span class="text-red-500">*</span></label>
                    <input v-model="form.code" type="text" placeholder="Contoh: WH-JKT-01" class="w-full border-border rounded px-3 py-2 text-sm" />
                    <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nama Gudang <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" placeholder="Contoh: Gudang Utama Jakarta" class="w-full border-border rounded px-3 py-2 text-sm" />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Alamat</label>
                    <textarea v-model="form.address" rows="3" placeholder="Alamat lengkap gudang..." class="w-full border-border rounded px-3 py-2 text-sm"></textarea>
                    <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select v-model="form.status" class="w-full border-border rounded px-3 py-2 text-sm">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="INACTIVE">INACTIVE</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">User Scope Access (Penugasan User)</label>
                    <p class="text-xs text-text-muted mb-2">Pilih user yang diizinkan mengelola gudang ini. User dengan role <strong>SUPER_ADMIN</strong> otomatis memiliki akses penuh ke seluruh gudang.</p>
                    <div class="border border-border rounded p-3 max-h-48 overflow-y-auto space-y-2">
                        <label v-for="user in users" :key="user.id" class="flex items-center gap-2 cursor-pointer text-sm">
                            <input 
                                type="checkbox" 
                                :checked="form.user_ids.includes(user.id)" 
                                @change="toggleUser(user.id)" 
                                class="rounded border-border"
                            />
                            <span>{{ user.name }} <span class="text-text-muted text-xs">({{ user.email }})</span></span>
                        </label>
                    </div>
                    <div v-if="form.errors.user_ids" class="text-red-500 text-xs mt-1">{{ form.errors.user_ids }}</div>
                </div>

                <div class="flex gap-2 justify-end pt-4 border-t border-border">
                    <Link href="/warehouses" class="btn btn-secondary btn-sm">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-sm">
                        {{ isEdit ? 'Update Gudang' : 'Simpan Gudang' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
