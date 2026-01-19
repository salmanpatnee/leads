<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Calendar,
    CheckCircle2,
    Copy,
    Edit,
    Eye,
    EyeOff,
    Globe,
    Key,
    Shield,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import SiteController from '@/actions/App/Http/Controllers/SiteController';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { index as sitesIndex } from '@/routes/sites';
import type { BreadcrumbItem, Site } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';

interface Props {
    site: Site;
}

const props = defineProps<Props>();

// Debug: Log the props to verify API key is present
console.log('Site props:', props.site);
console.log('API Key present:', !!props.site.api_key);
console.log('API Key value:', props.site.api_key);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sites',
        href: sitesIndex().url,
    },
    {
        title: props.site.site_name,
        href: '#',
    },
];

const apiKeyVisible = ref<boolean>(false);
const copied = ref<boolean>(false);
const copyError = ref<boolean>(false);
const deleteDialogOpen = ref<boolean>(false);

const maskedApiKey = computed(() => {
    return '•'.repeat(32);
});

const displayedApiKey = computed(() => {
    return apiKeyVisible.value ? props.site.api_key : maskedApiKey.value;
});

function toggleApiKeyVisibility(): void {
    console.log('Toggling API key visibility');
    apiKeyVisible.value = !apiKeyVisible.value;
}

async function copyApiKey(): Promise<void> {
    console.log('copyApiKey called');
    console.log('API key to copy:', props.site.api_key);
    copyError.value = false;

    try {
        // Try modern clipboard API first (requires HTTPS)
        if (navigator.clipboard?.writeText) {
            console.log('Using modern clipboard API');
            await navigator.clipboard.writeText(props.site.api_key);
            copied.value = true;
        } else {
            console.log('Using fallback clipboard method');
            // Fallback for HTTP or older browsers
            const textArea = document.createElement('textarea');
            textArea.value = props.site.api_key;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    copied.value = true;
                } else {
                    copyError.value = true;
                }
            } finally {
                document.body.removeChild(textArea);
            }
        }

        if (copied.value) {
            setTimeout(() => {
                copied.value = false;
            }, 2000);
        } else if (copyError.value) {
            setTimeout(() => {
                copyError.value = false;
            }, 3000);
        }
    } catch (error) {
        console.error('Failed to copy API key:', error);
        copyError.value = true;
        setTimeout(() => {
            copyError.value = false;
        }, 3000);
    }
}

function handleDelete(): void {
    router.delete(SiteController.destroy.url(props.site.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
        },
    });
}

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="site.site_name" />

    <!-- Custom Fonts -->
    <component :is="'style'">
        @import
        url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap');
    </component>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="site-detail-page relative flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Decorative background gradient -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.04] dark:opacity-[0.03]"
                style="
                    background: radial-gradient(
                        circle at 80% 20%,
                        hsl(var(--primary)) 0%,
                        transparent 50%
                    );
                "
            />

            <!-- Header -->
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-3">
                    <Button
                        variant="ghost"
                        size="sm"
                        as-child
                        class="-ml-3 font-body transition-all hover:-translate-x-1"
                    >
                        <Link :href="sitesIndex().url" aria-label="Back to Sites">
                            <ArrowLeft class="mr-2 h-4 w-4" aria-hidden="true" />
                            Back to Sites
                        </Link>
                    </Button>

                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20"
                        >
                            <Globe
                                class="h-6 w-6 text-primary"
                                aria-hidden="true"
                            />
                        </div>
                        <div>
                            <h1 class="font-display text-3xl font-bold tracking-tight text-foreground">
                                {{ site.site_name }}
                            </h1>
                            <p class="font-body text-base text-muted-foreground">
                                <code class="font-mono text-sm">{{ site.domain }}</code>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="lg"
                        as-child
                        class="font-body font-medium shadow-sm"
                    >
                        <Link
                            :href="SiteController.edit.url(site.id)"
                            :aria-label="`Edit ${site.site_name}`"
                        >
                            <Edit class="mr-2 h-4 w-4" aria-hidden="true" />
                            Edit
                        </Link>
                    </Button>
                    <Button
                        variant="destructive"
                        size="lg"
                        @click="deleteDialogOpen = true"
                        class="font-body font-semibold shadow-md shadow-destructive/20"
                        :aria-label="`Delete ${site.site_name}`"
                    >
                        <Trash2 class="mr-2 h-4 w-4" aria-hidden="true" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="relative grid gap-6 lg:grid-cols-2">
                <!-- Site Information Card -->
                <Card class="detail-card border-border shadow-sm">
                    <CardHeader class="space-y-2 pb-4">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-primary/10 p-2">
                                <Shield class="h-4 w-4 text-primary" aria-hidden="true" />
                            </div>
                            <CardTitle class="font-display text-xl">Site Information</CardTitle>
                        </div>
                        <CardDescription class="font-body">
                            Basic details about this WordPress site
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="info-item space-y-2">
                            <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Site Name
                            </p>
                            <p class="font-body text-base font-semibold text-foreground">
                                {{ site.site_name }}
                            </p>
                        </div>

                        <div class="info-item space-y-2">
                            <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Domain
                            </p>
                            <p class="font-mono text-base text-foreground">{{ site.domain }}</p>
                        </div>

                        <div class="info-item space-y-2">
                            <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Status
                            </p>
                            <Badge
                                :variant="site.is_active ? 'default' : 'secondary'"
                                class="gap-1.5 font-body font-medium"
                                :aria-label="
                                    site.is_active ? 'Active site' : 'Inactive site'
                                "
                            >
                                <component
                                    :is="site.is_active ? CheckCircle2 : AlertCircle"
                                    class="h-3.5 w-3.5"
                                    aria-hidden="true"
                                />
                                {{ site.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>

                        <div class="info-item space-y-2">
                            <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <Calendar class="mr-1 inline-block h-3.5 w-3.5" aria-hidden="true" />
                                Created At
                            </p>
                            <p class="font-body text-base text-foreground">
                                {{ formatDate(site.created_at) }}
                            </p>
                        </div>

                        <div class="info-item space-y-2">
                            <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <Calendar class="mr-1 inline-block h-3.5 w-3.5" aria-hidden="true" />
                                Last Updated
                            </p>
                            <p class="font-body text-base text-foreground">
                                {{ formatDate(site.updated_at) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- API Access Card -->
                <Card class="detail-card border-border shadow-sm">
                    <CardHeader class="space-y-2 pb-4">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-primary/10 p-2">
                                <Key class="h-4 w-4 text-primary" aria-hidden="true" />
                            </div>
                            <CardTitle class="font-display text-xl">API Access</CardTitle>
                        </div>
                        <CardDescription class="font-body">
                            Use this API key to authenticate requests from WordPress
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-3">
                            <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                API Key
                            </p>
                            <div
                                class="api-key-container flex items-center gap-2 rounded-lg border border-border/60 bg-muted/30 p-3 transition-colors hover:border-border hover:bg-muted/50"
                            >
                                <code
                                    class="flex-1 select-all font-mono text-sm text-foreground/80"
                                    :aria-label="
                                        apiKeyVisible
                                            ? 'API key visible'
                                            : 'API key hidden'
                                    "
                                >
                                    {{ displayedApiKey }}
                                </code>
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click="toggleApiKeyVisibility"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-primary/10 hover:text-foreground"
                                        :aria-label="
                                            apiKeyVisible
                                                ? 'Hide API key'
                                                : 'Show API key'
                                        "
                                    >
                                        <EyeOff
                                            v-if="apiKeyVisible"
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                        <Eye
                                            v-else
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                    <button
                                        type="button"
                                        @click="copyApiKey"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-primary/10 hover:text-foreground"
                                        aria-label="Copy API key to clipboard"
                                    >
                                        <CheckCircle2
                                            v-if="copied"
                                            class="h-4 w-4 text-primary"
                                            aria-hidden="true"
                                        />
                                        <Copy
                                            v-else
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                            </div>

                            <Transition
                                enter-active-class="transition-all duration-300 ease-out"
                                enter-from-class="opacity-0 scale-95"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div
                                    v-show="copied"
                                    class="flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-2 dark:bg-primary/20"
                                    role="status"
                                    aria-live="polite"
                                >
                                    <CheckCircle2
                                        class="h-4 w-4 text-primary"
                                        aria-hidden="true"
                                    />
                                    <span class="font-body text-sm font-medium text-primary">
                                        API key copied to clipboard!
                                    </span>
                                </div>
                            </Transition>

                            <Transition
                                enter-active-class="transition-all duration-300 ease-out"
                                enter-from-class="opacity-0 scale-95"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <Alert
                                    v-show="copyError"
                                    class="border-destructive/50 bg-destructive/5"
                                    role="alert"
                                    aria-live="assertive"
                                >
                                    <AlertCircle class="h-4 w-4 text-destructive" />
                                    <AlertTitle class="font-body text-destructive">Error</AlertTitle>
                                    <AlertDescription class="font-body text-sm text-destructive/90">
                                        Failed to copy. Please select and copy manually.
                                    </AlertDescription>
                                </Alert>
                            </Transition>
                        </div>

                        <Alert class="border-primary/30 bg-primary/5 dark:border-primary/30 dark:bg-primary/10">
                            <Key class="h-4 w-4 text-primary" />
                            <AlertTitle class="font-body font-semibold text-foreground">
                                How to use this API key
                            </AlertTitle>
                            <AlertDescription class="space-y-2 font-body text-muted-foreground">
                                <p class="text-sm">
                                    Include this key in the
                                    <code
                                        class="rounded bg-primary/10 px-1.5 py-0.5 font-mono text-xs"
                                    >
                                        X-API-Key
                                    </code>
                                    header when making requests to the leads API endpoint.
                                </p>
                                <p class="text-xs">
                                    This ensures secure communication between your WordPress site
                                    and the leads system.
                                </p>
                            </AlertDescription>
                        </Alert>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent class="font-body">
                <DialogHeader class="space-y-4">
                    <DialogTitle class="font-display text-2xl">
                        Delete {{ site.site_name }}?
                    </DialogTitle>
                    <DialogDescription class="space-y-3 text-base leading-relaxed">
                        <p>
                            Once deleted, you will no longer be able to receive leads
                            from
                            <strong class="font-semibold text-foreground">{{ site.site_name }}</strong>
                            <span class="text-muted-foreground">({{ site.domain }})</span>.
                        </p>
                        <p>
                            This action is permanent and cannot be undone.
                        </p>
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2 sm:gap-3">
                    <DialogClose as-child>
                        <Button variant="outline" size="lg" class="font-body font-medium">
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        type="button"
                        variant="destructive"
                        size="lg"
                        @click="handleDelete"
                        class="font-body font-semibold shadow-lg shadow-destructive/20"
                        data-test="confirm-delete-site-button"
                    >
                        Delete Site
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
.site-detail-page {
    font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}

.font-display {
    font-family: 'Crimson Pro', ui-serif, Georgia, serif;
    letter-spacing: -0.02em;
}

.font-body {
    font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}

/* Card entrance animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.detail-card {
    animation: fadeInUp 0.5s ease-out backwards;
}

.detail-card:nth-child(2) {
    animation-delay: 0.1s;
}

/* Info item subtle animation */
.info-item {
    position: relative;
    padding-left: 12px;
    border-left: 2px solid transparent;
    transition: all 0.2s ease;
}

.info-item:hover {
    border-left-color: hsl(var(--primary) / 0.3);
    padding-left: 16px;
}

/* API key container */
.api-key-container {
    position: relative;
}

.api-key-container::before {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(
        135deg,
        transparent,
        hsl(var(--primary) / 0.1),
        transparent
    );
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.api-key-container:hover::before {
    opacity: 1;
}
</style>
