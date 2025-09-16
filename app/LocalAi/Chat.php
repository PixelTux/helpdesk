<?php

namespace App\LocalAi;

use App\Services\LocalAiService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Chat
{
    public const ALLOWED_ROLES = ['user', 'assistant', 'system', 'tool'];

    protected array $messages = [];

    protected LocalAiService $localAiService;

    public function __construct(LocalAiService $localAiService)
    {
        $this->localAiService = $localAiService;
    }

    public function send(array $aiRequestMessages, bool $memory = false): string
    {
        // Validate input
        $validator = Validator::make(
            ['messages' => $aiRequestMessages],
            [
                'messages' => 'required|array|min:1',
                'messages.*.role' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_ROLES)],
                'messages.*.content' => 'required|string',
            ],
            [
                'messages.*.role.in' => 'The role must be one of: ' . implode(', ', self::ALLOWED_ROLES),
                'messages.*.content.required' => 'Each message must have content.',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->messages = $aiRequestMessages;

        $response = $this->localAiService->getAiOutput($this->messages);

        if ($memory) $this->addContextMemory($response);

        return $response ?? '';
    }

    public function addContextMemory($addContext)
    {
        if ($addContext !== null) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $addContext,
            ];
        }
    }

    public function replay(array $message, bool $memory)
    {
        $messageIn[] = [
            [
                'role' => 'user',
                'content' => $message
            ]
        ];

        return $this->send($message, $memory);
    }


    public function messages()
    {
        return $this->messages;
    }
}
