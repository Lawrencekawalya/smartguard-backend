<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { 
    Smartphone, 
    Plus, 
    Search, 
    Eye, 
    Edit, 
    Trash2,
    MoreHorizontal 
} from '@lucide/vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Device {
    id: number;
    device_name: string;
    device_code: string;
    location: string | null;
    status: string;
    last_seen_at: string | null;
}

interface Props {
    devices: {
        data: Device[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get('/devices', { search: value }, { preserveState: true, replace: true });
});

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleString();
};

const deleteDevice = (id: number) => {
    if (confirm('Are you sure you want to delete this device?')) {
        router.delete(`/devices/${id}`);
    }
};

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <Head title="Devices" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Devices</h1>
                <p class="text-muted-foreground">Manage your SmartGuard IoT devices.</p>
            </div>
            <Button as-child>
                <Link href="/devices/create">
                    <Plus class="mr-2 h-4 w-4" /> Add Device
                </Link>
            </Button>
        </div>

        <div class="flex items-center gap-4">
            <div class="relative w-full max-w-sm">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="search"
                    placeholder="Search devices..."
                    class="pl-10"
                />
            </div>
        </div>

        <div class="rounded-xl border border-sidebar-border/70 overflow-hidden bg-sidebar dark:bg-sidebar-accent/10">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/30 text-muted-foreground uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Device Name</th>
                            <th class="px-4 py-3">Device Code</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Last Seen</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/70">
                        <tr v-for="device in devices.data" :key="device.id" class="hover:bg-muted/20">
                            <td class="px-4 py-4 font-medium">{{ device.device_name }}</td>
                            <td class="px-4 py-4 font-mono text-xs">{{ device.device_code }}</td>
                            <td class="px-4 py-4">
                                <span :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase', device.status === 'active' ? 'bg-green-500/10 text-green-600' : 'bg-red-500/10 text-red-600']">
                                    {{ device.status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-muted-foreground">{{ device.location || '-' }}</td>
                            <td class="px-4 py-4 text-muted-foreground">{{ formatDate(device.last_seen_at) }}</td>
                            <td class="px-4 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/devices/${device.id}`">
                                                <Eye class="mr-2 h-4 w-4" /> View Details
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/devices/${device.id}/edit`">
                                                <Edit class="mr-2 h-4 w-4" /> Edit
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="text-red-600" @click="deleteDevice(device.id)">
                                            <Trash2 class="mr-2 h-4 w-4" /> Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="devices.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <Smartphone class="h-12 w-12 text-muted-foreground/50" />
                                    <p class="text-lg font-medium text-muted-foreground">No devices found</p>
                                    <p class="text-sm text-muted-foreground">Add a new device to get started.</p>
                                    <Button as-child variant="outline" class="mt-4">
                                        <Link href="/devices/create">
                                            <Plus class="mr-2 h-4 w-4" /> Add Device
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="devices.total > 0" class="flex items-center justify-between border-t border-sidebar-border/70 px-4 py-4 bg-muted/20">
                <div class="text-xs text-muted-foreground">
                    Showing {{ devices.data.length }} of {{ devices.total }} devices
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in devices.links"
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        :disabled="!link.url"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        as-child
                    >
                        <Link :href="link.url || '#'" v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
