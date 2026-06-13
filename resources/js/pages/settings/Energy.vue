<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface EnergySetting {
    id: number;
    tariff_rate: number;
    currency: string;
    description: string | null;
}

const props = defineProps<{ setting: EnergySetting }>();
const form = ref({
    ...props.setting,
    description: props.setting.description ?? '',
});
const saving = ref(false);
const message = ref('');
const error = ref('');

const save = async () => {
    saving.value = true;
    message.value = '';
    error.value = '';

    try {
        const response = await fetch('/api/v1/energy/settings', {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(form.value),
        });

        if (!response.ok) {
            const payload = await response.json();

            throw new Error(
                payload.message || 'Unable to save energy settings.',
            );
        }

        form.value = await response.json();
        message.value = 'Energy settings saved.';
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Unable to save energy settings.';
    } finally {
        saving.value = false;
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Energy settings',
                href: '/settings/energy',
            },
        ],
    },
});
</script>

<template>
    <Head title="Energy Settings" />

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Energy Settings"
            description="Manage the tariff used for consumption cost estimates and reports"
        />

        <Alert v-if="message">
            <AlertDescription>{{ message }}</AlertDescription>
        </Alert>
        <Alert v-if="error" variant="destructive">
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <Card>
            <CardHeader>
                <CardTitle>Electricity tariff</CardTitle>
                <CardDescription
                    >All estimated costs are calculated as energy (kWh) × tariff
                    rate.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form class="space-y-5" @submit.prevent="save">
                    <div class="space-y-2">
                        <Label for="tariff-rate">Tariff Rate</Label>
                        <Input
                            id="tariff-rate"
                            v-model.number="form.tariff_rate"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="currency">Currency</Label>
                        <Input
                            id="currency"
                            v-model="form.currency"
                            maxlength="3"
                            placeholder="UGX"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="UMEME Residential Tariff"
                        />
                    </div>
                    <Button type="submit" :disabled="saving">
                        {{ saving ? 'Saving...' : 'Save tariff' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
