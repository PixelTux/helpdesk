<template>
  <div class="flex flex-col items-start">
    <div class="rounded-r-lg p-4 max-w-2xl bg-chart-2/15 border-1 border-l-chart-2 text-primary">
      <!-- Message Header -->
      <div class="flex items-center gap-3 mb-3 cursor-default">
        <div class="flex items-center gap-2">
          <!-- Customer icon -->
          <div class="h-6 w-6 rounded-full flex items-center justify-center bg-chart-2 text-primay">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <span class="font-semibold text-sm">{{ message.message_owner_name || 'Customer' }}</span>
          <span class="text-xs px-1.5 py-0.5 rounded-md border bg-input/80 text-primary">
            Customer
          </span>
        </div>
        <span class="text-xs opacity-60 ml-auto font-medium">{{ formatDate(message.created_at) }}</span>
      </div>

      <!-- Message Content -->
      <div class="text-sm leading-relaxed" v-html="message.content"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';
import { formatRelativeDate } from '@/utils/dateFormatting';

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
