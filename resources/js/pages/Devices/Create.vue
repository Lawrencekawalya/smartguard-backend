<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Save } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

const form = useForm({
    device_name: '',
    device_code: '',
    location: '',
    status: 'active',
    firmware_version: '',
    ip_address: '',
});

const submit = () => {
    form.post('/devices');
};
</script>

<template>
    <Head title="Add Device" />

    <div class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link href="/devices">
                    <ChevronLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Add Device</h1>
                <p class="text-muted-foreground">
                    Register a new SmartGuard hardware unit.
                </p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Device Information</CardTitle>
                <CardDescription
                    >Enter the details for the new SmartGuard
                    device.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="device_name">Device Name</Label>
                        <Input
                            id="device_name"
                            v-model="form.device_name"
                            placeholder="e.g. Main Distribution Board"
                            required
                        />
                        <InputError :message="form.errors.device_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="device_code">Device Code</Label>
                        <Input
                            id="device_code"
                            v-model="form.device_code"
                            placeholder="e.g. SmartGuard-MTR-001"
                            required
                        />
                        <p class="text-[10px] text-muted-foreground italic">
                            Must be unique and match the firmware configuration.
                        </p>
                        <InputError :message="form.errors.device_code" />
                    </div>

                    <div class="space-y-2">
                        <Label for="location">Location (Optional)</Label>
                        <Input
                            id="location"
                            v-model="form.location"
                            placeholder="e.g. Server Room, Zone A"
                        />
                        <InputError :message="form.errors.location" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="status">Initial Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active"
                                        >Active</SelectItem
                                    >
                                    <SelectItem value="inactive"
                                        >Inactive</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="space-y-2">
                            <Label for="firmware_version"
                                >Firmware Version (Optional)</Label
                            >
                            <Input
                                id="firmware_version"
                                v-model="form.firmware_version"
                                placeholder="e.g. v1.0.4"
                            />
                            <InputError
                                :message="form.errors.firmware_version"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="ip_address">IP Address (Optional)</Label>
                        <Input
                            id="ip_address"
                            v-model="form.ip_address"
                            placeholder="e.g. 192.168.1.100"
                        />
                        <InputError :message="form.errors.ip_address" />
                    </div>

                    <div class="flex justify-end gap-4 pt-4">
                        <Button type="button" variant="outline" as-child>
                            <Link href="/devices">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="mr-2 h-4 w-4" /> Save Device
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
