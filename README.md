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
mailer_template_dir = "%base_dir%/mailer_templates"

; Directory where queued mails are stored
mailer_queue_dir = "%base_dir%/mailer_queue"
```

## Triggering templates

Templates are stored in the `mailer_templates` dir postfixed with the donor
event or state name that should trigger message creation. For example:

* `foo_template.NEW_MANDATE`
* `bar_template.REVOKED`
* `baz_template.DONOR_POSTAL_ADDRESS_UPDATED`

## Writing templates

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
<p>Hi {{getDonor.getName}}, you are now a donor.</p>
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
{{# getDonor.getComment}}
    There is a comment, so this mail will be generated..
{{/ getDonor.getComment}}
```

### An example temple on address updates

When listening to update events make sure to access the updated value directly
from the event object (use `getNewPostalAddress` instead of `getDonor.getPostalAddress`).

```
---
from: some@mail.com
Subject: Address updated
---
<p>Hi {{getDonor.getName}}!</p>

<p>We have updated your address</p>

<pre>
{{getNewPostalAddress}}
</pre>
```

## The mail queue

Plugin registers four giroapp commands.

To inspect the current mail queue

```shell
giroapp mailer:status
```

To send mails in queue

```shell
giroapp mailer:send
```

To clear the queue without sending mails

```shell
giroapp mailer:clear
```

To remove all mails to recipient without sending them

```shell
giroapp mailer:rm <recipient>
```
