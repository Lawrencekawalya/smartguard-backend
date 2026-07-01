<script setup lang="ts">
import type { ApexOptions } from 'apexcharts';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps<{
    data: Array<{ time: string; value: number }>;
    loading?: boolean;
}>();

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        id: 'current-trend',
        toolbar: { show: false },
        animations: { enabled: true },
        background: 'transparent',
    },
    theme: {
        mode: 'dark' as const,
    },
    stroke: {
        curve: 'smooth' as const,
        width: 3,
    },
    colors: ['#3b82f6'], // Blue for current
    xaxis: {
        categories: props.data.map((d) => d.time),
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => val.toFixed(3) + ' A',
            style: { colors: '#94a3b8' },
        },
    },
    grid: {
        borderColor: '#334155',
        strokeDashArray: 4,
    },
    tooltip: {
        theme: 'dark',
        x: { show: true },
    },
    dataLabels: { enabled: false },
}));

const series = computed(() => [
    {
        name: 'Current',
        data: props.data.map((d) => d.value),
    },
]);
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-sidebar p-4 dark:bg-sidebar-accent/10"
    >
        <h3 class="mb-4 text-sm font-medium text-muted-foreground">
            AC Current Trend (Irms)
        </h3>
        <div v-if="loading" class="flex h-[200px] items-center justify-center">
            <div
                class="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"
            ></div>
        </div>
        <div
            v-else-if="data.length === 0"
            class="flex h-[200px] items-center justify-center text-muted-foreground italic"
        >
            No trend data available.
        </div>
        <div v-else>
            <VueApexCharts
                type="line"
                height="200"
                :options="chartOptions"
                :series="series"
            />
        </div>
    </div>
</template>
