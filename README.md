# Simple Google Tag Manager

In most cases, this'll be all you need to add Google Tag Manger to your WordPress site. If your site is complicated, then you'll want a different solution that is sufficiently complicated to complete your idiom, but if you're like me, and you just want to insert the GTM snippets on your site, this plugin is your huckleberry.

## Requirements

Your own Google Tag Manager account, as well as its Container ID. It probably looks something like: `GTM-Z3V1L`.

Plus, your theme should follow two particular WordPress standards. Those standards are two function calls:

- your theme calls `wp_head()` from within the <head> html element.
- your theme calls `wp_body_open()` right after the <body> element begins.

## Set up

- Install the plugin.
- Navigate to Admin -> Settings -> Google Tag Manager and enter your Container ID. *Important:* without a Container ID, the plugin does nothing.
- Optionally choose to defer loading.
- Hit Save.

## Validation

Load a WordPress page in your favorite web browser and view the source code. Search the code for `Google Tag Manager`. You should find two snippets, and each snippet should include your container ID.

## Questions

### What does the plugin do if I activate it but don't enter a Container ID?

Nothing. Well, nothing meaningful. If this is the case, disable the plugin until you're ready to add a Container ID.

### What's "defer loading"?

It instructs the browser not to load GTM and everything that comes with it until the user interacts with the page. Interaction could be moving the cursor, a click, a key press, or something else. But if none of that happens, GTM doesn't load.

### What is the benefit of defer loading?

Your Page Speed scores will go up and you will reap all the associated benefits.

### Will defer loading affect my analytics stats?

Yes. It'll filter out bots and bounces which will probably lead to a drop in pageviews and sessions. If you're still leaning on those metrics and want to talk through options, email me at johnpennypacker@cosidigital.com, let's talk it through. 

### Can I override defer settings for a particular page?

Yes, you can add sgtm=nodefer to the URL and that'll cause GTM to load when the rest of the page loads regardless of what the user does.

