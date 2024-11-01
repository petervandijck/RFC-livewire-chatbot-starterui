<?php

namespace App\Livewire;

use Livewire\Component;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class Chat extends Component
{
    public $prompt = '';
    public $messages = [];
    public $currentQuestion = '';
    public $currentAnswer = '';

    public function sendMessage()
    {
        if (empty($this->prompt)) {
            return;
        }

        $this->currentQuestion = $this->prompt;
        $this->prompt = '';
        $this->currentAnswer = '';

        $this->js('$wire.streamResponse()');

    }


    public function streamResponse()
    {
        try {
            // Add the current question to messages array for API context
            $messagesForApi = array_merge($this->messages, [[
                'role' => 'user',
                'content' => $this->currentQuestion
            ]]);

            // Add the turn to the conversation model for storage

            $response = OpenAI::chat()->createStreamed([
                'model' => 'gpt-4o-mini',
                'messages' => $messagesForApi,
                'temperature' => 0.7,
            ]);

            $fullResponse = '';

            foreach ($response as $chunk) {
                if (isset($chunk->choices[0]->delta->content)) {
                    $content = $chunk->choices[0]->delta->content;
                    $this->stream('currentAnswer', $content);
                    $fullResponse .= $content;
                }
            }

            // After completion, add both messages to the history
            $this->messages[] = [
                'role' => 'user',
                'content' => $this->currentQuestion,
                'timestamp' => now()
            ];

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $fullResponse,
                'timestamp' => now()
            ];

            // Clear current exchange
            $this->currentQuestion = '';
            $this->currentAnswer = '';

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
