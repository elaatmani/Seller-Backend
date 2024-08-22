<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .email-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .email-header img {
            max-width: 200px;
            height: auto;
        }
        .email-header h1 {
            margin: 0;
            color: #333;
        }
        .email-content {
            margin-bottom: 20px;
        }
        .email-content p {
            margin: 0;
            margin-bottom: 10px;
            color: #555;
        }
        .email-footer {
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="https://codsquad.net/images/logo.png" alt="Logo">
            <h1>Sourcing # {{ $sourcingId }} has been updated with {{ $statusChanged }}.</h1>
        </div>
        <div class="email-content">
            <p><strong>Sourcing ID:</strong> {{ $sourcingId }}</p>
            <p><strong>Product Name:</strong> {{ $productName }}</p>
            <p><strong>Product URL:</strong> <a href='{{ $productUrl }}'>{{ $productUrl }}</a></p>
        </div>
    </div>
</body>
</html>