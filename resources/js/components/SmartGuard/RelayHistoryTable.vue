<script setup lang="ts">
defineProps<{
    logs: Array<{
        id: number;
        action: string;
        triggered_by: string;
        created_at: string;
    }>;
}>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <div class="rounded-xl border border-sidebar-border/70 overflow-hidden bg-sidebar dark:bg-sidebar-accent/10">
        <div class="p-4 border-b border-sidebar-border/70 bg-muted/50">
            <h3 class="font-semibold">Relay History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-muted/30 text-muted-foreground uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">Action</th>
                        <th class="px-4 py-2">Triggered By</th>
                        <th class="px-4 py-2">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70">
                    <tr v-for="log in logs" :key="log.id" class="hover:bg-muted/20">
                        <td class="px-4 py-3 font-medium">
                            <span :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase', log.action === 'ON' ? 'bg-green-500/10 text-green-600' : 'bg-red-500/10 text-red-600']">
                                {{ log.action }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ log.triggered_by }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ formatDate(log.created_at) }}</td>
                    </tr>
                    <tr v-if="logs.length === 0">
                        <td colspan="3" class="px-4 py-8 text-center text-muted-foreground italic">No relay activity recorded.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
