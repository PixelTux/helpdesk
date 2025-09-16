<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-6">
      <!-- Search Header -->
      <div class="space-y-6">
        <h1 class="text-3xl font-bold">Knowledge Base</h1>

        <!-- AI Assistant -->
        <div
          class="rounded-lg border border-muted bg-gradient-to-r from-chart-4 to-chart-1 p-6 shadow-2xl"
        >
          <AIAnswerGenerator />
        </div>

        <!-- Search Bar -->
        <div class="max-w-md">
          <Input
            v-model="searchQuery"
            placeholder="Search articles..."
            @input="handleSearch"
            class="w-full"
          />
        </div>
      </div>

      <!-- Active Filters -->
      <div class="flex flex-wrap gap-2" v-if="searchQuery || selectedTag">
        <div
          v-if="searchQuery"
          class="flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800"
        >
          <span>Search: "{{ searchQuery }}"</span>
          <button @click="clearSearch" class="hover:text-blue-600">&times;</button>
        </div>
        <div
          v-if="currentTag"
          class="flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm text-green-800"
        >
          <span>Tag: {{ currentTag.name }}</span>
          <button @click="clearTag" class="hover:text-green-600">&times;</button>
        </div>
      </div>

      <!-- Tag Filter Chips -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="tag in tags"
          :key="tag.slug"
          @click="filterByTag(tag.slug)"
          :class="[
            'rounded-full px-3 py-1 text-sm transition-colors',
            selectedTag === tag.slug
              ? 'bg-sidebar-ring text-primary'
              : 'bg-sidebar-border text-primary hover:bg-foreground hover:text-secondary',
          ]"
        >
          {{ tag.name }}
        </button>
      </div>

      <!-- Search Results Count -->
      <div v-if="searchQuery" class="text-gray-600">
        Found {{ articles.data.length }} results
        <span v-if="searchQuery">for "{{ searchQuery }}"</span>
      </div>

      <!-- Article Cards -->
      <div
        v-if="articles.data.length > 0"
        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
      >
        <Card
          v-for="article in articles.data"
          :key="article.id"
          class="transition-shadow hover:shadow-lg"
        >
          <CardHeader>
            <CardTitle>
              <div
                v-if="article.highlighted_title"
                v-html="article.highlighted_title"
                class="search-highlight"
              ></div>
              <div v-else>{{ article.title }}</div>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="article.highlighted_excerpt"
              v-html="article.highlighted_excerpt"
              class="search-highlight text-gray-600"
            ></div>
            <p v-else class="text-muted-foreground">{{ article.excerpt }}</p>

            <!-- Tags -->
            <div v-if="article.tag_names.length > -1" class="mt-3 flex flex-wrap gap-1">
              <span
                v-for="tagName in article.tag_names"
                :key="tagName"
                class="rounded bg-input px-2 py-1 text-xs"
              >
                {{ tagName }}
              </span>
            </div>
          </CardContent>
          <CardFooter>
            <Link
              :href="route('knowledge-base.show', article.slug)"
              class="font-medium text-blue-600 hover:text-blue-800"
            >
              Read more →
            </Link>
          </CardFooter>
        </Card>
      </div>

      <!-- No Results -->
      <div v-else class="py-12 text-center">
        <div class="text-lg text-gray-500">
          <div v-if="searchQuery">No articles found for "{{ searchQuery }}"</div>
          <div v-else-if="selectedTag">No articles found with the selected tag</div>
          <div v-else>No articles available</div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="articles.links && articles.links.length > 3" class="flex justify-center">
        <nav class="flex items-center space-x-1">
          <template v-for="link in articles.links" :key="link.url || link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'px-3 py-2 text-sm',
                link.active
                  ? 'rounded bg-blue-600 text-white'
                  : 'rounded text-blue-600 hover:bg-blue-50',
              ]"
              v-html="link.label"
            />
            <span
              v-else
              :class="[
                'cursor-not-allowed px-3 py-2 text-sm text-gray-400',
                link.active ? 'rounded bg-blue-600 text-white' : '',
              ]"
              v-html="link.label"
            />
          </template>
        </nav>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";

import AIAnswerGenerator from "@/components/AIAnswerGenerator.vue";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import CardFooter from "@/components/ui/card/CardFooter.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import Input from "@/components/ui/input/Input.vue";
import { Link, router } from "@inertiajs/vue3";
import { useThrottleFn } from "@vueuse/core";
import { ref } from "vue";

interface Article {
  id: string;
  title: string;
  slug: string;
  excerpt: string;
  highlighted_title?: string;
  highlighted_excerpt?: string;
  tag_names: string[];
}

const props = defineProps<{
  articles: { data: Article[]; links: any[]; current_page: number; last_page: number };
  tags: Array<{ name: string; slug: string }>;
  currentFilters: { search: string; tag: string; page: number };
  currentTag?: { name: string; slug: string } | null;
}>();

const searchQuery = ref(props.currentFilters.search || "");
const selectedTag = ref(props.currentFilters.tag || "");

const handleSearch = useThrottleFn(() => {
  performSearch();
}, 500);

const performSearch = () => {
  router.get(
    route("knowledge-base.index"),
    {
      search: searchQuery.value || undefined,
      tag: selectedTag.value || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

const filterByTag = (slug: string) => {
  selectedTag.value = slug === selectedTag.value ? "" : slug;
  performSearch();
};

const clearSearch = () => {
  searchQuery.value = "";
  performSearch();
};

const clearTag = () => {
  selectedTag.value = "";
  performSearch();
};
</script>

<style scoped>
.flex {
  display: flex;
}
.gap-2 {
  gap: 0.5rem;
}
.gap-4 {
  gap: 1rem;
}

/* Search highlighting styles */
:deep(.search-highlight mark) {
  background-color: #fef08a; /* yellow-200 */
  color: #854d0e; /* yellow-900 */
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
  font-weight: 600;
}
</style>
