<template>
    <div class="ai-answer-generator">
        <!-- Question Input -->
        <div class="space-y-4">
            <div>
                <Label for="question">Ask AI Assistant</Label>
                <div class="mt-1 flex gap-2">
                    <Input
                        id="question"
                        v-model="question"
                        placeholder="Ask a question about our knowledge base..."
                        class="flex-1"
                        @keyup.enter="generateAnswer"
                        :disabled="isGenerating"
                    />
                    <Button @click="generateAnswer" :disabled="isGenerating || !question.trim()" class="whitespace-nowrap">
                        <Icon v-if="isGenerating" name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                        <Icon v-else name="sparkles" class="mr-2 h-4 w-4" />
                        {{ isGenerating ? 'Generating...' : 'Ask AI' }}
                    </Button>
                </div>
            </div>

            <!-- Error Display -->
            <div v-if="error" class="rounded-lg border border-red-200/90 bg-red-50/90 p-4">
                <div class="flex items-start">
                    <Icon name="alert-circle" class="mt-0.5 mr-3 h-5 w-5 text-red-500" />
                    <div>
                        <h4 class="text-sm font-medium text-red-800/80">Error</h4>
                        <p class="mt-1 text-sm text-red-600/90">{{ error }}</p>
                    </div>
                </div>
            </div>

            <!-- Answer Display -->
            <div v-if="answer || isGenerating" class="rounded-lg border border-gray-200">
                <!-- Header -->
                <div class="rounded-t-lg border-b border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <Icon name="brain" class="mr-2 h-5 w-5 text-blue-600" />
                            <h3 class="text-sm font-medium text-gray-900">AI Assistant</h3>
                        </div>
                        <div v-if="isGenerating" class="flex items-center text-xs text-gray-500">
                            <Icon name="loader-2" class="mr-1 h-3 w-3 animate-spin" />
                            Generating...
                        </div>
                        <div v-else-if="answer" class="text-xs text-gray-500">Generated {{ formatTimestamp(timestamp) }}</div>
                    </div>
                </div>

                <!-- Answer Content -->
                <div class="p-4">
                    <div class="prose prose-sm max-w-none">
                        <div v-if="streamingAnswer" class="whitespace-pre-wrap">{{ streamingAnswer }}</div>
                        <div v-else-if="answer" class="whitespace-pre-wrap">{{ answer }}</div>
                        <div v-else class="flex items-center text-gray-500">
                            <Icon name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                            Thinking...
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div v-if="isGenerating && streamingAnswer" class="mt-2 flex items-center">
                        <div class="flex space-x-1">
                            <div class="h-2 w-2 animate-bounce rounded-full bg-blue-500"></div>
                            <div class="h-2 w-2 animate-bounce rounded-full bg-blue-500" style="animation-delay: 0.1s"></div>
                            <div class="h-2 w-2 animate-bounce rounded-full bg-blue-500" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>

                <!-- Sources -->
                <div v-if="sources.length > 0" class="border-t border-gray-200 p-4">
                    <h4 class="mb-3 flex items-center text-sm font-medium text-gray-900">
                        <Icon name="book-open" class="mr-2 h-4 w-4" />
                        Sources
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="source in sources"
                            :key="source.id"
                            class="flex items-start rounded-lg bg-gray-50 p-3 transition-colors hover:bg-gray-100"
                        >
                            <Icon name="file-text" class="mt-0.5 mr-3 h-4 w-4 flex-shrink-0 text-gray-400" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h5 class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                            <a :href="source.url" class="hover:underline">
                                                {{ source.title }}
                                            </a>
                                        </h5>
                                        <p v-if="source.excerpt" class="mt-1 line-clamp-2 text-xs text-gray-600">
                                            {{ source.excerpt }}
                                        </p>
                                    </div>
                                    <Icon name="external-link" class="ml-2 h-3 w-3 flex-shrink-0 text-gray-400" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { computed, ref, watch } from 'vue';

interface Source {
    id: number;
    title: string;
    url: string;
    excerpt?: string;
}

interface Message {
    id: string;
    type: 'customer' | 'agent' | 'internal';
    content: string;
    created_at: string;
    message_owner_name?: string;
}

interface Conversation {
    id: string;
    subject: string;
    contact: {
        name: string;
        email: string;
    };
}

interface Props {
    useStreaming?: boolean;
    conversation?: Conversation;
    messages?: Message[];
}

const props = withDefaults(defineProps<Props>(), {
    useStreaming: true,
});

// Reactive state
const question = ref('');
const answer = ref('');
const streamingAnswer = ref('');
const sources = ref<Source[]>([]);
const isGenerating = ref(false);
const error = ref('');
const timestamp = ref<string | null>(null);

// Auto-populate question with latest customer message when component loads
const initializeQuestion = () => {
    if (props.messages && props.messages.length > 0) {
        // Find the latest customer message
        const customerMessages = props.messages.filter((m) => m.type === 'customer');
        if (customerMessages.length > 0) {
            const latestMessage = customerMessages[customerMessages.length - 1];
            // Strip HTML tags and get plain text
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = latestMessage.content;
            question.value = tempDiv.textContent || tempDiv.innerText || '';
        }
    }
};

// Initialize question when component mounts or props change
watch(() => props.messages, initializeQuestion, { immediate: true });

// Computed
const formatTimestamp = computed(() => {
    return (ts: string | null) => {
        if (!ts) return '';
        const date = new Date(ts);
        return date.toLocaleTimeString();
    };
});

// Methods
const generateAnswer = async () => {
    if (!question.value.trim() || isGenerating.value) return;

    // Reset state
    error.value = '';
    answer.value = '';
    streamingAnswer.value = '';
    sources.value = [];
    timestamp.value = null;
    isGenerating.value = true;

    try {
        if (props.useStreaming) {
            await generateStreamingAnswer();
        } else {
            await generateStaticAnswer();
        }
    } catch (err) {
        error.value = 'Failed to generate answer. Please try again.';
    } finally {
        isGenerating.value = false;
    }
};

const generateStreamingAnswer = async () => {
    const requestData: any = {
        query: question.value.trim(),
    };

    // Add conversation context if available
    if (props.conversation) {
        requestData.conversation_id = props.conversation.id;
        requestData.conversation_context = {
            subject: props.conversation.subject,
            contact: props.conversation.contact,
        };
    }

    // Add messages if available
    if (props.messages) {
        requestData.messages = props.messages;
    }

    const response = await fetch(route('ai.answer.stream'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(requestData),
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    const reader = response.body?.getReader();
    if (!reader) {
        throw new Error('No response stream available');
    }

    const decoder = new TextDecoder();

    try {
        while (true) {
            const { done, value } = await reader.read();

            if (done) break;

            const chunk = decoder.decode(value);
            const lines = chunk.split('\n');

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const data = JSON.parse(line.slice(6));

                        if (data.type === 'start') {
                            sources.value = data.sources || [];
                            timestamp.value = data.timestamp;
                        } else if (data.type === 'chunk') {
                            streamingAnswer.value += data.content;
                        } else if (data.type === 'complete') {
                            answer.value = streamingAnswer.value;
                        } else if (data.type === 'error') {
                            throw new Error(data.error || 'Unknown streaming error');
                        }
                    } catch (parseError) {
                        error.value = 'Failed to parse response data. Please try again.';
                    }
                }
            }
        }
    } finally {
        reader.releaseLock();
    }
};

const generateStaticAnswer = async () => {
    const requestData: any = {
        query: question.value.trim(),
    };

    // Add conversation context if available
    if (props.conversation) {
        requestData.conversation_id = props.conversation.id;
        requestData.conversation_context = {
            subject: props.conversation.subject,
            contact: props.conversation.contact,
        };
    }

    // Add messages if available
    if (props.messages) {
        requestData.messages = props.messages;
    }

    const response = await fetch(route('ai.answer.generate'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(requestData),
    });

    const data = await response.json();

    if (!data.success) {
        throw new Error(data.error || 'Unknown error');
    }

    answer.value = data.answer;
    sources.value = data.sources || [];
    timestamp.value = data.timestamp;
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.animate-bounce:nth-child(2) {
    animation-delay: 0.1s;
}

.animate-bounce:nth-child(3) {
    animation-delay: 0.2s;
}
</style>
