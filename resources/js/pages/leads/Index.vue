<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

// Components
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { EyeIcon, Search, X, FileText, Inbox, Flag, AlertTriangle, Copy, Zap, MessageCircleWarning, ChevronDown } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

// Types
interface Lead {
  id: number
  site: {
    id: number
    site_name: string
    domain: string
  }
  form_name: string
  form_data: Record<string, any>
  status: string
  submitted_at: string
  submitted_at_formatted: string
  flag_reason: string | null
  flagged_at: string | null
}

interface Site {
  id: number
  name: string
}

interface Props {
  leads: {
    data: Lead[]
    links: Record<string, any>[]
    meta: {
      current_page: number
      from: number
      last_page: number
      path: string
      per_page: number
      to: number
      total: number
    }
  }
  filters: {
    search?: string
    site_id?: string
    status?: string
    date_from?: string
    date_to?: string
  }
  sites: Site[]
}

const props = defineProps<Props>()

// Filter form state
const search = ref(props.filters?.search || '')
const siteId = ref(props.filters?.site_id || 'all')
const status = ref(props.filters?.status || 'all')
const dateFrom = ref(props.filters?.date_from || '')
const dateTo = ref(props.filters?.date_to || '')

// Flag menu state
const openFlagMenu = ref<number | null>(null)
const flaggingIds = ref<Set<number>>(new Set())

// Computed properties
const hasLeads = computed(() => props.leads.data.length > 0)
const hasFilters = computed(() =>
  search.value ||
  (siteId.value && siteId.value !== 'all') ||
  (status.value && status.value !== 'all') ||
  dateFrom.value ||
  dateTo.value
)

// Handle filter submission
const applyFilters = () => {
  const params: Record<string, string> = {}

  if (search.value) params.search = search.value
  if (siteId.value && siteId.value !== 'all') params.site_id = siteId.value
  if (status.value && status.value !== 'all') params.status = status.value
  if (dateFrom.value) params.date_from = dateFrom.value
  if (dateTo.value) params.date_to = dateTo.value

  router.get('/leads', params, {
    preserveState: true,
    preserveScroll: true,
  })
}

// Reset filters
const resetFilters = () => {
  search.value = ''
  siteId.value = 'all'
  status.value = 'all'
  dateFrom.value = ''
  dateTo.value = ''

  router.get('/leads', {}, {
    preserveState: false,
    preserveScroll: true,
  })
}

// Format date helper
const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  });
}

// Get email from form data
const getEmailFromFormData = (formData: Record<string, any>) => {
  const emailKey = Object.keys(formData).find(key =>
    key.toLowerCase().includes('email')
  )
  return emailKey ? formData[emailKey] : null
}

// Get status badge styling
const getStatusClass = (status: string) => {
  switch (status) {
    case 'new':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
    case 'contacted':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
    case 'converted':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
  }
}

// Get flag badge styling and icon
const getFlagDisplay = (reason: string | null) => {
  switch (reason) {
    case 'test':
      return {
        class: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300',
        icon: Zap,
        label: 'Test',
      }
    case 'fake':
      return {
        class: 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300',
        icon: AlertTriangle,
        label: 'Fake',
      }
    case 'spam':
      return {
        class: 'bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300',
        icon: MessageCircleWarning,
        label: 'Spam',
      }
    case 'duplicate':
      return {
        class: 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300',
        icon: Copy,
        label: 'Duplicate',
      }
    default:
      return null
  }
}

// Flag reason options
const flagReasons = [
  { value: 'test', label: 'Test', icon: Zap },
  { value: 'fake', label: 'Fake', icon: AlertTriangle },
  { value: 'spam', label: 'Spam', icon: MessageCircleWarning },
  { value: 'duplicate', label: 'Duplicate', icon: Copy },
]

// Toggle flag on lead
const toggleFlag = async (lead: Lead, reason: string | null) => {
  flaggingIds.value.add(lead.id)

  try {
    const response = await fetch(`/leads/${lead.id}/flag`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ flag_reason: reason }),
    })

    if (response.ok) {
      router.reload({
        only: ['leads'],
        preserveState: true,
      })
    }
  } catch (error) {
    console.error('Failed to flag lead:', error)
  } finally {
    flaggingIds.value.delete(lead.id)
    openFlagMenu.value = null
  }
}
</script>

<template>
  <Head title="Leads" />
  <AppLayout>
    <!-- Custom Fonts -->
    <component :is="'style'">
      @import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap');
    </component>

    <div class="leads-manager relative flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
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
          <h1 class="font-display text-3xl font-bold tracking-tight text-foreground md:text-4xl">
            Leads
          </h1>
          <p class="max-w-2xl font-body text-base text-muted-foreground">
            View and manage leads submitted from your WordPress sites
          </p>
        </div>
      </div>

      <!-- Filters - Single Row Layout -->
      <div class="relative">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-3">
          <!-- Search -->
          <div class="relative flex-1 lg:min-w-[240px] lg:max-w-[320px]">
            <Search
              class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
              aria-hidden="true"
            />
            <Input
              v-model="search"
              type="search"
              placeholder="Search leads..."
              class="h-11 pl-11 font-body shadow-sm"
              @keydown.enter="applyFilters"
              aria-label="Search leads"
            />
          </div>

          <!-- Site Filter -->
          <Select v-model="siteId" @update:modelValue="applyFilters" aria-label="Filter by site">
            <SelectTrigger class="h-11 w-full font-body shadow-sm lg:w-[180px]">
              <SelectValue placeholder="All Sites" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Sites</SelectItem>
              <SelectItem v-for="site in props.sites" :key="site.id" :value="String(site.id)">
                {{ site.name }}
              </SelectItem>
            </SelectContent>
          </Select>

          <!-- Status Filter -->
          <Select v-model="status" @update:modelValue="applyFilters" aria-label="Filter by status">
            <SelectTrigger class="h-11 w-full font-body shadow-sm lg:w-[180px]">
              <SelectValue placeholder="All Statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Statuses</SelectItem>
              <SelectItem value="new">New</SelectItem>
              <SelectItem value="contacted">Contacted</SelectItem>
              <SelectItem value="converted">Converted</SelectItem>
            </SelectContent>
          </Select>

          <!-- Date Range -->
          <div class="flex gap-2">
            <Input
              v-model="dateFrom"
              type="date"
              placeholder="From date"
              class="h-11 w-full font-body shadow-sm lg:w-[160px]"
              aria-label="From date"
              @change="applyFilters"
            />
            <Input
              v-model="dateTo"
              type="date"
              placeholder="To date"
              class="h-11 w-full font-body shadow-sm lg:w-[160px]"
              aria-label="To date"
              @change="applyFilters"
            />
          </div>

          <!-- Filter Actions -->
          <div class="flex items-center gap-2 lg:ml-auto">
            <Button
              variant="outline"
              size="lg"
              @click="applyFilters"
              class="h-11 shadow-sm"
              aria-label="Apply filters"
            >
              <Search class="mr-2 h-4 w-4" />
              Apply Filters
            </Button>

            <Button
              v-if="hasFilters"
              variant="ghost"
              size="lg"
              @click="resetFilters"
              class="h-11 font-body"
              aria-label="Clear all filters"
            >
              <X class="mr-2 h-4 w-4" aria-hidden="true" />
              Clear
            </Button>
          </div>
        </div>
      </div>

      <!-- Table Container -->
      <div class="table-container relative overflow-hidden rounded-xl border border-border bg-card shadow-sm">
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow class="bg-primary hover:bg-primary">
                <TableHead class="h-12 font-body font-semibold text-primary-foreground">Site</TableHead>
                <TableHead class="h-12 font-body font-semibold text-primary-foreground">Form Name</TableHead>
                <TableHead class="h-12 font-body font-semibold text-primary-foreground">Email</TableHead>
                <TableHead class="h-12 font-body font-semibold text-primary-foreground">Status</TableHead>
                <TableHead class="h-12 font-body font-semibold text-primary-foreground">Flag</TableHead>
                <TableHead class="h-12 font-body font-semibold text-primary-foreground">Submitted</TableHead>
                <TableHead class="h-12 text-right font-body font-semibold text-primary-foreground">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="!hasLeads">
                <TableCell colspan="7" class="h-48 text-center" role="cell">
                  <div class="flex flex-col items-center justify-center gap-3 text-muted-foreground">
                    <div class="rounded-full bg-muted p-4">
                      <Inbox class="h-8 w-8 text-muted-foreground/60" aria-hidden="true" />
                    </div>
                    <div class="space-y-1">
                      <p class="font-body text-base font-semibold text-foreground">
                        {{ hasFilters ? 'No leads found' : 'No leads yet' }}
                      </p>
                      <p class="font-body text-sm">
                        {{
                          hasFilters
                            ? 'Try adjusting your search filters'
                            : 'Leads from your WordPress sites will appear here'
                        }}
                      </p>
                    </div>
                    <Button
                      v-if="hasFilters"
                      variant="outline"
                      size="sm"
                      @click="resetFilters"
                      class="mt-2 font-body"
                    >
                      Clear filters
                    </Button>
                  </div>
                </TableCell>
              </TableRow>

              <TableRow
                v-for="lead in props.leads.data"
                :key="lead.id"
                class="group transition-colors hover:bg-muted/30"
              >
                <TableCell class="font-body font-medium">
                  <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary/20">
                      <FileText class="h-4 w-4 text-primary dark:text-primary" aria-hidden="true" />
                    </div>
                    <span>{{ lead.site.site_name }}</span>
                  </div>
                </TableCell>
                <TableCell class="font-body text-muted-foreground">
                  {{ lead.form_name || 'N/A' }}
                </TableCell>
                <TableCell class="max-w-xs">
                  <div class="truncate text-sm font-body text-muted-foreground">
                    {{ getEmailFromFormData(lead.form_data) || 'N/A' }}
                  </div>
                </TableCell>
                <TableCell>
                  <Badge
                    :class="getStatusClass(lead.status)"
                    class="gap-1.5 font-body font-medium capitalize"
                  >
                    {{ lead.status }}
                  </Badge>
                </TableCell>
                <TableCell class="relative">
                  <div class="flex items-center gap-2">
                    <!-- Flag display or button -->
                    <template v-if="lead.flag_reason">
                      <div class="flag-badge-container">
                        <Badge :class="getFlagDisplay(lead.flag_reason)?.class" class="gap-1.5 font-body font-medium capitalize flex items-center animate-in fade-in-50 zoom-in-95">
                          <component :is="getFlagDisplay(lead.flag_reason)?.icon" class="h-3.5 w-3.5" />
                          {{ getFlagDisplay(lead.flag_reason)?.label }}
                        </Badge>
                        <button
                          @click="openFlagMenu = openFlagMenu === lead.id ? null : lead.id"
                          class="flag-menu-trigger ml-1 opacity-0 group-hover:opacity-100 transition-opacity"
                          :aria-label="'Flag options for lead ' + lead.id"
                        >
                          <ChevronDown class="h-3.5 w-3.5 text-muted-foreground" />
                        </button>
                      </div>
                    </template>
                    <template v-else>
                      <button
                        @click="openFlagMenu = openFlagMenu === lead.id ? null : lead.id"
                        class="flag-button opacity-0 group-hover:opacity-100 transition-opacity p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded"
                        :aria-label="'Flag options for lead ' + lead.id"
                      >
                        <Flag class="h-4 w-4 text-muted-foreground" />
                      </button>
                    </template>

                    <!-- Flag menu dropdown -->
                    <div v-if="openFlagMenu === lead.id" class="flag-menu absolute right-0 top-full mt-2 z-50">
                      <div class="bg-card border border-border rounded-lg shadow-lg overflow-hidden">
                        <div class="p-2">
                          <!-- Flag options -->
                          <button
                            v-for="reason in flagReasons"
                            :key="reason.value"
                            @click="toggleFlag(lead, reason.value)"
                            :disabled="flaggingIds.has(lead.id)"
                            class="flag-menu-item w-full text-left px-3 py-2 rounded hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2 text-sm"
                          >
                            <component :is="reason.icon" class="h-4 w-4" />
                            {{ reason.label }}
                          </button>

                          <!-- Unflag option -->
                          <div v-if="lead.flag_reason" class="border-t border-border my-2" />
                          <button
                            v-if="lead.flag_reason"
                            @click="toggleFlag(lead, null)"
                            :disabled="flaggingIds.has(lead.id)"
                            class="flag-menu-item w-full text-left px-3 py-2 rounded hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm text-destructive"
                          >
                            Remove Flag
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="font-body text-sm text-muted-foreground">
                  {{ formatDate(lead.submitted_at) }}
                </TableCell>
                <TableCell class="text-right">
                  <Button
                    variant="ghost"
                    size="sm"
                    as-child
                    class="h-8 font-body"
                    :aria-label="`View lead from ${lead.site.site_name}`"
                  >
                    <Link :href="`/leads/${lead.id}`">
                      <EyeIcon class="mr-1.5 h-4 w-4" aria-hidden="true" />
                      View
                    </Link>
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <!-- Pagination -->
      <Pagination v-if="hasLeads" :data="props.leads" :preserve-query="true" />
    </div>
  </AppLayout>
</template>

<style scoped>
.leads-manager {
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

/* Flag badge animations */
@keyframes slideInScale {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-4px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-in {
  animation: slideInScale 0.2s ease-out;
}

/* Flag menu animation */
@keyframes menuFadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.flag-menu {
  animation: menuFadeIn 0.15s ease-out;
  min-width: 160px;
}

/* Flag menu items hover effect */
.flag-menu-item {
  position: relative;
  overflow: hidden;
}

.flag-menu-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  transition: left 0.3s ease;
}

.flag-menu-item:hover::before {
  left: 100%;
}

/* Flag badge container */
.flag-badge-container {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  position: relative;
}

.flag-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Smooth transitions for flag state changes */
:deep(.fade-in-50) {
  animation: fadeInUp 0.2s ease-out;
}

:deep(.zoom-in-95) {
  animation: slideInScale 0.2s ease-out;
}
</style>
