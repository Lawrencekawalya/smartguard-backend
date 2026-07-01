<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    Zap,
    Activity,
    Cpu,
    Gauge,
    ZapOff,
    ShieldAlert,
    Battery,
    Clock,
} from '@lucide/vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import CurrentTrendChart from '@/components/SmartGuard/CurrentTrendChart.vue';
import FaultHistoryTable from '@/components/SmartGuard/FaultHistoryTable.vue';
import MetricCard from '@/components/SmartGuard/MetricCard.vue';
import PowerTrendChart from '@/components/SmartGuard/PowerTrendChart.vue';
import RelayHistoryTable from '@/components/SmartGuard/RelayHistoryTable.vue';
import StatusBanner from '@/components/SmartGuard/StatusBanner.vue';
import VoltageTrendChart from '@/components/SmartGuard/VoltageTrendChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

const page = usePage();
const token = computed(() => page.props.smartguard_api_token as string);
const deviceCode = 'SmartGuard-MTR-001';

const statusData = ref<any>(null);
const readingData = ref<any>(null);
const voltageTrend = ref<any[]>([]);
const currentTrend = ref<any[]>([]);
const powerTrend = ref<any[]>([]);
const faultHistory = ref<any[]>([]);
const relayHistory = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const pollInterval = ref<any>(null);
const contextPollInterval = ref<any>(null);

const requestHeaders = () => ({
    'X-SmartGuard-Token': token.value,
    Accept: 'application/json',
});

const fetchLiveData = async (isInitial = false) => {
    if (isInitial) {
        loading.value = true;
        error.value = null;
    }

    const headers = requestHeaders();

    try {
        const [statusRes, readingRes] = await Promise.all([
            fetch(
                `/api/v1/smartguard/dashboard/status?device_code=${deviceCode}`,
                { headers },
            ),
            fetch(
                `/api/v1/smartguard/dashboard/latest-reading?device_code=${deviceCode}`,
                { headers },
            ),
        ]);

        if (!statusRes.ok || !readingRes.ok) {
            throw new Error('Failed to fetch dashboard data');
        }

        const [statusJson, readingJson] = await Promise.all([
            statusRes.json(),
            readingRes.json(),
        ]);

        statusData.value = statusJson.data;
        readingData.value = readingJson.data;

        if (!isInitial) {
            error.value = null; // Clear non-blocking error on success
        }
    } catch (e: any) {
        if (isInitial) {
            error.value = e.message || 'An unexpected error occurred';
        } else {
            console.error('Polling error:', e);
        }
    } finally {
        if (isInitial) {
            loading.value = false;
        }
    }
};

const fetchContextData = async () => {
    const headers = requestHeaders();

    try {
        const [faultRes, relayRes, vTrendRes, iTrendRes, pTrendRes] =
            await Promise.all([
                fetch(
                    `/api/v1/smartguard/dashboard/fault-history?device_code=${deviceCode}&limit=5`,
                    { headers },
                ),
                fetch(
                    `/api/v1/smartguard/dashboard/relay-history?device_code=${deviceCode}&limit=5`,
                    { headers },
                ),
                fetch(
                    `/api/v1/smartguard/dashboard/voltage-trend?device_code=${deviceCode}`,
                    { headers },
                ),
                fetch(
                    `/api/v1/smartguard/dashboard/current-trend?device_code=${deviceCode}`,
                    { headers },
                ),
                fetch(
                    `/api/v1/smartguard/dashboard/power-trend?device_code=${deviceCode}`,
                    { headers },
                ),
            ]);

        const [faultJson, relayJson, vTrendJson, iTrendJson, pTrendJson] =
            await Promise.all([
                faultRes.json(),
                relayRes.json(),
                vTrendRes.json(),
                iTrendRes.json(),
                pTrendRes.json(),
            ]);

        faultHistory.value = faultJson.data;
        relayHistory.value = relayJson.data;
        voltageTrend.value = vTrendJson;
        currentTrend.value = iTrendJson;
        powerTrend.value = pTrendJson;
    } catch (e) {
        console.error('Context polling error:', e);
    }
};

onMounted(() => {
    fetchLiveData(true);
    fetchContextData();

    pollInterval.value = setInterval(() => {
        fetchLiveData();
    }, 1000);

    contextPollInterval.value = setInterval(() => {
        fetchContextData();
    }, 5000);
});

onUnmounted(() => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
    }

    if (contextPollInterval.value) {
        clearInterval(contextPollInterval.value);
    }
});
</script>

<template>
    <Head title="SmartGuard Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div v-if="loading" class="flex flex-1 items-center justify-center">
            <div class="flex flex-col items-center gap-2">
                <div
                    class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"
                ></div>
                <p class="text-muted-foreground">Loading telemetry...</p>
            </div>
        </div>

        <div v-else-if="error" class="flex flex-1 items-center justify-center">
            <div
                class="rounded-xl border border-red-500/20 bg-red-500/10 p-6 text-center"
            >
                <ShieldAlert class="mx-auto h-12 w-12 text-red-500" />
                <h3 class="mt-4 text-lg font-bold text-red-600">
                    Connection Error
                </h3>
                <p class="mt-1 text-muted-foreground">{{ error }}</p>
                <button
                    @click="fetchLiveData(true)"
                    class="mt-4 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                >
                    Retry Connection
                </button>
            </div>
        </div>

        <template v-else>
            <!-- Status Banner -->
            <StatusBanner
                :status="statusData?.status"
                :fault-reason="
                    statusData?.status !== 'RUN' &&
                    statusData?.status !== 'OFFLINE'
                        ? statusData?.fault_status
                        : ''
                "
            />

            <!-- Metric Cards Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <MetricCard
                    label="Voltage"
                    :value="readingData?.voltage || '0.00'"
                    unit="Vrms"
                    :icon="Zap"
                />
                <MetricCard
                    label="Current"
                    :value="readingData?.current || '0.000'"
                    unit="Irms"
                    :icon="Activity"
                />
                <MetricCard
                    label="Real Power"
                    :value="readingData?.real_power || '0'"
                    unit="W"
                    :icon="Cpu"
                />
                <MetricCard
                    label="Apparent Power"
                    :value="readingData?.apparent_power || '0'"
                    unit="VA"
                    :icon="Gauge"
                />
                <MetricCard
                    label="Power Factor"
                    :value="readingData?.power_factor || '0.00'"
                    :icon="Clock"
                />
                <MetricCard
                    label="Relay Status"
                    :value="statusData?.relay_status ? 'ON' : 'OFF'"
                    :icon="statusData?.relay_status ? Zap : ZapOff"
                />
                <MetricCard
                    label="Fault Status"
                    :value="statusData?.status || 'UNKNOWN'"
                    :icon="ShieldAlert"
                />
                <MetricCard
                    label="Energy"
                    :value="readingData?.energy_kwh || '0.00'"
                    unit="kWh"
                    :icon="Battery"
                />
            </div>

            <!-- Trend Charts Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <VoltageTrendChart :data="voltageTrend" :loading="loading" />
                <CurrentTrendChart :data="currentTrend" :loading="loading" />
                <PowerTrendChart :data="powerTrend" :loading="loading" />
            </div>

            <!-- History Tables -->
            <div class="grid gap-6 lg:grid-cols-2">
                <FaultHistoryTable :faults="faultHistory" />
                <RelayHistoryTable :logs="relayHistory" />
            </div>
        </template>
    </div>
</template>
