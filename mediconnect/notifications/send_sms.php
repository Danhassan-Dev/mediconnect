<?php
/**
 * Provider adapter placeholder.
 * Implement this function only after selecting an SMS gateway and reviewing
 * its official API documentation. Never hard-code API keys here.
 */
function send_notification_sms(array $config, string $to, string $message): void
{
    if (empty($config['enabled'])) {
        throw new RuntimeException('SMS is disabled: select and configure an SMS gateway first.');
    }
    throw new RuntimeException('SMS gateway adapter has not been implemented yet.');
}
