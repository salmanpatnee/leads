<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import Form from '@/pages/Sites/Form.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as sitesIndex } from '@/routes/sites';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sites',
        href: sitesIndex().url,
    },
    {
        title: 'Create',
        href: '#',
    },
];
</script>

<template>
    <Head title="Create Site" />

    <!-- Custom Fonts -->
    <component :is="'style'">
        @import
        url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap');
    </component>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="create-site-page flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Decorative background gradient -->
            <div
                class="pointer-events-none fixed inset-0 opacity-[0.04] dark:opacity-[0.03]"
                style="
                    background: radial-gradient(
                        circle at 80% 20%,
                        hsl(var(--primary)) 0%,
                        transparent 50%
                    );
                "
            />

            <Form />
        </div>
    </AppLayout>
</template>

<style scoped>
.create-site-page {
    font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}

/* Page entrance animation */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.create-site-page {
    animation: fadeIn 0.3s ease-out;
}
</style>
