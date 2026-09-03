<?php
namespace App\Services;

use Gemini\Laravel\Facades\Gemini;

class ChatAgentService
{
    public function generateReply(string $persona, string $conversationHistory): string
    {
        $prompt = "You are participating in a real-time chat simulation test.
Your persona: {$persona}.
Conversation history so far:
{$conversationHistory}

Reply with ONLY the next message in character. Keep it natural, short (1-2 sentences), and relevant to a normal chat context.";

        $result = Gemini::generativeModel('gemini-3.6-flash')->generateContent($prompt);;

        return trim($result->text());
    }
}
