<?php
function generateImageFromPrompt($prompt) {
    $url = 'https://api.openai.com/v1/images/generations';

    $data = [
        'prompt' => $prompt,
        'n' => 1,
        'size' => '1024x1024'
    ];

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}
