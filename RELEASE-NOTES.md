# Forms 1.0.0 Release Notes

Forms 1.0.0 is the first stable release of the independent Forms plugin for Geeklog.

## Highlights

- Build and manage multiple forms without nexForm or nexPro.
- Embed active forms with `[forms:slug]` or use their explicit public URL.
- Store submissions, send optional email notifications and export stored data as CSV.
- Use contact, feedback, event registration, support request and quote request starter templates.
- Support text, email, textarea, select, radio, checkbox, multiple checkboxes, date, time, date/time, number, phone, URL, consent, heading and informational fields.
- Protect submissions with Geeklog CSRF tokens, honeypot, minimum-fill-time checks and Spam-X integration when available.

## Geeklog interoperability

1.0.0 introduces the shared provider contract recommended by the Geeklog memorandum:

- `plugin_getcapabilities_forms()`
- `forms.list`
- `forms.schema.read`
- `dashboard.summary`

This lets Agent and Hub discover Forms capabilities and lets Eclipse render Forms administration metrics without direct access to Forms tables.

The exposed form-list and form-schema services are read-only and deliberately exclude submission values, recipients, IP hashes and user-agent data.

## Metadata and packaging

- Version normalized to 1.0.0 in installer, runtime and `plugin.xml`.
- Added the static `plugin.json` metadata manifest.
- Distribution workflow includes the manifest, roadmap and release notes.
- Distribution workflow rejects dot-prefixed files or paths for Geeklog 2.2.2 compatibility.
- The installable archive is generated as `dist/forms-1.0.0.zip`.

## Compatibility

- Geeklog 2.1.1 through 2.2.2.
- PHP 5.6 through PHP 8.x.
- MySQL / mysqli.

## Upgrade

Existing 0.x installations can use Geeklog's normal plugin upgrade flow. The upgrade refreshes Forms configuration and updates the registered plugin version to 1.0.0 without changing the existing Forms data model.
