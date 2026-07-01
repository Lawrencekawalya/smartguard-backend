<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    unit?: string;
    icon?: any;
}>();
</script>

<template>
    <div
        class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-sidebar p-4 shadow-sm dark:border-sidebar-border dark:bg-sidebar-accent/10"
    >
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    {{ label }}
                </p>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-2xl font-semibold tracking-tight">{{
                        value
                    }}</span>
                    <span
                        v-if="unit"
                        class="text-sm font-medium text-muted-foreground"
                        >{{ unit }}</span
                    >
                </div>
            </div>
            <div v-if="icon" class="text-muted-foreground/50">
                <component :is="icon" class="h-8 w-8" />
            </div>
        </div>
    </div>
</template>
