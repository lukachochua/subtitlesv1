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

## Decision: Represent the editing workspace as a VideoProject

### Context

The application needs to persist one uploaded source video before transcription, captions, editing, or rendering exist. A source video will eventually belong to a broader editing workflow.

### Decision

Use a `VideoProject` domain record backed by `video_projects`. Initially store only `original_filename`, `disk`, `path`, `mime_type`, `size_bytes`, and Laravel timestamps. The filesystem address is the combination of disk and generated relative path.

### Reason

The name leaves room for the record to own captions and exports later, while the initial fields capture only metadata required to identify and locate the stored source video.

### Consequences

- Original filenames are display metadata and must never determine storage paths.
- Video bytes stay in filesystem storage rather than the database.
- Ownership, status, duration, ASR data, captions, styles, and export fields remain deferred until their corresponding workflows are designed.

## Decision: Limit initial uploads to MP4 files up to 500 MB

### Context

Personal V1 needs a narrow, useful upload policy before file storage is connected. Browser file-picker hints cannot be trusted as validation.

### Decision

Accept one required MP4 whose content-derived media type matches MP4 and whose size is no greater than Laravel's `500mb` limit. Enforce this policy in `StoreVideoProjectRequest`.

### Reason

MP4 is the first supported product format, and 500 MB is a bounded but useful development limit for short Georgian source videos.

### Consequences

- Other video containers are rejected initially.
- The browser `accept` attribute remains only a convenience hint.
- PHP and any future web server or proxy must allow requests at least as large as the application policy before Laravel can validate them.
## Decision: Serve personal-V1 source video through a range-capable application route

### Context

Uploaded source videos live on the private local disk and must be playable by the native browser video element. Browser seeking requires HTTP byte-range support, while exposing storage paths or creating a public storage symlink would bypass the application's project boundary.

### Decision

Resolve the project with route-model binding, verify its stored file exists, and return the local file through Laravel's `BinaryFileResponse` with its recorded MIME type and private, no-store cache directives. Generate the media URL in Vue through Wayfinder.

### Reason

`BinaryFileResponse` provides byte-range handling without reading the entire MP4 into PHP memory. The application route keeps the private filesystem path out of frontend data and gives us a natural place to add authorization if accounts are introduced later.

### Consequences

- Native video playback and seeking can use a normal URL while the source remains outside the public web root.
- The current personal-V1 route has no user authorization because the application has no accounts.
- This local-file response assumes the current local disk; a future remote-storage choice will require a different delivery mechanism.
## Decision: Store video duration as integer milliseconds

### Context

The first real ffprobe inspection returned the container duration as `7.966667` decimal seconds. Caption timing will require reliable comparisons against overall video duration, while existing and newly uploaded projects may remain uninspected temporarily.

### Decision

Store overall video duration in a nullable unsigned big integer column named `duration_ms`. Convert ffprobe's container-level decimal seconds by multiplying by 1,000 and rounding to the nearest integer; the observed sample therefore becomes `7,967` milliseconds.

### Reason

Integer milliseconds avoid floating-point comparison behavior, retain sufficient precision for caption boundaries, and align naturally with future browser and cue timing. Null explicitly represents “not inspected yet.”

### Consequences

- Existing projects remain valid with `duration_ms = null` until inspected.
- Downstream duration comparisons can use integer arithmetic.
- This decision covers overall video duration only; detailed ASR timestamp and cue-boundary rules remain deferred until real ASR output is available.
