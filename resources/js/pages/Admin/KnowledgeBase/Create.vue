<template>
    <div class="space-y-6 px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <Heading title="Create Article" />
            <Button @click="goBack" variant="outline">
                <ArrowLeftIcon class="mr-2 h-4 w-4" />
                Back to Articles
            </Button>
        </div>

        <!-- Form -->
        <div class="rounded-lg border shadow-sm">
            <form @submit.prevent="submitForm" class="space-y-6 p-6">
                <!-- Title -->
                <div>
                    <label for="title" class="mb-2 block text-sm font-medium"> Title * </label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Enter article title"
                        :class="{ 'border-red-300': errors.title }"
                        @input="generateSlug"
                    />
                    <div v-if="errors.title" class="mt-1 text-sm text-red-600">
                        {{ errors.title }}
                    </div>
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="mb-2 block text-sm font-medium"> Slug * </label>
                    <Input id="slug" v-model="form.slug" placeholder="article-slug" :class="{ 'border-red-300': errors.slug }" />
                    <div v-if="errors.slug" class="mt-1 text-sm text-red-600">
                        {{ errors.slug }}
                    </div>
                    <div class="mt-1 text-sm text-muted-foreground">URL: {{ baseUrl }}/knowledge-base/{{ form.slug || 'article-slug' }}</div>
                </div>

                <!-- Tags -->
                <div>
                    <label class="mb-2 block text-sm font-medium"> Tags </label>
                    <TagSelector v-model="form.tags" :available-tags="tags" :errors="errors.tags" />
                </div>

                <!-- Content Editor -->
                <div>
                    <label class="mb-2 block text-sm font-medium"> Content * </label>
                    <TiptapEditor v-model="form.body" :error="errors.body" />
                    <div v-if="errors.body" class="mt-1 text-sm text-red-600">
                        {{ errors.body }}
                    </div>
                </div>

                <!-- Publishing Options -->
                <div class="border-t pt-6">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.is_published"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm font-medium"> Publish immediately </span>
                        </label>
                    </div>
                    <div v-if="form.is_published" class="mt-4">
                        <label for="published_at" class="mb-2 block text-sm font-medium"> Publish Date </label>
                        <Input
                            id="published_at"
                            type="datetime-local"
                            v-model="form.published_at"
                            :class="{ 'border-red-300': errors.published_at }"
                        />
                        <div v-if="errors.published_at" class="mt-1 text-sm text-red-600">
                            {{ errors.published_at }}
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between border-t pt-6">
                    <Button type="button" @click="goBack" variant="outline"> Cancel </Button>
                    <div class="flex gap-2">
                        <Button type="button" @click="saveDraft" variant="outline" :disabled="processing"> Save as Draft </Button>
                        <Button type="submit" :disabled="processing">
                            {{ form.is_published ? 'Publish' : 'Save' }}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import TagSelector from '@/components/TagSelector.vue';
import TiptapEditor from '@/components/TiptapEditorWithImages.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeftIcon } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface Tag {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    tags: Tag[];
    errors?: Record<string, string>;
}

const props = defineProps<Props>();

const baseUrl = window.location.origin;
const processing = ref(false);

const form = reactive({
    title: '',
    slug: '',
    excerpt: '',
    body: [],
    tags: [] as Array<{ id?: number; name: string }>,
    is_published: false,
    published_at: '',
});

const errors = ref(props.errors || {});

const generateSlug = () => {
    if (form.title && !form.slug) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
};

const submitForm = () => {
    processing.value = true;

    router.post(route('admin.knowledge-base.store'), form, {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (pageErrors) => {
            errors.value = pageErrors;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const saveDraft = () => {
    const originalPublished = form.is_published;
    form.is_published = false;

    processing.value = true;

    router.post(route('admin.knowledge-base.store'), form, {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (pageErrors) => {
            errors.value = pageErrors;
            form.is_published = originalPublished;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const goBack = () => {
    router.visit(route('admin.knowledge-base.index'));
};
</script>
