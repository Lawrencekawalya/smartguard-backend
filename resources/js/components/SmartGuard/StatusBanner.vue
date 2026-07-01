<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: 'RUN' | 'TRIP' | string;
    faultReason?: string;
}>();

const isStable = computed(() => props.status === 'RUN');
const isOffline = computed(() => props.status === 'OFFLINE');
const bannerClass = computed(() => {
    if (isStable.value) {
        return 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400';
    }

    if (isOffline.value) {
        return 'bg-zinc-500/10 border-zinc-500/20 text-zinc-500 dark:text-zinc-300';
    }

    return 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400 animate-pulse';
});
</script>

<template>
    <div :class="['rounded-xl border p-4 font-bold text-center tracking-wider transition-colors', bannerClass]">
        <template v-if="isStable">
            SYSTEM STABLE / LOAD ONLINE
        </template>
        <template v-else-if="isOffline">
            DEVICE OFFLINE / AWAITING TELEMETRY
        </template>
        <template v-else>
            CRITICAL ALERT: {{ faultReason || 'UNKNOWN FAULT' }} UNRESOLVED - LOAD ISOLATED
        </template>
    </div>
</template>
