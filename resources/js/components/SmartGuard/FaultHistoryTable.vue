<script setup lang="ts">
defineProps<{
    faults: Array<{
        id: number;
        fault_type: string;
        occurred_at: string;
        resolved_at: string | null;
    }>;
}>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <div class="rounded-xl border border-sidebar-border/70 overflow-hidden bg-sidebar dark:bg-sidebar-accent/10">
        <div class="p-4 border-b border-sidebar-border/70 bg-muted/50">
            <h3 class="font-semibold">Fault History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-muted/30 text-muted-foreground uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">Fault Type</th>
                        <th class="px-4 py-2">Occurred At</th>
                        <th class="px-4 py-2">Resolved At</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70">
                    <tr v-for="fault in faults" :key="fault.id" class="hover:bg-muted/20">
                        <td class="px-4 py-3 font-medium">{{ fault.fault_type }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ formatDate(fault.occurred_at) }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ fault.resolved_at ? formatDate(fault.resolved_at) : '-' }}</td>
                        <td class="px-4 py-3">
                            <span :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase', fault.resolved_at ? 'bg-green-500/10 text-green-600' : 'bg-red-500/10 text-red-600']">
                                {{ fault.resolved_at ? 'Resolved' : 'Active' }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="faults.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-muted-foreground italic">No faults recorded.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
