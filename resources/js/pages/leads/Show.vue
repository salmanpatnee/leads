<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
  ArrowLeft,
  Mail,
  MapPin,
  Phone,
  Globe,
  Clock,
  Calendar,
  Copy,
  Check,
  ExternalLink,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

import AppLayout from '@/layouts/AppLayout.vue'

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
  ip_address: string | null
  user_agent: string | null
  submitted_at: string
  created_at: string
  updated_at: string
}

interface RelatedLead {
  id: number
  site_name: string
  form_name: string
  status: string
  submitted_at: string
}

interface Props {
  lead: Lead
  email: string | null
  relatedLeads: RelatedLead[]
}

const props = defineProps<Props>()

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
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`
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

// Extract primary contact info for header display
const primaryInfo = computed(() => {
  const info: Record<string, string> = {}
  const priorityKeys = ['name', 'full_name', 'contact_name', 'your_name', 'first_name']
  const emailKeys = ['email', 'e-mail', 'email_address', 'contact_email', 'sender_email']
  const phoneKeys = ['phone', 'telephone', 'mobile', 'cell_phone', 'contact_phone']

  for (const key of priorityKeys) {
    if (props.lead.form_data[key]) {
      info.name = String(props.lead.form_data[key])
      break
    }
  }

  for (const key of emailKeys) {
    if (props.lead.form_data[key]) {
      info.email = String(props.lead.form_data[key])
      break
    }
  }

  for (const key of phoneKeys) {
    if (props.lead.form_data[key]) {
      info.phone = String(props.lead.form_data[key])
      break
    }
  }

  return info
})

// Categorize form fields
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
    let icon = Mail
    if (lowerKey.includes('email')) icon = Mail
    else if (lowerKey.includes('phone') || lowerKey.includes('tel') || lowerKey.includes('mobile')) icon = Phone
    else if (lowerKey.includes('address') || lowerKey.includes('city') || lowerKey.includes('country')) icon = MapPin

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

// Parse user agent
const parsedUserAgent = computed(() => {
  if (!props.lead.user_agent) return null

  const ua = props.lead.user_agent
  let browser = 'Unknown'
  let os = 'Unknown'

  if (ua.includes('Chrome') && !ua.includes('Edg')) browser = 'Chrome'
  else if (ua.includes('Firefox')) browser = 'Firefox'
  else if (ua.includes('Safari') && !ua.includes('Chrome')) browser = 'Safari'
  else if (ua.includes('Edg')) browser = 'Edge'

  if (ua.includes('Windows')) os = 'Windows'
  else if (ua.includes('Mac OS')) os = 'macOS'
  else if (ua.includes('Linux')) os = 'Linux'
  else if (ua.includes('Android')) os = 'Android'
  else if (ua.includes('iOS') || ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS'

  return { browser, os }
})

// Get status badge color
const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    new: 'bg-blue-50 text-blue-700 border-blue-200',
    contacted: 'bg-amber-50 text-amber-700 border-amber-200',
    converted: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  }
  return colors[status] || colors.new
}
</script>

<template>
  <Head :title="`Lead #${lead.id}`" />

  <!-- Montserrat Font Import -->
  <component :is="'style'">
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');
  </component>

  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
      <!-- Navigation -->
      <div class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 backdrop-blur-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 py-4">
          <Link
            href="/leads"
            class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors group"
          >
            <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
            <span class="text-sm font-medium">Back to Leads</span>
          </Link>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Header: Lead Submitter Card -->
        <div class="mb-8 animate-in fade-in slide-in-from-top-4 duration-500">
          <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <!-- Header Background Accent -->
            <div class="h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500" />

            <div class="p-8">
              <!-- Lead ID and Timestamp -->
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h1 class="text-4xl font-bold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                    Lead #{{ lead.id }}
                  </h1>
                  <p class="text-sm text-slate-500 dark:text-slate-400 mt-2" style="font-family: 'Montserrat', sans-serif;">
                    Submitted {{ getRelativeTime(lead.submitted_at) }}
                  </p>
                </div>
              </div>

              <!-- Primary Contact Info Grid -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Name -->
                <div v-if="primaryInfo.name" class="space-y-2">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                    Contact Name
                  </p>
                  <p class="text-lg font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                    {{ primaryInfo.name }}
                  </p>
                </div>

                <!-- Email -->
                <div v-if="props.email" class="space-y-2">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                    Email
                  </p>
                  <div class="flex items-center gap-2 group">
                    <a
                      :href="`mailto:${props.email}`"
                      class="text-base font-medium text-blue-600 dark:text-blue-400 hover:underline break-all"
                      style="font-family: 'Montserrat', sans-serif;"
                    >
                      {{ props.email }}
                    </a>
                    <button
                      @click="copyToClipboard(props.email, 'email')"
                      class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded"
                      :aria-label="`Copy email`"
                    >
                      <Check v-if="copiedField === 'email'" class="w-4 h-4 text-emerald-500" />
                      <Copy v-else class="w-4 h-4 text-slate-400" />
                    </button>
                  </div>
                </div>

                <!-- Phone -->
                <div v-if="primaryInfo.phone" class="space-y-2">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                    Phone
                  </p>
                  <a
                    :href="`tel:${primaryInfo.phone}`"
                    class="text-base font-medium text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                    style="font-family: 'Montserrat', sans-serif;"
                  >
                    {{ primaryInfo.phone }}
                  </a>
                </div>
              </div>

              <!-- Source Information Bar -->
              <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                  <!-- Site -->
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                      Source Site
                    </p>
                    <p class="text-sm font-medium text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                      {{ lead.site.site_name }}
                    </p>
                  </div>

                  <!-- Domain -->
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                      Domain
                    </p>
                    <a
                      :href="`https://${lead.site.domain}`"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
                      style="font-family: 'Montserrat', sans-serif;"
                    >
                      {{ lead.site.domain }}
                      <ExternalLink class="w-3 h-3" />
                    </a>
                  </div>

                  <!-- Form -->
                  <div v-if="lead.form_name" class="space-y-1">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                      Form
                    </p>
                    <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium rounded-lg" style="font-family: 'Montserrat', sans-serif;">
                      {{ lead.form_name }}
                    </span>
                  </div>

                  <!-- Status -->
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                      Status
                    </p>
                    <span :class="`inline-block px-3 py-1 text-sm font-medium rounded-lg border ${getStatusColor(lead.status)}`" style="font-family: 'Montserrat', sans-serif;">
                      {{ lead.status.charAt(0).toUpperCase() + lead.status.slice(1) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Grid: Form Data & Related Leads -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          <!-- Left Column: Form Data -->
          <div class="lg:col-span-2 space-y-6 animate-in fade-in slide-in-from-left-4 duration-500 delay-100">
            <!-- Contact Information Section -->
            <div
              v-if="categorizedFormData.contactFields.length > 0"
              class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
            >
              <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                  Contact Information
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1" style="font-family: 'Montserrat', sans-serif;">
                  Details from the form submission
                </p>
              </div>

              <div class="divide-y divide-slate-200 dark:divide-slate-800">
                <div
                  v-for="field in categorizedFormData.contactFields"
                  :key="field.key"
                  class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group"
                >
                  <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                      <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1" style="font-family: 'Montserrat', sans-serif;">
                        {{ formatLabel(field.key) }}
                      </p>
                      <p class="text-base font-medium text-slate-900 dark:text-white break-all" style="font-family: 'Montserrat', sans-serif;">
                        {{ normalizeValue(field.value) }}
                      </p>
                    </div>
                    <button
                      @click="copyToClipboard(normalizeValue(field.value), field.key)"
                      class="opacity-0 group-hover:opacity-100 transition-opacity p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg shrink-0"
                      :aria-label="`Copy ${formatLabel(field.key)}`"
                    >
                      <Check v-if="copiedField === field.key" class="w-4 h-4 text-emerald-500" />
                      <Copy v-else class="w-4 h-4 text-slate-400" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Message/Inquiry Section -->
            <div
              v-if="categorizedFormData.messageFields.length > 0"
              class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
            >
              <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                  Message
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1" style="font-family: 'Montserrat', sans-serif;">
                  Inquiry details and comments
                </p>
              </div>

              <div class="p-6 space-y-6">
                <div
                  v-for="field in categorizedFormData.messageFields"
                  :key="field.key"
                  class="space-y-2"
                >
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                    {{ formatLabel(field.key) }}
                  </p>
                  <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-base text-slate-900 dark:text-white whitespace-pre-wrap leading-relaxed" style="font-family: 'Montserrat', sans-serif;">
                      {{ normalizeValue(field.value) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Fields Section -->
            <div
              v-if="categorizedFormData.otherFields.length > 0"
              class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
            >
              <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                  Additional Information
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1" style="font-family: 'Montserrat', sans-serif;">
                  Other form fields
                </p>
              </div>

              <div class="divide-y divide-slate-200 dark:divide-slate-800">
                <div
                  v-for="field in categorizedFormData.otherFields"
                  :key="field.key"
                  class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                >
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1" style="font-family: 'Montserrat', sans-serif;">
                    {{ formatLabel(field.key) }}
                  </p>
                  <p class="text-base text-slate-900 dark:text-white break-all" style="font-family: 'Montserrat', sans-serif;">
                    {{ normalizeValue(field.value) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Sidebar -->
          <div class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500 delay-150">
            <!-- Submission Timeline -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
              <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                  Timeline
                </h3>
              </div>

              <div class="p-6 space-y-6">
                <div class="flex gap-4">
                  <div class="flex flex-col items-center">
                    <div class="w-3 h-3 rounded-full bg-blue-500 ring-4 ring-blue-100 dark:ring-blue-950/50" />
                    <div class="w-px h-8 bg-slate-200 dark:bg-slate-700" />
                  </div>
                  <div class="pb-4">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                      Submitted
                    </p>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white mt-1" style="font-family: 'Montserrat', sans-serif;">
                      {{ formatDate(lead.submitted_at) }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" style="font-family: 'Montserrat', sans-serif;">
                      {{ formatTime(lead.submitted_at) }}
                    </p>
                  </div>
                </div>

                <div class="flex gap-4">
                  <div class="flex flex-col items-center">
                    <div class="w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-600" />
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                      Last Updated
                    </p>
                    <p class="text-sm text-slate-900 dark:text-white mt-1" style="font-family: 'Montserrat', sans-serif;">
                      {{ getRelativeTime(lead.updated_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Technical Details -->
            <div v-if="lead.ip_address || parsedUserAgent" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
              <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                  Technical Details
                </h3>
              </div>

              <div class="p-6 space-y-4">
                <div v-if="lead.ip_address" class="space-y-1">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                    IP Address
                  </p>
                  <p class="text-sm font-mono text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                    {{ lead.ip_address }}
                  </p>
                </div>

                <div v-if="parsedUserAgent" class="space-y-1">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                    Browser
                  </p>
                  <p class="text-sm text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                    {{ parsedUserAgent.browser }} on {{ parsedUserAgent.os }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Leads History Section -->
        <div v-if="props.email" class="animate-in fade-in slide-in-from-bottom-4 duration-500 delay-200">
          <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
              <h2 class="text-lg font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                Contact History
              </h2>
              <p class="text-sm text-slate-500 dark:text-slate-400 mt-1" style="font-family: 'Montserrat', sans-serif;">
                Previous leads from <span class="font-medium">{{ props.email }}</span>
              </p>
            </div>

            <!-- Leads List or Empty State -->
            <div v-if="relatedLeads.length > 0" class="divide-y divide-slate-200 dark:divide-slate-800">
              <Link
                v-for="(relatedLead, index) in relatedLeads"
                :key="relatedLead.id"
                :href="`/leads/${relatedLead.id}`"
                class="px-6 py-4 hover:bg-blue-50 dark:hover:bg-slate-800/50 transition-colors group flex items-center justify-between cursor-pointer"
                :style="{ animationDelay: `${index * 50}ms` }"
              >
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3">
                    <div class="flex-1">
                      <p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" style="font-family: 'Montserrat', sans-serif;">
                        {{ relatedLead.site_name }}
                      </p>
                      <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 dark:text-slate-400">
                        <span v-if="relatedLead.form_name" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded" style="font-family: 'Montserrat', sans-serif;">
                          {{ relatedLead.form_name }}
                        </span>
                        <span style="font-family: 'Montserrat', sans-serif;">
                          {{ getRelativeTime(relatedLead.submitted_at) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="ml-4 flex items-center gap-3">
                  <span :class="`px-3 py-1 text-xs font-medium rounded-lg border ${getStatusColor(relatedLead.status)}`" style="font-family: 'Montserrat', sans-serif;">
                    {{ relatedLead.status.charAt(0).toUpperCase() + relatedLead.status.slice(1) }}
                  </span>
                  <ArrowLeft class="w-4 h-4 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors transform rotate-180" />
                </div>
              </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="px-6 py-12 text-center">
              <div class="flex justify-center mb-4">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                  <Mail class="w-6 h-6 text-slate-400" />
                </div>
              </div>
              <p class="text-base font-semibold text-slate-900 dark:text-white" style="font-family: 'Montserrat', sans-serif;">
                No Previous Submissions
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400 mt-2" style="font-family: 'Montserrat', sans-serif;">
                This is the first lead from this email address
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Animation utilities */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideInFromTop {
  from {
    opacity: 0;
    transform: translateY(-16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInFromLeft {
  from {
    opacity: 0;
    transform: translateX(-16px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInFromRight {
  from {
    opacity: 0;
    transform: translateX(16px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation-fill-mode: both;
  animation-duration: 500ms;
}

.fade-in {
  animation-name: fadeIn;
}

.slide-in-from-top-4 {
  animation-name: slideInFromTop;
}

.slide-in-from-left-4 {
  animation-name: slideInFromLeft;
}

.slide-in-from-right-4 {
  animation-name: slideInFromRight;
}

.slide-in-from-bottom-4 {
  animation-name: slideInFromBottom;
}

.duration-500 {
  animation-duration: 500ms;
}

.delay-100 {
  animation-delay: 100ms;
}

.delay-150 {
  animation-delay: 150ms;
}

.delay-200 {
  animation-delay: 200ms;
}
</style>
