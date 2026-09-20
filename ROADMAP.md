# Forms Roadmap

## 1.0.0 — stable foundation

- [x] Independent form builder with no nexForm/nexPro dependency.
- [x] Multiple forms and reusable starter templates.
- [x] Core field types, validation, required consent and informational fields.
- [x] Database storage and optional email notification.
- [x] CSRF, honeypot, minimum-fill-time and Spam-X integration.
- [x] Stored submission administration and CSV export.
- [x] `[forms:slug]` autotag.
- [x] Geeklog 2.1.1–2.2.2 and PHP 5.6–8.x transition compatibility.
- [x] Static `plugin.json` metadata manifest.
- [x] Shared capability declaration.
- [x] `forms.list` bounded read service.
- [x] `forms.schema.read` bounded read service.
- [x] `dashboard.summary` for Eclipse and other administration consumers.
- [x] Installable archive generated in `dist/` with a dot-file safety check.

## Next

### 1.1

- Per-form access controls and clearer visibility policy.
- Optional reCAPTCHA integration through Geeklog's native callback.
- Improved submission filtering, pagination and export controls.
- Optional notification templates and confirmation messages.
- Formal service tests for Agent/Hub/Eclipse consumers.

### Later

- Conditional fields.
- Multi-page forms.
- Import/export of form definitions.
- Optional file-upload fields with explicit persistent-storage rules.
- Additional authorized actions only after a shared action/permission contract is stable.
- Consider `forms.submit` machine action only with explicit authorization, CSRF/action semantics and abuse controls.
- Consider `forms.submissions.read` only as an administrator-authorized capability; it must never become public discovery data.
- Visual form builder if it can remain accessible and maintainable.

## Interoperability principles

Forms remains authoritative for its definitions, validation, submission storage, permissions and privacy rules. Agent, Hub, Eclipse and future consumers must use the shared capability/services contract rather than private SQL tables.
