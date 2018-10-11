# giroapp-mailer-plugin

Plugin for sending mails on giroapp events.

## Installation

1. Download the latest phar archive from the github
   [releases](https://github.com/byrokrat/giroapp-mailer-plugin/releases) page.
1. Place the phar in the `plugins` directory of you giroapp user directory.
1. Copy the following snippet to `giroapp.ini` and edit to your needs.

```ini
; Mailer smtp authentication string
mailer_smtp_string = "smtp://user:pass@host/"

; Directory where mail templates are stored
; Should be an absolute path
mailer_template_dir = "templates"

; Directory where queued mails are stored
; Should be an absolute path
mailer_queue_dir = "queue"
```

## Templates

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
    There is a comment, so this mail will be generated..
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

## The mail queue

Plugin registers two giroapp commands.

To inspect the current mail queue use

```shell
giroapp mailer:status
```

To send mails in queue use

```shell
giroapp mailer:send
```
