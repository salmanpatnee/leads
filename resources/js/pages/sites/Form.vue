<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, Info, Loader2 } from 'lucide-vue-next';
import { computed, useTemplateRef } from 'vue';

import SiteController from '@/actions/App/Http/Controllers/SiteController';
import InputError from '@/components/InputError.vue';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type { Site } from '@/types';

interface Props {
    site?: Site;
}

const props = defineProps<Props>();

const isEditMode = computed(() => !!props.site);

const siteNameInput = useTemplateRef('siteNameInput');

const formData = useForm({
    site_name: props.site?.site_name || '',
    domain: props.site?.domain || '',
    is_active: props.site ? Boolean(props.site.is_active) : true,
});

function submitForm(): void {
    if (isEditMode.value && props.site) {
        formData.patch(SiteController.update.url(props.site.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Success message is handled via flash message from backend
            },
            onError: () => {
                siteNameInput.value?.$el?.focus();
            },
        });
    } else {
        formData.post(SiteController.store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                // Success message and redirect are handled by backend
            },
            onError: () => {
                siteNameInput.value?.$el?.focus();
            },
        });
    }
}

function handleDelete(): void {
    if (!isEditMode.value || !props.site) {
        return;
    }

    formData.delete(SiteController.destroy.url(props.site.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect is handled by backend
        },
    });
}
</script>

<template>
    <!-- Custom Fonts -->
    <component :is="'style'">
        @import
        url('https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap');
    </component>

    <div class="site-form-container mx-auto max-w-3xl space-y-8">
        <!-- Header -->
        <div class="space-y-3">
            <h2 class="font-display text-3xl font-bold tracking-tight text-foreground">
                {{ isEditMode ? 'Edit Site Details' : 'Add New Site' }}
            </h2>
            <p class="max-w-2xl font-body text-base text-muted-foreground">
                {{
                    isEditMode
                        ? 'Update the information for your WordPress site below.'
                        : 'Register a new WordPress site to start receiving leads. You\'ll get a unique API key for secure integration.'
                }}
            </p>
        </div>

        <!-- Info Alert -->
        <Alert
            v-if="!isEditMode"
            class="border-primary/30 bg-primary/5 dark:border-primary/30 dark:bg-primary/10"
        >
            <Info class="h-5 w-5 text-primary" />
            <AlertTitle class="font-body text-foreground">
                API Key Generation
            </AlertTitle>
            <AlertDescription class="font-body text-muted-foreground">
                A unique API key will be automatically generated when you create this
                site. You'll need this key to configure the Contact Form 7 integration
                on your WordPress site.
            </AlertDescription>
        </Alert>

        <!-- Main Form -->
        <form
            @submit.prevent="submitForm"
            class="form-card space-y-8 rounded-2xl border border-border bg-card p-6 shadow-sm md:p-8"
        >
            <!-- Site Name Field -->
            <div class="form-field space-y-3">
                <div class="space-y-1.5">
                    <Label
                        for="site_name"
                        class="font-body text-base font-semibold text-foreground"
                    >
                        Site Name
                        <span class="text-destructive" aria-label="required">*</span>
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        A friendly name to identify this WordPress site in your dashboard.
                    </p>
                </div>
                <Input
                    id="site_name"
                    ref="siteNameInput"
                    v-model="formData.site_name"
                    type="text"
                    name="site_name"
                    class="h-12 font-body text-base shadow-sm transition-all focus:shadow-md"
                    required
                    placeholder="My WordPress Site"
                    :aria-invalid="!!formData.errors.site_name"
                    :aria-describedby="
                        formData.errors.site_name ? 'site-name-error' : undefined
                    "
                />
                <InputError
                    id="site-name-error"
                    :message="formData.errors.site_name"
                    class="flex items-center gap-2 font-body"
                />
            </div>

            <!-- Domain Field -->
            <div class="form-field space-y-3">
                <div class="space-y-1.5">
                    <Label
                        for="domain"
                        class="font-body text-base font-semibold text-foreground"
                    >
                        Domain
                        <span class="text-destructive" aria-label="required">*</span>
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        The domain name where your WordPress site is hosted.
                    </p>
                </div>
                <Input
                    id="domain"
                    v-model="formData.domain"
                    type="text"
                    name="domain"
                    class="h-12 font-body font-mono text-base shadow-sm transition-all focus:shadow-md"
                    required
                    placeholder="example.com"
                    :aria-invalid="!!formData.errors.domain"
                    :aria-describedby="formData.errors.domain ? 'domain-error' : undefined"
                />
                <div class="flex items-start gap-2 rounded-lg bg-muted/50 p-3">
                    <Info class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                    <p class="text-sm text-muted-foreground">
                        Enter the domain without
                        <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">http://</code>
                        or
                        <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">www.</code>
                        (e.g., <span class="font-medium">example.com</span>)
                    </p>
                </div>
                <InputError
                    id="domain-error"
                    :message="formData.errors.domain"
                    class="flex items-center gap-2 font-body"
                />
            </div>

            <!-- Active Status Toggle -->
            <div class="form-field space-y-4">
                <div
                    class="flex flex-col gap-4 rounded-xl border border-border/50 bg-muted/20 p-5 transition-colors md:flex-row md:items-center md:justify-between"
                >
                    <div class="space-y-1.5">
                        <Label
                            for="is_active"
                            class="font-body text-base font-semibold text-foreground"
                        >
                            Active Status
                        </Label>
                        <p class="text-sm text-muted-foreground">
                            When active, this site can send leads to your system. Disable
                            to temporarily stop receiving submissions.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="font-body text-sm font-medium"
                            :class="
                                formData.is_active
                                    ? 'text-primary'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ formData.is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <Switch
                            id="is_active"
                            v-model:checked="formData.is_active"
                            name="is_active"
                            :aria-label="
                                formData.is_active
                                    ? 'Site is active'
                                    : 'Site is inactive'
                            "
                        />
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col gap-4 border-t border-border/50 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Button
                        type="submit"
                        size="lg"
                        :disabled="formData.processing"
                        class="submit-btn font-body font-semibold shadow-lg shadow-primary/20 transition-all hover:shadow-xl hover:shadow-primary/30"
                        data-test="save-site-button"
                    >
                        <Loader2
                            v-if="formData.processing"
                            class="mr-2 h-5 w-5 animate-spin"
                            aria-hidden="true"
                        />
                        {{ isEditMode ? 'Update Site' : 'Create Site' }}
                    </Button>

                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-show="formData.recentlySuccessful"
                            class="flex items-center gap-2 rounded-lg bg-primary/10 px-4 py-2 dark:bg-primary/20"
                            role="status"
                            aria-live="polite"
                        >
                            <CheckCircle2 class="h-4 w-4 text-primary" />
                            <span class="font-body text-sm font-medium text-primary">
                                Saved successfully
                            </span>
                        </div>
                    </Transition>
                </div>
            </div>
        </form>

        <!-- Delete Section (Edit Mode Only) -->
        <div v-if="isEditMode && site" class="danger-zone space-y-6">
            <div class="space-y-2">
                <h3 class="font-display text-2xl font-bold tracking-tight text-foreground">
                    Danger Zone
                </h3>
                <p class="font-body text-base text-muted-foreground">
                    Permanently delete this site and remove it from your system.
                </p>
            </div>

            <Alert class="border-destructive/50 bg-destructive/5">
                <AlertCircle class="h-5 w-5 text-destructive" />
                <AlertTitle class="font-body font-semibold text-destructive">
                    Warning: This action cannot be undone
                </AlertTitle>
                <AlertDescription class="space-y-4">
                    <p class="font-body text-sm text-destructive/90">
                        Deleting this site will permanently remove it from your system. All
                        leads associated with this site will remain in your database but
                        will no longer be linked to an active site connection.
                    </p>
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button
                                variant="destructive"
                                size="lg"
                                class="font-body font-semibold shadow-lg shadow-destructive/20"
                                data-test="delete-site-button"
                            >
                                <AlertCircle class="mr-2 h-5 w-5" aria-hidden="true" />
                                Delete Site Permanently
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="font-body">
                            <DialogHeader class="space-y-4">
                                <DialogTitle class="font-display text-2xl">
                                    Delete {{ site.site_name }}?
                                </DialogTitle>
                                <DialogDescription class="space-y-3 text-base leading-relaxed">
                                    <p>
                                        You're about to permanently delete
                                        <strong class="font-semibold text-foreground">
                                            {{ site.site_name }}
                                        </strong>
                                        <span class="text-muted-foreground">
                                            ({{ site.domain }})
                                        </span>.
                                    </p>
                                    <p>
                                        This will immediately stop all lead submissions from
                                        this WordPress site. This action is permanent and
                                        cannot be undone.
                                    </p>
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="gap-2 sm:gap-3">
                                <DialogClose as-child>
                                    <Button
                                        variant="outline"
                                        size="lg"
                                        class="font-medium"
                                    >
                                        Cancel
                                    </Button>
                                </DialogClose>

                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="lg"
                                    :disabled="formData.processing"
                                    @click="handleDelete"
                                    class="font-semibold shadow-lg shadow-destructive/20"
                                    data-test="confirm-delete-site-button"
                                >
                                    <Loader2
                                        v-if="formData.processing"
                                        class="mr-2 h-5 w-5 animate-spin"
                                        aria-hidden="true"
                                    />
                                    Yes, Delete Permanently
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </AlertDescription>
            </Alert>
        </div>
    </div>
</template>

<style scoped>
.site-form-container {
    font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}

.font-display {
    font-family: 'Crimson Pro', ui-serif, Georgia, serif;
    letter-spacing: -0.02em;
}

.font-body {
    font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}

/* Form card entrance animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-card {
    animation: fadeInUp 0.5s ease-out;
}

/* Field focus animation */
.form-field {
    position: relative;
}

.form-field input:focus {
    transform: scale(1.005);
}

/* Submit button shimmer effect */
.submit-btn {
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        transparent 0%,
        rgba(255, 255, 255, 0.15) 50%,
        transparent 100%
    );
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.submit-btn:hover::before {
    transform: translateX(100%);
}

/* Danger zone styling */
.danger-zone {
    animation: fadeInUp 0.6s ease-out 0.2s backwards;
}
</style>
