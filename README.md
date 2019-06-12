# giroapp-mailer-plugin

[![Build Status](https://img.shields.io/travis/byrokrat/giroapp-mailer-plugin/master.svg?style=flat-square)](https://travis-ci.org/byrokrat/giroapp-mailer-plugin)

Plugin for sending mails on donor state transitions.

## Installation

1. Download the latest phar archive from the github
   [releases](https://github.com/byrokrat/giroapp-mailer-plugin/releases) page.
1. Place the phar in the `plugins` directory of you giroapp user directory.
1. Copy the following snippet to `giroapp.ini` and edit to your needs.

```ini
; Mailer smtp authentication string
mailer_smtp_string = "smtp://user:pass@host/"

; Directory where mail templates are stored
mailer_template_dir = "%base_dir%/templates"

; Directory where queued mails are stored
mailer_queue_dir = "%base_dir%/queue"
```

## Templates

Templates are html formatted mustache templates with a YAML frontmatter.
Supported frontmatter variables are (case insensitive):

* `Subject`
* `From`
* `ReplyTo`
* `To` (single address or array, note that mail is always sent to donor mail address)
* `Cc` (single address or array)
* `Bcc` (single address or array)

A simple template may look like:

```
---
from: some@mail.com
ReplyTo: some@other.mail.com
bcc: keep-a-copy@here.com
subject: Welcome as a donor
---
<p>Hi {{getName}}, you are now a donor.</p>
```

### Ignored donors

Donors that does not have an email address are ignored when generating mails.

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

Templates are stored in the `templates` dir postfixed with the donor state name
that should trigger message creation. For example:

* `foo_template.MANDATE_APPROVED`
* `bar_template.INACTIVE`
* `baz_template.PAUSED`

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
