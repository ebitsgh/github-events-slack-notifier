# GitHub Events Slack Notifier

This project posts GitHub webhook events to a Slack Incoming Webhook.

## Security: Preventing Abuse

This endpoint now verifies GitHub webhook signatures using HMAC SHA-256 (header `X-Hub-Signature-256`) and a shared secret. Requests without a valid signature are rejected with 401.

Steps:
- Set `WEBHOOK_SECRET` in your environment/.env.
- Configure the exact same secret in your GitHub repository/org webhook settings under "Secret".
- GitHub will sign payloads; the app verifies them before posting to Slack.

## Environment variables (.env)

This project supports loading environment variables from a local `.env` file using `vlucas/phpdotenv`.

1. Install dependencies (in your PHP environment):

   composer install

2. Create your `.env` from the example and set your variables:

   cp .env.example .env
   # then edit .env

```
SLACK_WEBHOOK_URL="https://hooks.slack.com/services/XXX/YYY/ZZZ"
WEBHOOK_SECRET="your-shared-secret"
```

3. Deploy or run a web server that points to this project; configure your GitHub webhook to send events to your public URL (POST). In your GitHub webhook configuration, set the same "Secret" as `WEBHOOK_SECRET`.

If you prefer not to use `.env`, you can set these variables in your hosting environment; the app will pick them up.

### Manual testing (optional)

You can simulate a GitHub request:

```
BODY='{"zen":"Keep it logically awesome."}'
SECRET='your-shared-secret'
SIG="sha256=$(printf "%s" "$BODY" | openssl dgst -sha256 -hmac "$SECRET" | sed 's/^.* //')"

curl -i \
  -X POST \
  -H "Content-Type: application/json" \
  -H "X-GitHub-Event: ping" \
  -H "X-Hub-Signature-256: $SIG" \
  --data "$BODY" \
  https://your-server.example.com/path/to/index.php
```
