<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

import SiteController from '@/actions/App/Http/Controllers/SiteController';
import Form from '@/pages/Sites/Form.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as sitesIndex } from '@/routes/sites';
import type { BreadcrumbItem, Site } from '@/types';

interface Props {
    site: Site;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sites',
        href: sitesIndex().url,
    },
    {
        title: props.site.site_name,
        href: SiteController.show.url(props.site.id),
    },
    {
        title: 'Edit',
        href: '#',
    },
];
</script>

<template>
    <Head :title="`Edit ${site.site_name}`" />

    <!-- Custom Fonts -->
    <component :is="'style'">
        @import
        url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap');
    </component>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="edit-site-page relative flex h-full flex-1 flex-col gap-8 p-4 md:p-8">
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

            <!-- Back Button -->
            <div class="relative">
                <Button
                    variant="ghost"
                    size="lg"
                    as-child
                    class="back-btn -ml-3 font-body font-medium transition-all hover:-translate-x-1"
                >
                    <Link
                        :href="SiteController.show.url(site.id)"
                        aria-label="Back to Site Details"
                    >
                        <ArrowLeft class="mr-2 h-5 w-5" aria-hidden="true" />
                        Back to Details
                    </Link>
                </Button>
            </div>

            <Form :site="site" />
        </div>
    </AppLayout>
</template>

<style scoped>
.edit-site-page {
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

.edit-site-page {
    animation: fadeIn 0.3s ease-out;
}

/* Back button hover animation */
.back-btn {
    transition: all 0.2s ease;
}
</style>
