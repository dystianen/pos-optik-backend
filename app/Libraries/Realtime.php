<?php

namespace App\Libraries;

use Pusher\Pusher;

class Realtime
{
    /**
     * Trigger a real-time update event and invalidate relevant caches.
     * 
     * @param string $eventType The type of event (e.g., 'order-new', 'stock-update')
     * @return bool
     */
    public static function triggerUpdate(string $eventType): bool
    {
        // 1. Invalidate Dashboard Cache
        cache()->delete('dashboard_stats');

        // 2. Initialize Pusher
        $options = [
            'cluster' => env('pusher.cluster', 'ap1'),
            'useTLS'  => true
        ];

        $appId  = env('pusher.appId');
        $key    = env('pusher.key');
        $secret = env('pusher.secret');

        // If credentials are not set, just exit gracefully (silent fail for dev)
        if (empty($appId) || empty($key) || empty($secret)) {
            log_message('debug', 'Pusher credentials not set. Skipping real-time trigger.');
            return false;
        }

        try {
            $pusher = new Pusher($key, $secret, $appId, $options);

            // 3. Trigger Event
            $pusher->trigger('pos-channel', 'dashboard-update', [
                'type' => $eventType,
                'time' => date('Y-m-d H:i:s')
            ]);

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Pusher Error: ' . $e->getMessage());
            return false;
        }
    }
}
