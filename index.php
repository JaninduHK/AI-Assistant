<?php
// index.php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #responseArea {
            min-height: 300px;
            max-height: 600px;
            overflow-y: auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        footer {
            text-align: center;
            margin-top: 60px;
            padding: 20px;
            font-size: 0.95rem;
            color: #888;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">AI Assistant (ChatGPT & DALL·E + Vision)</h2>
    <form id="aiForm" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="model" class="form-label">Select Model</label>
            <select class="form-select" id="model" name="model">
                <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                <option value="gpt-4">GPT-4</option>
                <option value="gpt-4-turbo">GPT-4 Turbo (Vision)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="prompt" class="form-label">Enter your prompt</label>
            <textarea class="form-control" name="prompt" id="prompt" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="userfile" class="form-label">Optional File Upload</label>
            <input type="file" class="form-control" name="userfile" id="userfile" accept=".txt,.csv,.md,.docx,.pptx,.pdf,.jpg,.jpeg,.png,.gif,.webp">
        </div>
        <div class="mb-3">
            <label class="form-check-label">
                <input type="checkbox" id="generateImage" class="form-check-input"> Generate Image (DALL·E)
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <hr>
    <div id="responseArea" class="mt-4"></div>
    <div id="imageResult" class="mt-4"></div>
</div>

<footer>
    Made with ❤️ by Shaggy
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $("#aiForm").submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append("generate_image", $('#generateImage').is(':checked') ? 1 : 0);

        $("#responseArea").html("<div class='text-center'>Loading...</div>");

        $.ajax({
            url: "process.php",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                console.log("AJAX Response:", res);
                $('#responseArea').html("<pre>" + res.chat_response + "</pre>");
                if (res.image_url) {
                    $('#imageResult').html("<img src='" + res.image_url + "' class='img-fluid mt-3'>");
                }
            },
            error: function (xhr, status, error) {
                $('#responseArea').html("<div class='text-danger'>Error: " + error + "</div>");
            }
        });
    });
</script>
</body>
</html>
