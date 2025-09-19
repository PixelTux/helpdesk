<template>
    <div
        class="cursor-pointer border-b border-gray-200 transition-colors hover:bg-gray-50"
        :class="{ 'bg-zinc-100': isActive }"
        @click="$emit('click')"
    >
        <div class="p-4">
            <div class="flex items-center justify-between">
                <h3 class="truncate font-medium" :class="{ 'text-primary': isActive }">
                    {{ conversation.subject }}
                </h3>
                <span class="text-xs text-muted-foreground">
                    {{ formatDate(conversation.last_activity_at) }}
                </span>
            </div>

            <div class="mt-1 flex items-center text-sm text-muted-foreground">
                <span class="truncate">{{ conversation.contact.name }}</span>
                <span class="mx-1">•</span>
                <span class="truncate text-muted-foreground">{{ conversation.contact.company || 'No company' }}</span>
            </div>

            <div class="mt-2 flex items-center space-x-2">
                <Tag :value="conversation.status" size="sm" />
                <Tag :value="conversation.priority" size="sm" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Tag } from '@/components/ui/tag';
import { formatConversationDate } from '@/utils/dateFormatting';
import { defineEmits, defineProps } from 'vue';

// Define props
const props = defineProps<{
    conversation: {
        id: string;
        subject: string;
        status: string; // Using string instead of enum to avoid type errors
        priority: string; // Using string instead of enum to avoid type errors
        contact: {
            id: string;
            name: string;
            email: string;
            company: string | null;
        };
        last_activity_at: string;
        created_at: string;
    };
    isActive: boolean;
}>();

// Define emits
defineEmits<{
    (e: 'click'): void;
}>();

// Use shared date formatting utility
const formatDate = formatConversationDate;
</script>
