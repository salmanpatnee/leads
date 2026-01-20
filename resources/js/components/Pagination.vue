<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

import {
    Pagination,
    PaginationContent,
    PaginationItem,
} from '@/components/ui/pagination';
import { Button } from '@/components/ui/button';
import { ChevronLeftIcon, ChevronRightIcon, MoreHorizontal } from 'lucide-vue-next';
import type { LaravelPagination } from '@/types';

interface Props {
    data: LaravelPagination<unknown>;
    preserveQuery?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    preserveQuery: true,
});

const totalPages = computed(() => props.data.last_page);
const currentPage = computed(() => props.data.current_page);

const hasPages = computed(() => props.data.last_page > 1);
const hasPrevious = computed(() => props.data.prev_page_url !== null);
const hasNext = computed(() => props.data.next_page_url !== null);

function getPageNumbers(): (number | 'ellipsis')[] {
    const pages: (number | 'ellipsis')[] = [];
    const current = currentPage.value;
    const total = totalPages.value;

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
        return pages;
    }

    if (current <= 3) {
        for (let i = 1; i <= 4; i++) {
            pages.push(i);
        }
        pages.push('ellipsis');
        pages.push(total);
    } else if (current >= total - 2) {
        pages.push(1);
        pages.push('ellipsis');
        for (let i = total - 3; i <= total; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);
        pages.push('ellipsis');
        pages.push(current - 1);
        pages.push(current);
        pages.push(current + 1);
        pages.push('ellipsis');
        pages.push(total);
    }

    return pages;
}

function navigateToPage(page: number): void {
    if (page < 1 || page > totalPages.value || page === currentPage.value) {
        return;
    }

    const url = new URL(props.data.path, window.location.origin);

    if (props.preserveQuery) {
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.forEach((value, key) => {
            if (key !== 'page') {
                url.searchParams.set(key, value);
            }
        });
    }

    url.searchParams.set('page', page.toString());

    router.visit(url.pathname + url.search, {
        preserveState: true,
        preserveScroll: true,
    });
}

function goToPrevious(): void {
    if (hasPrevious.value) {
        navigateToPage(currentPage.value - 1);
    }
}

function goToNext(): void {
    if (hasNext.value) {
        navigateToPage(currentPage.value + 1);
    }
}

const pageNumbers = computed(() => getPageNumbers());
</script>

<template>
    <div v-if="hasPages" class="flex items-center justify-between gap-4">
        <div class="shrink-0 whitespace-nowrap text-sm text-muted-foreground">
            <template v-if="data.from && data.to">
                Showing
                <span class="font-medium">{{ data.from }}</span>
                to
                <span class="font-medium">{{ data.to }}</span>
                of
                <span class="font-medium">{{ data.total }}</span>
                results
            </template>
            <template v-else> No results </template>
        </div>

        <Pagination
            :total="data.total"
            :items-per-page="data.per_page"
            :sibling-count="1"
            :default-page="data.current_page"
            aria-label="Pagination navigation"
        >
            <PaginationContent class="flex items-center gap-1">
                <li class="list-none">
                    <Button
                        variant="ghost"
                        :disabled="!hasPrevious"
                        @click.prevent="goToPrevious"
                        :aria-label="`Go to previous page (page ${currentPage - 1})`"
                        class="gap-1 px-2.5"
                    >
                        <ChevronLeftIcon class="h-4 w-4" />
                        <span class="hidden sm:block">Previous</span>
                    </Button>
                </li>

                <template v-for="(page, index) in pageNumbers" :key="`page-${index}`">
                    <li v-if="page === 'ellipsis'" class="flex size-9 list-none items-center justify-center">
                        <MoreHorizontal class="h-4 w-4" />
                        <span class="sr-only">More pages</span>
                    </li>
                    <PaginationItem
                        v-else
                        :value="page"
                        :is-active="page === currentPage"
                        class="h-9 w-9"
                        @click.prevent="navigateToPage(page)"
                        :aria-label="`Go to page ${page}`"
                        :aria-current="page === currentPage ? 'page' : undefined"
                    >
                        {{ page }}
                    </PaginationItem>
                </template>

                <li class="list-none">
                    <Button
                        variant="ghost"
                        :disabled="!hasNext"
                        @click.prevent="goToNext"
                        :aria-label="`Go to next page (page ${currentPage + 1})`"
                        class="gap-1 px-2.5"
                    >
                        <span class="hidden sm:block">Next</span>
                        <ChevronRightIcon class="h-4 w-4" />
                    </Button>
                </li>
            </PaginationContent>
        </Pagination>
    </div>
</template>
