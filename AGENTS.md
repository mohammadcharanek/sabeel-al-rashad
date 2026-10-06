<laravel-boost-guidelines>

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated for this application.

Follow these guidelines closely when working on the project.

## Foundational Context

This application is a Laravel application running on PHP 8.3.

You are an expert with the Laravel ecosystem.

Always use APIs and syntax compatible with the versions actually installed in this project. Never assume a package version.

Before relying on a package API, command, option, or behavior, confirm the installed version when relevant:

- PHP packages:
  - `composer show --direct`
  - `composer show <vendor/package>`

- JavaScript packages:
  - inspect `package.json`
  - inspect the lock file when exact resolution matters

Do not upgrade dependencies merely to make an implementation easier.

---

## Skills Activation

This project has domain-specific skills available in:

```text
**/skills/**
```

You MUST activate the relevant skill whenever working in that domain.

Do not wait until you encounter a problem before loading the relevant guidance.

Examples include:

- Laravel
- Filament
- Pest/testing
- Tailwind/frontend
- deployment

---

## Conventions

- Follow the existing conventions already used in this application.
- Before creating or editing a file, inspect relevant sibling files for:
  - structure
  - naming
  - patterns
  - validation style
  - relationships
  - testing conventions

- Reuse existing components, helpers, scopes, relationships, services, and patterns before introducing new ones.

- Use descriptive variable and method names.

Prefer:

```php
isRegisteredForDiscounts()
```

instead of:

```php
discount()
```

- Do not introduce abstractions merely for theoretical cleanliness.
- Avoid unnecessary repositories, service layers, DTOs, enums, events, listeners, or packages unless they provide clear value within the existing architecture.

---

## Verification Scripts

- Do not create ad-hoc verification scripts when automated tests already prove the behavior.
- Unit and feature tests are preferred for application logic.
- Temporary browser automation scripts are allowed only when explicitly needed for browser/UI verification.
- Temporary verification files must remain outside the repository.
- Never commit temporary verification scripts.
- Remove or ignore temporary artifacts after verification where appropriate.

---

## Application Structure & Architecture

- Follow the existing directory structure.
- Do not create new top-level/base directories without a clear reason or user approval.
- Do not change application dependencies without approval.
- Do not perform large architectural refactors when a smaller compatible change solves the problem.
- Preserve existing public interfaces and persisted data whenever practical.

---

## Frontend Bundling

If a frontend change is not reflected in the UI, possible required commands include:

```bash
npm run build
```

or:

```bash
npm run dev
```

or:

```bash
composer run dev
```

Run the appropriate command when possible.

Do not assume a frontend bug exists before confirming the current assets have been built.

---

## Documentation Files

- Only create documentation files when explicitly requested by the user or when the project already uses a documented rules system such as `.ai/rules`.
- Do not create miscellaneous Markdown summaries after tasks unless requested.
- Project rules that need to persist should go into the established `.ai/rules` system instead of random documentation files.

---

## Replies

- Be concise.
- Focus on meaningful implementation details, risks, test results, and decisions.
- Do not explain obvious framework behavior unless it is relevant to a decision or failure.
- Clearly distinguish:
  - completed work
  - verification performed
  - warnings
  - blockers
  - recommended follow-up work

---

## Data Safety

Protect existing project data.

Never run destructive database commands unless the user explicitly requests and approves the destructive action.

Do NOT run commands such as:

```bash
php artisan migrate:fresh
php artisan db:wipe
```

or equivalent destructive operations against the user's working database without explicit approval.

Do not:

- truncate production or working tables
- mass-delete existing records
- destructively reseed the working database
- recreate existing stages, grades, applications, or users unnecessarily
- change existing primary IDs without an explicit migration requirement
- remove historical records to simplify an implementation

Prefer isolated test databases for automated tests.

Tests should use the configured isolated testing database and must not modify the user's working data.

Before modifying schema that contains existing records:

1. inspect the current schema
2. inspect relevant models and relationships
3. understand existing data dependencies
4. design a backward-compatible migration
5. preserve existing IDs and historical records whenever possible

Migrations must preserve:

- existing records
- primary IDs
- historical relationships
- uploaded file references
- application/document references

unless the requested feature explicitly requires otherwise.

When introducing a required relationship to an existing populated table, prefer safe transitional strategies such as:

```text
add nullable relationship
→ backfill safely
→ validate data
→ enforce stronger constraint later if appropriate
```

Do not use destructive migration shortcuts.

---

## Git & Deployment Safety

Before substantial work, inspect:

```bash
git status
```

and relevant diffs.

Preserve unrelated working-tree changes.

Do not commit, push, deploy, reset, force-push, discard changes, or clean the repository unless the user explicitly requests that specific action.

Never use destructive Git commands such as:

```bash
git reset --hard
git clean -fd
git checkout -- .
```

against user work without explicit approval.

Do not overwrite unrelated modified files.

When continuing interrupted work:

1. inspect `git status`
2. inspect `git diff`
3. understand existing modifications
4. continue from the current implementation

Do not restart completed work unless necessary.

Do not automatically commit after implementing a feature.

Implementation, verification, commit, push, and deployment are separate steps unless the user explicitly requests otherwise.

---

=== boost rules ===

# Laravel Boost

## Tools

Laravel Boost is an MCP server with tools designed specifically for this application.

Prefer appropriate Boost tools over manual alternatives where they provide better project context.

- Use `database-query` for read-only database queries instead of writing raw SQL in Tinker.
- Use `database-schema` to inspect table structure before changing database architecture.
- Use `get-absolute-url` to resolve the correct application URL when available.
- Use `browser-logs` when debugging browser errors or exceptions.
- Prefer recent browser logs; old entries may be irrelevant.

Do not use a Boost tool unnecessarily when the installed source code or existing tests provide the answer more directly.

---

## Searching Documentation

Use `search-docs` before changes that depend on version-specific Laravel ecosystem APIs, configuration, behavior, or syntax.

Skip documentation searches for:

- simple copy changes
- basic styling changes
- obvious project-local behavior
- situations where sufficient version-compatible documentation is already available in context

When using documentation search:

- scope with a `packages` array when relevant
- use broad topic-based queries
- use multiple related queries when useful
- do not unnecessarily include package names inside queries if package context is already supplied

Prefer:

```text
test resource table
```

instead of:

```text
filament 4 test resource table
```

Example queries:

```text
rate limiting
routing rate limiting
routing
```

---

### Search Syntax

1. Words use auto-stemmed AND matching.

Example:

```text
rate limit
```

matches content containing both concepts.

2. Use quoted phrases for exact adjacent text:

```text
"infinite scroll"
```

3. Combine words and phrases:

```text
middleware "rate limit"
```

4. Use multiple queries when OR-style discovery is useful.

---

## Project Rules

This project may contain committed, area-specific rules under:

```text
.ai/rules
```

These contain settled project decisions, non-obvious constraints, compatibility requirements, and path-specific guidance.

Before entering plan mode or creating/editing files:

1. Check whether `.ai/rules` exists.
2. If it exists, open:

```text
.ai/rules/index.md
```

3. Read every rule file whose glob or scope covers the files being changed.
4. Search the rules directory for domain keywords relevant to the task.

For example:

```bash
grep -rin "education" .ai/rules
grep -rin "registration" .ai/rules
```

Do not write code until relevant rules have been reviewed.

If `.ai/rules` does not exist, continue normally.

Only record a new persistent rule when the user explicitly asks to preserve that decision as a project rule.

Task instructions are not automatically permanent rules.

Examples of task-only instructions:

```text
fix this typo
change this label
move this button
```

Do not create permanent rules for such temporary work.

Permanent architectural or business decisions may belong in `.ai/rules` when requested.

---

## Artisan

Run Artisan commands directly when appropriate.

Examples:

```bash
php artisan route:list
php artisan migrate:status
php artisan config:show app.name
```

Use:

```bash
php artisan list
```

to discover commands.

Use:

```bash
php artisan <command> --help
```

to inspect supported parameters.

When generating files through Artisan, use:

```bash
--no-interaction
```

where supported.

Inspect routes with:

```bash
php artisan route:list
```

Useful filters include:

```bash
--method=GET
--name=users
--path=api
--except-vendor
--only-vendor
```

Read configuration using:

```bash
php artisan config:show app.name
php artisan config:show database.default
```

or inspect the relevant files in `config/`.

---

## Tinker

Use Tinker only when it provides meaningful application-context debugging that is not already covered by tests or dedicated tools.

Do not create or modify persistent models through Tinker without user approval.

Prefer:

- automated tests
- factories
- existing Artisan commands
- read-only database tools

When using Tinker from a shell, prefer single quotes around the command to avoid shell expansion:

```bash
php artisan tinker --execute 'User::where("active", true)->count();'
```

---

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

Example:

```php
if ($active) {
    return true;
}
```

- Use explicit return type declarations where appropriate.
- Use type hints for method parameters.

Example:

```php
public function isAccessible(User $user, ?string $path = null): bool
{
    //
}
```

- Prefer PHP 8 constructor property promotion where appropriate.

Example:

```php
public function __construct(public GitHub $github)
{
}
```

- Do not leave empty public zero-parameter constructors unless there is a specific reason.
- Use TitleCase Enum case names.

Example:

```php
FavoritePerson
BestLake
Monthly
```

- Prefer PHPDoc blocks over explanatory inline comments.
- Add inline comments only when logic is genuinely difficult to understand.
- Use array-shape PHPDoc definitions when useful.
- Follow existing project formatting conventions.

---

=== deployments rules ===

# Deployment

Deployment is a separate operation from implementation.

Do not deploy unless the user explicitly asks for deployment.

Before deployment:

1. ensure relevant tests pass
2. inspect the working tree
3. understand pending migrations
4. confirm build status when frontend assets changed
5. identify any required environment/configuration changes
6. preserve current production data

Laravel Cloud may be used when the project is actually configured for it.

Activate the relevant deployment skill whenever deploying to Laravel Cloud, configuring Cloud resources, using the Cloud CLI, or troubleshooting a Laravel Cloud deployment.

Do not assume Laravel Cloud is the project's deployment platform.

Follow the project's actual hosting/deployment architecture.

---

=== tests rules ===

# Test Enforcement

Add or update automated tests when behavior or application logic changes and meaningful regression coverage is possible.

Pure changes involving only:

- copy
- labels
- static styling
- non-functional layout

do not automatically require tests.

When test coverage applies:

1. identify the narrowest relevant tests
2. run them during implementation
3. fix regressions caused by the change
4. rerun affected tests after modifications

Test:

- expected behavior
- important failure modes
- authorization/validation where relevant
- compatibility when the feature modifies existing behavior

Do not add excessive tests that provide no meaningful regression protection.

Read the `testing-best-practices` skill before substantial test work.

---

## Final Verification

During active implementation, run the narrowest tests that cover the changed behavior.

After affected tests pass, run the complete test suite when:

- the user explicitly requests final verification
- the feature is broad or cross-cutting
- database relationships or migrations changed
- authentication/registration behavior changed
- a major feature is about to be declared ready for commit
- regressions could reasonably affect multiple areas

Do not ask the user to run tests that the agent can run itself.

If the full suite is too large or cannot run because of an environment limitation, state that clearly and report exactly what was verified.

Do not modify unrelated tests merely to force the suite green.

If an unrelated/pre-existing failure appears:

1. identify it
2. determine whether the current changes caused it
3. report it clearly
4. do not hide it by weakening the test

---

=== laravel/core rules ===

# Do Things the Laravel Way

Use Laravel's standard patterns and existing project conventions.

Use:

```bash
php artisan make:
```

commands for Laravel-managed files when appropriate.

Examples include:

- migrations
- models
- controllers
- requests
- policies
- tests

Use:

```bash
php artisan make:class
```

for generic PHP classes when appropriate.

Pass:

```bash
--no-interaction
```

to Artisan generation commands where supported.

---

## Model Creation

When creating a new persistent model, consider whether useful supporting artifacts are needed:

- factory
- seeder
- relationships
- tests

Do not automatically add unnecessary factories or seeders if the model is not intended for generated/test data.

Follow existing project conventions.

---

## APIs & Eloquent Resources

For APIs, prefer Eloquent API Resources and API versioning when consistent with the application's existing API architecture.

If the existing project follows a different established convention, preserve that convention instead of introducing a new API structure.

---

## URL Generation

When generating internal application links, prefer named routes:

```php
route('route.name')
```

rather than hardcoded URLs.

---

## Testing

When creating models in tests:

- prefer factories
- inspect existing factory states before manually constructing complex records
- preserve valid domain relationships

Follow existing Faker conventions.

Examples:

```php
$this->faker->word()
```

or:

```php
fake()->randomDigit()
```

Use Pest for this project.

Create feature tests with:

```bash
php artisan make:test --pest SomeFeatureTest --no-interaction
```

Use unit tests only when a unit-level test is genuinely appropriate.

Most application behavior should be tested through feature tests.

---

## Vite Errors

If Laravel reports:

```text
Illuminate\Foundation\ViteException:
Unable to locate file in Vite manifest
```

verify frontend assets have been built.

Run when appropriate:

```bash
npm run build
```

or use the development server:

```bash
npm run dev
```

Do not change application code merely to work around a stale or missing Vite manifest.

---

=== pint/core rules ===

# Laravel Pint Code Formatter

After modifying PHP files, run the project's installed Laravel Pint formatter on the changed files.

Prefer:

```bash
vendor/bin/pint --dirty
```

when supported by the installed version.

If the project or installed Pint version uses supported additional formatting options, follow the project's existing convention.

Do not assume optional CLI flags exist without checking the installed version when uncertain.

Formatting should fix code, not merely report style errors, unless the task explicitly requests a check-only operation.

After formatting, ensure the diff still contains only intended changes.

---

=== pest/core rules ===

# Pest

This project uses Pest.

Create Pest tests with:

```bash
php artisan make:test --pest SomeFeatureTest --no-interaction
```

Do not include the suite directory inside the test name.

Use:

```text
SomeFeatureTest
```

not:

```text
Feature/SomeFeatureTest
```

Read the `testing-best-practices` skill for guidance on:

- coverage
- naming
- structure
- dependency isolation
- factories
- regression testing
- review

Do not delete tests or test files without explicit approval.

Tests are part of the application's expected behavior.

---

## Running Tests

Run the narrowest set of tests that covers the current change.

Examples:

```bash
php artisan test --compact tests/Feature/SomeFeatureTest.php
```

or:

```bash
php artisan test --compact --filter=testName
```

Rerun a test after modifying code intended to fix that test.

You may also run Pest directly:

```bash
vendor/bin/pest
```

with the appropriate path or filter.

For broad or final verification, run:

```bash
php artisan test --compact
```

when appropriate under the Final Verification rules above.

Always report exact failures rather than masking them.

</laravel-boost-guidelines>