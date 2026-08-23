# Development Log

## 2026-08-23 — Step 0.1

### Goal

Inspect the existing Laravel/Vue repository without modifying it.

### Changes

No application or documentation files were changed.

### Decisions

- Treat the repository and installed package versions as the source of truth.
- Continue from the existing Laravel/Inertia/Vue application rather than recreating or replacing its stack.

### Verification

- Confirmed Laravel 13.26.1 and PHP 8.3.33, with Composer requiring PHP `^8.3`.
- Confirmed Vue 3.5.41, Inertia 3.7.0 on the frontend, Inertia Laravel 3.3.1, TypeScript, Tailwind CSS 4.3.3, Vite 8.2.2, and npm.
- Confirmed SQLite is the default database, the database file exists, and the three starter migrations have run.
- Inspected routes, frontend pages, storage, queues, tests, starter code, and installed dependencies.

### Result

The project is a lean Laravel blank Vue starter with one Inertia welcome page. It has no application-specific video, transcription, cue, caption, or rendering functionality yet.

### Problems / Notes

- Authentication data structures exist, but no authentication routes or screens are installed.
- The current Git working tree reports all project files as untracked even though `main` tracks `origin/main`; the committed baseline should be checked before relying on Git diffs.
- Georgian glyph coverage has not been verified for the starter fonts.

### Next

Create the documentation foundation.

## 2026-08-23 — Step 0.2

### Goal

Create the minimum shared documentation needed to continue development deliberately across sessions.

### Changes

- Added `docs/DEVELOPMENT_LOG.md` as the chronological implementation record.
- Added `docs/BACKLOG.md` to protect immediate scope and preserve deferred ideas.
- Added `docs/DECISIONS.md` for durable product and technical decisions.

### Decisions

- Keep personal V1 focused on Georgian video captioning rather than SaaS infrastructure or general-purpose video editing.
- Keep the current installed Laravel/Inertia/Vue/TypeScript/Tailwind stack unless a concrete product requirement demonstrates a need to change it.

### Verification

- Reviewed all three documentation files for the agreed structure and consistency with the inspected repository baseline.
- No application code, dependencies, configuration, migrations, or generated scaffold files were changed.

### Result

The project now has a small documentation foundation describing its baseline, immediate work, deferred scope, and durable direction.

### Problems / Notes

- Git still reports the existing project files as untracked, so repository cleanliness is not established.

### Next

Evaluate the existing starter/scaffold code and propose what to retain or remove. Do not remove anything without approval.

## 2026-08-23 — Step 0.3

### Goal

Evaluate the generated Laravel/Vue starter code before removing or replacing any of it.

### Changes

No application files were changed during the evaluation.

### Decisions

- Keep the dormant authentication model, configuration, migration, factory, shared prop, and TypeScript types for now because removing them would create churn without advancing personal V1.
- Keep the cache, session, queue, Inertia, Wayfinder, and application-provider foundations.
- Replace only the visible Laravel promotional welcome screen in the minimal shell step.

### Verification

- Traced references between the user model, factory, seeder, auth configuration, shared Inertia props, TypeScript declarations, and combined user/session migration.
- Confirmed there are no generated dashboard, settings, profile, login, or registration screens to remove.
- Confirmed the Git baseline is committed and the branch tracks `origin/main`.

### Result

No separate destructive cleanup was justified. The starter is already minimal apart from its visible promotional welcome screen.

### Problems / Notes

- Starter font coverage for Georgian Mkhedruli remains unverified.

### Next

Replace the Laravel promotional page with the minimal Georgian Captioner application shell.

## 2026-08-23 — Step 0.4

### Goal

Establish the minimum visible shell for the Georgian captioning product without adding upload or media behavior.

### Changes

- Replaced the Laravel promotional content in `resources/js/pages/Welcome.vue` with a focused Georgian Captioner introduction.
- Removed promotional links, Laravel artwork, and the page-level external Inter stylesheet.
- Preserved the existing root route, Inertia page, TypeScript setup, Tailwind styling, and dark-mode treatment.

### Decisions

- Keep the existing `Welcome` page name and root route until a product need justifies renaming or additional navigation.
- Describe the next milestone as text rather than displaying a nonfunctional upload control.

### Verification

- The existing home feature test passes.
- Frontend type checking, lint checking, formatting checking, and the production build pass.
- Browser display has not yet been manually verified.

### Result

The root page now presents the Georgian Captioner product direction and accurately states that the application foundation is ready.

### Problems / Notes

- Georgian text is present, but its glyph rendering has not yet been manually checked in a browser.

### Next

Propose the minimum domain model for representing one uploaded video. Do not change the database before approval.

## 2026-08-23 — Step 1.1

### Goal

Propose the minimum domain representation for one uploaded video before changing the database.

### Changes

No files or database structures were changed during the proposal.

### Decisions

- Use `VideoProject` as the domain name because the record will represent the editing workspace, not only the source media asset.
- Store only the original filename, filesystem disk, generated path, MIME type, byte size, and Laravel timestamps initially.
- Defer ownership, processing status, duration, transcription, captions, styles, and export fields until their workflows exist.

### Verification

- Compared the proposal with the current SQLite schema and Laravel filesystem model.
- Confirmed the proposed fields are sufficient to identify and locate one stored upload without storing media in the database.

### Result

The minimum `video_projects` schema was approved.

### Problems / Notes

- Upload validation and storage behavior are not implemented yet.

### Next

Create only the approved `video_projects` migration.

## 2026-08-23 — Step 1.2

### Goal

Create the approved migration for one video project without introducing the model or upload behavior.

### Changes

- Added the `video_projects` table migration.
- Added required columns for `original_filename`, `disk`, `path`, `mime_type`, and `size_bytes`.
- Added a unique index to the generated storage path.

### Decisions

- Keep all media columns required and avoid defaults so the future upload flow must persist complete storage metadata explicitly.

### Verification

- Laravel Pint passed for the migration.
- The migration ran successfully against the configured SQLite database.
- Schema inspection confirmed all expected non-null columns and the unique path index.
- A one-step rollback removed the table successfully.
- The migration ran successfully again, leaving the approved table present.

### Result

The database can now represent the storage identity and basic metadata of a video project. It contains no video-project records yet.

### Problems / Notes

- No model exists yet, so application code does not interact with the table.

### Next

Create the minimal `VideoProject` model in a separate step.

## 2026-08-23 — Step 1.3

### Goal

Create the minimal Eloquent model for the approved `video_projects` table.

### Changes

- Added the `App\Models\VideoProject` model.
- Declared the five approved media metadata fields as mass assignable using Laravel's `Fillable` attribute.
- Added a focused Pest feature test for conventional table mapping and metadata persistence, including a Georgian filename.

### Decisions

- Do not add relationships, casts, scopes, statuses, or lifecycle behavior until a concrete workflow requires them.
- Defer a factory and seeder until tests or development setup need reusable video-project data; creating realistic stored-file records currently requires upload behavior that does not exist yet.

### Verification

- The focused model feature test passes with three assertions.
- Laravel Pint passes after formatting the new PHP files.
- The full Pest suite passes.

### Result

Application code can now persist and retrieve the approved video-project metadata through Eloquent. No records were added to the development database by the test.

### Problems / Notes

- Upload validation, file storage, and browser interaction are not implemented.

### Next

Add a deliberately simple upload interface without validation or storage behavior.

## 2026-08-23 — Step 2.1

### Goal

Add the smallest visible interface for selecting one MP4 without implying that upload behavior already exists.

### Changes

- Added a native file input to the existing Georgian Captioner page.
- Limited the browser file picker hint to MP4 files through the input's `accept` attribute.
- Added an explicitly disabled submit control stating that upload is not connected yet.
- Preserved responsive and dark-mode styling without adding components or dependencies.

### Decisions

- Use the native browser file input rather than introducing drag-and-drop or upload UI libraries.
- Do not create an Inertia form object or backend endpoint until validation and submission behavior are designed.
- Treat the HTML `accept` attribute only as a file-picker hint, not as security validation.

### Verification

- Frontend type checking, ESLint, Prettier, and the production build pass.
- The full Pest suite passes.
- Selecting a file and visual appearance have not yet been manually verified in a browser.

### Result

The application now presents a simple MP4 selection interface. It does not submit, validate, store, or persist the selected file.

### Problems / Notes

- The submit control is intentionally disabled until a validated backend endpoint exists.

### Next

Define the initial server-side MP4 validation rules and development upload-size limit before connecting submission.

## 2026-08-23 — Step 2.2

### Goal

Define and test the initial server-side file policy without creating an upload endpoint or storing files.

### Changes

- Added `StoreVideoProjectRequest` with a required, content-inspected MP4 rule and a 500 MB maximum.
- Added focused Pest tests for a valid MP4 at the limit, a missing file, a non-MP4 file, and an MP4 one kilobyte over the limit.
- Displayed the 500 MB policy next to the existing file input.

### Decisions

- Support only MP4 for the initial workflow.
- Set the personal-V1 development limit to 500 MB using Laravel's human-readable `500mb` file rule.
- Keep browser `accept` filtering as a convenience only; the Form Request is the security boundary.

### Verification

- All four focused validation tests pass with seven assertions.
- The full Pest suite, PHPStan, Laravel Pint, frontend type checking, ESLint, Prettier, and the production build pass.
- Tests use fake uploaded files and do not write video files to application storage.

### Result

The application has a reusable server-side validation policy for one MP4 up to 500 MB. No route currently invokes it.

### Problems / Notes

- PHP currently allows only 2 MB per uploaded file and 8 MB per POST request. Those environment limits must be raised before a real 500 MB upload can reach Laravel validation.

### Next

Manually align PHP's upload limits with the 500 MB application policy before implementing storage.

## 2026-08-23 — Step 2.3

### Goal

Submit and store one validated MP4 using a safe generated path without persisting video-project metadata yet.

### Changes

- Added a named POST route for video-project storage.
- Added an invokable controller that receives the existing Form Request and stores the validated file under `video-projects/` on the private `local` disk.
- Connected the Vue upload interface to the named backend route through Wayfinder and Inertia's `Form` component.
- Enabled the submit control and added processing, validation-error, reset-on-success, and success states.
- Added a focused feature test using a fake local disk.

### Decisions

- Use Laravel's `store()` method so the source filename is generated rather than derived from untrusted client input.
- Store source videos on the private local disk initially.
- Keep metadata persistence out of this step even though the stored path will be needed immediately afterward.

### Verification

- Confirmed PHP CLI now reports `upload_max_filesize=500M` and `post_max_size=510M`.
- The focused storage test passes and verifies the stored file exists under `video-projects/` without reusing the Georgian original filename.
- The full Pest suite, PHPStan, Laravel Pint, frontend type checking, ESLint, Prettier, and the production build pass.
- Automated storage tests use `Storage::fake('local')`; they write no video to real application storage.
- A real browser upload has not yet been manually verified.

### Result

The application can validate and privately store one MP4 using a generated path. The stored file is not yet represented by a `VideoProject` database record.

### Problems / Notes

- A successful real upload creates a file that is not yet discoverable through the database. Phase 2.4 should be completed before relying on real uploads.

### Next

Persist the stored file's approved metadata as a `VideoProject` and handle storage cleanup if persistence fails.

## 2026-08-23 — Step 2.4

### Goal

Persist the stored MP4's approved metadata and avoid leaving an orphaned file if database persistence fails.

### Changes

- Created one `VideoProject` after successfully storing an upload.
- Persisted the original filename, private disk, generated relative path, detected MIME type, and byte size.
- Deleted the newly stored file before rethrowing an exception if model persistence fails.
- Extended the upload feature test to verify all persisted metadata.
- Added a focused failure-path test that forces a persistence exception and verifies file cleanup.

### Decisions

- Keep the upload and single-record persistence flow in the existing invokable controller until repeated processing logic justifies an action or service.
- Preserve the client-supplied original filename only as display metadata; never use it to construct a storage path.
- Let persistence exceptions retain their normal reporting and response behavior after storage cleanup.

### Verification

- The focused upload feature tests pass with 15 assertions, covering successful persistence and cleanup after a simulated persistence failure.
- The complete verification results are recorded in the checkpoint for this step.
- Automated tests use `Storage::fake('local')`; a real browser upload and real-disk/database pairing have not yet been manually verified.

### Result

A successful MP4 upload now creates one discoverable `VideoProject` record whose path identifies its private stored file. Failed metadata persistence does not leave that newly uploaded file behind.

### Problems / Notes

- The application still redirects to the upload page and does not display the newly created project.

### Next

Display the uploaded video's project information after a successful upload.

## 2026-08-23 — Step 2.5

### Goal

Redirect a successful upload to a minimal page that displays the newly created video project's safe metadata.

### Changes

- Added a named show route with implicit `VideoProject` route-model binding.
- Added an invokable show controller that returns an Inertia response containing the project's ID, original filename, MIME type, and byte size.
- Redirected successful uploads to the new project page.
- Added a typed Vue page that displays the filename, MIME type, and human-readable size, with a Wayfinder-backed link to upload another video.
- Added feature coverage for the Inertia response, omitted private storage details, missing-project 404 behavior, and the updated post-upload redirect.

### Decisions

- Do not send the private disk or stored path to the browser because this page only needs display metadata.
- Keep video delivery and playback out of this step; private media access needs its own route and security boundary.

### Verification

- The focused show and upload feature tests pass with 28 assertions.
- The complete verification results are recorded in the checkpoint for this step.
- Page appearance and post-upload navigation have not yet been manually verified in a browser.

### Result

After a successful upload, the application redirects to a dedicated project page and displays the uploaded video's safe metadata. It does not serve or play the private video yet.

### Problems / Notes

- The project page intentionally states that video playback is the next development step.

### Next

Serve the stored MP4 safely and play it with the native browser video element.

## 2026-08-23 — Step 2.6

### Goal

Play a project's private MP4 with the native browser video element while supporting efficient seeking.

### Changes

- Added a named media route with implicit `VideoProject` binding.
- Added an invokable media controller that verifies the referenced file exists and returns a private `BinaryFileResponse` with the recorded MIME type.
- Added the native HTML `<video controls>` element to the project page using a Wayfinder-generated media URL and metadata-only preloading.
- Added feature tests for successful media delivery, HTTP byte-range handling, and missing-file 404 behavior.

### Decisions

- Keep uploaded source videos outside the public web root and deliver them through a project-scoped application route.
- Use `BinaryFileResponse` so browsers can request byte ranges without PHP loading the entire MP4 into memory.
- Use the browser's native video controls; do not add a video-player dependency.

### Verification

- The focused media and project-page tests pass with 22 assertions.
- A range request for bytes 0–3 returns `206 Partial Content` and `Content-Range: bytes 0-3/10`.
- The complete automated verification results are recorded in the checkpoint for this step.
- Playback, audio, duration display, and seeking have not yet been manually verified with a real MP4.

### Result

The project page now contains a native player whose source is served from private local storage through a range-capable application route.

### Problems / Notes

- The current personal-V1 application has no accounts, so the media route has no per-user authorization policy yet.
- Remote object storage would require a different delivery mechanism; this implementation intentionally targets the current local disk.

### Next

Manually verify playback and seeking with a real Georgian MP4, then check whether FFmpeg and ffprobe are installed.

## 2026-08-23 — Step 3.1

### Goal

Determine whether FFmpeg and ffprobe are available before introducing any media-inspection code or installation work.

### Changes

- Confirmed `ffmpeg` and `ffprobe` are available at `/usr/bin/ffmpeg` and `/usr/bin/ffprobe`.
- Recorded the installed Ubuntu package build and relevant capabilities in the project baseline.
- Marked Phase 3.2 installation as unnecessary.

### Decisions

- Use the already installed system binaries for the next media-inspection experiment.
- Do not install an FFmpeg wrapper or change system configuration before a concrete application boundary is proposed.

### Verification

- `ffmpeg -version` reports `6.1.1-3ubuntu5` and exits successfully.
- `ffprobe -version` reports `6.1.1-3ubuntu5` and exits successfully.
- The installed build includes libass, x264, x265, Opus, and common media libraries; these capabilities were observed only and are not yet used by the application.

### Result

The machine is ready for a small ffprobe experiment without dependency installation or environment changes.

### Problems / Notes

- Availability is currently a development-machine fact, not an application startup requirement or deployment guarantee.

### Next

Inspect one uploaded MP4 with ffprobe and extract only duration plus video/audio stream presence.

## 2026-08-23 — Step 3.3

### Goal

Inspect one real uploaded MP4 with ffprobe and establish whether it contains usable video and audio streams.

### Changes

- Selected the latest existing `VideoProject` record without modifying it.
- Resolved and verified its generated path under the private local disk.
- Ran ffprobe with a deliberately narrow JSON output containing format duration and stream index, type, codec, and duration.

### Decisions

- Use the container-level format duration as the candidate overall video duration rather than assuming an individual stream duration represents the complete file.
- Do not decide or persist an internal duration unit until this observed decimal-seconds output is reviewed as part of Phase 3.4.

### Verification

- Project 3 (`test.mp4`) resolves to an existing private file of 1,374,550 bytes.
- ffprobe exits successfully and reports an overall duration of `7.966667` seconds.
- Stream 0 is H.264 video with a reported duration of `7.966667` seconds.
- Stream 1 is AAC audio with a reported duration of `7.802993` seconds.

### Result

The inspected upload contains both a usable video stream and a usable audio stream. Its container duration is available as decimal seconds suitable for explicit conversion in the next step.

### Problems / Notes

- The audio stream is shorter than the overall container/video duration by approximately 0.164 seconds; this is normal evidence that stream duration should not be substituted blindly for container duration.
- No application code currently executes ffprobe, and no media metadata was persisted.

### Next

Decide the minimum internal representation for video duration, then persist it in a separate approved step.

## 2026-08-23 — Step 3.4a

### Goal

Add the approved integer-millisecond duration representation without inventing duration values for existing uninspected projects.

### Changes

- Added a reversible migration with nullable unsigned-big-integer `duration_ms` storage.
- Added `duration_ms` to the `VideoProject` fillable attributes, property documentation, and integer casts.
- Extended model tests to cover a persisted `7,967` millisecond duration and an unknown null duration.
- Applied the migration to the development SQLite database.

### Decisions

- Convert ffprobe container seconds with `round(seconds × 1000)` when application inspection is implemented.
- Keep the column nullable so `null` unambiguously means media inspection has not populated duration.
- Do not backfill existing rows from guessed or stale information.

### Verification

- The focused model tests pass with four assertions.
- Migration preview showed one `duration_ms` integer column addition.
- The development migration completed successfully in 35.04 ms.
- Schema inspection confirms `duration_ms` is a nullable integer.
- All three existing development records remain present with `duration_ms = null`.

### Result

`VideoProject` can now persist an overall duration as integer milliseconds while retaining an explicit unknown state before inspection.

### Problems / Notes

- No application code invokes ffprobe or writes `duration_ms` yet, so Phase 3.4 is not complete.

### Next

Introduce the smallest application operation that inspects one project, converts the container duration to milliseconds, and persists it.
