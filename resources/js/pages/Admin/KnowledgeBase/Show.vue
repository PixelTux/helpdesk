<template>
  <div class="space-y-6 px-4 py-6">
    <!-- Header -->
    <div class="rounded-lg shadow-sm border">
      <div class="flex justify-between items-start p-6">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-4">
            <Heading :title="article.title">{{ article.title }}</Heading>
            <span
              :class="{
                'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300': article.is_published,
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300': !article.is_published,
                'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300': article.deleted_at
              }"
              class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
            >
              {{ article.deleted_at ? 'Trashed' : (article.is_published ? 'Published' : 'Draft') }}
            </span>
          </div>

          <div class="flex items-center gap-6 text-sm text-muted-foreground">
            <div class="flex items-center gap-2">
              <span class="font-medium">Slug:</span>
              <span class="font-mono bg-accent px-2 py-1 rounded text-xs">{{ article.slug }}</span>
            </div>
            <div v-if="article.published_at">
              <span class="font-medium">Published:</span> {{ formatDate(article.published_at) }}
            </div>
            <div>
              <span class="font-medium">Views:</span> {{ article.view_count || 0 }}
            </div>
          </div>

          <!-- Tags -->
          <div v-if="article.tags.length > 0" class="flex flex-wrap gap-2 mt-4">
            <span
              v-for="tag in article.tags"
              :key="tag.id"
              class="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"
            >
              {{ tag.name }}
            </span>
          </div>
        </div>

        <div class="flex gap-2 ml-4">
          <Button @click="editArticle" variant="default" size="sm">
            <PencilIcon class="w-4 h-4 mr-2" />
            Edit
          </Button>
          <Button @click="goBack" variant="outline" size="sm">
            <ArrowLeftIcon class="w-4 h-4 mr-2" />
            Back
          </Button>
        </div>
      </div>
    </div>

    <!-- Article Meta -->
    <div class="rounded-lg shadow-sm border p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <h3 class="text-sm font-bold mb-2">Slug</h3>
          <p class="font-mono bg-accent px-2 py-1 rounded text-sm text-muted-foreground">
            {{ article.slug }}
          </p>
        </div>
        <div>
          <h3 class="text-sm font-bold mb-3">Published At</h3>
          <p v-if="article.published_at" class="text-sm text-muted-foreground">
            {{ formatDate(article.published_at) }}
          </p>
          <p v-else class="text-sm text-muted-foreground">
            Not yet published
          </p>
        </div>
        <div>
          <h3 class="text-sm font-bold mb-3">View Count</h3>
          <p class="text-sm text-muted-foreground">
            {{ article.view_count || 0 }} views
          </p>
        </div>
      </div>
    </div>

    <!-- Tags -->
    <div v-if="article.tags.length > 0" class="rounded-lg shadow-sm border p-6">
      <h3 class="text-sm font-bold mb-2">Tags</h3>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="tag in article.tags"
          :key="tag.id"
          class="inline-flex px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full"
        >
          {{ tag.name }}
        </span>
      </div>
    </div>

    <!-- Excerpt -->
    <div v-if="article.excerpt" class="rounded-lg shadow-sm border p-6">
      <h3 class="text-sm font-bold mb-3">Excerpt</h3>
      <p class="text-muted-foreground">{{ article.excerpt }}</p>
    </div>

    <!-- Content -->
    <div class="rounded-lg shadow-sm border p-6">
      <h3 class="text-sm font-bold mb-3">Content</h3>
      <div class="prose max-w-none text-muted-foreground">
        <TiptapRenderer :content="article.body" />
      </div>
    </div>

    <!-- Author Info -->
    <div class="rounded-lg shadow-sm border p-6">
      <h3 class="text-sm font-bold mb-3">Article Info</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h4 class="text-sm font-medium">Created</h4>
          <p class="text-sm text-muted-foreground">
            {{ formatDate(article.created_at) }}
            <span v-if="article.created_by" class="italic text-accent-foreground">
              by {{ article.created_by.name }}
            </span>
          </p>
        </div>
        <div>
          <h4 class="text-sm font-medium">Last Updated</h4>
          <p class="text-sm text-muted-foreground">
            {{ formatDate(article.updated_at) }}
            <span v-if="article.updated_by" class="italic text-accent-foreground">
              by {{ article.updated_by.name }}
            </span>
          </p>
        </div>
      </div>
    </div>

    <!-- Public URL -->
    <div v-if="article.is_published" class="bg-primary-foreground  rounded-lg border border-accent-foreground/10 p-4">
      <div class="flex items-center justify-between">
        <div class="text-accent-foreground">
          <h3 class="text-sm font-bold">Public URL</h3>
          <p class="text-sm mt-1">
            This article is publicly accessible at:
          </p>
          <a
            :href="publicUrl"
            target="_blank"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-violet-400 dark:hover:text-violet-500 underline font-mono px-2 py-1 rounded mt-2 inline-block"
          >
            {{ publicUrl }}
          </a>
        </div>
        <Button
          @click="openPublicUrl"
          variant="outline"
          size="sm"
        >
          <ExternalLinkIcon class="w-4 h-4 mr-2" />
          View Public
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import Heading from '@/components/Heading.vue'
import TiptapRenderer from '@/components/TiptapRenderer.vue'
import {
  ArrowLeftIcon,
  PencilIcon,
  ExternalLinkIcon
} from 'lucide-vue-next'

defineOptions({
  layout: AppLayout,
})

interface Tag {
  id: number
  name: string
  slug: string
}

interface User {
  id: number
  name: string
}

interface Article {
  id: number
  title: string
  slug: string
  excerpt: string | null
  body: any
  is_published: boolean
  published_at: string | null
  created_at: string
  updated_at: string
  view_count?: number
  deleted_at: string | null
  created_by: User | null
  updated_by: User | null
  tags: Tag[]
}

interface Props {
  article: Article
}

const props = defineProps<Props>()

const publicUrl = computed(() => {
  return `${window.location.origin}/knowledge-base/${props.article.slug}`
})

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const editArticle = () => {
  router.visit(route('admin.knowledge-base.edit', props.article.id))
}

const goBack = () => {
  router.visit(route('admin.knowledge-base.index'))
}

const openPublicUrl = () => {
  window.open(publicUrl.value, '_blank')
}
</script>
