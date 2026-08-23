# Decisions

## Decision: Build a Georgian-first captioning workflow

### Context

The product must solve more than transcription. Its core value is turning Georgian speech into accurately timed captions that already appear over the source video and can be corrected efficiently.

### Decision

Prioritize the workflow Georgian video → timestamped speech → automatic cues → captions on video → human correction → styling → rendered export.

### Reason

This workflow is the product differentiator and provides a clear test for whether proposed work belongs in the current scope.

### Consequences

- Georgian language quality, timestamp integrity, cue readability, live video preview, and final rendering are first-class concerns.
- Generic transcription, multilingual architecture, and broad video-editing features must not displace the core workflow.

## Decision: Prove personal V1 before productization

### Context

Accounts, billing, collaboration, quotas, and production scaling could eventually support a Georgian market product, but they do not prove that the captioning workflow is useful.

### Decision

Build and validate a single-user personal V1 before introducing SaaS or multi-user infrastructure.

### Reason

This keeps early development understandable and directs effort toward transcription quality, cue timing, editing, styling, and export.

### Consequences

- Authentication and generated user infrastructure will be evaluated separately rather than assumed necessary.
- Productization features remain postponed until the personal workflow is demonstrably useful.

## Decision: Preserve the installed application stack

### Context

The repository already contains Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Vite 8, npm, and SQLite.

### Decision

Use the repository and installed package versions as the source of truth. Do not recreate the application, reinstall Vue, or replace working parts of the stack without a concrete requirement and approval.

### Reason

The existing stack supports the intended browser editor and Laravel backend while avoiding unnecessary setup and migration work.

### Consequences

- Vue will own immediate editor and playback state using built-in Vue primitives initially.
- Laravel will own routing, validation, persistence, uploads, processing, and rendering concerns as they are introduced.
- New state, UI, media, ASR, or infrastructure dependencies require a demonstrated current problem and explicit discussion.

## Decision: Separate live caption preview from final rendering

### Context

Browser preview needs immediate feedback while editing, whereas final export must produce a portable captioned video.

### Decision

Use native HTML video with an HTML/CSS caption overlay for the initial live preview. Evaluate ASS/libass with FFmpeg later for final rendering rather than implementing it now.

### Reason

An HTML overlay is the smallest approach for responsive editing and avoids transcoding merely to preview changes.

### Consequences

- Vue playback state will eventually select the active cue from the video's current time.
- Preview styles should be introduced with awareness that final rendering must plausibly reproduce them.
- ASS/libass remains a likely direction, not yet a validated implementation choice.

## Decision: Retain dormant starter infrastructure during personal V1

### Context

The blank starter includes a user model, authentication configuration and types, database sessions, cache and queue tables, and generated Wayfinder files. Authentication screens and routes are not installed, and none of this infrastructure currently complicates the visible application.

### Decision

Retain the dormant starter infrastructure for now and replace only the visible Laravel promotional page.

### Reason

Removing the user infrastructure would require coordinated changes across a combined user/session migration, model, factory, seeder, configuration, shared props, and frontend types without materially advancing the captioning workflow.

### Consequences

- Personal V1 remains unauthenticated even though dormant authentication foundations exist.
- Existing database session, cache, and queue configuration stays intact.
- These files may be reconsidered if they create a concrete maintenance problem or when productization begins.
