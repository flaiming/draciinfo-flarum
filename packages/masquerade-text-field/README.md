# Masquerade Text Field

A [Flarum 2](https://flarum.org) extension that adds a **formatted text** field type
(multi-line, Markdown) to [FoF Masquerade](https://github.com/FriendsOfFlarum/masquerade) 3.x profiles.

- Admins pick "Formatted text" as the field type in the Masquerade settings.
- Users get a textarea with a live preview in their profile editor.
- Answers are stored as source text and rendered on the server with s9e
  TextFormatter: Markdown (Litedown), autolinks, tables, typography and a few
  attribute-less inline HTML tags (`b`, `i`, `strong`, …); single line breaks are
  kept. There are no media embeds (they fetch remote URLs) and other HTML is
  escaped. Links get `rel="ugc nofollow noopener"`.
- Text fields always use the validation `string|max:10000`.

It replaces the `text` field type of the old fork `flaiming/masquerade` (branch
`text-field-1.0`, Flarum 1.x). On enabling, a migration converts answers the fork
stored as s9e XML back to source text, so existing profiles keep their content.
Markdown renders as before; the fork's extra `<br>` between paragraphs and list
items is gone, and raw HTML beyond the tags above (unused on draci.info) is
now shown as text.

## Development

```sh
cd js
npm install
npm run build   # or: npm run dev
```

The built `js/dist` is committed.
