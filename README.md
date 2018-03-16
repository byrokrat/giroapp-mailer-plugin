# giroapp-mailer-plugin

Plugin for sending mails on giroapp events. Possible merge with giroapp master.

## Installation

1. Clone repo in your giroapp user directory.
1. From install dir run `composer install --no-dev`.
1. Copy `GiroappMailerPlugin.php` to giroapp `plugins` directory.
1. Edit your settings in `GiroappMailerPlugin.php`. Specifically the `SMTP` setting
   needs a value.
1. Create the `templates` and `queue` directories in your giroapp user dir.

## Usage

Templates are html formatted mustache templates with a YAML frontmatter.
Supported frontmatter variables are (case insensitive):

* `Subject`
* `From`
* `ReplyTo`
* `To` (single address or array, defaults to donor mail address if omitted)
* `Cc` (single address or array)
* `Bcc` (single address or array)

A template may look something like this:

```
---
to: {{getEmail}}
from: some@mail.com
ReplyTo: some@other.mail.com
bcc: keep-a-copy@here.com
subject: Welcome as a donor
---
<p>Hi {{getName}}, you are now a donor.</p>
```

### Ignored templates

Renderings that return empty bodies are ignored when generating mails. Use this
feature to make a mail conditional on some donor feature.

```
---
from: some@mail.com
subject: Only sent if there is a commment in donor
---
{{# getComment}}
    There is a comment, so thing mail will be generated..
{{/ getComment}}
```

### Triggering templates

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
