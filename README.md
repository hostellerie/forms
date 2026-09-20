# Forms for Geeklog 1.0.0

Forms is an independent form builder for Geeklog with reusable form definitions, stored submissions, email notifications and a shared interoperability surface for modern Geeklog consumers.

## Compatibility

- Geeklog 2.1.1 through 2.2.2
- PHP 5.6 through PHP 8.x
- MySQL / mysqli

## Main features

- Create and manage multiple forms.
- Field types: text, email, textarea, select, radio, checkbox, multiple checkboxes, date, time, date/time, number, phone, URL, consent, heading and informational text.
- Required fields, ordering, placeholders and help text.
- Anonymous submissions can be enabled or disabled per form.
- Store submissions in the database or disable storage.
- Optional email notification per form.
- Honeypot and minimum-fill-time anti-spam protection.
- Geeklog CSRF token protection and Spam-X integration when available.
- Forms are exposed only by explicit URL or the `[forms:slug]` autotag; there is no public catalogue.
- Administration list, form editor, field editor and stored submission views.
- Starter templates for Contact, Feedback, Event registration, Support request and Quote request.
- CSV export of stored submissions.
- English and French language files.

## Interoperability in 1.0.0

Forms follows the shared contracts documented in the Geeklog memorandum.

It declares the service role and these capabilities through `plugin_getcapabilities_forms()`:

- `forms.list`
- `forms.schema.read`
- `dashboard.summary`

The corresponding bounded services allow Agent, Hub, Eclipse and future consumers to discover active forms, read a form schema and display administration metrics without querying Forms private tables.

Stored submission values, recipients, IP hashes and user-agent data are deliberately not exposed by the public capability declaration.

## Installation

Install `forms-1.0.0.zip` from Geeklog's Plugins administration page.

After installation open **Command and Control > Forms**:

1. Create a form.
2. Save it.
3. Reopen the form and add fields.
4. Use its public URL or embed it with `[forms:slug]`.

## Security and privacy

Email uses the Geeklog site address as the sender for deliverability. A visitor email entered in the form is included in the message body and is not spoofed as the SMTP From address.

The 1.0 interoperability services expose form-definition metadata only. Submission data remains under Forms administration permissions.

## Release documentation

See [RELEASE-NOTES.md](RELEASE-NOTES.md) for the 1.0.0 release notes and [ROADMAP.md](ROADMAP.md) for planned work.
