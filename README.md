Slack
===========
*Simple Slack integration for Phproject*

### Installation
Clone the repo into the app/plugins/ directory of an existing Phproject installation.

### Slack Setup

1. Create a [Slack App](https://api.slack.com/slack-apps)
2. Copy the Verification Token (under Settings > App Credentials) into your Phproject site's Administration > Plugins > Slack configuration
3. Enable the Event Subscriptions feature and add the Request URL from your Phproject site
4. Subscribe to the `link_shared` workspace event
5. Add the `links:write` permission scope on the OAuth & Permissions page
5. Add your Phproject site's domain to the App Unfurl Domains
6. Install the app to your workspace from the Basic Information tab
7. Copy the OAuth Access Token (under Features > OAuth & Permissions) into your Phproject site's Administration > Plugins > Slack configuration
