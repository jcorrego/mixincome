## Why

The application currently only has a welcome page. An About page gives visitors basic information about MixIncome — what it is and what it offers.

## What Changes

- Add a new `/about` route
- Create an `about.blade.php` view styled consistently with the existing welcome page
- Display the MixIncome logo from `resources/brand/` (using the appropriate color/dark variants for light and dark mode)
- Include a navigation link so users can reach the page

## Capabilities

### New Capabilities
- `about-page`: A public About page accessible at `/about` that describes the application and displays the MixIncome brand logo

## Impact

- `routes/web.php`: New GET route for `/about`
- `resources/views/about.blade.php`: New view file featuring the brand logo
- `tests/Feature/AboutPageTest.php`: Feature test verifying the page loads
