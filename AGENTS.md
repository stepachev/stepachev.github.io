# AGENTS.md

This project is a personal blog built with [Eleventy](https://www.11ty.dev/).

## Project Overview
- **Input Directory**: `src`
- **Output Directory**: `_site`
- **Template Engines**: Markdown for content, Nunjucks (njk) for layouts and includes.

## Tech Stack
- **Static Site Generator**: Eleventy (v3+)
- **Styling**: Vanilla CSS (located in `src/css/`)
- **Date Handling**: Luxon

## Development Commands
- `npm run serve`: Starts the development server with hot-reloading at `http://localhost:8080`.
- `npm run build`: Generates the static site in the `_site` folder.

## File Structure
- `src/index.md`: Home page.
- `src/posts/`: Directory containing individual blog posts (Markdown files).
- `src/_includes/`: Layouts and reusable components.
- `src/css/`: Main stylesheet (`style.css`).

## Guidelines for Agents
1. **Adding Posts**: Create a new `.md` file in `src/posts/`. Ensure it has proper front matter:
   ```yaml
   ---
   title: "Post Title"
   date: YYYY-MM-DD
   layout: layouts/post.njk
   ---
   ```
2. **Styling**: Modify `src/css/style.css` for any design changes.
3. **Philosophy**: The site must remain simple, fast, and lightweight. Avoid unnecessary elements, complex animations, or bloated styles. Stick to a clean, minimalist aesthetic.
4. **Date Formatting**: Use `dateIso` or `dateReadable` filters in templates for consistency.
5. **Passthrough**: `src/css` is automatically copied to `_site/css` during build.
