<!--
 * Berkas: Dashboard.vue
 * Jalur: resources/js/Pages/Dashboard.vue
 * Tujuan: Menyediakan ringkasan halaman dasbor utama dengan indikator status, grafik pergerakan stok, daftar transaksi terbaru, dan utilisasi kapasitas gudang.
 * Digunakan untuk: Halaman utama setelah berhasil masuk ke sistem.
 * Referensi: docs/template/inventra_admin_dashboard.html
 -->

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// ==========================================
// KONFIGURASI, METODE & DATA MOCK
// ==========================================

const handleRefresh = () => {
    alert('Data dashboard diperbarui!');
};

const handleNewItem = () => {
    alert('Aksi "Item Baru" akan membuka form pendaftaran barang pada Sprint 04.');
};

const handleSeeAllTx = () => {
    alert('Mengarahkan ke modul riwayat mutasi barang pada Sprint 11.');
};

// Data Mock Statistik
const stats = ref([
    {
        title: 'Total Items',
        value: '1.248',
        icon: 'blue',
        trend: 'up',
        trendText: '+12% vs last month',
        svg: `<svg viewBox="0 0 24 24" fill="none"><path d="M20 7L12 3L4 7L12 11L20 7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 7V17L12 21V11" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M20 7V17L12 21" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>`
    },
    {
        title: 'Stock Value',
        value: 'Rp 4,28B',
        icon: 'green',
        trend: 'up',
        trendText: '+5.4% vs last month',
        svg: `<svg viewBox="0 0 24 24" fill="none"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`
    },
    {
        title: 'Low Stock Alerts',
        value: '4 Items',
        icon: 'amber',
        trend: 'flat',
        trendText: 'Tindakan diperlukan',
        svg: `<svg viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`
    },
    {
        title: 'Pending Approvals',
        value: '3 Req',
        icon: 'navy',
        trend: 'flat',
        trendText: 'Menunggu respon',
        svg: `<svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-5M12 3l8 3.5v4.5c0 5-3.5 8.7-8 10-4.5-1.3-8-5-8-10V6.5L12 3z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>`
    }
]);

// Data Mock Transaksi
const transactions = ref([
    { ref: 'IN-2026-001', type: 'in', name: 'Laptop ThinkPad L14', wh: 'Warehouse Utama', qty: '+100 PCS', desc: 'Supplier: Lenovo Indonesia', status: 'Approved' },
    { ref: 'OUT-2026-002', type: 'out', name: 'RAM DDR4 8GB', wh: 'Warehouse IT', qty: '-20 PCS', desc: 'Req: IT Support Department', status: 'Approved' },
    { ref: 'IN-2026-003', type: 'in', name: 'Monitor 24" Dell', wh: 'Warehouse Utama', qty: '+15 PCS', desc: 'Supplier: Dell Distributor', status: 'Approved' },
    { ref: 'OUT-2026-004', type: 'out', name: 'SSD 512GB M.2', wh: 'Warehouse IT', qty: '-5 PCS', desc: 'Req: R&D Department', status: 'Pending' }
]);

// Data Mock Barang Stok Menipis
const lowStockItems = ref([
    { name: 'RAM DDR4 8GB', wh: 'Warehouse IT', stock: 2, min: 10, unit: 'PCS' },
    { name: 'SSD 512GB M.2', wh: 'Warehouse IT', stock: 1, min: 5, unit: 'PCS' }
]);

// Data Mock Kapasitas Gudang
const warehouses = ref([
    { name: 'Warehouse Utama', utilization: 78, type: 'warn' },
    { name: 'Warehouse IT', utilization: 42, type: 'normal' },
    { name: 'Warehouse Asset', utilization: 92, type: 'danger' }
]);
</script>

<template>
    <Head title="Dashboard - Inventra" />

    <AuthenticatedLayout>
        <div class="breadcrumb">
            <a href="javascript:void(0)">Inventra</a>
            <span class="sep">/</span>
            <span class="current">Dashboard</span>
        </div>

        <div class="page-header">
            <div class="page-header-text">
                <h1 class="text-page-title">Dashboard</h1>
                <p class="text-helper">Ringkasan operasional inventory per hari ini, Senin 30 Agustus 2026.</p>
            </div>
            <div class="page-header-actions">
                <button class="btn btn-secondary btn-sm" @click="handleRefresh">
                    <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M21 12C21 16.97 16.97 21 12 21C7.03 21 3 16.97 3 12C3 7.03 7.03 3 12 3C15 3 17.5 4.5 19 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 3V6.5H15.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Refresh
                </button>
                <button class="btn btn-primary btn-sm" @click="handleNewItem">
                    <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Item Baru
                </button>
            </div>
        </div>

        <!-- 4-Stat Grid -->
        <div class="stat-grid">
            <div v-for="stat in stats" :key="stat.title" class="stat-card">
                <div class="stat-top">
                    <span class="text-card-title">{{ stat.title }}</span>
                    <div class="stat-icon" :class="stat.icon" v-html="stat.svg"></div>
                </div>
                <div class="stat-value num">{{ stat.value }}</div>
                <div class="stat-trend" :class="{ 'up': stat.trend === 'up', 'flat': stat.trend === 'flat' }">
                    <svg v-if="stat.trend === 'up'" viewBox="0 0 24 24" fill="none" class="w-3 h-3"><path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ stat.trendText }}
                </div>
            </div>
        </div>

        <!-- Dashboard Layout Grids -->
        <div class="dash-grid">
            
            <!-- Left Stack -->
            <div class="stack">
                
                <!-- Stock Movement Graph -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="text-section-title">Stock Movement</div>
                            <div class="text-helper mt-1">Stock In vs Stock Out — 7 hari terakhir</div>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-swatch" style="background:var(--color-accent)"></span>Stock In
                            </div>
                            <div class="legend-item">
                                <span class="legend-swatch" style="background:var(--color-secondary-500)"></span>Stock Out
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Custom SVG Chart matching the template aesthetic -->
                        <div class="chart-wrap h-[200px] flex items-end justify-between px-4 pb-2 border-b border-border">
                            <!-- Mon: In=45, Out=20 -->
                            <div class="flex flex-col items-center w-8">
                                <div class="flex gap-1 items-end h-[140px]">
                                    <div class="w-3 bg-accent rounded-t" style="height: 60%"></div>
                                    <div class="w-3 bg-secondary-500 rounded-t" style="height: 25%"></div>
                                </div>
                                <span class="text-[10px] text-text-secondary mt-2">Sen</span>
                            </div>
                            <!-- Tue: In=60, Out=40 -->
                            <div class="flex flex-col items-center w-8">
                                <div class="flex gap-1 items-end h-[140px]">
                                    <div class="w-3 bg-accent rounded-t" style="height: 80%"></div>
                                    <div class="w-3 bg-secondary-500 rounded-t" style="height: 50%"></div>
                                </div>
                                <span class="text-[10px] text-text-secondary mt-2">Sel</span>
                            </div>
                            <!-- Wed: In=30, Out=15 -->
                            <div class="flex flex-col items-center w-8">
                                <div class="flex gap-1 items-end h-[140px]">
                                    <div class="w-3 bg-accent rounded-t" style="height: 40%"></div>
                                    <div class="w-3 bg-secondary-500 rounded-t" style="height: 20%"></div>
                                </div>
                                <span class="text-[10px] text-text-secondary mt-2">Rab</span>
                            </div>
                            <!-- Thu: In=80, Out=60 -->
                            <div class="flex flex-col items-center w-8">
                                <div class="flex gap-1 items-end h-[140px]">
                                    <div class="w-3 bg-accent rounded-t" style="height: 95%"></div>
                                    <div class="w-3 bg-secondary-500 rounded-t" style="height: 75%"></div>
                                </div>
                                <span class="text-[10px] text-text-secondary mt-2">Kam</span>
                            </div>
                            <!-- Fri: In=50, Out=30 -->
                            <div class="flex flex-col items-center w-8">
                                <div class="flex gap-1 items-end h-[140px]">
                                    <div class="w-3 bg-accent rounded-t" style="height: 70%"></div>
                                    <div class="w-3 bg-secondary-500 rounded-t" style="height: 40%"></div>
                                </div>
                                <span class="text-[10px] text-text-secondary mt-2">Jum</span>
                            </div>
                            <!-- Sat: In=15, Out=10 -->
                            <div class="flex flex-col items-center w-8">
                                <div class="flex gap-1 items-end h-[140px]">
                                    <div class="w-3 bg-accent rounded-t" style="height: 20%"></div>
                                    <div class="w-3 bg-secondary-500 rounded-t" style="height: 15%"></div>
                                </div>
                                <span class="text-[10px] text-text-secondary mt-2">Sab</span>
                            </div>
                            <!-- Sun: In=0, Out=0 -->
                            <div class="flex flex-col items-center w-8">
                                <div class="flex gap-1 items-end h-[140px]">
                                    <div class="w-3 bg-accent rounded-t" style="height: 5%"></div>
                                    <div class="w-3 bg-secondary-500 rounded-t" style="height: 5%"></div>
                                </div>
                                <span class="text-[10px] text-text-secondary mt-2">Min</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card">
                    <div class="card-header">
                        <div class="text-section-title">Recent Transactions</div>
                        <button class="btn btn-ghost btn-sm" @click="handleSeeAllTx">Lihat semua</button>
                    </div>
                    <div class="card-body no-pad">
                        <div v-for="tx in transactions" :key="tx.ref" class="list-item">
                            <div class="list-icon" :class="tx.type">
                                <svg v-if="tx.type === 'in'" viewBox="0 0 24 24" fill="none"><path d="M12 19V5M12 5L6 11M12 5L18 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg v-else viewBox="0 0 24 24" fill="none"><path d="M12 5V19M12 19L6 13M12 19L18 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="list-body">
                                <div class="list-title">{{ tx.name }} <span class="text-xs text-text-muted font-normal">({{ tx.ref }})</span></div>
                                <div class="list-sub">{{ tx.wh }} &bull; {{ tx.desc }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="list-value num" :class="{ 'pos': tx.type === 'in', 'neg': tx.type === 'out' }">{{ tx.qty }}</span>
                                <span class="badge" :class="tx.status === 'Approved' ? 'badge-success' : 'badge-warning'">
                                    <span class="dot"></span>{{ tx.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Stack -->
            <div class="stack">
                
                <!-- Warehouse Capacity Utilization -->
                <div class="card card-pad">
                    <div class="text-card-title mb-4">Warehouse Capacity</div>
                    <div class="stack gap-4">
                        <div v-for="wh in warehouses" :key="wh.name">
                            <div class="flex justify-between items-center mb-1 text-sm font-medium">
                                <span>{{ wh.name }}</span>
                                <span class="num font-bold">{{ wh.utilization }}%</span>
                            </div>
                            <div class="progress">
                                <div 
                                    class="progress-fill" 
                                    :class="wh.type"
                                    :style="{ width: wh.utilization + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Items List -->
                <div class="card">
                    <div class="card-header">
                        <div class="text-section-title">Low Stock Items</div>
                        <span class="badge badge-warning">
                            <span class="dot"></span>{{ lowStockItems.length }} items
                        </span>
                    </div>
                    <div class="card-body no-pad">
                        <div v-for="item in lowStockItems" :key="item.name" class="list-item">
                            <div class="list-body">
                                <div class="list-title">{{ item.name }}</div>
                                <div class="list-sub">{{ item.wh }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-red-600 num">{{ item.stock }} {{ item.unit }}</div>
                                <div class="text-[10px] text-text-muted">Min: {{ item.min }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
