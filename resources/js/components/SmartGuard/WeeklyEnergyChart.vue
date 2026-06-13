<script setup lang="ts">
import type { ApexOptions } from 'apexcharts';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps<{
    data: Array<{ week: string; weekly_kwh: number }>;
}>();

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'bar',
        height: 350,
        toolbar: { show: false },
    },
    colors: ['#10b981'],
    plotOptions: {
        bar: {
            borderRadius: 4,
            horizontal: false,
        },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.data.map((item) => item.week),
        labels: { style: { colors: '#a1a1aa' } },
    },
    yaxis: {
        title: { text: 'Energy (kWh)', style: { color: '#a1a1aa' } },
        labels: { style: { colors: '#a1a1aa' } },
    },
    tooltip: { y: { formatter: (value: number) => `${value.toFixed(2)} kWh` } },
    grid: { borderColor: '#3f3f46' },
}));

const series = computed(() => [
    {
        name: 'Weekly Consumption',
        data: props.data.map((item) => parseFloat(item.weekly_kwh.toString())),
    },
]);
</script>

<template>
    <div
        class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
    >
        <h3 class="mb-1 text-lg font-semibold dark:text-zinc-100">
            Weekly Consumption
        </h3>
        <p class="mb-4 text-sm text-zinc-500">
            Last 12 weeks or selected date range
        </p>
        <VueApexCharts
            type="bar"
            height="350"
            :options="chartOptions"
            :series="series"
        />
    </div>
</template>
