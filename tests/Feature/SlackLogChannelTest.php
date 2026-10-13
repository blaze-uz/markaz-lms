<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * d767d9d1 put a live Slack webhook into config/logging.php as a literal, so
 * every site posted to the same URL and it could only be changed with a
 * deploy. These tests keep the webhook in each site's .env.
 */
class SlackLogChannelTest extends TestCase
{
    public function test_slack_channel_takes_its_webhook_from_env(): void
    {
        $fresh = $this->loggingConfigWith('https://hooks.example.test/services/T0/B0/test');

        $this->assertSame(
            'https://hooks.example.test/services/T0/B0/test',
            $fresh['channels']['slack']['url'],
        );
    }

    public function test_no_source_file_hardcodes_a_slack_webhook(): void
    {
        $offenders = [];

        foreach ([app_path(), config_path(), database_path(), base_path('routes')] as $dir) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                if (str_contains(file_get_contents($file->getPathname()), 'hooks.slack.com/services/')) {
                    $offenders[] = str_replace(base_path().'/', '', $file->getPathname());
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Put Slack webhooks in LOG_SLACK_WEBHOOK_URL, not in: '.implode(', ', $offenders),
        );
    }

    /**
     * Load config/logging.php as it reads with the given LOG_SLACK_WEBHOOK_URL,
     * then put the environment back. $_SERVER is set too, because env() reads
     * it first and a developer's .env may already hold a webhook there.
     */
    private function loggingConfigWith(string $webhook): array
    {
        $key = 'LOG_SLACK_WEBHOOK_URL';
        $saved = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];

        putenv("{$key}={$webhook}");
        $_ENV[$key] = $_SERVER[$key] = $webhook;

        try {
            return require base_path('config/logging.php');
        } finally {
            putenv($saved[0] === false ? $key : "{$key}={$saved[0]}");

            if ($saved[1] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $saved[1];
            }

            if ($saved[2] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $saved[2];
            }
        }
    }
}
