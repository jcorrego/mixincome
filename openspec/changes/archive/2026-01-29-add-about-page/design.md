## Context

The app has a single route (`GET /`) using a closure that returns a Blade view. There are no controllers or layout components yet. The welcome page is a standalone styled Blade template with Tailwind CSS v4 and dark mode support.

Brand assets exist in `resources/brand/` with color, dark, and white variants in SVG and PNG formats.

## Goals / Non-Goals

**Goals:**
- Add a publicly accessible About page at `/about`
- Display the MixIncome logo with proper light/dark mode support
- Match the visual style of the existing welcome page

**Non-Goals:**
- Extracting a shared layout component (premature for two pages)
- Adding navigation to the welcome page
- Creating a controller (follow the existing closure pattern)

## Decisions

### Decision 1: Route pattern

Follow the existing closure-returning-view pattern in `routes/web.php`. No controller needed for a static page.

### Decision 2: Logo handling

Use the SVG logo files from `resources/brand/` — specifically `logo-color.svg` for light mode and `logo-white.svg` for dark mode. SVGs are used directly in the Blade template for crisp rendering at any size. Tailwind's `dark:hidden` / `dark:block` classes toggle between variants.

### Decision 3: Standalone template

Keep `about.blade.php` as a standalone file like the welcome page. Extracting a layout is not warranted for two pages.
