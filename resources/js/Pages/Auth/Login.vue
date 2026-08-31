<!--
 * Berkas: Login.vue
 * Jalur: resources/js/Pages/Auth/Login.vue
 * Tujuan: Merender formulir email kerja dan kata sandi dengan tombol tampilkan/sembunyikan kata sandi, pilihan ingat-saya, dan penanganan validasi.
 * Digunakan untuk: Proses otentikasi masuk pengguna ke platform Inventra.
 * Referensi: docs/template/inventra-login.html
 -->

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

// ==========================================
// KONFIGURASI, STATE & AKSI
// ==========================================

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: 'admin@inventra.com', // nilai default email untuk login admin
    password: '',
    remember: false,
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const handleSSO = () => {
    toast.info('Autentikasi Single Sign-On (SSO) akan diintegrasikan pada Sprint berikutnya.');
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk — Inventra" />

        <div class="form-head">
            <div class="eyebrow">Selamat datang kembali</div>
            <h2 class="text-secondary font-extrabold">Masuk ke Inventra</h2>
            <p>Masukkan kredensial akun kerja Anda untuk melanjutkan.</p>
        </div>

        <div v-if="status" class="alert show bg-green-50 border border-green-200 text-green-700 p-3 rounded-md mb-4 text-xs font-semibold">
            {{ status }}
        </div>

        <!-- Alert Error Umum (Kesalahan Autentikasi Laravel) -->
        <div v-if="form.errors.email || form.errors.password" class="alert show mb-4">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <div>
                <div class="alert-title">Gagal Masuk</div>
                <div class="alert-desc">
                    {{ form.errors.email || form.errors.password }}
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <!-- Field Input Email -->
            <div class="field" :class="{ 'error': form.errors.email }">
                <label class="field-label" for="email">Email Kerja</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none"><path d="M3 6.5C3 5.7 3.7 5 4.5 5H19.5C20.3 5 21 5.7 21 6.5V17.5C21 18.3 20.3 19 19.5 19H4.5C3.7 19 3 18.3 3 17.5V6.5Z" stroke="currentColor" stroke-width="1.7"/><path d="M4 6.5L12 12.5L20 6.5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                    <input 
                        class="input has-icon" 
                        :class="{ 'has-error': form.errors.email }"
                        type="email" 
                        id="email" 
                        placeholder="nama@perusahaan.co.id" 
                        v-model="form.email" 
                        required 
                        autofocus
                        autocomplete="username"
                    >
                </div>
                <div v-if="form.errors.email" class="field-error">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 8V13M12 16H12.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    {{ form.errors.email }}
                </div>
            </div>

            <!-- Field Input Kata Sandi -->
            <div class="field" :class="{ 'error': form.errors.password }">
                <div class="field-label">
                    <label for="password">Kata Sandi</label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="link text-accent font-semibold hover:underline">
                        Lupa kata sandi?
                    </Link>
                </div>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none"><rect x="4.5" y="10.5" width="15" height="9.5" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 10.5V7.5C8 5.3 9.8 3.5 12 3.5C14.2 3.5 16 5.3 16 7.5V10.5" stroke="currentColor" stroke-width="1.7"/></svg>
                    <input 
                        class="input has-icon has-toggle" 
                        :class="{ 'has-error': form.errors.password }"
                        :type="showPassword ? 'text' : 'password'" 
                        id="password" 
                        placeholder="Masukkan kata sandi" 
                        v-model="form.password" 
                        required 
                        autocomplete="current-password"
                    >
                    <button type="button" class="input-toggle" @click="togglePasswordVisibility" aria-label="Tampilkan kata sandi">
                        <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none">
                            <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="1.7"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none">
                            <path d="M3 3L21 21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            <path d="M10.6 5.2C11 5.1 11.5 5 12 5C18.5 5 22 12 22 12C21.5 12.9 20.8 13.9 19.9 14.8M6.2 6.2C4 7.6 2 12 2 12C2 12 5.5 19 12 19C13.7 19 15.2 18.6 16.4 17.9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            <path d="M9.9 10C9.3 10.5 9 11.2 9 12C9 13.7 10.3 15 12 15C12.8 15 13.5 14.7 14 14.1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div v-if="form.errors.password" class="field-error">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 8V13M12 16H12.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    {{ form.errors.password }}
                </div>
            </div>

            <!-- Pilihan Ingat Saya -->
            <div class="row-between">
                <div class="checkbox-row">
                    <input type="checkbox" id="remember" v-model="form.remember">
                    <label for="remember">Ingat saya di perangkat ini</label>
                </div>
            </div>

            <!-- Tombol Kirim Form -->
            <button type="submit" class="btn btn-primary" :class="{ 'loading': form.processing }" :disabled="form.processing">
                <span class="spinner"></span>
                <span class="btn-label">Masuk</span>
            </button>

            <div class="divider"><span>atau</span></div>

            <!-- Tombol Masuk via SSO -->
            <button type="button" class="btn btn-secondary" @click="handleSSO">
                <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M3 9H21" stroke="currentColor" stroke-width="1.7"/><path d="M7 13H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                Masuk dengan Single Sign-On
            </button>
        </form>

        <div class="form-foot">
            Belum memiliki akun? <a href="javascript:void(0)" class="text-accent hover:underline">Hubungi Administrator</a>
        </div>
    </GuestLayout>
</template>
