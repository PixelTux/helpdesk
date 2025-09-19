<template>
    <div
        class="cursor-pointer border-b border-accent transition-colors hover:bg-input"
        :class="{
            'bg-secondary hover:bg-sidebar': isActive,
            'font-semibold': conversation.unread && !isActive,
        }"
        @click="$emit('click')"
    >
        <div class="p-3">
            <div class="flex items-center justify-between">
                <div class="flex min-w-0 flex-1 items-center space-x-2">
                    <!-- Unread indicator -->
                    <div v-if="conversation.unread" class="h-2 w-2 shrink-0 rounded-full bg-sidebar-ring" title="Unread conversation"></div>
                    <h3
                        class="truncate font-medium"
                        :class="{
                            'text-sidebar-ring': isActive,
                            'font-bold': conversation.unread && !isActive,
                        }"
                    >
                        {{ conversation.subject }}
                    </h3>
                </div>
                <div class="ml-2 flex items-center space-x-2 text-xs text-muted-foreground">
                    <span
                        class="rounded bg-primary px-2 pt-1 font-mono text-secondary"
                        :class="{ 'bg-sidebar-ring': conversation.unread && !isActive }"
                    >
                        #{{ conversation.case_number }}
                    </span>
                    <span>
                        {{ formatDate(conversation.last_activity_at) }}
                    </span>
                </div>
            </div>

            <div class="mt-1 flex items-center text-sm text-secondary-foreground">
                <span class="truncate">{{ conversation.contact.name }}</span>
                <span class="mx-1">•</span>
                <span class="truncate text-muted-foreground">{{ conversation.contact.company?.name || 'No company' }}</span>
                <template v-if="conversation.assigned_to">
                    <span class="mx-1">•</span>
                    <span class="truncate text-xs text-blue-600">Assigned to {{ conversation.assigned_to.name }}</span>
                </template>
            </div>

            <div class="mt-2 flex items-center space-x-2">
                <Tag :value="conversation.status" />
                <Tag :value="conversation.priority" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Tag } from '@/components/ui/tag';
import { defineEmits, defineProps } from 'vue';
// Import generated types
import '@/../types/generated.d';

// Define props
const props = defineProps<{
    conversation: App.Data.ConversationData;
    isActive?: boolean;
}>();

// Define emits
defineEmits<{
    (e: 'click'): void;
}>();

// Format date to relative time (e.g., "2 hours ago")
function formatDate(dateString: string | null): string {
    if (!dateString) return 'No activity';

    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) {
        return 'just now';
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
        return `${diffInMinutes}m ago`;
    }

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
        return `${diffInHours}h ago`;
    }

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) {
        return `${diffInDays}d ago`;
    }

    // Format as MM/DD/YYYY for older dates
    return date.toLocaleDateString();
}
</script>
