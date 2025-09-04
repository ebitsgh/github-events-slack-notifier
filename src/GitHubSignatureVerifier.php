<?php

namespace App\GithubEventsSlackNotifier;

class GitHubSignatureVerifier
{
    /**
     * Verify GitHub webhook signature using HMAC SHA-256.
     *
     * @param string $rawBody Raw request body as received.
     * @param string|null $providedSignature Header value from X-Hub-Signature-256 (e.g., "sha256=abc...").
     * @param string $secret Shared secret configured in GitHub and server.
     * @return bool True if signature matches; false otherwise.
     */
    public static function verify(?string $rawBody, ?string $providedSignature, string $secret): bool
    {
        if ($rawBody === null || $rawBody === '' || !$providedSignature) {
            return false;
        }

        // Expected format: sha256=<hexdigest>
        if (!str_starts_with($providedSignature, 'sha256=')) {
            return false;
        }
        $sig = substr($providedSignature, 7);
        if ($sig === '') {
            return false;
        }

        $computed = hash_hmac('sha256', $rawBody, $secret);
        // timing-attack safe compare
        return hash_equals($computed, $sig);
    }
}
