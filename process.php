<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include 'config.php';
include 'functions/chatgpt.php';
include 'functions/dalle.php';

$response = [
    'chat_response' => '',
    'image_url' => null,
    'debug' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $prompt = $_POST['prompt'] ?? '';
    $model = $_POST['model'] ?? 'gpt-3.5-turbo';
    $base64Image = null;
    $imageExt = null;

    if (!empty($_FILES['userfile']['name']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = basename($_FILES['userfile']['name']);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $target_file = $upload_dir . $filename;
        move_uploaded_file($_FILES['userfile']['tmp_name'], $target_file);

        $image_types = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $text_extensions = ['txt', 'csv', 'md'];
        $doc_extensions = ['docx'];
        $ppt_extensions = ['pptx'];
        $pdf_extensions = ['pdf'];

        // Handle image for GPT-4 Turbo Vision
        if (in_array($file_ext, $image_types)) {
            if ($model === 'gpt-4-turbo') {
                $base64Image = base64_encode(file_get_contents($target_file));
                $imageExt = $file_ext;
                $response['debug'][] = "Image converted for Vision model.";
            } else {
                $prompt .= "\n\nAn image was uploaded ($filename), but only GPT-4 Turbo can analyze images.";
            }
        }

        // Handle text file
        elseif (in_array($file_ext, $text_extensions)) {
            $text = file_get_contents($target_file);
            $text = substr($text, 0, 1500);
            $prompt .= "\n\n--- Text from uploaded file ($filename) ---\n$text\n\nPlease summarize or analyze it.";
        }

        // Handle DOCX file
        elseif (in_array($file_ext, $doc_extensions)) {
            $zip = new ZipArchive;
            if ($zip->open($target_file) === TRUE) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($xml) {
                    $text = strip_tags($xml);
                    $text = substr($text, 0, 1500);
                    $prompt .= "\n\n--- DOCX Content from ($filename) ---\n$text\n\nPlease summarize or analyze.";
                }
            }
        }

        // Handle PPTX file
        elseif (in_array($file_ext, $ppt_extensions)) {
            $zip = new ZipArchive;
            if ($zip->open($target_file) === TRUE) {
                $slides_text = '';
                foreach (range(1, 20) as $i) {
                    $slide = $zip->getFromName("ppt/slides/slide{$i}.xml");
                    if ($slide) {
                        $slides_text .= strip_tags($slide) . "\n";
                    }
                }
                $zip->close();
                $text = substr($slides_text, 0, 1500);
                $prompt .= "\n\n--- PPTX Content from ($filename) ---\n$text\n\nPlease summarize or analyze.";
            }
        }

        // Acknowledge PDF or unsupported
        elseif (in_array($file_ext, $pdf_extensions)) {
            $prompt .= "\n\nA PDF file was uploaded ($filename). Please respond based on the user's prompt.";
        } else {
            $prompt .= "\n\nThe file $filename was uploaded, but its format is not supported.";
        }
    }

    // Send to ChatGPT
    $chat_response = getChatGPTResponse($prompt, $model, $base64Image, $imageExt);
    $response['chat_response'] = $chat_response;

    // DALL·E image generation if checked
    if (!empty($_POST['generate_image'])) {
        $image_result = generateImageFromPrompt($prompt);
        if (!empty($image_result['data'][0]['url'])) {
            $response['image_url'] = $image_result['data'][0]['url'];
        }
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'chat_response' => 'ERROR: ' . $e->getMessage(),
        'debug' => $response['debug']
    ]);
}
