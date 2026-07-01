<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Edit,
    Zap,
    Activity,
    Cpu,
    Clock,
    Battery,
    ShieldAlert,
    History,
} from '@lucide/vue';
import FaultHistoryTable from '@/components/SmartGuard/FaultHistoryTable.vue';
import MetricCard from '@/components/SmartGuard/MetricCard.vue';
import RelayHistoryTable from '@/components/SmartGuard/RelayHistoryTable.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

interface Reading {
    voltage: number;
    current: number;
    real_power: number;
    apparent_power: number;
    power_factor: number;
    energy_kwh: number;
    relay_status: boolean;
    fault_status: string;
}

interface Fault {
    id: number;
    fault_type: string;
    occurred_at: string;
    resolved_at: string | null;
}

interface RelayLog {
    id: number;
    action: string;
    triggered_by: string;
    created_at: string;
}

interface Device {
    id: number;
    device_name: string;
    device_code: string;
    location: string | null;
    status: string;
    firmware_version: string | null;
    ip_address: string | null;
    last_seen_at: string | null;
    readings: Reading[];
    faults: Fault[];
    relayLogs: RelayLog[];
}

const props = defineProps<{
    device: Device;
}>();

const latestReading = props.device.readings[0] || null;

defineOptions({
    layout: AppLayout,
});

const formatDate = (dateString: string | null) => {
    if (!dateString) {
        return 'Never';
    }

    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Head :title="device.device_name" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link href="/devices">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ device.device_name }}
                    </h1>
                    <p class="text-muted-foreground">
                        {{ device.device_code }} •
                        {{ device.location || 'No location' }}
                    </p>
                </div>
            </div>
            <Button variant="outline" as-child>
                <Link :href="`/devices/${device.id}/edit`">
                    <Edit class="mr-2 h-4 w-4" /> Edit Device
                </Link>
            </Button>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <Card class="md:col-span-1">
                <CardHeader>
                    <CardTitle>Device Details</CardTitle>
                    <CardDescription
                        >Hardware and network information.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        class="flex justify-between border-b border-sidebar-border/50 pb-2"
                    >
                        <span class="text-sm text-muted-foreground"
                            >Status</span
                        >
                        <span
                            :class="[
                                'text-sm font-bold uppercase',
                                device.status === 'active'
                                    ? 'text-green-600'
                                    : 'text-red-600',
                            ]"
                        >
                            {{ device.status }}
                        </span>
                    </div>
                    <div
                        class="flex justify-between border-b border-sidebar-border/50 pb-2"
                    >
                        <span class="text-sm text-muted-foreground"
                            >Firmware</span
                        >
                        <span class="text-sm font-medium">{{
                            device.firmware_version || 'Unknown'
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-b border-sidebar-border/50 pb-2"
                    >
                        <span class="text-sm text-muted-foreground"
                            >IP Address</span
                        >
                        <span class="font-mono text-sm font-medium">{{
                            device.ip_address || 'Not assigned'
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-b border-sidebar-border/50 pb-2"
                    >
                        <span class="text-sm text-muted-foreground"
                            >Last Seen</span
                        >
                        <span class="text-sm font-medium">{{
                            formatDate(device.last_seen_at)
                        }}</span>
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-6 md:col-span-2">
                <h3 class="flex items-center gap-2 text-lg font-semibold">
                    <Activity class="h-5 w-5 text-primary" /> Latest Telemetry
                </h3>

                <div
                    v-if="latestReading"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <MetricCard
                        label="Voltage"
                        :value="latestReading.voltage"
                        unit="Vrms"
                        :icon="Zap"
                    />
                    <MetricCard
                        label="Current"
                        :value="latestReading.current"
                        unit="Irms"
                        :icon="Activity"
                    />
                    <MetricCard
                        label="Real Power"
                        :value="latestReading.real_power"
                        unit="W"
                        :icon="Cpu"
                    />
                    <MetricCard
                        label="Power Factor"
                        :value="latestReading.power_factor"
                        :icon="Clock"
                    />
                    <MetricCard
                        label="Relay"
                        :value="latestReading.relay_status ? 'ON' : 'OFF'"
                        :icon="Zap"
                    />
                    <MetricCard
                        label="Energy"
                        :value="latestReading.energy_kwh"
                        unit="kWh"
                        :icon="Battery"
                    />
                </div>
                <div
                    v-else
                    class="rounded-xl border border-dashed p-12 text-center text-muted-foreground italic"
                >
                    No telemetry data available for this device.
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <h3 class="flex items-center gap-2 text-lg font-semibold">
                    <ShieldAlert class="h-5 w-5 text-red-500" /> Recent Faults
                </h3>
                <FaultHistoryTable :faults="device.faults" />
            </div>
            <div class="space-y-4">
                <h3 class="flex items-center gap-2 text-lg font-semibold">
                    <History class="h-5 w-5 text-blue-500" /> Recent Relay
                    Activity
                </h3>
                <RelayHistoryTable :logs="device.relayLogs" />
            </div>
        </div>
    </div>
</template>
