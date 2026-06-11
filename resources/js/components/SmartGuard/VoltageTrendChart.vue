<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps<{
    data: Array<{ time: string, value: number }>;
    loading?: boolean;
}>();

const chartOptions = computed(() => ({
    chart: {
        id: 'voltage-trend',
        toolbar: { show: false },
        animations: { enabled: true },
        background: 'transparent',
    },
    theme: {
        mode: 'dark',
    },
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    colors: ['#ef4444'], // Red for voltage
    xaxis: {
        categories: props.data.map(d => d.time),
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => val.toFixed(1) + ' V',
            style: { colors: '#94a3b8' }
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

const series = computed(() => [{
    name: 'Voltage',
    data: props.data.map(d => d.value),
}]);
</script>

<template>
    <div class="rounded-xl border border-sidebar-border/70 p-4 bg-sidebar dark:bg-sidebar-accent/10">
        <h3 class="text-sm font-medium text-muted-foreground mb-4">Voltage Trend (Vrms)</h3>
        <div v-if="loading" class="h-[200px] flex items-center justify-center">
            <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
        </div>
        <div v-else-if="data.length === 0" class="h-[200px] flex items-center justify-center italic text-muted-foreground">
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
