<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
  ArrowLeft,
  Calendar,
  Clock,
  Copy,
  Check,
  FileText,
  Globe,
  Mail,
  MapPin,
  Monitor,
  Phone,
  User,
  MessageSquare,
  Tag,
  ExternalLink,
  Shield,
  Sparkles,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
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
  created_at: string
  updated_at: string
}

interface Props {
  lead: Lead
}

const props = defineProps<Props>()

// Status management
const currentStatus = ref(props.lead.status)
const isUpdatingStatus = ref(false)

const updateStatus = async (newStatus: string) => {
  if (newStatus === currentStatus.value) return

  isUpdatingStatus.value = true
  router.patch(`/leads/${props.lead.id}`, { status: newStatus }, {
    preserveScroll: true,
    onSuccess: () => {
      currentStatus.value = newStatus
      isUpdatingStatus.value = false
    },
    onError: () => {
      isUpdatingStatus.value = false
    },
  })
}

// Copy to clipboard
const copiedField = ref<string | null>(null)
const copyToClipboard = async (text: string, fieldName: string) => {
  await navigator.clipboard.writeText(text)
  copiedField.value = fieldName
  setTimeout(() => {
    copiedField.value = null
  }, 2000)
}

// Format date helper
const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getRelativeTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`
  return formatDate(dateString)
}

// Format label by replacing hyphens/underscores with spaces and capitalizing
const formatLabel = (label: string) => {
  return label
    .replace(/[-_]/g, ' ')
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ')
}

// Normalize value
const normalizeValue = (value: any): string => {
  if (Array.isArray(value)) {
    return value.join(', ')
  }
  if (typeof value === 'object' && value !== null) {
    return JSON.stringify(value, null, 2)
  }
  return String(value || '')
}

// Categorize form fields for better presentation
const categorizedFormData = computed(() => {
  const contactFields: Array<{ key: string; value: any; icon: any }> = []
  const messageFields: Array<{ key: string; value: any; icon: any }> = []
  const otherFields: Array<{ key: string; value: any; icon: any }> = []

  const contactKeywords = ['name', 'email', 'phone', 'tel', 'mobile', 'address', 'city', 'state', 'zip', 'country', 'company', 'organization']
  const messageKeywords = ['message', 'comment', 'inquiry', 'question', 'description', 'details', 'note', 'feedback', 'subject']
  const excludeKeywords = ['g-recaptcha-response', 'recaptcha', 'honeypot', '_token', 'csrf']

  Object.entries(props.lead.form_data).forEach(([key, value]) => {
    const lowerKey = key.toLowerCase()

    // Skip excluded fields
    if (excludeKeywords.some(kw => lowerKey.includes(kw))) return

    // Determine icon based on field name
    let icon = Tag
    if (lowerKey.includes('email')) icon = Mail
    else if (lowerKey.includes('phone') || lowerKey.includes('tel') || lowerKey.includes('mobile')) icon = Phone
    else if (lowerKey.includes('name') || lowerKey.includes('company')) icon = User
    else if (lowerKey.includes('address') || lowerKey.includes('city') || lowerKey.includes('country')) icon = MapPin
    else if (lowerKey.includes('message') || lowerKey.includes('comment') || lowerKey.includes('inquiry')) icon = MessageSquare

    const fieldData = { key, value, icon }

    if (contactKeywords.some(kw => lowerKey.includes(kw))) {
      contactFields.push(fieldData)
    } else if (messageKeywords.some(kw => lowerKey.includes(kw))) {
      messageFields.push(fieldData)
    } else {
      otherFields.push(fieldData)
    }
  })

  return { contactFields, messageFields, otherFields }
})

// Status configurations
const statusConfig = {
  new: {
    label: 'New',
    color: 'bg-blue-500',
    bgColor: 'bg-blue-50 dark:bg-blue-950/30',
    textColor: 'text-blue-700 dark:text-blue-300',
    borderColor: 'border-blue-200 dark:border-blue-800',
    description: 'Fresh lead awaiting first contact',
  },
  contacted: {
    label: 'Contacted',
    color: 'bg-amber-500',
    bgColor: 'bg-amber-50 dark:bg-amber-950/30',
    textColor: 'text-amber-700 dark:text-amber-300',
    borderColor: 'border-amber-200 dark:border-amber-800',
    description: 'Initial outreach has been made',
  },
  converted: {
    label: 'Converted',
    color: 'bg-emerald-500',
    bgColor: 'bg-emerald-50 dark:bg-emerald-950/30',
    textColor: 'text-emerald-700 dark:text-emerald-300',
    borderColor: 'border-emerald-200 dark:border-emerald-800',
    description: 'Successfully converted to customer',
  },
}

const currentStatusConfig = computed(() => statusConfig[currentStatus.value as keyof typeof statusConfig] || statusConfig.new)

// Parse user agent for display
const parsedUserAgent = computed(() => {
  if (!props.lead.user_agent) return null

  const ua = props.lead.user_agent
  let browser = 'Unknown Browser'
  let os = 'Unknown OS'

  // Detect browser
  if (ua.includes('Chrome') && !ua.includes('Edg')) browser = 'Chrome'
  else if (ua.includes('Firefox')) browser = 'Firefox'
  else if (ua.includes('Safari') && !ua.includes('Chrome')) browser = 'Safari'
  else if (ua.includes('Edg')) browser = 'Edge'
  else if (ua.includes('Opera') || ua.includes('OPR')) browser = 'Opera'

  // Detect OS
  if (ua.includes('Windows')) os = 'Windows'
  else if (ua.includes('Mac OS')) os = 'macOS'
  else if (ua.includes('Linux')) os = 'Linux'
  else if (ua.includes('Android')) os = 'Android'
  else if (ua.includes('iOS') || ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS'

  return { browser, os, full: ua }
})
</script>

<template>
  <Head :title="`Lead #${lead.id}`" />

  <!-- Custom Fonts -->
  <component :is="'style'">
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap');
  </component>

  <AppLayout>
    <div class="lead-detail-page relative min-h-screen">
      <!-- Subtle gradient background -->
      <div
        class="pointer-events-none absolute inset-0 opacity-40 dark:opacity-20"
        style="
          background:
            radial-gradient(ellipse at 0% 0%, hsl(var(--primary) / 0.08) 0%, transparent 50%),
            radial-gradient(ellipse at 100% 100%, hsl(var(--primary) / 0.05) 0%, transparent 50%);
        "
      />

      <div class="relative mx-auto max-w-6xl px-4 py-8 md:px-8">
        <!-- Navigation & Header -->
        <header class="mb-8 space-y-6">
          <Button
            variant="ghost"
            size="sm"
            as-child
            class="group -ml-2 font-body text-muted-foreground transition-all hover:text-foreground"
          >
            <Link href="/leads" aria-label="Back to Leads">
              <ArrowLeft class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" aria-hidden="true" />
              Back to Leads
            </Link>
          </Button>

          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <!-- Title Section -->
            <div class="space-y-2">
              <div class="flex items-center gap-3">
                <span class="font-body text-sm font-medium uppercase tracking-widest text-muted-foreground">
                  Lead Details
                </span>
                <div class="h-px flex-1 bg-border lg:hidden" />
              </div>
              <h1 class="font-display text-4xl font-semibold tracking-tight text-foreground md:text-5xl">
                #{{ lead.id }}
              </h1>
              <p class="font-body text-lg text-muted-foreground">
                {{ getRelativeTime(lead.submitted_at) }} from <span class="font-medium text-foreground">{{ lead.site.name }}</span>
              </p>
            </div>

            <!-- Status Card -->
            <div
              class="status-card flex flex-col gap-3 rounded-2xl border p-5 transition-all duration-300"
              :class="[currentStatusConfig.bgColor, currentStatusConfig.borderColor]"
            >
              <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div
                    class="status-indicator h-3 w-3 rounded-full"
                    :class="currentStatusConfig.color"
                  />
                  <span class="font-body text-sm font-semibold uppercase tracking-wider" :class="currentStatusConfig.textColor">
                    {{ currentStatusConfig.label }}
                  </span>
                </div>
                <Select :model-value="currentStatus" @update:model-value="updateStatus" :disabled="isUpdatingStatus">
                  <SelectTrigger class="h-9 w-[140px] border-0 bg-white/50 font-body text-sm shadow-sm dark:bg-black/20">
                    <SelectValue placeholder="Change status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="new">New</SelectItem>
                    <SelectItem value="contacted">Contacted</SelectItem>
                    <SelectItem value="converted">Converted</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <p class="font-body text-sm" :class="currentStatusConfig.textColor" style="opacity: 0.8;">
                {{ currentStatusConfig.description }}
              </p>
            </div>
          </div>
        </header>

        <!-- Main Content Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Left Column: Form Data -->
          <div class="space-y-6 lg:col-span-2">
            <!-- Contact Information -->
            <Card v-if="categorizedFormData.contactFields.length > 0" class="detail-card overflow-hidden border-border/50 shadow-sm">
              <CardHeader class="border-b border-border/50 bg-muted/30 pb-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <User class="h-5 w-5 text-primary" aria-hidden="true" />
                  </div>
                  <div>
                    <CardTitle class="font-display text-xl">Contact Information</CardTitle>
                    <CardDescription class="font-body text-sm">Primary contact details from the submission</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent class="p-0">
                <div class="divide-y divide-border/50">
                  <div
                    v-for="field in categorizedFormData.contactFields"
                    :key="field.key"
                    class="group flex items-start gap-4 px-6 py-4 transition-colors hover:bg-muted/30"
                  >
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted">
                      <component :is="field.icon" class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        {{ formatLabel(field.key) }}
                      </p>
                      <p class="mt-1 font-body text-base font-medium text-foreground break-words">
                        {{ normalizeValue(field.value) }}
                      </p>
                    </div>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-8 w-8 shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
                      @click="copyToClipboard(normalizeValue(field.value), field.key)"
                      :aria-label="`Copy ${formatLabel(field.key)}`"
                    >
                      <Check v-if="copiedField === field.key" class="h-4 w-4 text-emerald-500" />
                      <Copy v-else class="h-4 w-4 text-muted-foreground" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Message/Inquiry -->
            <Card v-if="categorizedFormData.messageFields.length > 0" class="detail-card overflow-hidden border-border/50 shadow-sm" style="animation-delay: 0.1s;">
              <CardHeader class="border-b border-border/50 bg-muted/30 pb-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <MessageSquare class="h-5 w-5 text-primary" aria-hidden="true" />
                  </div>
                  <div>
                    <CardTitle class="font-display text-xl">Inquiry Details</CardTitle>
                    <CardDescription class="font-body text-sm">Message and additional context</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent class="p-6">
                <div class="space-y-6">
                  <div
                    v-for="field in categorizedFormData.messageFields"
                    :key="field.key"
                    class="space-y-2"
                  >
                    <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                      {{ formatLabel(field.key) }}
                    </p>
                    <div class="rounded-xl bg-muted/50 p-4">
                      <p class="font-body text-base leading-relaxed text-foreground whitespace-pre-wrap">
                        {{ normalizeValue(field.value) }}
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Other Fields -->
            <Card v-if="categorizedFormData.otherFields.length > 0" class="detail-card overflow-hidden border-border/50 shadow-sm" style="animation-delay: 0.15s;">
              <CardHeader class="border-b border-border/50 bg-muted/30 pb-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <Sparkles class="h-5 w-5 text-primary" aria-hidden="true" />
                  </div>
                  <div>
                    <CardTitle class="font-display text-xl">Additional Information</CardTitle>
                    <CardDescription class="font-body text-sm">Other submitted form fields</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent class="p-0">
                <div class="divide-y divide-border/50">
                  <div
                    v-for="field in categorizedFormData.otherFields"
                    :key="field.key"
                    class="flex items-start gap-4 px-6 py-4 transition-colors hover:bg-muted/30"
                  >
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted">
                      <component :is="field.icon" class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        {{ formatLabel(field.key) }}
                      </p>
                      <p class="mt-1 font-body text-base text-foreground break-words">
                        {{ normalizeValue(field.value) }}
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-if="Object.keys(lead.form_data).length === 0" class="detail-card border-border/50 shadow-sm">
              <CardContent class="flex flex-col items-center justify-center py-16 text-center">
                <div class="rounded-2xl bg-muted p-4">
                  <FileText class="h-8 w-8 text-muted-foreground/60" aria-hidden="true" />
                </div>
                <p class="mt-4 font-display text-lg font-medium text-foreground">No form data available</p>
                <p class="mt-1 font-body text-sm text-muted-foreground">This lead submission didn't include any form fields</p>
              </CardContent>
            </Card>
          </div>

          <!-- Right Column: Metadata -->
          <div class="space-y-6">
            <!-- Source Information -->
            <Card class="detail-card overflow-hidden border-border/50 shadow-sm" style="animation-delay: 0.05s;">
              <CardHeader class="border-b border-border/50 bg-muted/30 pb-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <Globe class="h-5 w-5 text-primary" aria-hidden="true" />
                  </div>
                  <div>
                    <CardTitle class="font-display text-xl">Source</CardTitle>
                    <CardDescription class="font-body text-sm">Origin of this lead</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent class="space-y-4 p-6">
                <div class="space-y-1">
                  <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">Website</p>
                  <p class="font-body text-base font-medium text-foreground">{{ lead.site.name }}</p>
                </div>
                <div class="space-y-1">
                  <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">Domain</p>
                  <a
                    :href="`https://${lead.site.domain}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 font-mono text-sm text-primary hover:underline"
                  >
                    {{ lead.site.domain }}
                    <ExternalLink class="h-3 w-3" aria-hidden="true" />
                  </a>
                </div>
                <div v-if="lead.form_name" class="space-y-1">
                  <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">Form</p>
                  <Badge variant="secondary" class="font-body">
                    {{ lead.form_name }}
                  </Badge>
                </div>
              </CardContent>
            </Card>

            <!-- Timeline -->
            <Card class="detail-card overflow-hidden border-border/50 shadow-sm" style="animation-delay: 0.1s;">
              <CardHeader class="border-b border-border/50 bg-muted/30 pb-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <Calendar class="h-5 w-5 text-primary" aria-hidden="true" />
                  </div>
                  <div>
                    <CardTitle class="font-display text-xl">Timeline</CardTitle>
                    <CardDescription class="font-body text-sm">Important dates</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent class="p-6">
                <div class="relative space-y-6">
                  <!-- Timeline line -->
                  <div class="absolute bottom-4 left-[11px] top-4 w-px bg-border" />

                  <div class="relative flex gap-4">
                    <div class="z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary">
                      <div class="h-2 w-2 rounded-full bg-white" />
                    </div>
                    <div class="space-y-1 pb-2">
                      <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">Submitted</p>
                      <p class="font-body text-sm font-medium text-foreground">{{ formatDate(lead.submitted_at) }}</p>
                      <p class="flex items-center gap-1 font-body text-xs text-muted-foreground">
                        <Clock class="h-3 w-3" aria-hidden="true" />
                        {{ formatTime(lead.submitted_at) }}
                      </p>
                    </div>
                  </div>

                  <div class="relative flex gap-4">
                    <div class="z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-muted">
                      <div class="h-2 w-2 rounded-full bg-muted-foreground/50" />
                    </div>
                    <div class="space-y-1">
                      <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">Last Updated</p>
                      <p class="font-body text-sm text-foreground">{{ getRelativeTime(lead.updated_at) }}</p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Technical Details -->
            <Card class="detail-card overflow-hidden border-border/50 shadow-sm" style="animation-delay: 0.15s;">
              <CardHeader class="border-b border-border/50 bg-muted/30 pb-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <Shield class="h-5 w-5 text-primary" aria-hidden="true" />
                  </div>
                  <div>
                    <CardTitle class="font-display text-xl">Technical</CardTitle>
                    <CardDescription class="font-body text-sm">Submission metadata</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent class="space-y-4 p-6">
                <div v-if="lead.ip_address" class="space-y-1">
                  <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">IP Address</p>
                  <p class="font-mono text-sm text-foreground">{{ lead.ip_address }}</p>
                </div>

                <div v-if="parsedUserAgent" class="space-y-3">
                  <div class="space-y-1">
                    <p class="font-body text-xs font-semibold uppercase tracking-wider text-muted-foreground">Browser</p>
                    <div class="flex items-center gap-2">
                      <Monitor class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                      <p class="font-body text-sm text-foreground">{{ parsedUserAgent.browser }} on {{ parsedUserAgent.os }}</p>
                    </div>
                  </div>
                </div>

                <div v-if="!lead.ip_address && !lead.user_agent" class="py-4 text-center">
                  <p class="font-body text-sm text-muted-foreground">No technical data available</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.lead-detail-page {
  font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
}

.font-display {
  font-family: 'Playfair Display', ui-serif, Georgia, serif;
  letter-spacing: -0.02em;
}

.font-body {
  font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
}

/* Card entrance animation */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.detail-card {
  animation: slideInUp 0.5s ease-out backwards;
}

/* Status indicator pulse */
.status-indicator {
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    box-shadow: 0 0 0 0 currentColor;
  }
  50% {
    opacity: 0.8;
    box-shadow: 0 0 0 4px transparent;
  }
}

/* Status card subtle glow */
.status-card {
  backdrop-filter: blur(8px);
}

/* Smooth transitions for interactive elements */
.detail-card {
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.detail-card:hover {
  box-shadow: 0 4px 20px -4px hsl(var(--foreground) / 0.08);
}
</style>
