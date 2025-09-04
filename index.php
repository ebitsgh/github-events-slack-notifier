<?php
require __DIR__ . '/vendor/autoload.php';

use App\GithubEventsSlackNotifier\EnvLoader;
use App\GithubEventsSlackNotifier\GitHubEventMessageFormatter;
use App\GithubEventsSlackNotifier\SlackNotifier;
use App\GithubEventsSlackNotifier\GitHubSignatureVerifier;

// Enforce POST only
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Method Not Allowed';
    exit;
}

// Load environment variables from .env if present
EnvLoader::load(__DIR__);

$slackWebhookUrl = EnvLoader::get('SLACK_WEBHOOK_URL');
$webhookSecret   = EnvLoader::get('WEBHOOK_SECRET');

if (!$slackWebhookUrl) {
    http_response_code(500);
    echo "Missing SLACK_WEBHOOK_URL in environment (.env or server env).";
    exit;
}

if (!$webhookSecret) {
    http_response_code(500);
    echo "Missing WEBHOOK_SECRET in environment (.env or server env). Configure the same secret in your GitHub webhook settings.";
    exit;
}

$headers = function_exists('getallheaders') ? getallheaders() : [];
$event   = $headers['X-GitHub-Event'] ?? ($_SERVER['HTTP_X_GITHUB_EVENT'] ?? null);
$signature = $headers['X-Hub-Signature-256'] ?? ($_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? null);

$rawBody = file_get_contents('php://input');

// Verify signature before parsing JSON
if (!GitHubSignatureVerifier::verify($rawBody, $signature, $webhookSecret)) {
    http_response_code(401);
    echo 'Invalid signature';
    exit;
}

$payload = json_decode($rawBody, true);
if (!$event || !is_array($payload)) {
    http_response_code(400);
    echo "Invalid request";
    exit;
}

$formatter = new GitHubEventMessageFormatter();
$message = $formatter->format($event, $payload);

$notifier = new SlackNotifier($slackWebhookUrl);
$notifier->send($message);

http_response_code(200);
echo "OK";
