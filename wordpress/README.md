# Smart Logic Systems — WordPress

A block (Gutenberg) theme version of the static site in `../static`, plus a local
WordPress environment to run it.

## Run locally

Needs Podman with `podman-compose` (or Docker with `docker compose`).

```sh
./setup.sh
```

This starts WordPress and MariaDB, installs WordPress on first run, activates the
`sls` theme and creates the Home, Industries, Solutions, Articles and Contact Us
pages from the theme's patterns.

- Site: http://localhost:8090 (set `SLS_WP_PORT` to change the port)
- Admin: http://localhost:8090/wp-admin, user `admin`, password in `.admin-password`

Re-running `./setup.sh` resets those five pages to the patterns. Use
`SLS_SEED_KEEP=1 ./setup.sh` to keep edits made in the editor.
Stop with `podman-compose down`; add `-v` to also delete the database and uploads.

The theme folder is mounted read-only into the container, so file edits show up on
reload.

## Cloud (dev)

`infra/` deploys this theme to an Azure VM as a Podman quadlet:
https://dev.smartlogicsystems.com. See [infra/README.md](infra/README.md).

## How the theme is built

`wp-content/themes/sls/`

| Path | What it holds |
| --- | --- |
| `css/styles.css`, `js/main.js`, `assets/` | Copied from the static site. `styles.css` is unchanged; `main.js` has two small selector changes. |
| `css/wp.css` | The few WordPress-specific styles (admin bar, highlight formats, blog templates). |
| `css/editor.css` | Makes the fixed header and decorative layers behave inside the editor. |
| `patterns/` | One pattern per page section. Pages are composed from these, and editors can insert them from the **Smart Logic Systems** pattern category. |
| `templates/`, `parts/` | Page, blog, single post and 404 templates; header and footer template parts. |
| `blocks/` | Theme blocks: Site Header, Brand Lockup, Social Links, Icon, Solution Finder, Solutions Directory, Contact Links, Contact Form Panel, Subscribe Form. Server-rendered (`render.php`); one plain-JS editor script, no build step. |
| `inc/markup.php` | Adjusts core block output so it matches the HTML `styles.css` was written for. |

### Editing content

Most content is core blocks (Group, Heading, Paragraph, List, Buttons, Image,
Details) carrying the static site's class names under **Advanced › Additional CSS
class(es)**. Keep those classes when editing or duplicating blocks. A few marker
classes change the output element:

- `tag-ul`, `tag-ol`, `tag-li`, `tag-span`, `tag-nav` render a Group/Paragraph as that element (an Image with `tag-li` renders as `<li><img></li>`).
- `inner-span` wraps a Paragraph's text in a `<span>`; `li-span` does the same for each List item.
- `is-decorative` adds `aria-hidden="true"`.
- A Details block with class `faq__item` renders as an FAQ accordion item; "Open by default" opens it.

The header menu is **Appearance › Menus › Primary navigation**. Directory rows, finder
links, contact reasons and form endpoints are edited in each theme block's sidebar.
Headings have **Gold underline**, **Green underline** and **Oblique emphasis**
formats in the rich-text toolbar.

### Not carried over

- The Industries "Case Studies" section, which is hidden on the static site.
- Form handling works as on the static site: the contact form opens the visitor's
  email app and the newsletter form reports that it isn't connected, until an
  endpoint is set on the block.
