<?php

namespace App\GithubEventsSlackNotifier;

use GuzzleHttp\Client;

class SlackNotifier
{
    private Client $client;
    private string $webhookUrl;

    public function __construct(string $webhookUrl, ?Client $client = null)
    {
        $this->webhookUrl = $webhookUrl;
        $this->client = $client ?: new Client();
    }

    public function send(string $text): void
    {
        if ($text === '') {
            return;
        }
        $this->client->post($this->webhookUrl, [
            'json' => ['text' => $text],
        ]);
    }
}
