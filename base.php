<?php

/**
 * @package  Slack
 * @author   Alan Hardman <alan@phpizza.com>
 * @version  0.1.0
 */

namespace Plugin\Slack;

class Base extends \Plugin
{
    const CONFIG_KEY_TOKEN = "site.plugins.slack.token";
    const CONFIG_KEY_ACCESS_TOKEN = "site.plugins.slack.access_token";

    /**
     * Initialize the plugin
     */
    public function _load()
    {
        $f3 = \Base::instance();
        $f3->route("POST /slack-post", "Plugin\Slack\Controller->post");
    }

    /**
     * Notify user that plugin is not installed
     */
    public function _install()
    {
        $f3 = \Base::instance();
        $f3->set("error", "Slack plugin is not set up. Add your Slack App's tokens in the plugin configuration.");
    }

    /**
     * Check if plugin is installed
     * @return bool
     */
    public function _installed()
    {
        $f3 = \Base::instance();
        return $f3->exists(self::CONFIG_KEY_TOKEN) && $f3->exists(self::CONFIG_KEY_ACCESS_TOKEN);
    }

    /**
     * Generate page for admin panel
     */
    public function _admin()
    {
        $f3 = \Base::instance();
        if ($f3->get('POST.token')) {
            \Model\Config::setVal(self::CONFIG_KEY_TOKEN, trim($f3->get('POST.token')));
        }
        if ($f3->get('POST.access_token')) {
            \Model\Config::setVal(self::CONFIG_KEY_ACCESS_TOKEN, trim($f3->get('POST.access_token')));
        }
        echo \Helper\View::instance()->render("slack/view/admin.html");
    }
}
