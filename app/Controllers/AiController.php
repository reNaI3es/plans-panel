<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl, RateLimit, Config, Audit};

class AiController
{
    public function chat(): void
    {
        Acl::require('ai.use');
        $db = Db::instance();
        $conversations = $db->fetchAll(
            "SELECT * FROM ai_conversations WHERE user_id = ? ORDER BY last_message_at DESC LIMIT 20",
            [Auth::id()]
        );
        View::render('ai/chat', ['pageTitle' => 'ШІ-помічник', 'conversations' => $conversations]);
    }

    public function sendMessage(): void
    {
        Acl::require('ai.use');

        // Rate limit
        $ip = ip_hash();
        if (RateLimit::check('ai:user:' . Auth::id(), 20, 3600)) {
            flash('error', 'Перевищено ліміт повідомлень за годину.');
            redirect(url('/ai'));
        }

        $db = Db::instance();
        $message = trim($_POST['message'] ?? '');
        $conversationId = (int)($_POST['conversation_id'] ?? 0);

        if (strlen($message) < 1) {
            flash('error', 'Повідомлення не може бути порожнім.');
            redirect(url('/ai'));
        }

        // Создаём или получаем диалог
        if ($conversationId <= 0) {
            $title = mb_substr($message, 0, 50);
            $db->execute(
                "INSERT INTO ai_conversations (user_id, title, last_message_at) VALUES (?, ?, NOW())",
                [Auth::id(), $title]
            );
            $conversationId = (int)$db->lastInsertId();
        }

        // Сохраняем сообщение пользователя
        $db->execute(
            "INSERT INTO ai_messages (conversation_id, user_id, role, content, created_at) VALUES (?, ?, 'user', ?, NOW())",
            [$conversationId, Auth::id(), $message]
        );

        // Отправляем в AI API
        $response = $this->callAi($conversationId, $message);

        // Сохраняем ответ
        $db->execute(
            "INSERT INTO ai_messages (conversation_id, user_id, role, content, prompt_tokens, completion_tokens, created_at) VALUES (?, ?, 'assistant', ?, ?, ?, NOW())",
            [$conversationId, Auth::id(), $response['content'], $response['prompt_tokens'] ?? 0, $response['completion_tokens'] ?? 0]
        );

        // Обновляем диалог
        $db->execute(
            "UPDATE ai_conversations SET message_count = message_count + 2, total_tokens = total_tokens + ?, last_message_at = NOW() WHERE id = ?",
            [($response['prompt_tokens'] ?? 0) + ($response['completion_tokens'] ?? 0), $conversationId]
        );

        redirect(url('/ai') . '#conv-' . $conversationId);
    }

    private function callAi(int $conversationId, string $message): array
    {
        $baseUrl = Config::setting('ai_base_url', Config::get('ai_base_url'));
        $model = Config::setting('ai_model', Config::get('ai_model'));
        $apiKey = Config::setting('ai_api_key', Config::get('ai_api_key'));

        if (!$apiKey) {
            return ['content' => '⚠️ AI-ключ не налаштовано. Зверніться до Засновника.', 'prompt_tokens' => 0, 'completion_tokens' => 0];
        }

        // Собираем контекст
        $db = Db::instance();
        $history = $db->fetchAll(
            "SELECT role, content FROM ai_messages WHERE conversation_id = ? ORDER BY id DESC LIMIT 10",
            [$conversationId]
        );
        $history = array_reverse($history);

        $messages = [
            ['role' => 'system', 'content' => 'Ти — помічник команди reNaI3es (кіберспортивний проєкт: CS2, CS:GO, Unturned, Minecraft). Відповідай українською, коротко і по суті.'],
        ];
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        // Вызов API (OpenAI-совместимый)
        $ch = curl_init($baseUrl . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 1024,
            ]),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || !$response) {
            return ['content' => '⚠️ Помилка з\'єднання з AI: ' . $error, 'prompt_tokens' => 0, 'completion_tokens' => 0];
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            return ['content' => '⚠️ Некоректна відповідь від AI.', 'prompt_tokens' => 0, 'completion_tokens' => 0];
        }

        return [
            'content' => $data['choices'][0]['message']['content'],
            'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
        ];
    }
}
