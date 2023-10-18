# Simple Google Tag Manager

In most cases, this'll be all you need to add Google Tag Manger to your WordPress site. If your site is complicated, then you'll want a different solution that is sufficiently complicated to complete your idiom, but if you're like me, and you just want to insert the GTM snippets on your site, this plugin is your huckleberry.

## Requirements

Your own Google Tag Manager account, as well as its Container ID. It probably looks like: `GTM-Z3V1L`.

And your theme should follow two particular WordPress standards for this plugin, those standards include two function calls:

- your theme calls `wp_head()` from within the <head> html element.
- your theme calls `wp_body_open_()` right after the <body> element begins.

## Set up

- Install the plugin.
- Navigate to Admin -> Settings -> Google Tag Manager and enter your Container ID.
- Hit Save.

## Validation

Load a WordPress page in your favorite web browser and view the source code. Search the code for Google Tag Manager. Verify that the code snippets include your container ID.

## Questions

Ask me anything.  