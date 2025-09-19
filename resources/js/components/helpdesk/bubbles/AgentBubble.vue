<template>
    <div class="flex flex-col items-end">
        <div class="max-w-2xl rounded-l-lg border-1 border-r-sidebar-ring bg-sidebar-ring/15 p-4 text-primary">
            <!-- Message Header -->
            <div class="mb-3 flex cursor-default items-center gap-3">
                <div class="flex items-center gap-2">
                    <!-- Agent icon -->
                    <div class="text-primay flex h-6 w-6 items-center justify-center rounded-full bg-sidebar-ring">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold">{{ message.agent_name || currentAgent.name }}</span>
                    <span class="rounded-md border bg-input/80 px-1.5 py-0.5 text-xs text-primary"> Agent </span>
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
        type: 'agent';
        content: string;
        created_at: string;
        agent_name?: string; // Optional agent name property
    };
}>();

// In a real app, this would come from auth().user() or similar
const currentAgent = {
    name: 'You',
    id: '1',
};

// Use shared date formatting utility
const formatDate = formatRelativeDate;
</script>
