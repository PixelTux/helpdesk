<template>
    <div ref="messagesContainer" class="max-h-[calc(100vh-250px)] flex-1 overflow-y-auto p-4">
        <div v-for="message in messages" :key="message.id" class="mb-4 last:mb-0">
            <CustomerBubble v-if="message.type === 'customer'" :message="message" />
            <AgentBubble v-else-if="message.type === 'agent'" :message="message" />
            <InternalNoteBubble v-else-if="message.type === 'internal'" :message="message" />
        </div>
    </div>
</template>

<script setup lang="ts">
import AgentBubble from '@/components/helpdesk/bubbles/AgentBubble.vue';
import CustomerBubble from '@/components/helpdesk/bubbles/CustomerBubble.vue';
import InternalNoteBubble from '@/components/helpdesk/bubbles/InternalNoteBubble.vue';
import { nextTick, onMounted, ref, watch } from 'vue';

// Define message type interfaces with more specific types than the generated ones
interface CustomerMessage extends App.Data.MessageData {
    type: 'customer';
    message_owner_name?: string;
}

interface AgentMessage extends App.Data.MessageData {
    type: 'agent';
}

interface InternalMessage extends App.Data.MessageData {
    type: 'internal';
}

// Union type
type Message = CustomerMessage | AgentMessage | InternalMessage;

// Define props
const props = defineProps<{
    messages: Readonly<
        Message[] & {
            message_owner_name?: string;
            agent_name?: string;
        }
    >;
}>();

// Refs
const messagesContainer = ref<HTMLElement | null>(null);

// Methods
function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

// Scroll to bottom when messages change
watch(
    () => props.messages.length,
    () => {
        scrollToBottom();
    },
);

// Scroll to bottom on initial mount
onMounted(() => {
    scrollToBottom();
});
</script>
