<?php

namespace App\GithubEventsSlackNotifier;

class GitHubEventMessageFormatter
{
    public function format(string $event, array $payload): string
    {
        switch ($event) {
            case 'repository':
                return sprintf(
                    "📦 Repository *%s* was *%s* by @%s",
                    $payload['repository']['full_name'] ?? 'unknown',
                    $payload['action'] ?? 'updated',
                    $payload['sender']['login'] ?? 'unknown'
                );
            case 'pull_request':
                $pr = $payload['pull_request'] ?? [];
                return sprintf(
                    "🔀 Pull Request *#%s* (%s) was *%s* by @%s",
                    $pr['number'] ?? 'N/A',
                    $pr['title'] ?? 'Untitled',
                    $payload['action'] ?? 'updated',
                    $payload['sender']['login'] ?? 'unknown'
                );
            case 'push':
                $repo = $payload['repository']['full_name'] ?? 'unknown';
                $commits = isset($payload['commits']) && is_array($payload['commits']) ? count($payload['commits']) : 0;
                $pusher = $payload['pusher']['name'] ?? 'unknown';
                return sprintf("🚀 Push to *%s* by @%s (%d commits)", $repo, $pusher, $commits);
            default:
                $repo = $payload['repository']['full_name'] ?? 'an org';
                return sprintf("ℹ️ Event *%s* happened in *%s*", $event, $repo);
        }
    }
}
