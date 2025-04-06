<?php
function getChatGPTResponse($prompt, $model = 'gpt-3.5-turbo', $imageBase64 = null, $imageExt = null) {
    $url = 'https://api.openai.com/v1/chat/completions';

    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . OPENAI_API_KEY
    ];

    if ($model === 'gpt-4-turbo' && $imageBase64 && $imageExt) {
        // GPT-4 Vision model with base64 image input
        $data = [
            "model" => "gpt-4-turbo",
            "messages" => [[
                "role" => "user",
                "content" => [
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => "data:image/$imageExt;base64,$imageBase64"
                        ]
                    ],
                    [
                        "type" => "text",
                        "text" => $prompt
                    ]
                ]
            ]],
            "max_tokens" => 1000
        ];
    } else {
        // Standard text-only models
        $data = [
            "model" => $model,
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "max_tokens" => 1000
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? 'No response from OpenAI.';
}
