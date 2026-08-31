<!--
 * File: GuestLayout.vue
 * Path: resources/js/Layouts/GuestLayout.vue
 * Purpose: Provides a split-panel guest template (Left: Brand Panel, Right: Form Container).
 * Used for: Unauthenticated pages such as Login, Password Reset, and Account Request.
 * Reference: docs/template/inventra-login.html
 -->

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

// ==========================================
// CONFIGURATIONS & STATE MANAGEMENT
// ==========================================

const theme = ref('light');

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme.value);
    localStorage.setItem('theme', theme.value);
};

onMounted(() => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    theme.value = savedTheme;
    document.documentElement.setAttribute('data-theme', savedTheme);
});
</script>

<template>
    <div class="screen font-sans text-text">
        <!-- ============ LEFT BRAND PANEL ============ -->
        <aside class="brand-panel">
            <div class="brand-deco">
                <div class="deco-grid"></div>
                <div class="deco-glow"></div>
                <div class="deco-crates">
                    <div class="crate" style="width:70px;height:60px;"></div>
                    <div class="crate" style="width:90px;height:90px;"></div>
                    <div class="crate" style="width:60px;height:44px;"></div>
                </div>
            </div>

            <div class="brand-top">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M3 7L12 3L21 7L12 11L3 7Z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M3 12L12 16L21 12" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M3 17L12 21L21 17" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="brand-name">Inventra</span>
            </div>

            <div class="brand-mid">
                <h1>Satu sumber kebenaran untuk seluruh inventory perusahaan Anda.</h1>
                <p>Kelola barang, warehouse, stock movement, aset, dan persetujuan dalam satu sistem yang transparan dan dapat diaudit.</p>
                <div class="brand-stats">
                    <div>
                        <div class="brand-stat-value">99.9%</div>
                        <div class="brand-stat-label">Uptime sistem</div>
                    </div>
                    <div>
                        <div class="brand-stat-value">128+</div>
                        <div class="brand-stat-label">Perusahaan pengguna</div>
                    </div>
                    <div>
                        <div class="brand-stat-value">24/7</div>
                        <div class="brand-stat-label">Audit trail aktif</div>
                    </div>
                </div>
            </div>

            <div class="brand-foot">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 3L20 6.5V11C20 16 16.5 19.7 12 21C7.5 19.7 4 16 4 11V6.5L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 12L11 14L15.5 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Data terenkripsi end-to-end dan tercatat pada audit log.
            </div>
        </aside>

        <!-- ============ RIGHT FORM PANEL ============ -->
        <div class="form-panel">
            <div class="form-topbar">
                <div class="mobile-brand">
                    <div class="brand-mark">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M3 7L12 3L21 7L12 11L3 7Z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M3 12L12 16L21 12" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M3 17L12 21L21 17" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg text-secondary">Inventra</span>
                </div>
                
                <button class="icon-btn" @click="toggleTheme" aria-label="Ganti tema">
                    <svg v-if="theme === 'light'" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M12 2.5V5M12 19V21.5M4.5 12H2M22 12H19.5M5.6 5.6L7.4 7.4M18.4 5.6L16.6 7.4M5.6 18.4L7.4 16.6M18.4 18.4L16.6 16.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none">
                        <path d="M20 14.5C18.9 15 17.7 15.3 16.4 15.3C11.7 15.3 7.9 11.5 7.9 6.8C7.9 5.5 8.2 4.3 8.7 3.2C5.2 4.4 2.7 7.7 2.7 11.6C2.7 16.5 6.7 20.5 11.6 20.5C15.4 20.5 18.7 18.1 20 14.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="form-center">
                <div class="form-card">
                    <slot />
                </div>
            </div>

            <div class="form-bottombar">
                <a href="javascript:void(0)" class="text-xs text-text-muted hover:text-text">Bantuan</a>
                <a href="javascript:void(0)" class="text-xs text-text-muted hover:text-text">Kebijakan Privasi</a>
                <a href="javascript:void(0)" class="text-xs text-text-muted hover:text-text">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</template>
