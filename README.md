# Forms for Geeklog 0.3.2

Experimental first test release of an independent form builder for Geeklog.

## Compatibility target

- Geeklog 2.1.1 through 2.2.2
- PHP 5.6 through PHP 8.x (code intentionally avoids syntax newer than PHP 5.6)
- MySQL / mysqli

## 0.2.1 features

- Independent plugin: no dependency on nexPro, nexForm, Formmail, Contact or Polls.
- Create multiple forms.
- Field types: text, email, textarea, select, radio, checkbox, date and number.
- Required fields, ordering, placeholder and help text.
- Anonymous submissions can be enabled or disabled per form.
- Store submissions in the database or disable storage.
- Optional email notification per form.
- Honeypot and minimum-fill-time anti-spam protection.
- Uses Geeklog CSRF tokens and Spam-X integration when available.
- No public catalogue: forms are exposed only by explicit URL or autotag.
- Administration list, form editor, field editor and latest submissions view.
- English and French language files.

## Installation

Install the ZIP from Geeklog's Plugins administration page.

After installation open Command and Control > Forms.

1. Create a form.
2. Save it.
3. Reopen the form and add fields in its editor.
4. Use the public URL or embed it with `[forms:slug]`.

## Important test-release limitations

This is deliberately a focused 0.3.1 test foundation. It does not yet include file uploads, conditional logic, multi-page forms, import/export of form definitions, per-form Geeklog ACLs, advanced reporting, Poll-style vote controls, or a visual drag-and-drop builder.

Email uses the Geeklog site address as the sender for deliverability. A visitor email entered in the form is included in the message body; it is not spoofed as the SMTP From address.

## Suggested tests

- Install/uninstall on Geeklog 2.1.1 + PHP 5.6.
- Install/uninstall on Geeklog 2.2.2 + PHP 8.1/8.3.
- Anonymous and authenticated submissions.
- Store-only, email-only, and store+email forms.
- Required fields and invalid email validation.
- Spam honeypot and fast-submit rejection.
- French and English UI.


## 0.2.1 highlights

- Explicit required-field legend and localized validation messages.
- `[forms:slug]` autotag to embed active forms in Geeklog content.
- Contact and Feedback starter templates.
- Duplicate forms and fields.
- Move fields up/down directly from the editor.
- Friendlier select/radio options: one visible label per line is enough; `value|Label` remains supported.
- CSV export of stored submissions (UTF-8, semicolon separated).
- Localized field type names and clearer editor help.


## 0.2.1 admin usability

- Getting-started documentation on the Forms administration home page.
- Reorganized form editor with Identity, Behaviour, Fields, and Stored submissions sections.
- Direct preview, public URL, and autotag guidance in the editor.
- Contextual help for each important form and field setting.


## 0.3.1 additions

- New field types: phone, URL, time, date/time, multiple checkboxes, required consent, heading and informational text.
- Detailed submission view with visitor/user information and labelled values.
- Secure individual submission deletion with Geeklog CSRF tokens.
- Getting Started documentation moved below the daily administration area.


## 0.3.1

- The template area is permanently visible on the Forms administration page.
- Added demo templates: Event registration, Support request and Quote request, alongside Contact and Feedback.
- Template cards explain the purpose of each example before creation.
- Improved hover/focus contrast for administration buttons to remain readable with dark Geeklog themes.


## 0.3.2

- More compact template cards.
- Smaller Create/Créer buttons for starter templates.
- Hover/focus contrast retained for dark administration themes.
