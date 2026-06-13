<script setup lang="ts">
import type { ApexOptions } from 'apexcharts';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps<{
    data: Array<{ date: string; daily_kwh: number }>;
}>();

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'area',
        height: 350,
        zoom: { enabled: false },
        toolbar: { show: false },
    },
    colors: ['#3b82f6'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        type: 'datetime',
        labels: {
            datetimeUTC: false,
            style: { colors: '#a1a1aa' },
        },
    },
    yaxis: {
        title: { text: 'Energy (kWh)', style: { color: '#a1a1aa' } },
        labels: { style: { colors: '#a1a1aa' } },
    },
    tooltip: {
        x: { format: 'dd MMM yyyy' },
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.2,
            stops: [0, 90, 100],
        },
    },
    grid: { borderColor: '#3f3f46' },
}));

const series = computed(() => [
    {
        name: 'Daily Consumption',
        data: props.data.map((item) => ({
            x: item.date,
            y: parseFloat(item.daily_kwh.toString()),
        })),
    },
]);
</script>

<template>
    <div
        class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
    >
        <h3 class="mb-1 text-lg font-semibold dark:text-zinc-100">
            Daily Consumption
        </h3>
        <p class="mb-4 text-sm text-zinc-500">
            Last 30 days or selected date range
        </p>
        <VueApexCharts
            type="area"
            height="350"
            :options="chartOptions"
            :series="series"
        />
    </div>
</template>
