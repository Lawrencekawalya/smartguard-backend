# SmartGuard Development Rules

Follow these rules for every task.

## General Rules

* Implement only ONE phase at a time.
* Never start the next phase until the current phase is fully completed and verified.
* Preserve the existing Laravel + Vue + Inertia theme and layout.
* Do not redesign existing UI styling unless explicitly instructed.
* Reuse existing components, layouts, navigation, colors, typography, and spacing.
* Keep all code PSR-12 compliant.
* Use Laravel best practices.
* Use Vue 3 Composition API.

## Testing Requirements

For every phase:

1. Create automated tests.
2. Execute tests.
3. Fix failures.
4. Re-run tests.
5. Report results.

Required commands:

```bash
php artisan test
npm run build
```

If relevant:

```bash
npm run lint
```

No phase is complete unless all tests pass.

## Git Requirements

When a phase is complete:

1. Show changed files.
2. Provide a summary.
3. Create a commit.

Commit format:

feat(phase-x): short description

Example:

feat(phase-1): implement device telemetry schema

Do not continue to another phase until commit succeeds.

## Documentation Requirements

Update:

* task.md
* README.md

Include:

* Implemented features
* New endpoints
* Database changes
* Testing status

## Architecture Requirements

Use:

* Controllers
* Form Requests
* API Resources
* Services
* Models
* Feature Tests

Avoid putting business logic directly inside controllers.

## UI Requirements

Maintain existing theme.

Do not:

* Replace layouts
* Replace navigation
* Change color systems
* Change typography

Only add new pages and components that match the existing application styling.

## Final Output Per Phase

At the end of each phase provide:

* Files created
* Files modified
* Tests executed
* Test results
* Migration status
* Suggested commit message
