<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BellRing,
    Gauge,
    ListChecks,
    Save,
    Settings,
    ShieldCheck,
    Smartphone,
    Wifi,
} from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const sections = [
    {
        title: 'Daily monitoring',
        icon: Gauge,
        items: [
            'Open the Dashboard to view voltage, current, real power, apparent power, power factor, relay state, and energy consumption.',
            'Use the status banner first. It tells you whether the device is online, offline, stable, or in fault state.',
            'Trend charts help confirm whether a fault was sudden or building up over time.',
        ],
    },
    {
        title: 'Device online status',
        icon: Wifi,
        items: [
            'The web app marks the SmartGuard device online only when recent telemetry has arrived.',
            'If the device is powered off, disconnected from Wi-Fi, or unable to reach Laravel, it changes to offline after the configured timeout.',
            'When offline, the dashboard should not be treated as live electrical state.',
        ],
    },
    {
        title: 'Fault thresholds',
        icon: Settings,
        items: [
            'Go to Settings, then Fault Thresholds, to edit protection limits.',
            'Change the minimum or maximum values, enable or disable a threshold, then press Save or Save all changes.',
            'Laravel validates the values against hardware-safe limits before accepting them.',
        ],
    },
    {
        title: 'How thresholds reach hardware',
        icon: Save,
        items: [
            'After thresholds are saved, Laravel marks the device config as pending sync.',
            'The NodeMCU polls Laravel for config updates and forwards the latest config frame to the Mega 2560.',
            'The Mega validates the config, applies it if safe, then replies with an acknowledgement that NodeMCU sends back to Laravel.',
        ],
    },
    {
        title: 'Fault handling',
        icon: ShieldCheck,
        items: [
            'When telemetry crosses an enabled threshold, the backend records a fault and the dashboard shows the fault state.',
            'Relay history shows switching actions, while fault history shows what happened and whether it has been resolved.',
            'Use the fault reason and timestamp to decide whether the load should be restored.',
        ],
    },
    {
        title: 'Mobile alarm',
        icon: BellRing,
        items: [
            'The mobile app is focused on alarm response, not full administration.',
            'When an active unacknowledged fault exists, the phone alarm should ring at the same time as the hardware buzzer.',
            'The alarm stops when the phone holder acknowledges it or when the fault is resolved and the hardware reset button is pressed.',
        ],
    },
];

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Documentation',
                href: '/documentation',
            },
        ],
    },
});
</script>

<template>
    <Head title="Documentation" />

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Documentation"
            description="How to operate the SmartGuard protection and monitoring system"
        />

        <div class="grid gap-4 md:grid-cols-3">
            <Link
                href="/dashboard"
                class="rounded-lg border border-border bg-card p-4 transition hover:border-[#00C853]/60"
            >
                <Gauge class="mb-3 h-5 w-5 text-[#00C853]" />
                <h2 class="font-medium">Dashboard</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    View live electrical telemetry and system status.
                </p>
            </Link>
            <Link
                href="/settings/fault-thresholds"
                class="rounded-lg border border-border bg-card p-4 transition hover:border-[#00C853]/60"
            >
                <ListChecks class="mb-3 h-5 w-5 text-[#00C853]" />
                <h2 class="font-medium">Thresholds</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Configure protection limits sent to hardware.
                </p>
            </Link>
            <Link
                href="/devices"
                class="rounded-lg border border-border bg-card p-4 transition hover:border-[#00C853]/60"
            >
                <Smartphone class="mb-3 h-5 w-5 text-[#00C853]" />
                <h2 class="font-medium">Devices</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Register and inspect SmartGuard hardware units.
                </p>
            </Link>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <Card v-for="section in sections" :key="section.title">
                <CardHeader>
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-[#102A18] text-[#00C853]"
                        >
                            <component :is="section.icon" class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle>{{ section.title }}</CardTitle>
                            <CardDescription
                                >SmartGuard operating guide</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li
                            v-for="item in section.items"
                            :key="item"
                            class="flex gap-2"
                        >
                            <span
                                class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[#00C853]"
                            ></span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
