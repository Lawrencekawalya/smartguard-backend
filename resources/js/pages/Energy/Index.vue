<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Zap,
    CreditCard,
    Calendar,
    TrendingUp,
    Filter,
    X,
    AlertCircle,
    Download,
    FileText,
} from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import DailyEnergyChart from '@/components/SmartGuard/DailyEnergyChart.vue';
import MonthlyEnergyChart from '@/components/SmartGuard/MonthlyEnergyChart.vue';
import WeeklyEnergyChart from '@/components/SmartGuard/WeeklyEnergyChart.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface EnergySummary {
    today_kwh: number;
    weekly_kwh: number;
    monthly_kwh: number;
    total_kwh: number;
    estimated_cost: number;
    tariff_rate: number;
    currency: string;
    tariff_description: string;
    cost_analysis: CostAnalysisItem[];
}

interface CostAnalysisItem {
    period: string;
    energy_kwh: number;
    tariff_rate: number;
    cost: number;
}

interface ReportItem {
    date: string;
    energy_used: number;
    estimated_cost: number;
    peak_power: number;
    fault_count: number;
}

interface ReportMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

const summary = ref<EnergySummary>({
    today_kwh: 0,
    weekly_kwh: 0,
    monthly_kwh: 0,
    total_kwh: 0,
    estimated_cost: 0,
    tariff_rate: 0,
    currency: 'UGX',
    tariff_description: '',
    cost_analysis: [],
});

const dailyData = ref<Array<{ date: string; daily_kwh: number }>>([]);
const weeklyData = ref<Array<{ week: string; weekly_kwh: number }>>([]);
const monthlyData = ref<Array<{ month: string; monthly_kwh: number }>>([]);
const reportData = ref<ReportItem[]>([]);
const reportMeta = ref<ReportMeta>({
    current_page: 1,
    from: null,
    last_page: 1,
    per_page: 25,
    to: null,
    total: 0,
});
const loading = ref(true);
const error = ref<string | null>(null);

const startDate = ref('');
const endDate = ref('');
let refreshTimer: ReturnType<typeof setInterval> | undefined;

const fetchData = async (silent = false) => {
    if (!silent) {
        loading.value = true;
    }

    error.value = null;

    try {
        const params = new URLSearchParams();

        if (startDate.value) {
            params.append('start_date', startDate.value);
        }

        if (endDate.value) {
            params.append('end_date', endDate.value);
        }

        const queryString = params.toString() ? `?${params.toString()}` : '';
        const reportParams = new URLSearchParams(params);
        reportParams.set('page', reportMeta.value.current_page.toString());
        const reportQueryString = `?${reportParams.toString()}`;
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        const fetchJson = async (url: string) => {
            const res = await fetch(url, {
                headers,
                credentials: 'same-origin',
            });

            if (!res.ok) {
                const text = await res.text();
                console.error(`API Error for ${url}:`, res.status, text);

                throw new Error(`Failed to load data from ${url}`);
            }

            return res.json();
        };

        const [sumData, dailyRes, weeklyRes, monthlyRes, reportRes] =
            await Promise.all([
                fetchJson(`/api/v1/energy/summary${queryString}`),
                fetchJson(`/api/v1/energy/daily${queryString}`),
                fetchJson(`/api/v1/energy/weekly${queryString}`),
                fetchJson(`/api/v1/energy/monthly${queryString}`),
                fetchJson(`/api/v1/energy/report${reportQueryString}`),
            ]);

        summary.value = sumData;
        dailyData.value = dailyRes.data || [];
        weeklyData.value = weeklyRes.data || [];
        monthlyData.value = monthlyRes.data || [];
        reportData.value = reportRes.data || [];
        reportMeta.value = reportRes.meta || reportMeta.value;
    } catch (err: any) {
        console.error('Error fetching energy data:', err);
        error.value =
            err.message || 'An unexpected error occurred while loading data.';
    } finally {
        if (!silent) {
            loading.value = false;
        }
    }
};

const clearFilter = () => {
    startDate.value = '';
    endDate.value = '';
    reportMeta.value.current_page = 1;
    fetchData();
};

const applyFilter = () => {
    reportMeta.value.current_page = 1;
    fetchData();
};

const changeReportPage = (page: number) => {
    if (page < 1 || page > reportMeta.value.last_page) {
        return;
    }

    reportMeta.value.current_page = page;
    fetchData(true);
};

const exportReport = (format: 'csv' | 'pdf') => {
    const params = new URLSearchParams();

    if (startDate.value) {
        params.append('start_date', startDate.value);
    }

    if (endDate.value) {
        params.append('end_date', endDate.value);
    }

    const query = params.toString() ? `?${params.toString()}` : '';

    window.location.assign(`/api/v1/energy/export/${format}${query}`);
};

onMounted(() => {
    fetchData();
    refreshTimer = setInterval(() => fetchData(true), 10_000);
});

onBeforeUnmount(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-UG', {
        style: 'currency',
        currency: summary.value.currency,
        maximumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Energy Analysis" />

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div
            class="mb-8 flex flex-col justify-between gap-4 xl:flex-row xl:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    Energy Analytics
                </h1>
                <p class="text-zinc-500 dark:text-zinc-400">
                    Consumption reporting, cost analysis, and budgeting
                    insights.
                </p>
            </div>

            <!-- Date Filter -->
            <div
                class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-zinc-500">From</span>
                    <Input type="date" v-model="startDate" class="h-9 w-40" />
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-zinc-500">To</span>
                    <Input type="date" v-model="endDate" class="h-9 w-40" />
                </div>
                <div class="flex gap-2">
                    <Button @click="applyFilter" size="sm" class="h-9">
                        <Filter class="mr-2 h-4 w-4" />
                        Apply
                    </Button>
                    <Button
                        v-if="startDate || endDate"
                        @click="clearFilter"
                        variant="ghost"
                        size="sm"
                        class="h-9 text-zinc-500"
                    >
                        <X class="mr-2 h-4 w-4" />
                        Clear
                    </Button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div
                        class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400"
                    >
                        <Zap class="h-6 w-6" />
                    </div>
                    <span class="text-sm font-medium text-zinc-500">Today</span>
                </div>
                <div class="text-2xl font-bold dark:text-white">
                    {{ summary.today_kwh.toFixed(2) }} kWh
                </div>
                <p class="mt-1 text-sm text-zinc-500">Today's Usage</p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div
                        class="rounded-lg bg-green-50 p-2 text-green-600 dark:bg-green-900/20 dark:text-green-400"
                    >
                        <Calendar class="h-6 w-6" />
                    </div>
                    <span class="text-sm font-medium text-zinc-500"
                        >This Week</span
                    >
                </div>
                <div class="text-2xl font-bold dark:text-white">
                    {{ summary.weekly_kwh.toFixed(2) }} kWh
                </div>
                <p class="mt-1 text-sm text-zinc-500">Weekly Usage</p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div
                        class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                    <span class="text-sm font-medium text-zinc-500"
                        >This Month</span
                    >
                </div>
                <div class="text-2xl font-bold dark:text-white">
                    {{ summary.monthly_kwh.toFixed(2) }} kWh
                </div>
                <p class="mt-1 text-sm text-zinc-500">Monthly Usage</p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div
                        class="rounded-lg bg-orange-50 p-2 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400"
                    >
                        <CreditCard class="h-6 w-6" />
                    </div>
                    <span class="text-sm font-medium text-zinc-500">
                        {{
                            startDate && endDate
                                ? 'Selection Total'
                                : 'Estimated Cost'
                        }}
                    </span>
                </div>
                <div class="text-2xl font-bold dark:text-white">
                    {{ formatCurrency(summary.estimated_cost) }}
                </div>
                <p class="mt-1 text-sm text-zinc-500">
                    {{
                        startDate && endDate
                            ? `Total: ${summary.total_kwh.toFixed(2)} kWh`
                            : `Tariff: ${summary.tariff_rate} / kWh`
                    }}
                </p>
            </div>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-20">
            <div
                class="h-12 w-12 animate-spin rounded-full border-b-2 border-primary"
            ></div>
        </div>

        <div
            v-else-if="error"
            class="rounded-xl border border-red-200 bg-red-50 p-6 text-center dark:border-red-900/30 dark:bg-red-900/10"
        >
            <AlertCircle
                class="mx-auto mb-4 h-12 w-12 text-red-600 dark:text-red-500"
            />
            <h3 class="text-lg font-medium text-red-900 dark:text-red-400">
                Failed to load analytics
            </h3>
            <p class="mb-4 text-red-600 dark:text-red-400/80">{{ error }}</p>
            <Button
                @click="fetchData()"
                variant="outline"
                class="border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400"
            >
                Try Again
            </Button>
        </div>

        <template v-else>
            <!-- Charts -->
            <div
                v-if="
                    dailyData.length > 0 ||
                    weeklyData.length > 0 ||
                    monthlyData.length > 0
                "
                class="mb-8 grid grid-cols-1 gap-8"
            >
                <DailyEnergyChart
                    v-if="dailyData.length > 0"
                    :data="dailyData"
                />
                <WeeklyEnergyChart
                    v-if="weeklyData.length > 0"
                    :data="weeklyData"
                />
                <MonthlyEnergyChart
                    v-if="monthlyData.length > 0"
                    :data="monthlyData"
                />
            </div>
            <div
                v-else
                class="mb-8 rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900"
            >
                <TrendingUp class="mx-auto mb-4 h-12 w-12 text-zinc-400" />
                <h3
                    class="text-lg font-medium text-zinc-900 dark:text-zinc-100"
                >
                    No analytics data found
                </h3>
                <p class="text-zinc-500">
                    Try adjusting your date filters or check back later.
                </p>
            </div>

            <!-- Cost Analysis -->
            <div
                class="mb-8 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="border-b border-zinc-200 p-6 dark:border-zinc-800">
                    <h3 class="text-lg font-bold dark:text-white">
                        Energy Cost Analysis
                    </h3>
                    <p class="text-sm text-zinc-500">
                        {{ summary.tariff_description }} ·
                        {{ summary.currency }}
                        {{ summary.tariff_rate.toLocaleString() }} per kWh
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Period
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Energy (kWh)
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Tariff
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Cost
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-zinc-200 dark:divide-zinc-800"
                        >
                            <tr
                                v-for="item in summary.cost_analysis"
                                :key="item.period"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-medium dark:text-white"
                                >
                                    {{ item.period }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm dark:text-zinc-300"
                                >
                                    {{ item.energy_kwh.toFixed(2) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm dark:text-zinc-300"
                                >
                                    {{ item.tariff_rate.toLocaleString() }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm font-semibold dark:text-zinc-100"
                                >
                                    {{ formatCurrency(item.cost) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Report Table -->
            <div
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex flex-col gap-4 border-b border-zinc-200 p-6 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800"
                >
                    <div>
                        <h3 class="text-lg font-bold dark:text-white">
                            Energy Report
                        </h3>
                        <p class="text-sm text-zinc-500">
                            Daily consumption, cost, peak demand, and faults.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="exportReport('csv')"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Export CSV
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="exportReport('pdf')"
                        >
                            <FileText class="mr-2 h-4 w-4" />
                            Export PDF
                        </Button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Energy (kWh)
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Cost ({{ summary.currency }})
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Peak (W)
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase"
                                >
                                    Faults
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-zinc-200 dark:divide-zinc-800"
                        >
                            <tr
                                v-for="item in reportData"
                                :key="item.date"
                                class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-medium dark:text-white"
                                >
                                    {{ item.date }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm dark:text-zinc-300"
                                >
                                    {{ item.energy_used.toFixed(2) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm dark:text-zinc-300"
                                >
                                    {{ formatCurrency(item.estimated_cost) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm dark:text-zinc-300"
                                >
                                    {{ item.peak_power.toFixed(0) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <span
                                        :class="
                                            item.fault_count > 0
                                                ? 'font-bold text-red-600'
                                                : 'text-zinc-500'
                                        "
                                    >
                                        {{ item.fault_count }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="reportData.length === 0">
                                <td
                                    colspan="5"
                                    class="px-6 py-10 text-center text-zinc-500"
                                >
                                    No report data available yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    v-if="reportMeta.total > 0"
                    class="flex flex-col gap-3 border-t border-zinc-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800"
                >
                    <p class="text-sm text-zinc-500">
                        Showing {{ reportMeta.from }} to {{ reportMeta.to }} of
                        {{ reportMeta.total }} rows
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="reportMeta.current_page === 1"
                            @click="
                                changeReportPage(reportMeta.current_page - 1)
                            "
                        >
                            Previous
                        </Button>
                        <span class="px-2 text-sm text-zinc-500">
                            Page {{ reportMeta.current_page }} of
                            {{ reportMeta.last_page }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="
                                reportMeta.current_page === reportMeta.last_page
                            "
                            @click="
                                changeReportPage(reportMeta.current_page + 1)
                            "
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
