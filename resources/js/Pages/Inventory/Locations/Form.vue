<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    location: Object,
    warehouses: Array,
    parentLocations: Array,
});

const isEdit = !!props.location.id;

const form = useForm({
    warehouse_id: props.location.warehouse_id || (props.warehouses[0]?.id || ''),
    parent_id: props.location.parent_id || '',
    code: props.location.code || '',
    name: props.location.name || '',
    description: props.location.description || '',
    status: props.location.status || 'ACTIVE',
});

const onWarehouseChange = () => {
    form.parent_id = '';
    router.get(route(isEdit ? 'locations.edit' : 'locations.create', isEdit ? props.location.id : []), {
        warehouse_id: form.warehouse_id
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['parentLocations']
    });
};

const availableParents = computed(() => {
    return props.parentLocations.filter(p => p.id !== props.location.id);
});

const submit = () => {
    if (isEdit) {
        form.put(route('locations.update', props.location.id));
    } else {
        form.post(route('locations.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Lokasi Rak/Bin' : 'Tambah Lokasi Rak/Bin'" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <Link href="/locations">Lokasi Rak & Bin</Link>
            <span class="sep">/</span>
            <span class="current">{{ isEdit ? 'Edit' : 'Tambah' }}</span>
        </div>

        <div class="page-header">
            <h1 class="text-page-title">{{ isEdit ? 'Edit Lokasi Rak/Bin' : 'Tambah Lokasi Rak/Bin Baru' }}</h1>
        </div>

        <div class="card card-pad max-w-2xl">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Gudang <span class="text-red-500">*</span></label>
                    <select v-model="form.warehouse_id" @change="onWarehouseChange" class="w-full border-border rounded px-3 py-2 text-sm">
                        <option value="" disabled>-- Pilih Gudang --</option>
                        <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                            {{ wh.code }} - {{ wh.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.warehouse_id" class="text-red-500 text-xs mt-1">{{ form.errors.warehouse_id }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Parent Location (Opsional)</label>
                    <p class="text-xs text-text-muted mb-1">Pilih lokasi tingkat atas jika lokasi ini merupakan sub-rak atau bin spesifik.</p>
                    <select v-model="form.parent_id" class="w-full border-border rounded px-3 py-2 text-sm">
                        <option value="">-- Root (Aisle / Tanpa Parent) --</option>
                        <option v-for="p in availableParents" :key="p.id" :value="p.id">
                            [{{ p.code }}] {{ p.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.parent_id" class="text-red-500 text-xs mt-1">{{ form.errors.parent_id }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Kode Lokasi <span class="text-red-500">*</span></label>
                    <input v-model="form.code" type="text" placeholder="Contoh: AISLE-01, RAK-A1, BIN-05" class="w-full border-border rounded px-3 py-2 text-sm" />
                    <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" placeholder="Contoh: Rak Komponen Elektronik A" class="w-full border-border rounded px-3 py-2 text-sm" />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi</label>
                    <textarea v-model="form.description" rows="2" placeholder="Catatan lokasi atau petunjuk penyimpanan..." class="w-full border-border rounded px-3 py-2 text-sm"></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select v-model="form.status" class="w-full border-border rounded px-3 py-2 text-sm">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="INACTIVE">INACTIVE</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                </div>

                <div class="flex gap-2 justify-end pt-4 border-t border-border">
                    <Link href="/locations" class="btn btn-secondary btn-sm">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-sm">
                        {{ isEdit ? 'Update Lokasi' : 'Simpan Lokasi' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
