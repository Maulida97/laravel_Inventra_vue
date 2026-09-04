<!--
File: Edit.vue
Module: Master Data / Settings
Layer: Page Component
Purpose: Form pengelolaan profil dan konfigurasi perusahaan (Company Profile & Settings Management).

Related Documentation:
- docs/sprints/SPRINT-03-MASTER-DATA.md
-->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useCan } from '@/Composables/useCan';
import { toast } from 'vue-sonner';

const props = defineProps({
    companyProfile: Object,
    currencies: Array,
    timezones: Array,
    months: Array,
});

const { can } = useCan();
const logoPreview = ref(props.companyProfile.logo_url || null);

const form = useForm({
    _method: 'POST',
    name: props.companyProfile.name || '',
    code: props.companyProfile.code || '',
    email: props.companyProfile.email || '',
    phone: props.companyProfile.phone || '',
    website: props.companyProfile.website || '',
    address: props.companyProfile.address || '',
    tax_id: props.companyProfile.tax_id || '',
    currency: props.companyProfile.currency || 'IDR',
    timezone: props.companyProfile.timezone || 'Asia/Jakarta',
    fiscal_year_start: props.companyProfile.fiscal_year_start || 1,
    logo: null,
});

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('company-profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Profil & Pengaturan Perusahaan berhasil diperbarui.');
        },
        onError: (errors) => {
            toast.error('Gagal memperbarui profil. Periksa kembali isian formulir.');
        },
    });
};
</script>

<template>
    <Head title="Company Profile & Settings Management" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <Link href="/dashboard">Inventra</Link>
            <span class="sep">/</span>
            <span>Master Data</span>
            <span class="sep">/</span>
            <span class="current">Company Profile & Settings</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">Company Profile & Settings Management</h1>
                <p class="text-sm text-text-secondary mt-1">
                    Kelola identitas resmi perusahaan, lokasi, kontak, serta preferensi dasar sistem Inventra.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Card 1: Identitas Perusahaan & Branding -->
            <div class="card card-pad">
                <div class="border-b border-border pb-3 mb-4">
                    <h2 class="text-base font-semibold text-text flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16" />
                            <path d="M1-21h22" />
                            <path d="M9 7h1" />
                            <path d="M9 11h1" />
                            <path d="M9 15h1" />
                            <path d="M14 7h1" />
                            <path d="M14 11h1" />
                            <path d="M14 15h1" />
                        </svg>
                        Identitas & Branding Perusahaan
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Logo Upload Section -->
                    <div class="flex flex-col items-center justify-center p-4 border border-dashed border-border rounded-lg bg-surface-hover/50">
                        <div class="relative w-32 h-32 rounded-lg border border-border bg-surface flex items-center justify-center overflow-hidden mb-3">
                            <img v-if="logoPreview" :src="logoPreview" alt="Logo Perusahaan" class="w-full h-full object-contain p-2" />
                            <div v-else class="text-center p-2 text-text-muted">
                                <svg class="w-10 h-10 mx-auto text-text-muted mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                                <span class="text-xs">Belum ada logo</span>
                            </div>
                        </div>

                        <label v-if="can('setting.update')" class="btn btn-outline btn-sm cursor-pointer">
                            <span>Upload Logo</span>
                            <input type="file" @change="handleLogoChange" accept="image/*" class="hidden" />
                        </label>
                        <span class="text-xs text-text-muted mt-2 text-center">Format: PNG, JPG, WEBP, SVG (Max: 2MB)</span>
                        <div v-if="form.errors.logo" class="text-xs text-red-500 mt-1">{{ form.errors.logo }}</div>
                    </div>

                    <!-- Input Fields -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" class="input w-full" placeholder="Contoh: PT Inventra Solusi Logistik" required />
                                <div v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-text mb-1">Kode Perusahaan <span class="text-red-500">*</span></label>
                                <input v-model="form.code" type="text" class="input w-full" placeholder="Contoh: INV-HQ" required />
                                <div v-if="form.errors.code" class="text-xs text-red-500 mt-1">{{ form.errors.code }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-text mb-1">NPWP / Tax ID</label>
                            <input v-model="form.tax_id" type="text" class="input w-full" placeholder="Contoh: 01.234.567.8-012.000" />
                            <div v-if="form.errors.tax_id" class="text-xs text-red-500 mt-1">{{ form.errors.tax_id }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Kontak & Alamat Perusahaan -->
            <div class="card card-pad">
                <div class="border-b border-border pb-3 mb-4">
                    <h2 class="text-base font-semibold text-text flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        Kontak & Alamat Lokasi
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Email Perusahaan</label>
                        <input v-model="form.email" type="email" class="input w-full" placeholder="info@company.com" />
                        <div v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Nomor Telepon</label>
                        <input v-model="form.phone" type="text" class="input w-full" placeholder="+62 21 555 1234" />
                        <div v-if="form.errors.phone" class="text-xs text-red-500 mt-1">{{ form.errors.phone }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Situs Web (URL)</label>
                        <input v-model="form.website" type="url" class="input w-full" placeholder="https://company.com" />
                        <div v-if="form.errors.website" class="text-xs text-red-500 mt-1">{{ form.errors.website }}</div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-text mb-1">Alamat Lengkap Kantor Utama / Pusat</label>
                        <textarea v-model="form.address" rows="3" class="input w-full" placeholder="Alamat jalan, gedung, kota, provinsi, kode pos..."></textarea>
                        <div v-if="form.errors.address" class="text-xs text-red-500 mt-1">{{ form.errors.address }}</div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Konfigurasi Sistem & Lokalisasi -->
            <div class="card card-pad">
                <div class="border-b border-border pb-3 mb-4">
                    <h2 class="text-base font-semibold text-text flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                        </svg>
                        Konfigurasi Sistem & Finansial
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Mata Uang Default <span class="text-red-500">*</span></label>
                        <select v-model="form.currency" class="input w-full" required>
                            <option v-for="curr in currencies" :key="curr.code" :value="curr.code">
                                {{ curr.label }}
                            </option>
                        </select>
                        <div v-if="form.errors.currency" class="text-xs text-red-500 mt-1">{{ form.errors.currency }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Zona Waktu Sistem <span class="text-red-500">*</span></label>
                        <select v-model="form.timezone" class="input w-full" required>
                            <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                {{ tz.label }}
                            </option>
                        </select>
                        <div v-if="form.errors.timezone" class="text-xs text-red-500 mt-1">{{ form.errors.timezone }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Awal Bulan Tahun Fiskal <span class="text-red-500">*</span></label>
                        <select v-model="form.fiscal_year_start" class="input w-full" required>
                            <option v-for="m in months" :key="m.value" :value="m.value">
                                {{ m.label }}
                            </option>
                        </select>
                        <div v-if="form.errors.fiscal_year_start" class="text-xs text-red-500 mt-1">{{ form.errors.fiscal_year_start }}</div>
                    </div>
                </div>
            </div>

            <!-- Action Button Footer -->
            <div v-if="can('setting.update')" class="flex justify-end gap-3 pt-2">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
