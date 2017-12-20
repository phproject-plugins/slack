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
        $data['token'] = $f3->get(Base::CONFIG_KEY_TOKEN);

        $url = self::BASE_URL . $method;
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
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
