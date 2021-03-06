<?php

/**
 * @package Slack
 */

namespace Plugin\Slack;

class Api extends \Prefab
{
    const BASE_URL = 'https://slack.com/api/';

    /**
     * Call an API method
     *
     * @param string $method
     * @param array  $data
     * @return \StdClass
     */
    public function call($method, array $data = [])
    {
        $f3 = \Base::instance();
        $url = self::BASE_URL . $method;
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $f3->get(Base::CONFIG_KEY_ACCESS_TOKEN) . "\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        if ($f3->get('DEBUG')) {
            $log = new \Log("slack.log");
            $log->write("[API] Calling {$method}: " . http_build_query($data));
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response);
        if (isset($result->ok) && $result->ok === false) {
            $log = new \Log("slack.log");
            $log->write("[API] Error response: " . $response);
        } elseif ($f3->get('DEBUG') && isset($result->warning)) {
            $log->write("[API] Warning: " . $result->warning);
        }
        return $result;
    }

    /**
     * Unfurl a chat message
     *
     * @param string $channel
     * @param string $ts
     * @param array  $unfurls
     * @return \StdClass
     */
    public function chat_unfurl($channel, $ts, array $unfurls)
    {
        return $this->call('chat.unfurl', [
            'channel' => $channel,
            'ts' => $ts,
            'unfurls' => json_encode($unfurls),
        ]);
    }
}
