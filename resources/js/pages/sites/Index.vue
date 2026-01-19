<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Copy,
    Edit,
    Eye,
    Globe,
    Key,
    Plus,
    Search,
    Trash2,
    XCircle,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import SiteController from '@/actions/App/Http/Controllers/SiteController';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { create as createSite } from '@/routes/sites';
import type { BreadcrumbItem, LaravelPagination, Site } from '@/types';

interface Props {
    sites: LaravelPagination<Site>;
    filters: {
        search?: string;
        is_active?: string | boolean;
        per_page?: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sites',
        href: SiteController.index.url(),
    },
];

const searchQuery = ref<string>(props.filters.search || '');
const statusFilter = ref<string>(
    typeof props.filters.is_active === 'boolean'
        ? props.filters.is_active
            ? '1'
            : '0'
        : props.filters.is_active || 'all',
);

const siteToDelete = ref<Site | null>(null);
const deleteDialogOpen = ref<boolean>(false);
const copiedApiKey = ref<number | null>(null);

const hasSites = computed(() => props.sites.data.length > 0);
const hasFilters = computed(
    () => searchQuery.value || statusFilter.value !== 'all',
);

function performSearch(): void {
    const params: Record<string, string> = {};

    if (searchQuery.value) {
        params.search = searchQuery.value;
    }

    if (statusFilter.value !== 'all') {
        params.is_active = statusFilter.value;
    }

    router.visit(SiteController.index.url({ query: params }), {
        preserveState: true,
        preserveScroll: true,
        only: ['sites', 'filters'],
    });
}

function clearFilters(): void {
    searchQuery.value = '';
    statusFilter.value = 'all';
    performSearch();
}

function openDeleteDialog(site: Site): void {
    siteToDelete.value = site;
    deleteDialogOpen.value = true;
}

function handleDelete(): void {
    if (!siteToDelete.value) {
        return;
    }

    router.delete(SiteController.destroy.url(siteToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
            siteToDelete.value = null;
        },
    });
}

async function copyApiKey(siteId: number, apiKey: string): Promise<void> {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(apiKey);
            copiedApiKey.value = siteId;
            setTimeout(() => {
                copiedApiKey.value = null;
            }, 2000);
        } else {
            // Fallback for HTTP or older browsers
            const textArea = document.createElement('textarea');
            textArea.value = apiKey;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    copiedApiKey.value = siteId;
                    setTimeout(() => {
                        copiedApiKey.value = null;
                    }, 2000);
                }
            } finally {
                document.body.removeChild(textArea);
            }
        }
    } catch (err) {
        console.error('Failed to copy API key:', err);
        // Still show success feedback even if we can't verify
        copiedApiKey.value = siteId;
        setTimeout(() => {
            copiedApiKey.value = null;
        }, 2000);
    }
}

watch(statusFilter, () => {
    performSearch();
});
</script>

<template>
    <Head title="Sites" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Custom Fonts -->
        <component :is="'style'">
            @import
            url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap');
        </component>

        <div class="sites-manager relative flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Decorative background gradient -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.04] dark:opacity-[0.03]"
                style="
                    background: radial-gradient(
                        circle at 20% 20%,
                        hsl(var(--primary)) 0%,
                        transparent 50%
                    );
                "
            />

            <!-- Header -->
            <div class="relative flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="space-y-1.5">
                    <h1
                        class="font-display text-3xl font-bold tracking-tight text-foreground md:text-4xl"
                    >
                        Sites
                    </h1>
                    <p class="max-w-2xl font-body text-base text-muted-foreground">
                        Manage WordPress sites that send leads to your system
                    </p>
                </div>
                <Button
                    as-child
                    size="lg"
                    class="create-btn shrink-0 font-body font-semibold shadow-md transition-all hover:shadow-lg"
                >
                    <Link :href="createSite().url" data-test="create-site-link">
                        <Plus class="mr-2 h-5 w-5" aria-hidden="true" />
                        Create Site
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <Search
                        class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <Input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search by site name or domain..."
                        class="h-11 pl-11 font-body shadow-sm"
                        @keydown.enter="performSearch"
                        aria-label="Search sites"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <Select v-model="statusFilter" aria-label="Filter by status">
                        <SelectTrigger class="h-11 w-[180px] font-body shadow-sm">
                            <SelectValue placeholder="All Sites" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Sites</SelectItem>
                            <SelectItem value="1">Active Only</SelectItem>
                            <SelectItem value="0">Inactive Only</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button
                        variant="outline"
                        size="lg"
                        @click="performSearch"
                        class="h-11 shadow-sm"
                        aria-label="Apply filters"
                    >
                        <Search class="h-4 w-4" />
                    </Button>

                    <Button
                        v-if="hasFilters"
                        variant="ghost"
                        size="lg"
                        @click="clearFilters"
                        class="h-11 font-body"
                        aria-label="Clear all filters"
                    >
                        <X class="mr-2 h-4 w-4" aria-hidden="true" />
                        Clear
                    </Button>
                </div>
            </div>

            <!-- Table Container -->
            <div
                class="table-container relative overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/40 hover:bg-muted/40">
                                <TableHead class="h-12 font-body font-semibold">Site Name</TableHead>
                                <TableHead class="h-12 font-body font-semibold">Domain</TableHead>
                                <TableHead class="h-12 font-body font-semibold">API Key</TableHead>
                                <TableHead class="h-12 font-body font-semibold">Status</TableHead>
                                <TableHead class="h-12 text-right font-body font-semibold">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="!hasSites">
                                <TableCell
                                    colspan="5"
                                    class="h-48 text-center"
                                    role="cell"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center gap-3 text-muted-foreground"
                                    >
                                        <div class="rounded-full bg-muted p-4">
                                            <Globe
                                                class="h-8 w-8 text-muted-foreground/60"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="font-body text-base font-semibold text-foreground">
                                                {{
                                                    hasFilters
                                                        ? 'No sites found'
                                                        : 'No sites yet'
                                                }}
                                            </p>
                                            <p class="font-body text-sm">
                                                {{
                                                    hasFilters
                                                        ? 'Try adjusting your search filters'
                                                        : 'Create your first site to start receiving leads'
                                                }}
                                            </p>
                                        </div>
                                        <Button
                                            v-if="hasFilters"
                                            variant="outline"
                                            size="sm"
                                            @click="clearFilters"
                                            class="mt-2 font-body"
                                        >
                                            Clear filters
                                        </Button>
                                        <Button
                                            v-else
                                            as-child
                                            size="sm"
                                            class="mt-2 font-body"
                                        >
                                            <Link :href="createSite().url">
                                                <Plus class="mr-2 h-4 w-4" aria-hidden="true" />
                                                Create Your First Site
                                            </Link>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow
                                v-for="site in sites.data"
                                :key="site.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <TableCell class="font-body font-medium">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary/20"
                                        >
                                            <Globe
                                                class="h-4 w-4 text-primary dark:text-primary"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <span>{{ site.site_name }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="font-body text-muted-foreground">
                                    <code class="font-mono text-sm">{{ site.domain }}</code>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <code
                                            class="flex-1 font-mono text-xs text-muted-foreground line-clamp-1"
                                        >
                                            {{ site.api_key.substring(0, 20) }}...
                                        </code>
                                        <button
                                            type="button"
                                            @click.stop="copyApiKey(site.id, site.api_key)"
                                            class="rounded p-1.5 text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                            :aria-label="`Copy API key for ${site.site_name}`"
                                        >
                                            <CheckCircle2
                                                v-if="copiedApiKey === site.id"
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
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="site.is_active ? 'default' : 'secondary'"
                                        class="gap-1.5 font-body font-medium"
                                        :aria-label="site.is_active ? 'Active site' : 'Inactive site'"
                                    >
                                        <component
                                            :is="site.is_active ? CheckCircle2 : XCircle"
                                            class="h-3 w-3"
                                            aria-hidden="true"
                                        />
                                        {{ site.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            as-child
                                            class="h-8 font-body"
                                            :aria-label="`View ${site.site_name}`"
                                        >
                                            <Link
                                                :href="
                                                    SiteController.show.url(
                                                        site.id,
                                                    )
                                                "
                                            >
                                                <Eye
                                                    class="mr-1.5 h-4 w-4"
                                                    aria-hidden="true"
                                                />
                                                View
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            as-child
                                            class="h-8 font-body"
                                            :aria-label="`Edit ${site.site_name}`"
                                        >
                                            <Link
                                                :href="
                                                    SiteController.edit.url(
                                                        site.id,
                                                    )
                                                "
                                            >
                                                <Edit
                                                    class="mr-1.5 h-4 w-4"
                                                    aria-hidden="true"
                                                />
                                                Edit
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openDeleteDialog(site)"
                                            class="h-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            :aria-label="`Delete ${site.site_name}`"
                                        >
                                            <Trash2 class="h-4 w-4" aria-hidden="true" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination v-if="hasSites" :data="sites" :preserve-query="true" />
        </div>

        <!-- Delete Dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent class="font-body">
                <DialogHeader class="space-y-4">
                    <DialogTitle class="font-display text-2xl">
                        Delete this site?
                    </DialogTitle>
                    <DialogDescription class="text-base leading-relaxed">
                        <template v-if="siteToDelete">
                            Once deleted, you will no longer be able to receive
                            leads from
                            <strong class="font-semibold text-foreground">{{
                                siteToDelete.site_name
                            }}</strong>
                            <span class="text-muted-foreground">({{ siteToDelete.domain }})</span>.
                            This action is permanent and cannot be undone.
                        </template>
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
.sites-manager {
    font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}

.font-display {
    font-family: 'Crimson Pro', ui-serif, Georgia, serif;
    letter-spacing: -0.02em;
}

.font-body {
    font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}

/* Table entrance animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.table-container {
    animation: fadeInUp 0.4s ease-out;
}

/* Create button shimmer effect */
.create-btn {
    position: relative;
    overflow: hidden;
}

.create-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        transparent 0%,
        rgba(255, 255, 255, 0.1) 50%,
        transparent 100%
    );
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.create-btn:hover::before {
    transform: translateX(100%);
}
</style>
