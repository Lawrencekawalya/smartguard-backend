<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: 'RUN' | 'TRIP' | string;
    faultReason?: string;
}>();

const isStable = computed(() => props.status === 'RUN');
const bannerClass = computed(() => isStable.value 
    ? 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400' 
    : 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400 animate-pulse');
</script>

<template>
    <div :class="['rounded-xl border p-4 font-bold text-center tracking-wider transition-colors', bannerClass]">
        <template v-if="isStable">
            SYSTEM STABLE / LOAD ONLINE
        </template>
        <template v-else>
            CRITICAL ALERT: {{ faultReason || 'UNKNOWN FAULT' }} UNRESOLVED - LOAD ISOLATED
        </template>
    </div>
</template>
