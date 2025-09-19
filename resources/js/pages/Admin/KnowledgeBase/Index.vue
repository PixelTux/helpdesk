<template>
  <div class="space-y-6 px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <Heading title="Knowledge Base Articles" />
      <div class="flex gap-2">
        <Button @click="createArticle" variant="default">
          <PlusIcon class="w-4 h-4 mr-2" />
          Create Article
        </Button>
        <Button @click="manageTags" variant="outline">
          <TagIcon class="w-4 h-4 mr-2" />
          Manage Tags
        </Button>
      </div>
    </div>

    <!-- Filters -->
    <div class="p-4 rounded-lg shadow-sm border">
      <div class="flex flex-wrap gap-4">
        <!-- Search -->
        <div class="flex-1 min-w-64">
          <Input
            v-model="filters.search"
            placeholder="Search articles..."
            @input="handleSearch"
          />
        </div>

        <!-- Status Filter -->
        <div class="min-w-32">
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-accent bg-sidebar"
          >
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="trashed">Trashed</option>
            <option value="all">All</option>
          </select>
        </div>

        <!-- Tag Filter -->
        <div class="min-w-32" v-if="tags.length > 0">
          <select
            v-model="filters.tag"
            @change="applyFilters"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-accent bg-sidebar"
          >
            <option value="">All Tags</option>
            <option
                v-for="tag in tags"
                :key="tag.id"
                :value="tag.slug"
                class="dark:bg-sidebar dark:text-primary"
            >
              {{ tag.name }} ({{ tag.knowledge_base_articles_count }})
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Articles Table -->
    <div class="shadow-sm border rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y">
          <thead>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider cursor-pointer"
                  @click="sort('title')">
                Title
                <ChevronUpIcon v-if="filters.sort_by === 'title' && filters.sort_dir === 'asc'" class="inline w-4 h-4" />
                <ChevronDownIcon v-if="filters.sort_by === 'title' && filters.sort_dir === 'desc'" class="inline w-4 h-4" />
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Tags
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Author
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider cursor-pointer"
                  @click="sort('updated_at')">
                Updated
                <ChevronUpIcon v-if="filters.sort_by === 'updated_at' && filters.sort_dir === 'asc'" class="inline w-4 h-4" />
                <ChevronDownIcon v-if="filters.sort_by === 'updated_at' && filters.sort_dir === 'desc'" class="inline w-4 h-4" />
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y cursor-default">
            <tr v-for="article in articles.data" :key="article.id" class="hover:bg-muted-foreground/25">
              <td class="px-6 py-4">
                <div class="text-sm font-medium">
                  {{ article.title }}
                </div>
                <div class="text-sm text-muted-foreground truncate max-w-xs">
                  {{ article.slug }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300': article.is_published,
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300': !article.is_published,
                    'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300': article.deleted_at
                  }"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ article.deleted_at ? 'Trashed' : (article.is_published ? 'Published' : 'Draft') }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="tag in article.tags"
                    :key="tag.id"
                    class="inline-flex px-2 py-1 text-xs bg-blue-100 text-red-800 rounded-full"
                  >
                    {{ tag.name }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                {{ article.created_by?.name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                {{ formatDate(article.updated_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <Button
                    v-if="!article.deleted_at && article.is_published"
                    variant="outline"
                    size="sm"
                    @click="viewArticle(article.id)"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </Button>
                  <Button
                    v-if="!article.deleted_at"
                    variant="outline"
                    size="sm"
                    @click="editArticle(article.id)"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </Button>
                  <Button
                    v-if="!article.deleted_at"
                    variant="outline"
                    size="sm"
                    @click="deleteArticle(article)"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </Button>
                  <Button
                    v-if="article.deleted_at"
                    variant="outline"
                    size="sm"
                    @click="restoreArticle(article.id)"
                  >
                    <RefreshCwIcon class="w-4 h-4" />
                  </Button>
                  <Button
                    v-if="article.deleted_at"
                    class="text-white primary bg-red-800 hover:bg-red-600 border border-accent hover:border-muted-foreground/25"
                    size="sm"
                    @click="forceDeleteArticle(article.id)"
                  >
                    <XIcon class="w-4 h-4" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="articles.links" class="flex justify-between items-center">
      <div class="text-sm text-muted-foreground">
        Showing {{ articles.from }} to {{ articles.to }} of {{ articles.total }} results
      </div>
      <div class="flex gap-2">
        <Button
          v-for="link in articles.links"
          :key="link.label"
          :variant="link.active ? 'default' : 'outline'"
          size="sm"
          :disabled="!link.url"
          @click="link.url ? changePage(link.url) : undefined"
          v-html="link.label"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="articles.data.length === 0" class="text-center py-12">
      <p class="text-muted-foreground mb-4">No articles found</p>
      <Button @click="createArticle">Create your first article</Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Heading from '@/components/Heading.vue'
import {
  PlusIcon,
  TagIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  RefreshCwIcon,
  XIcon,
  ChevronUpIcon,
  ChevronDownIcon
} from 'lucide-vue-next'

defineOptions({
  layout: AppLayout,
})

interface Tag {
  id: number
  name: string
  slug: string
  knowledge_base_articles_count: number
}

interface User {
  id: number
  name: string
}

interface Article {
  id: number
  title: string
  slug: string
  is_published: boolean
  deleted_at: string | null
  tags: Tag[]
  created_by: User | null
  updated_at: string
}

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface ArticleCollection {
  data: Article[]
  links: PaginationLink[]
  from: number
  to: number
  total: number
}

interface Props {
  articles: ArticleCollection
  tags: Tag[]
  currentFilters: {
    search: string | null
    tag: string | null
    sort_by: string
    sort_dir: string
    status: string
    per_page: number
  }
}

const props = defineProps<Props>()

const filters = reactive({
  search: props.currentFilters.search || '',
  tag: props.currentFilters.tag || '',
  sort_by: props.currentFilters.sort_by || 'updated_at',
  sort_dir: props.currentFilters.sort_dir || 'desc',
  status: props.currentFilters.status || 'published',
  per_page: props.currentFilters.per_page || 15
})

let searchTimeout: NodeJS.Timeout | null = null

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 500)
}

const applyFilters = () => {
  router.get(route('admin.knowledge-base.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const sort = (column: string) => {
  if (filters.sort_by === column) {
    filters.sort_dir = filters.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    filters.sort_by = column
    filters.sort_dir = 'asc'
  }
  applyFilters()
}

const changePage = (url: string | null) => {
  if (url) {
    router.get(url, {}, {
      preserveState: true,
      preserveScroll: true
    })
  }
}

const createArticle = () => {
  router.visit(route('admin.knowledge-base.create'))
}

const manageTags = () => {
  router.visit(route('admin.tags.index'))
}

const viewArticle = (id: number) => {
  router.visit(route('admin.knowledge-base.show', id))
}

const editArticle = (id: number) => {
  router.visit(route('admin.knowledge-base.edit', id))
}

const deleteArticle = (article: Article) => {
  if (confirm(`Are you sure you want to delete "${article.title}"?`)) {
    router.delete(route('admin.knowledge-base.destroy', article.id), {
      onSuccess: () => {
        // Article moved to trash
      }
    })
  }
}

const restoreArticle = (id: number) => {
  router.post(route('admin.knowledge-base.restore', id))
}

const forceDeleteArticle = (id: number) => {
  if (confirm('Are you sure you want to permanently delete this article? This action cannot be undone.')) {
    router.delete(route('admin.knowledge-base.force-delete', id))
  }
}
</script>
