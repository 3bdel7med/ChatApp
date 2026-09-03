<?php
namespace App\Services;

use Gemini\Laravel\Facades\Gemini;

class AgentChatService
{
    public function generateResponse(string $userMessage, string $persona = 'helpful_user'): string
    {
        $systemPrompt = match ($persona) {
            'tester' => "You are a software QA tester interacting in a chat room. Keep replies short, casual, and natural.",
            'developer' => "You are a senior developer discussing code and system architecture. Speak technically.",
            default => "You are a friendly active participant in a group chat. Keep messages under 2 sentences."
        };

        $prompt = "{$systemPrompt}\n\nUser message: {$userMessage}\n\nYour response:";

        $result = Gemini::geminiPro()->generateContent($prompt);

        return $result->text();
    }
}
