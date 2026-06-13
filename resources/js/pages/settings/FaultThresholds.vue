<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ref } from 'vue';

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

const props = defineProps<{
    settings: FaultSetting[];
}>();

const saving = ref<number | null>(null);

const saveSetting = async (setting: FaultSetting) => {
    saving.value = setting.id;
    try {
        const response = await fetch(`/api/v1/fault-settings/${setting.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                min_value: setting.min_value,
                max_value: setting.max_value,
                enabled: setting.enabled,
            }),
        });
        
        if (!response.ok) {
            throw new Error('Failed to save setting');
        }
    } catch (error) {
        console.error('Failed to save setting:', error);
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

        <div class="grid gap-6">
            <Card v-for="setting in settings" :key="setting.id">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>{{ setting.fault_code }}</CardTitle>
                            <CardDescription>{{ setting.description }}</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox 
                                :id="'enabled-' + setting.id" 
                                :checked="setting.enabled" 
                                @update:checked="setting.enabled = $event; saveSetting(setting)"
                            />
                            <Label :for="'enabled-' + setting.id">Enabled</Label>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label :for="'min-' + setting.id">Minimum Value ({{ setting.unit }})</Label>
                            <Input 
                                :id="'min-' + setting.id" 
                                type="number" 
                                step="0.01" 
                                v-model="setting.min_value"
                                @change="saveSetting(setting)"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label :for="'max-' + setting.id">Maximum Value ({{ setting.unit }})</Label>
                            <Input 
                                :id="'max-' + setting.id" 
                                type="number" 
                                step="0.01" 
                                v-model="setting.max_value"
                                @change="saveSetting(setting)"
                            />
                        </div>
                    </div>
                    <div v-if="saving === setting.id" class="mt-2 text-sm text-muted-foreground">
                        Saving changes...
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
