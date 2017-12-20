<?php

/**
 * @package Slack
 */

namespace Plugin\Slack;

class Controller extends \Controller
{
    /**
     * Handle HTTP POST request
     * @param \Base $f3
     */
    public function post(\Base $f3)
    {
        $post = file_get_contents('php://input');
        $request = json_decode($post);
        if (!$request) {
            $f3->error(400);
            return;
        }
        if ($request->token != $f3->get(Base::CONFIG_KEY_TOKEN)) {
            $f3->error(403);
            return;
        }

        // Respond to URL verification challenge
        if ($request->type == 'url_verification') {
            $this->_printJson([
                'challenge' => $request->challenge
            ]);
            return;
        }

        // Handle events
        switch ($request->event->type) {
            case 'link_shared':
                $this->linkShared($request->event);
                break;
        }
    }

    /**
     * Handle link_shared event
     *
     * @link https://api.slack.com/events/link_shared
     *
     * @param \StdClass $event
     * @return void
     */
    protected function linkShared(\StdClass $event)
    {
        $f3 = \Base::instance();
        $baseUrl = $f3->get('site.url');
        $unfurls = [];
        foreach ($event->links as $link) {
            // Ignore links that don't match our Phproject base URL
            if (substr($link->url, 0, strlen($baseUrl)) != $baseUrl) {
                continue;
            }

            // Issue link
            if (preg_match('@issues/([0-9]+)@', $path, $matches)) {
                $issue = new \Model\Issue\Detail;
                $issue->load($matches[1]);
                if ($issue->id) {
                    $unfurls[$link] = [
                        'fallback' => $issue->name,
                        'title' => $issue->name,
                        'title_link' => "{$baseUrl}issues/{$issue->id}",
                        // 'text' => $issue->description,
                        'fields' => [
                            [
                                'title' => $f3->get('dict.cols.author'),
                                'value' => "<{$baseUrl}user/$issue->author_username|$issue->author_name>",
                                'short' => true,
                            ],
                            [
                                'title' => $f3->get('dict.cols.assignee'),
                                'value' => "<{$baseUrl}user/$issue->owner_username|$issue->owner_name>",
                                'short' => true,
                            ],
                            [
                                'title' => $f3->get('dict.cols.type'),
                                'value' => $issue->type_name,
                                'short' => true,
                            ],
                            [
                                'title' => $f3->get('dict.cols.status'),
                                'value' => $issue->status_name,
                                'short' => true,
                            ],
                            [
                                'title' => $f3->get('dict.cols.priority'),
                                'value' => $issue->priority_name,
                                'short' => true,
                            ],
                        ],
                        "ts" => strtotime($issue->created_date),
                        "footer" => $f3->get('site.name'),
                    ];
                }
                continue;
            }

            // User link
            if (preg_match('@user/([^/]+)@', $path, $matches)) {
                $user = new \Model\User;
                $user->load(['username = ?', $matches[1]]);
                if ($user->id) {
                    $unfurls[$link] = [
                        'fallback' => $user->name,
                        'title' => $user->name,
                        'title_link' => "{$baseUrl}user/{$user->username}",
                        'fields' => [
                            [
                                'title' => $f3->get('dict.username'),
                                'value' => $user->username,
                                'short' => true,
                            ],
                            [
                                'title' => $f3->get('dict.cols.email'),
                                'value' => $user->email,
                                'short' => true,
                            ],
                        ],
                        "footer" => $f3->get('site.name'),
                    ];
                }
                continue;
            }
        }
        if ($unfurls) {
            if ($f3->get("DEBUG")) {
                $log = new \Log("slack.log");
                $log->write("Unfurling URLs");
            }
            Api::instance()->chat_unfurl($event->channel, $event->message_ts, $unfurls);
        }
    }
}
