<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
  ArrowLeft,
  Calendar,
  FileText,
  Globe,
  Monitor,
  Info,
} from 'lucide-vue-next'
import { computed } from 'vue'

import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'

interface Lead {
  id: number
  site: {
    id: number
    name: string
    domain: string
  }
  form_name: string
  form_data: Record<string, any>
  status: string
  ip_address: string | null
  user_agent: string | null
  submitted_at: string
}

interface Props {
  lead: Lead
}

const props = defineProps<Props>()

// Format date helper
const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Format label by replacing hyphens with spaces and capitalizing
const formatLabel = (label: string) => {
  return label
    .replace(/-/g, ' ') // Replace hyphens with spaces
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

// Normalize value by removing brackets and quotes if it's an array
const normalizeValue = (value: any) => {
  if (Array.isArray(value)) {
    return value.join(', ')
  }
  return value
}

// Filter out unwanted fields like g-recaptcha-response
const filteredFormData = computed(() => {
  const filtered: Record<string, any> = {}

  Object.entries(props.lead.form_data).forEach(([key, value]) => {
    // Exclude g-recaptcha-response field
    if (key !== 'g-recaptcha-response') {
      filtered[key] = value
    }
  })

  return filtered
})
</script>

<template>
  <Head :title="`Lead #${lead.id}`" />

  <!-- Custom Fonts -->
  <component :is="'style'">
    @import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap');
  </component>

  <AppLayout>
    <div class="lead-detail-page relative flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
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
            <Link href="/leads" aria-label="Back to Leads">
              <ArrowLeft class="mr-2 h-4 w-4" aria-hidden="true" />
              Back to Leads
            </Link>
          </Button>

          <div class="flex items-center gap-3">
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20"
            >
              <FileText class="h-6 w-6 text-primary" aria-hidden="true" />
            </div>
            <div>
              <h1 class="font-display text-3xl font-bold tracking-tight text-foreground">
                Lead #{{ lead.id }}
              </h1>
              <p class="font-body text-base text-muted-foreground">
                From {{ lead.site.name }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="relative grid gap-6">
        <!-- Lead Information Card -->
        <Card class="detail-card border-border shadow-sm">
          <CardHeader class="space-y-2 pb-4">
            <div class="flex items-center gap-2">
              <div class="rounded-lg bg-primary/10 p-2">
                <Info class="h-4 w-4 text-primary" aria-hidden="true" />
              </div>
              <CardTitle class="font-display text-xl">Lead Information</CardTitle>
            </div>
            <CardDescription class="font-body">
              Details about this lead submission
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div class="info-item space-y-2">
                <p class="font-body text-xs font-semibold uppercase tracking-wider text-foreground">
                  <Globe class="mr-1 inline-block h-3.5 w-3.5" aria-hidden="true" />
                  Site
                </p>
                <div class="space-y-1">
                  <p class="font-body text-base font-semibold text-foreground">
                    {{ lead.site.name }}
                  </p>
                  <p class="font-mono text-sm text-foreground">{{ lead.site.domain }}</p>
                </div>
              </div>

              <div class="info-item space-y-2">
                <p class="font-body text-xs font-semibold uppercase tracking-wider text-foreground">
                  Form Name
                </p>
                <p class="font-body text-base text-foreground">
                  {{ lead.form_name || 'N/A' }}
                </p>
              </div>

              <div class="info-item space-y-2">
                <p class="font-body text-xs font-semibold uppercase tracking-wider text-foreground">
                  <Calendar class="mr-1 inline-block h-3.5 w-3.5" aria-hidden="true" />
                  Submitted At
                </p>
                <p class="font-body text-base text-foreground">
                  {{ formatDate(lead.submitted_at) }}
                </p>
              </div>

              <div v-if="lead.ip_address" class="info-item space-y-2">
                <p class="font-body text-xs font-semibold uppercase tracking-wider text-foreground">
                  IP Address
                </p>
                <p class="font-mono text-sm text-foreground">{{ lead.ip_address }}</p>
              </div>

              <div v-if="lead.user_agent" class="info-item space-y-2">
                <p class="font-body text-xs font-semibold uppercase tracking-wider text-foreground">
                  <Monitor class="mr-1 inline-block h-3.5 w-3.5" aria-hidden="true" />
                  User Agent
                </p>
                <p class="font-mono text-xs text-foreground break-all">
                  {{ lead.user_agent }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Form Submission Data Card -->
        <Card class="detail-card border-border shadow-sm">
          <CardHeader class="space-y-2 pb-4">
            <div class="flex items-center gap-2">
              <div class="rounded-lg bg-primary/10 p-2">
                <FileText class="h-4 w-4 text-primary" aria-hidden="true" />
              </div>
              <CardTitle class="font-display text-xl">Form Submission</CardTitle>
            </div>
            <CardDescription class="font-body">
              Data submitted through the contact form
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="Object.keys(lead.form_data).length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="(value, key) in filteredFormData"
                :key="key"
                class="form-data-item space-y-2"
              >
                <p class="font-body text-sm font-semibold uppercase tracking-wide text-foreground">
                  {{ formatLabel(key) }}
                </p>
                <p class="font-body text-base text-foreground">
                  {{ normalizeValue(value) }}
                </p>
              </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
              <div class="rounded-full bg-muted p-3">
                <FileText class="h-6 w-6 text-muted-foreground/60" aria-hidden="true" />
              </div>
              <p class="mt-3 font-body text-sm text-muted-foreground">
                No form data available
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.lead-detail-page {
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

/* Form data item subtle animation */
.form-data-item {
  position: relative;
  padding-left: 12px;
  border-left: 2px solid transparent;
  transition: all 0.2s ease;
}

.form-data-item:hover {
  border-left-color: hsl(var(--primary) / 0.3);
  padding-left: 16px;
}
</style>
