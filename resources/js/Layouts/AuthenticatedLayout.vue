<!--
 * Berkas: AuthenticatedLayout.vue
 * Jalur: resources/js/Layouts/AuthenticatedLayout.vue
 * Tujuan: Menyediakan kerangka utama aplikasi untuk pengguna terautentikasi, termasuk navigasi sidebar, top bar, dan konten halaman.
 * Digunakan untuk: Menampilkan halaman dasbor back-office, modul transaksi, pelaporan, dan formulir administrasi.
 * Referensi: docs/template/inventra_admin_dashboard.html
 -->

<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';

// ==========================================
// KONFIGURASI & MANAJEMEN STATE
// ==========================================
const page = usePage();
const user = page.props.auth.user;

// State Sidebar
const isCollapsed = ref(false);
const isMobileOpen = ref(false);

// State Tema
const theme = ref('light');

// Menu Dropdown
const showUserMenu = ref(false);
const showNotifMenu = ref(false);

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
};

const toggleMobileMenu = () => {
    isMobileOpen.value = !isMobileOpen.value;
};

const closeMobileMenu = () => {
    isMobileOpen.value = false;
};

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme.value);
    localStorage.setItem('theme', theme.value);
};

onMounted(() => {
    // Load theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    theme.value = savedTheme;
    document.documentElement.setAttribute('data-theme', savedTheme);
});

const handleMockFeature = (name) => {
    toast.info(`Modul "${name}" belum diaktifkan. Modul ini akan diimplementasikan pada Sprint berikutnya.`);
};
</script>

<template>
    <div class="app-shell font-sans text-text">
        <!-- Toaster untuk Notifikasi Modern -->
        <Toaster richColors position="top-right" />
        <!-- Scrim for mobile sidebar -->
        <div 
            class="scrim" 
            :class="{ 'open': isMobileOpen }" 
            @click="closeMobileMenu"
        ></div>

        <!-- ================= SIDEBAR ================= -->
        <aside 
            class="sidebar" 
            :class="{ 'collapsed': isCollapsed, 'mobile-open': isMobileOpen }"
        >
            <div class="sidebar-brand">
                <div class="sidebar-brand-mark">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M3 7L12 3L21 7L12 11L3 7Z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M3 12L12 16L21 12" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M3 17L12 21L21 17" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="sidebar-brand-text">Inventra</span>
            </div>

            <nav class="sidebar-nav">
                <!-- Group General -->
                <div class="nav-group">
                    <Link 
                        :href="route('dashboard')" 
                        class="nav-item" 
                        :class="{ 'active': route().current('dashboard') }"
                    >
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <rect x="13" y="3" width="8" height="5" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <rect x="13" y="10" width="8" height="11" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <span class="nav-label">Dashboard</span>
                    </Link>
                </div>

                <!-- Group Inventory -->
                <div class="nav-group">
                    <div class="nav-group-label">Inventory</div>
                    
                    <button @click="handleMockFeature('Items')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M20 7L12 3L4 7L12 11L20 7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M4 7V17L12 21V11" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M20 7V17L12 21" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                        <span class="nav-label">Items</span>
                    </button>

                    <button @click="handleMockFeature('Stock In')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 19V5M12 5L6 11M12 5L18 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="nav-label">Stock In</span>
                    </button>

                    <button @click="handleMockFeature('Stock Out')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 5V19M12 19L6 13M12 19L18 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="nav-label">Stock Out</span>
                    </button>

                    <button @click="handleMockFeature('Stock Opname')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M9 11L11 13L15 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <span class="nav-label">Stock Opname</span>
                    </button>
                </div>

                <!-- Group Operations -->
                <div class="nav-group">
                    <div class="nav-group-label">Operations</div>

                    <button @click="handleMockFeature('Warehouse')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M3 21V9L12 3L21 9V21H15V14H9V21H3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                        <span class="nav-label">Warehouse</span>
                    </button>

                    <button @click="handleMockFeature('Assets')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="16" height="12" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M9 20H15M12 16V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span class="nav-label">Assets</span>
                    </button>

                    <button @click="handleMockFeature('Approvals')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M9 12L11 14L15.5 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 3L20 6.5V11C20 16 16.5 19.7 12 21C7.5 19.7 4 16 4 11V6.5L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                        <span class="nav-label">Approvals</span>
                    </button>

                    <button @click="handleMockFeature('Reports')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 20V10M10 20V4M16 20V13M22 20H2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="nav-label">Reports</span>
                    </button>
                </div>

                <!-- Group Administration -->
                <div class="nav-group">
                    <div class="nav-group-label">Administration</div>

                    <button @click="handleMockFeature('Users')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M3 20C3 16.5 5.7 14 9 14C12.3 14 15 16.5 15 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M15.5 8.2C16.6 8.6 17.4 9.6 17.4 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M17 14.3C19.4 14.9 21 17.1 21 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span class="nav-label">Users</span>
                    </button>

                    <button @click="handleMockFeature('Roles & Permissions')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 3L20 6.5V11C20 16 16.5 19.7 12 21C7.5 19.7 4 16 4 11V6.5L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M12 8V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <circle cx="12" cy="15.6" r="0.9" fill="currentColor"/>
                        </svg>
                        <span class="nav-label">Roles & Permissions</span>
                    </button>

                    <button @click="handleMockFeature('Audit Log')" class="nav-item">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 3H15L20 8V21H6V3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M15 3V8H20" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M9 13H16M9 17H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span class="nav-label">Audit Log</span>
                    </button>
                </div>
            </nav>

            <div class="sidebar-footer">
                <button class="sidebar-collapse-btn" @click="toggleSidebar">
                    <svg viewBox="0 0 24 24" fill="none" :style="isCollapsed ? { transform: 'rotate(180deg)' } : {}">
                        <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Collapse sidebar</span>
                </button>
            </div>
        </aside>

        <!-- ================= MAIN ================= -->
        <div class="main-col">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="icon-btn mobile-menu-btn" @click="toggleMobileMenu" aria-label="Buka navigasi">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                    <div class="topbar-search">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input type="text" placeholder="Cari item, transaksi, aset...">
                        <span class="kbd">⌘K</span>
                    </div>
                </div>

                <div class="topbar-right">
                    <!-- Theme Toggle -->
                    <button class="icon-btn" @click="toggleTheme" aria-label="Ganti tema">
                        <svg v-if="theme === 'light'" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 2.5V5M12 19V21.5M4.5 12H2M22 12H19.5M5.6 5.6L7.4 7.4M18.4 5.6L16.6 7.4M5.6 18.4L7.4 16.6M18.4 18.4L16.6 16.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none">
                            <path d="M20 14.5C18.9 15 17.7 15.3 16.4 15.3C11.7 15.3 7.9 11.5 7.9 6.8C7.9 5.5 8.2 4.3 8.7 3.2C5.2 4.4 2.7 7.7 2.7 11.6C2.7 16.5 6.7 20.5 11.6 20.5C15.4 20.5 18.7 18.1 20 14.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <!-- Notifications Dropdown (Interactive Mock) -->
                    <div class="relative">
                        <button class="icon-btn" @click="showNotifMenu = !showNotifMenu; showUserMenu = false" aria-label="Notifikasi">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M6 9C6 5.7 8.7 3 12 3C15.3 3 18 5.7 18 9V13.5L20 17H4L6 13.5V9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M9.5 17C9.5 18.4 10.6 19.5 12 19.5C13.4 19.5 14.5 18.4 14.5 17" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                            <span class="absolute top-[6px] right-[7px] width-[7px] height-[7px] rounded-full bg-red-600 border border-white"></span>
                        </button>

                        <div v-if="showNotifMenu" class="absolute right-0 mt-2 bg-surface border border-border rounded-md shadow-lg min-w-[240px] z-50 p-4">
                            <div class="font-bold text-xs uppercase tracking-wider text-text-secondary mb-2">Notifikasi Terbaru</div>
                            <div class="text-xs text-text-secondary py-2 border-b border-border">Selamat! Proyek Inventra V1.0 siap dimulai.</div>
                            <div class="text-xs text-text-secondary py-2">Tidak ada notifikasi sistem lainnya.</div>
                        </div>
                    </div>

                    <div class="divider-v"></div>

                    <!-- User Dropdown Menu -->
                    <div class="relative">
                        <button class="user-chip" @click="showUserMenu = !showUserMenu; showNotifMenu = false">
                            <div class="avatar">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="user-chip-text">
                                <div class="user-chip-name">{{ user.name }}</div>
                                <div class="user-chip-role">Administrator</div>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="showUserMenu" class="absolute right-0 mt-2 bg-surface border border-border rounded-md shadow-lg min-w-[160px] z-50 p-2">
                            <Link :href="route('profile.edit')" class="block w-full text-left px-3 py-2 rounded text-sm hover:bg-surface-alt transition">
                                Profile
                            </Link>
                            <hr class="border-border my-1">
                            <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-3 py-2 rounded text-sm hover:bg-surface-alt text-red-600 font-semibold transition">
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content">
                <!-- Page Content Slot -->
                <slot />
            </main>
        </div>
    </div>
</template>
