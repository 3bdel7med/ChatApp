<?php

namespace App\Jobs;

use App\Events\NewSimulationMessage;
use App\Models\Conversation;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessGeminiSimulation implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;
    public int $tries = 3;

    public function __construct(
        public int $conversationId,
        public string $topic,
        public int $senderId,
        public ?int $receiverId = null
    ) {}

    public function handle(): void
    {
        try {
            $conversation = Conversation::findOrFail($this->conversationId);

            // 1. Fetch latest 10 messages
            $previousMessages = $conversation->messages()
                ->latest()
                ->take(10)
                ->get()
                ->reverse();

            // 2. Build context string
            $chatHistory = "";
            foreach ($previousMessages as $msg) {
                $role = $msg->is_ai_agent ? "AI Assistant" : "User";
                $chatHistory .= "{$role}: {$msg->body}\n";
            }

            // 3. Construct Gemini Prompt
            $prompt = "You are a friendly, natural, and engaging AI participant in an ongoing chat (group or one-to-one).\n"
                . "Your goal is to converse naturally like a real human friend or colleague. You are NOT restricted to sales or support.\n"
                . "You can chat about anything: casual greetings (e.g., 'hello', 'welcome', 'how are you '), general topics, programming, languages, hobbies, or open up new interesting discussions.\n\n"
                . "Guidelines:\n"
                . "- Match the tone and language of the conversation (if the user speaks Egyptian Arabic or English, respond in the same dialect/language naturally).\n"
                . "- Keep responses human-like, friendly, and context-aware.\n"
                . "- If the topic of interest is empty or casual, just keep the natural flow of the conversation going.\n\n"
                . "Recent conversation history:\n"
                . $chatHistory . "\n"
                . "Topic / Focus (if any): {$this->topic}\n\n"
                . "AI Assistant: Generate the next natural reply continuing the conversation history above.";

            // 4. Query Gemini API
            $result = Gemini::generativeModel('gemini-3.6-flash')->generateContent($prompt);
            $aiReplyText = $result->text();

            // 5. Store generated response in DB
            $aiMessage = $conversation->messages()->create([
                'sender_id' => $this->senderId,
                'receiver_id' => $this->receiverId,
                'body' => $aiReplyText,
                'is_ai_agent' => true,
            ]);

            // 6. Broadcast event over WebSockets
            event(new NewSimulationMessage($aiMessage, 'AI Assistant'));

        } catch (\Throwable $e) {
            Log::error('Simulation Job Failed: ' . $e->getMessage(), [
                'conversation_id' => $this->conversationId,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
