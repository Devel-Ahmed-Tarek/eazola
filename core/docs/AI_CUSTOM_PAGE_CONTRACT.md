# AI Custom Page JSON Contract

The AI endpoint for custom pages returns one strict JSON object.

## Required top-level keys

- `entity_name` string
- `page_title` string
- `page_summary` string
- `fields` array
- `sections` array
- `actions` array
- `dashboard_view` object
- `ui_bindings` array

## Field item shape

Each item inside `fields`:

- `name` (snake_case, unique)
- `label` (human readable)
- `type` (`text|email|number|date|datetime-local|tel|select|textarea`)
- `required` (boolean)
- `placeholder` (string)
- `options` (array of strings; used for `select`)

## Example

```json
{
  "entity_name": "custom_booking",
  "page_title": "Book a Consultation",
  "page_summary": "Submit your booking request and track latest entries.",
  "fields": [
    { "name": "full_name", "label": "Full Name", "type": "text", "required": true, "placeholder": "Your name", "options": [] },
    { "name": "email", "label": "Email", "type": "email", "required": true, "placeholder": "name@example.com", "options": [] },
    { "name": "booking_date", "label": "Booking Date", "type": "date", "required": true, "placeholder": "", "options": [] }
  ],
  "sections": [],
  "actions": ["create", "list"],
  "dashboard_view": {
    "columns": ["full_name", "email", "booking_date"],
    "default_sort": "latest"
  },
  "ui_bindings": []
}
```

## Route bindings used by backend

- Create: `POST /ai-custom-page/submit`
- Public list: `GET /ai-custom-page/records`
- Admin page submissions: `GET /admin-home/pages/ai-submissions`
