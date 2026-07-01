<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Cpu, Save } from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface FaultSetting {
    id: number;
    parameter: string;
    fault_code: string;
    min_value: number;
    max_value: number;
    unit: string;
    enabled: boolean;
    description: string;
}

interface ThresholdConfig {
    version: number;
    max_current: number;
    min_voltage: number;
    max_voltage: number;
    min_power_factor: number;
    max_real_power: number;
    max_apparent_power: number;
}

interface DeviceThresholdSync {
    device_name: string;
    device_code: string;
    threshold_config_version: number | null;
    threshold_config_ack_version: number | null;
    threshold_config_ack_payload: ThresholdConfig | null;
    threshold_config_status: string;
    threshold_config_error: string | null;
    threshold_config_synced_at: string | null;
}

const props = defineProps<{
    settings: FaultSetting[];
    pendingConfig: ThresholdConfig;
    devices: DeviceThresholdSync[];
}>();

const form = ref<FaultSetting[]>(
    props.settings.map((setting) => ({ ...setting })),
);
const saving = ref<number | 'all' | null>(null);
const message = ref('');
const error = ref('');
const selectedDeviceCode = ref(props.devices[0]?.device_code ?? '');

const selectedDevice = computed(
    () =>
        props.devices.find(
            (device) => device.device_code === selectedDeviceCode.value,
        ) ?? null,
);

const boardConfig = computed(
    () => selectedDevice.value?.threshold_config_ack_payload ?? null,
);

const syncStatusLabel = computed(() => {
    if (!selectedDevice.value) {
        return 'No board has requested config yet';
    }

    if (selectedDevice.value.threshold_config_status === 'synced') {
        return 'Board synced';
    }

    if (selectedDevice.value.threshold_config_status === 'failed') {
        return 'Board rejected last config';
    }

    if (selectedDevice.value.threshold_config_status === 'board_reported') {
        return 'Board reported active config';
    }

    return 'Waiting for board sync';
});

const formatDateTime = (value: string | null) => {
    if (!value) {
        return 'Never';
    }

    return new Date(value).toLocaleString();
};

const formatNumber = (value: number | null | undefined, unit: string) => {
    if (value === null || value === undefined) {
        return 'Not reported';
    }

    return `${Number(value).toLocaleString(undefined, {
        maximumFractionDigits: 3,
    })} ${unit}`;
};

const boardReferenceFor = (setting: FaultSetting) => {
    const config = boardConfig.value;

    if (!config) {
        return 'Not reported by board yet';
    }

    return (
        {
            voltage: `${formatNumber(config.min_voltage, setting.unit)} - ${formatNumber(config.max_voltage, setting.unit)}`,
            current: `Max ${formatNumber(config.max_current, setting.unit)}`,
            power_factor: `Min ${formatNumber(config.min_power_factor, setting.unit)}`,
            real_power: `Max ${formatNumber(config.max_real_power, setting.unit)}`,
            apparent_power: `Max ${formatNumber(config.max_apparent_power, setting.unit)}`,
        }[setting.parameter] ?? 'No board reference'
    );
};

const persistSetting = async (setting: FaultSetting, showSuccess: boolean) => {
    try {
        const response = await fetch(`/api/v1/fault-settings/${setting.id}`, {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                min_value: setting.min_value,
                max_value: setting.max_value,
                enabled: setting.enabled,
            }),
        });

        if (!response.ok) {
            const payload = await response.json();

            throw new Error(payload.message || 'Unable to save threshold.');
        }

        const payload = await response.json();
        const index = form.value.findIndex((item) => item.id === setting.id);

        if (index !== -1 && payload.data) {
            form.value[index] = payload.data;
        }

        if (showSuccess) {
            message.value = `${setting.fault_code} threshold saved. NodeMCU will pick it up on the next config poll.`;
        }

        return true;
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Unable to save threshold.';

        return false;
    }
};

const saveSetting = async (setting: FaultSetting) => {
    saving.value = setting.id;
    message.value = '';
    error.value = '';

    try {
        await persistSetting(setting, true);
    } finally {
        saving.value = null;
    }
};

const saveAll = async () => {
    saving.value = 'all';
    message.value = '';
    error.value = '';

    try {
        for (const setting of form.value) {
            const saved = await persistSetting(setting, false);

            if (!saved) {
                return;
            }
        }

        message.value =
            'Fault thresholds saved. NodeMCU will pick up the latest config on the next poll.';
    } finally {
        saving.value = null;
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Fault thresholds',
                href: '/settings/fault-thresholds',
            },
        ],
    },
});
</script>

<template>
    <Head title="Fault Thresholds" />

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Fault Thresholds"
            description="Manage device protection limits and fault detection"
        />

        <Alert v-if="message">
            <AlertDescription>{{ message }}</AlertDescription>
        </Alert>
        <Alert v-if="error" variant="destructive">
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <Card>
            <CardHeader>
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-[#102A18] text-[#00C853]"
                        >
                            <Cpu class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle>Board threshold reference</CardTitle>
                            <CardDescription>
                                Last threshold config accepted by the hardware
                            </CardDescription>
                        </div>
                    </div>

                    <select
                        v-if="devices.length > 1"
                        v-model="selectedDeviceCode"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    >
                        <option
                            v-for="device in devices"
                            :key="device.device_code"
                            :value="device.device_code"
                        >
                            {{ device.device_name }} ({{ device.device_code }})
                        </option>
                    </select>
                </div>
            </CardHeader>
            <CardContent>
                <div
                    v-if="selectedDevice"
                    class="grid gap-4 text-sm md:grid-cols-2 xl:grid-cols-4"
                >
                    <div>
                        <p class="text-muted-foreground">Sync status</p>
                        <p class="mt-1 font-medium">{{ syncStatusLabel }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Pending version</p>
                        <p class="mt-1 font-medium">
                            {{ pendingConfig.version }}
                        </p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">
                            Board accepted version
                        </p>
                        <p class="mt-1 font-medium">
                            {{
                                selectedDevice.threshold_config_ack_version ??
                                'Not acknowledged'
                            }}
                        </p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Last board sync</p>
                        <p class="mt-1 font-medium">
                            {{
                                formatDateTime(
                                    selectedDevice.threshold_config_synced_at,
                                )
                            }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="selectedDevice?.threshold_config_error"
                    class="mt-4 rounded-md border border-destructive/40 bg-destructive/10 p-3 text-sm text-destructive"
                >
                    {{ selectedDevice.threshold_config_error }}
                </div>

                <div
                    v-if="boardConfig"
                    class="mt-5 grid gap-3 text-sm md:grid-cols-2 xl:grid-cols-3"
                >
                    <div class="rounded-md border border-border p-3">
                        <p class="text-muted-foreground">Voltage range</p>
                        <p class="mt-1 font-medium">
                            {{ formatNumber(boardConfig.min_voltage, 'V') }} -
                            {{ formatNumber(boardConfig.max_voltage, 'V') }}
                        </p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-muted-foreground">Maximum current</p>
                        <p class="mt-1 font-medium">
                            {{ formatNumber(boardConfig.max_current, 'A') }}
                        </p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-muted-foreground">
                            Minimum power factor
                        </p>
                        <p class="mt-1 font-medium">
                            {{
                                formatNumber(boardConfig.min_power_factor, 'PF')
                            }}
                        </p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-muted-foreground">Maximum real power</p>
                        <p class="mt-1 font-medium">
                            {{ formatNumber(boardConfig.max_real_power, 'W') }}
                        </p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-muted-foreground">
                            Maximum apparent power
                        </p>
                        <p class="mt-1 font-medium">
                            {{
                                formatNumber(
                                    boardConfig.max_apparent_power,
                                    'VA',
                                )
                            }}
                        </p>
                    </div>
                </div>

                <p v-else class="text-sm text-muted-foreground">
                    No board threshold reference is available yet. It will
                    appear after the NodeMCU fetches config, sends it to the
                    Mega, and reports an ACK.
                </p>
            </CardContent>
        </Card>

        <div class="flex justify-end">
            <Button
                type="button"
                :disabled="saving !== null || form.length === 0"
                @click="saveAll"
            >
                <Save class="mr-2 h-4 w-4" />
                {{ saving === 'all' ? 'Saving...' : 'Save all changes' }}
            </Button>
        </div>

        <div class="grid gap-6">
            <Card v-for="setting in form" :key="setting.id">
                <CardHeader>
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <CardTitle>{{ setting.fault_code }}</CardTitle>
                            <CardDescription>{{
                                setting.description
                            }}</CardDescription>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Board currently:
                                <span class="font-medium text-foreground">
                                    {{ boardReferenceFor(setting) }}
                                </span>
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                :id="'enabled-' + setting.id"
                                :checked="setting.enabled"
                                @update:checked="
                                    setting.enabled = Boolean($event)
                                "
                            />
                            <Label :for="'enabled-' + setting.id"
                                >Enabled</Label
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <form
                        class="space-y-4"
                        @submit.prevent="saveSetting(setting)"
                    >
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label :for="'min-' + setting.id"
                                    >Minimum Value ({{ setting.unit }})</Label
                                >
                                <Input
                                    :id="'min-' + setting.id"
                                    v-model.number="setting.min_value"
                                    type="number"
                                    step="0.01"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label :for="'max-' + setting.id"
                                    >Maximum Value ({{ setting.unit }})</Label
                                >
                                <Input
                                    :id="'max-' + setting.id"
                                    v-model.number="setting.max_value"
                                    type="number"
                                    step="0.01"
                                    required
                                />
                            </div>
                        </div>

                        <Button type="submit" :disabled="saving !== null">
                            <Save class="mr-2 h-4 w-4" />
                            {{ saving === setting.id ? 'Saving...' : 'Save' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="form.length === 0">
                <CardContent class="py-6 text-sm text-muted-foreground">
                    No fault thresholds have been configured yet.
                </CardContent>
            </Card>
        </div>
    </div>
</template>
