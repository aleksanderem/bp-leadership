# BP Leadership

Featured Stories and Leadership custom post types with ACF fields, Elementor integration, and visual settings.

## Features

- Leadership profiles with custom single page template
- Featured Stories cards with logo and URL
- Assigned stories per leader (Elementor Loop Grid integration)
- Per-leader grid column control (desktop/tablet/mobile)
- Visual settings for aspect ratios and heights (per breakpoint)
- Optional root-level URLs (remove /leadership/ prefix)
- Dummy data generator for Featured Stories
- CSS variables output for theme integration

## Post Types

### Leadership

Public post type for team/leadership profiles.

- **Slug**: `/leadership/` (or root-level when disabled)
- **Supports**: title, thumbnail, page-attributes
- **Template**: optional "Leadership Profile" template from plugin

#### ACF Fields

- **First Name** / **Last Name** (required)
- **Header Content** (WYSIWYG) — hero section below name
- **Body Content** (WYSIWYG) — main content area
- **LinkedIn URL**
- **Video URL** (YouTube/Vimeo) or **Video File** (mp4/webm/mov)
- **Featured Story** — single highlighted story
- **Featured Stories** — relationship field for story grid
- **Grid Columns** — desktop/tablet/mobile column count
- **Exclude from Search** — hide from site search

### Featured Stories

Private post type (admin-only, no public URL). Used as content cards assigned to leaders.

- **Supports**: title, excerpt, thumbnail
- **Icon**: star

#### ACF Fields

- **Story URL** (required) — link to the original article
- **Logo** — publication/source logo image

## Settings

### Leadership Settings

Access via WordPress admin → Leadership → Settings:

- **Disable Public Slug** — removes `/leadership/` prefix, leaders accessible at root URLs (e.g. `/john-doe/`)
- **Visual — Stories** — aspect ratio or fixed height per breakpoint for story cards
- **Visual — Featured Image** — aspect ratio or fixed height per breakpoint for leader photos
- Toggle between aspect ratio and height modes

### Featured Stories Settings

Access via WordPress admin → Featured Stories → Settings:

- **Generate Dummy Data** — create 1-100 sample stories with random images and logos
- **Delete All** — bulk remove all featured stories

## Elementor Integration

### Custom Queries

Two custom query sources for Loop Grid widgets:

- **assigned_stories** — displays stories assigned to the current leader
- **featured_story_single** — displays the single featured story for the current leader

### CSS Variables

The plugin outputs CSS custom properties on `wp_head` based on visual settings:

```
--bp-leadership-story-ratio
--bp-leadership-featured-story-ratio
--bp-leadership-story-height
--bp-leadership-featured-story-height
--bp-leadership-image-ratio
--bp-leadership-image-height
```

Tablet and mobile variants use `-tablet` and `-mobile` suffixes.

### Stories Grid

The assigned stories Loop Grid gets class `bp-stories-grid` with per-leader column overrides via inline CSS.

## Requirements

- Advanced Custom Fields PRO
- Elementor Pro (for Loop Grid and custom queries)
