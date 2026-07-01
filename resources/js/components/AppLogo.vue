<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-[#102A18] text-white shadow-sm ring-1 ring-[#00C853]/30"
    >
        <AppLogoIcon class="size-7" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >SmartGuard</span
        >
        <span class="truncate text-xs leading-tight text-muted-foreground"
            >Protective Energy Monitor</span
        >
    </div>
</template>
