<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey  = (string) config('gemini.api_key');
        $this->baseUrl = rtrim((string) config('gemini.base_url'), '/');
        $this->model   = (string) config('gemini.model', 'gemini-2.0-flash');
        $this->timeout = (int) config('gemini.timeout', 30);

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gemini API key is missing. Set GEMINI_API_KEY in .env');
        }
    }

    protected function systemPrompt(): string
    {
        return implode("\n", [
            "أنت مساعد سياحي متخصص. تجيب حصراً عن مواضيع السياحة، التأشيرات، برامج الرحلات، الميزانيات التقديرية، التنقل، الطعام، والثقافة.",
            "تجنّب المواضيع الخارجة عن السياحة. اذكر تحذيرات السلامة عند الحاجة دون تهويل.",
            "احرص على العربية الفصيحة الواضحة، نقاطٍ مرتّبة عند الاقتضاء، وإجابات مختصرة بلا حشو.",
        ]);
    }

    protected function postChat(array $messages, array $params = []): string
    {
        // Gemini expects a single prompt string, so we'll concatenate messages
        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = strtoupper($msg['role']) . ": " . $msg['content'];
        }
        $prompt = implode("\n\n", $contents);

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        $url = $this->baseUrl . "/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini request failed: ' . $response->status() . ' - ' . $response->body());
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function askTourism(string $query, string $lang = 'ar'): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            [
                'role' => 'user',
                'content' => "اللغة: {$lang}\nالمهمة: أجب بإيجاز ووضوح عن السؤال السياحي التالي.\nالسؤال: {$query}",
            ],
        ];

        return $this->postChat($messages);
    }

    public function buildItinerary(string $destination, array $prefs = []): array
    {
        $days  = (int) ($prefs['days'] ?? 3);
        $budget = (string) ($prefs['budget'] ?? 'متوسط');
        $style = (string) ($prefs['style'] ?? 'مزيج من الثقافة والطبيعة والطعام');
        $lang  = (string) ($prefs['lang'] ?? 'ar');

        $schema = json_encode([
            'title' => 'string',
            'summary' => 'string',
            'days' => [
                [
                    'day' => 1,
                    'theme' => 'string',
                    'morning' => ['string'],
                    'afternoon' => ['string'],
                    'evening' => ['string'],
                    'notes' => 'string',
                ],
            ],
            'estimated_budget' => [
                'currency' => 'USD',
                'amount_per_person' => 0,
            ],
            'tips' => ['string'],
        ], JSON_UNESCAPED_UNICODE);

        $prompt = implode("\n", [
            "اللغة: {$lang}",
            "المهمة: أنشئ برنامج رحلة مفصل إلى {$destination} لمدة {$days} أيام.",
            "الميزانية: {$budget}.",
            "الأسلوب المفضل: {$style}.",
            "التنسيق: JSON فقط مطابق للمخطط التالي، بدون أي نص خارجه.",
            "المخطط: {$schema}",
            "التفاصيل: احرص على أن تكون المقترحات واقعية ويمكن تنفيذها، مع توزيع منطقي للوقت وتخفيف التنقلات.",
            "نصائح السلامة والطقس والتنقل في قسم tips باختصار.",
        ]);

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $prompt],
        ];

        $raw = $this->postChat($messages);
        $parsed = $this->tryJson($raw);

        return $parsed ?? ['raw' => $raw];
    }

    protected function tryJson(string $raw): ?array
    {
        $clean = trim($raw);
        $clean = preg_replace('/^```json\s*/', '', $clean);
        $clean = preg_replace('/^```\s*/', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $decoded = json_decode($clean, true);
        return is_array($decoded) ? $decoded : null;
    }
}