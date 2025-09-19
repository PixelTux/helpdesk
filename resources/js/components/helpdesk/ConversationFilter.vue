<template>
    <div class="-mt-1 mb-3 space-y-4 rounded-md border-1 bg-sidebar p-4">
        <!-- Collapsible header - only show if not controlled externally -->
        <button v-if="!hideCollapseButton" @click="toggleFilterCollapse" class="flex w-full items-center justify-between font-medium">
            Filters
            <Icon :name="filterCollapsed ? 'chevron-down' : 'chevron-up'" class="h-4 w-4" />
        </button>
        <div v-show="!filterCollapsed || hideCollapseButton" class="space-y-4">
            <!-- Search Input -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Search</label>
                <Input v-model="searchQuery" type="text" placeholder="Search conversations..." class="w-full" @input="debouncedSearch" />
            </div>

            <!-- Quick Filters -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Quick Filters</label>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" :class="{ 'border-blue-200 bg-blue-50': filters.unread }" @click="toggleUnread">
                        <div class="mr-1 h-2 w-2 rounded-full bg-sidebar-ring"></div>
                        Unread
                        <span class="ml-1 text-xs text-muted-foreground">({{ stats?.unread || 0 }})</span>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        :class="{ 'border-red-200 bg-red-50': filters.priority?.includes('urgent') }"
                        @click="toggleUrgent"
                    >
                        Urgent
                        <span class="ml-1 text-xs text-gray-500">({{ stats?.by_priority?.urgent || 0 }})</span>
                    </Button>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Status</label>
                <FilterDropdown
                    :selected-items="filters.status"
                    :options="filterOptions?.statuses || []"
                    :stats="stats?.by_status"
                    placeholder="Select Status"
                    @toggle="toggleStatus"
                />
            </div>

            <!-- Priority Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Priority</label>
                <FilterDropdown
                    :selected-items="filters.priority"
                    :options="filterOptions?.priorities || []"
                    :stats="stats?.by_priority"
                    placeholder="Select Priority"
                    @toggle="togglePriority"
                />
            </div>

            <!-- Clear All Filters -->
            <div v-if="hasActiveFilters">
                <Button variant="ghost" size="sm" class="w-full" @click="clearAllFilters"> Clear All Filters </Button>
            </div>

            <!-- Stats Summary -->
            <div class="border-active cursor-default border-t pt-2 text-xs text-muted-foreground">
                Showing {{ filteredCount || 0 }} of {{ stats?.total || 0 }} conversations
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { useConversationCollapseState } from '@/composables/useConversationCollapseState';
import { navigateWithFilters } from '@/utils/inertiaNavigation';
import { computed, defineAsyncComponent, reactive, ref, watch } from 'vue';
// Simple debounce implementation to avoid lodash dependency
function createDebounce<T extends (...args: any[]) => void>(func: T, wait: number): T {
    let timeout: NodeJS.Timeout | null = null;
    return ((...args: any[]) => {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    }) as T;
}
const FilterDropdown = defineAsyncComponent(() => import('@/components/ui/filter/FilterDropdown.vue'));
// Import generated types
import '@types/generated.d';

// Define props
const props = defineProps<{
    currentFilters: Record<string, any>;
    filterOptions: App.Data.ConversationFilterData;
    filteredCount: number;
    conversationId?: string; // Optional conversation ID for state persistence
    hideCollapseButton?: boolean; // Hide internal collapse button when controlled externally
}>();

// Initialize collapse state
const { filterCollapsed, toggleFilterCollapse } = useConversationCollapseState(props.conversationId);

// Reactive filters state
const filters = reactive<{
    search?: string;
    status?: string[];
    priority?: string[];
    unread?: boolean;
}>({ ...props.currentFilters });

// Search query for debouncing
const searchQuery = ref(filters.search || '');

// Computed properties
const stats = computed(() => props.filterOptions?.stats || { total: 0, unread: 0, by_status: {}, by_priority: {} });

const hasActiveFilters = computed(() => {
    return !!(filters.search || (filters.status && filters.status.length > 0) || (filters.priority && filters.priority.length > 0) || filters.unread);
});

// Debounced search
const debouncedSearch = createDebounce(() => {
    filters.search = searchQuery.value;
    updateFilters();
}, 300);

// Methods
function toggleUnread() {
    filters.unread = filters.unread ? undefined : true;
    updateFilters();
}

function toggleUrgent() {
    if (!filters.priority) filters.priority = [];
    if (filters.priority.includes('urgent')) {
        filters.priority = filters.priority.filter((p) => p !== 'urgent');
        if (filters.priority.length === 0) filters.priority = undefined;
    } else {
        filters.priority.push('urgent');
    }
    updateFilters();
}

function toggleStatus(statusValue: string) {
    if (!filters.status) filters.status = [];
    if (filters.status.includes(statusValue)) {
        filters.status = filters.status.filter((s) => s !== statusValue);
        if (filters.status.length === 0) filters.status = undefined;
    } else {
        filters.status.push(statusValue);
    }
    updateFilters();
}

function togglePriority(priorityValue: string) {
    if (!filters.priority) filters.priority = [];
    if (filters.priority.includes(priorityValue)) {
        filters.priority = filters.priority.filter((p) => p !== priorityValue);
        if (filters.priority.length === 0) filters.priority = undefined;
    } else {
        filters.priority.push(priorityValue);
    }
    updateFilters();
}

function clearAllFilters() {
    filters.search = undefined;
    filters.status = undefined;
    filters.priority = undefined;
    filters.unread = undefined;
    searchQuery.value = '';
    updateFilters();
}

function updateFilters() {
    // Use the navigation utility that handles state persistence
    navigateWithFilters('/helpdesk', filters);
}

// Watch for prop changes to sync filters
watch(
    () => props.currentFilters,
    (newFilters) => {
        Object.assign(filters, newFilters);
        searchQuery.value = filters.search || '';
    },
    { deep: true },
);
</script>
