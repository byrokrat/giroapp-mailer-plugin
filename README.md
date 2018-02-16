# giroapp-mailer-plugin

Plugin for sending mails on giroapp events. Possible merge with giroapp master.

## Installation

1. Clone repo in your giroapp user directory
1. From install dir run `composer install --no-dev`
1. Copy `GiroappMailerPlugin.php` to giroapp `plugin` directory
1. Edit your settings in `GiroappMailerPlugin`. Specifically the `SMTP` setting
   needs a value.
1. Create the `templates` and `queue` directories in your giroapp user dir

## Creating templats

Templates are mustache templates with a yson frontmatter. Supported frontmatter
variables are:

* `subject`
* `from`
* `to` (defaults to donor mail address if omitted)

A template may look something like this:

```
---
to: {{getEmail}}
from: some@mail.com
subject: Welcome as a donor
---
Hi {{getName}}, you are now a donor.
```

Templates are stored in the `templates` dir postfixed with the event name
that should trigger message creation. Possible values are:

* `DONOR_ADDED`
* `DONOR_REMOVED`
* `MANDATE_APPROVED`
* `MANDATE_REVOKED`
* `MANDATE_INVALIDATED`

## Sending queued mails

Invoke the send script using something like

```shell
~/.giroapp/giroapp-mailer-plugin/send_mail.php
```
