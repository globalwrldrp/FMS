<?php
// Load webhook configuration
$configFile = 'webhooks.json';
if (!file_exists($configFile)) {
    die('Configuration file not found.');
}

$config = json_decode(file_get_contents($configFile), true);
if (!$config) {
    die('Error reading configuration file.');
}

// Extract data from configuration
$webhookUrl = $config['webhook_url'] ?? '';
$footer = $config['footer'] ?? 'Default Footer';
$title = $config['title'] ?? 'Default Title';
$thumbnail = $config['thumbnail'] ?? '';

// Prepare the webhook payload
$data = [
    "embeds" => [
        [
            "title" => $title,
            "thumbnail" => [
                "url" => $thumbnail
            ],
            "footer" => [
                "text" => $footer
            ]
        ]
    ]
];

// Send the webhook notification
$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($webhookUrl, false, $context);

if ($response === FALSE) {
    die('Error sending notification.');
}

echo 'Notification sent successfully!';
?>
