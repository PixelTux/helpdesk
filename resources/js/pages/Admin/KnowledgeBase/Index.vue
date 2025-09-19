<template>
    <div class="space-y-6 px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <Heading title="Knowledge Base Articles" />
            <div class="flex gap-2">
                <Button @click="createArticle" variant="default">
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Create Article
                </Button>
                <Button @click="manageTags" variant="outline">
                    <TagIcon class="mr-2 h-4 w-4" />
                    Manage Tags
                </Button>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-lg border p-4 shadow-sm">
            <div class="flex flex-wrap gap-4">
                <!-- Search -->
                <div class="min-w-64 flex-1">
                    <Input v-model="filters.search" placeholder="Search articles..." @input="handleSearch" />
                </div>

                <!-- Status Filter -->
                <div class="min-w-32">
                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="w-full rounded-md border bg-sidebar px-3 py-2 focus:ring-2 focus:ring-accent focus:outline-none"
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
                        class="w-full rounded-md border bg-sidebar px-3 py-2 focus:ring-2 focus:ring-accent focus:outline-none"
                    >
                        <option value="">All Tags</option>
                        <option v-for="tag in tags" :key="tag.id" :value="tag.slug" class="dark:bg-sidebar dark:text-primary">
                            {{ tag.name }} ({{ tag.knowledge_base_articles_count }})
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Articles Table -->
        <div class="overflow-hidden rounded-lg border shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y">
                    <thead>
                        <tr>
                            <th
                                class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                @click="sort('title')"
                            >
                                Title
                                <ChevronUpIcon v-if="filters.sort_by === 'title' && filters.sort_dir === 'asc'" class="inline h-4 w-4" />
                                <ChevronDownIcon v-if="filters.sort_by === 'title' && filters.sort_dir === 'desc'" class="inline h-4 w-4" />
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Tags</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Author</th>
                            <th
                                class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                @click="sort('updated_at')"
                            >
                                Updated
                                <ChevronUpIcon v-if="filters.sort_by === 'updated_at' && filters.sort_dir === 'asc'" class="inline h-4 w-4" />
                                <ChevronDownIcon v-if="filters.sort_by === 'updated_at' && filters.sort_dir === 'desc'" class="inline h-4 w-4" />
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-muted-foreground uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="cursor-default divide-y">
                        <tr v-for="article in articles.data" :key="article.id" class="hover:bg-muted-foreground/25">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium">
                                    {{ article.title }}
                                </div>
                                <div class="max-w-xs truncate text-sm text-muted-foreground">
                                    {{ article.slug }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300': article.is_published,
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300': !article.is_published,
                                        'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300': article.deleted_at,
                                    }"
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ article.deleted_at ? 'Trashed' : article.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in article.tags"
                                        :key="tag.id"
                                        class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs text-red-800"
                                    >
                                        {{ tag.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                {{ article.created_by?.name || 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">
                                {{ formatDate(article.updated_at) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        v-if="!article.deleted_at && article.is_published"
                                        variant="outline"
                                        size="sm"
                                        @click="viewArticle(article.id)"
                                    >
                                        <EyeIcon class="h-4 w-4" />
                                    </Button>
                                    <Button v-if="!article.deleted_at" variant="outline" size="sm" @click="editArticle(article.id)">
                                        <PencilIcon class="h-4 w-4" />
                                    </Button>
                                    <Button v-if="!article.deleted_at" variant="outline" size="sm" @click="deleteArticle(article)">
                                        <TrashIcon class="h-4 w-4" />
                                    </Button>
                                    <Button v-if="article.deleted_at" variant="outline" size="sm" @click="restoreArticle(article.id)">
                                        <RefreshCwIcon class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-if="article.deleted_at"
                                        class="primary border border-accent bg-red-800 text-white hover:border-muted-foreground/25 hover:bg-red-600"
                                        size="sm"
                                        @click="forceDeleteArticle(article.id)"
                                    >
                                        <XIcon class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="articles.links" class="flex items-center justify-between">
            <div class="text-sm text-muted-foreground">Showing {{ articles.from }} to {{ articles.to }} of {{ articles.total }} results</div>
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
        <div v-if="articles.data.length === 0" class="py-12 text-center">
            <p class="mb-4 text-muted-foreground">No articles found</p>
            <Button @click="createArticle">Create your first article</Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ChevronDownIcon, ChevronUpIcon, EyeIcon, PencilIcon, PlusIcon, RefreshCwIcon, TagIcon, TrashIcon, XIcon } from 'lucide-vue-next';
import { reactive } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface Tag {
    id: number;
    name: string;
    slug: string;
    knowledge_base_articles_count: number;
}

interface User {
    id: number;
    name: string;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    is_published: boolean;
    deleted_at: string | null;
    tags: Tag[];
    created_by: User | null;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ArticleCollection {
    data: Article[];
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
}

interface Props {
    articles: ArticleCollection;
    tags: Tag[];
    currentFilters: {
        search: string | null;
        tag: string | null;
        sort_by: string;
        sort_dir: string;
        status: string;
        per_page: number;
    };
}

const props = defineProps<Props>();

const filters = reactive({
    search: props.currentFilters.search || '',
    tag: props.currentFilters.tag || '',
    sort_by: props.currentFilters.sort_by || 'updated_at',
    sort_dir: props.currentFilters.sort_dir || 'desc',
    status: props.currentFilters.status || 'published',
    per_page: props.currentFilters.per_page || 15,
});

let searchTimeout: NodeJS.Timeout | null = null;

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
};

const applyFilters = () => {
    router.get(route('admin.knowledge-base.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const sort = (column: string) => {
    if (filters.sort_by === column) {
        filters.sort_dir = filters.sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
        filters.sort_by = column;
        filters.sort_dir = 'asc';
    }
    applyFilters();
};

const changePage = (url: string | null) => {
    if (url) {
        router.get(
            url,
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
};

const createArticle = () => {
    router.visit(route('admin.knowledge-base.create'));
};

const manageTags = () => {
    router.visit(route('admin.tags.index'));
};

const viewArticle = (id: number) => {
    router.visit(route('admin.knowledge-base.show', id));
};

const editArticle = (id: number) => {
    router.visit(route('admin.knowledge-base.edit', id));
};

const deleteArticle = (article: Article) => {
    if (confirm(`Are you sure you want to delete "${article.title}"?`)) {
        router.delete(route('admin.knowledge-base.destroy', article.id), {
            onSuccess: () => {
                // Article moved to trash
            },
        });
    }
};

const restoreArticle = (id: number) => {
    router.post(route('admin.knowledge-base.restore', id));
};

const forceDeleteArticle = (id: number) => {
    if (confirm('Are you sure you want to permanently delete this article? This action cannot be undone.')) {
        router.delete(route('admin.knowledge-base.force-delete', id));
    }
};
</script>
