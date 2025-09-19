<template>
    <div class="flex flex-col items-start">
        <div class="max-w-2xl rounded-r-lg border-1 border-l-chart-2 bg-chart-2/15 p-4 text-primary">
            <!-- Message Header -->
            <div class="mb-3 flex cursor-default items-center gap-3">
                <div class="flex items-center gap-2">
                    <!-- Customer icon -->
                    <div class="text-primay flex h-6 w-6 items-center justify-center rounded-full bg-chart-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold">{{ message.message_owner_name || 'Customer' }}</span>
                    <span class="rounded-md border bg-input/80 px-1.5 py-0.5 text-xs text-primary"> Customer </span>
                </div>
                <span class="ml-auto text-xs font-medium opacity-60">{{ formatDate(message.created_at) }}</span>
            </div>

            <!-- Message Content -->
            <div class="text-sm leading-relaxed" v-html="message.content"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { formatRelativeDate } from '@/utils/dateFormatting';
import { defineProps } from 'vue';

// Define props
const props = defineProps<{
    message: {
        id: string;
        conversation_id: string;
        type: 'customer';
        content: string;
        created_at: string;
        message_owner_name?: string; // Optional customer name property
    };
}>();

// Use shared date formatting utility
const formatDate = formatRelativeDate;
</script>
