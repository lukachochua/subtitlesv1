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

## 2026-08-23 — Step 3.4b

### Goal

Create one safe, testable application operation for inspecting a `VideoProject` and persisting its overall duration.

### Changes

- Added `App\Actions\InspectVideoProject` as the first focused application action.
- Resolved the media path from the project's recorded storage disk and generated path.
- Invoked ffprobe through Laravel's Process facade with argument-array execution and a 30-second timeout.
- Parsed the narrow JSON response, required positive container duration plus video and audio streams, and persisted rounded integer milliseconds only after validation.
- Added focused Pest coverage using process fakes and stray-process prevention.

### Decisions

- Use Laravel's existing Process facade rather than adding an FFmpeg wrapper or custom process abstraction.
- Keep inspection synchronous for this short metadata operation; no queue has been justified.
- Surface existing process/runtime exceptions initially rather than introducing a custom exception hierarchy before UI error requirements exist.

### Verification

- Eight focused test cases pass with 20 assertions.
- The success case verifies `7.966667` seconds persists as `7,967` milliseconds.
- Failure cases cover a missing source file, non-zero process exit, invalid JSON, missing/non-positive duration, and missing video or audio streams.
- Tests assert the exact argument-array command and timeout while preventing any real subprocess from running.
- The complete automated verification results are recorded in the checkpoint for this step.

### Result

The application now has a tested internal operation that can inspect and persist one project's duration, but it has no command, route, or UI entry point yet.

### Problems / Notes

- The ffprobe executable path is currently the verified development path `/usr/bin/ffprobe`.

### Next

Add the smallest manual entry point for invoking this action against one existing `VideoProject`.

## 2026-08-23 — Step 3.4c

### Goal

Expose the existing inspection action through one minimal manual application entry point.

### Changes

- Added `video-projects:inspect {videoProject}` as an auto-registered Artisan command.
- Resolved a positive project ID, delegated inspection to `InspectVideoProject`, and printed the persisted millisecond duration on success.
- Added clear failure output for non-positive and missing project IDs.
- Added focused command tests using fake storage and Laravel's fake Process API.

### Decisions

- Keep this entry point manual and console-only until the real ffprobe invocation and persisted value are verified.
- Let media-inspection exceptions surface unchanged so failures remain visible during the proof-of-concept stage.

### Verification

- Three focused command tests pass with nine assertions.
- The success test verifies command delegation and persistence of `7,967` milliseconds.
- Invalid and missing IDs fail without starting a subprocess.
- `php artisan help video-projects:inspect` confirms automatic command registration, required argument, description, and usage.
- The complete automated verification results are recorded in the checkpoint for this step.

### Result

One existing project can now be inspected through application code with `php artisan video-projects:inspect <id>`.

### Problems / Notes

- The command has not yet run against a real stored MP4, so application-level ffprobe execution is not manually verified.

### Next

Run the command for one existing project and verify that its real ffprobe duration is persisted correctly.

## 2026-08-23 — Step 3.4d

### Goal

Make the persisted inspection duration visible on the existing project page without triggering inspection from the browser.

### Changes

- Added nullable `duration_ms` to the project page's approved Inertia props and TypeScript interface.
- Displayed inspected duration in seconds with millisecond precision.
- Displayed `Not inspected` when duration remains null.
- Expanded the existing metadata grid from two to three responsive columns.
- Extended project-page feature coverage for both known and null duration values.

### Decisions

- Display the persisted value as seconds with three decimal places so the UI does not hide the stored millisecond precision.
- Keep inspection manual and separate from page rendering in this step.

### Verification

- Three focused project-page tests pass with 22 assertions.
- Prettier, ESLint, and Vue TypeScript checks pass.
- The complete automated verification results are recorded in the checkpoint for this step.
- The known-duration and `Not inspected` states have not yet been manually verified in a browser.

### Result

The project page now communicates whether media inspection has occurred and displays the persisted overall duration when available.

### Problems / Notes

- Real application-level inspection still requires running the Artisan command against an existing project.

### Next

Run the command against one real upload, refresh its project page, and verify the displayed duration matches the command output.

## 2026-08-23 — Step 4.2a

### Goal

Create the smallest tested application boundary that can extract one ASR-ready audio file from a stored video project.

### Changes

- Added `ExtractVideoProjectAudio` as a single-purpose action.
- Added safe FFmpeg execution with array arguments, a 120-second timeout, and an application-controlled private output path.
- Configured extraction as mono, 16 kHz, 16-bit PCM WAV.
- Added stale-output removal, failed-process cleanup, and a non-empty output check.
- Added focused tests covering success, missing source media, partial output after FFmpeg failure, and a successful process that produces no output.

### Decisions

- Keep extraction synchronous and callable only as an internal action for this step.
- Reuse a deterministic per-project audio path without persisting another database field yet.
- Defer ffprobe verification of the resulting audio's format and duration to the next verification step.

### Verification

- Four focused Pest tests pass with 11 assertions.
- Laravel Process fakes prevent real subprocess execution while asserting the exact FFmpeg argument array and timeout.
- Pint passes for the changed PHP files.
- A real MP4 has not yet been processed through this application action.

### Result

The application has a tested internal operation for producing one private ASR-ready WAV artifact, but it has no command, route, or UI entry point yet.

### Problems / Notes

- The FFmpeg executable path is currently the verified development path `/usr/bin/ffmpeg`.
- Output existence is tested; its actual codec, sample rate, channel count, decoding, and duration still require real-media verification.

### Next

Add the smallest manual Artisan entry point for invoking this action against one existing `VideoProject`.

## 2026-08-23 — Step 4.2b

### Goal

Expose the tested audio-extraction action through one minimal manual application entry point.

### Changes

- Added `video-projects:extract-audio {videoProject}` as an auto-registered Artisan command.
- Validated a positive project ID, delegated extraction to `ExtractVideoProjectAudio`, and printed the private relative output path.
- Added focused command tests for success, missing projects, and invalid IDs.
- Expanded the README with a concise command reference for both current video-project operations.

### Decisions

- Keep extraction manual and console-only until it has been verified against real media.
- Use the README as the durable operator-facing command reference; keep the development log chronological rather than relying on it as command documentation.
- Let extraction exceptions remain visible during this proof-of-concept stage, matching the existing inspection command.

### Verification

- Three focused command tests cover the successful and rejected invocation paths without starting a real subprocess.
- Command discovery and help output are checked after implementation.
- The full automated verification results are recorded in the checkpoint for this step.

### Result

One existing video project can now have its audio extracted through application code with `php artisan video-projects:extract-audio <id>`.

### Problems / Notes

- The command has not yet run against a real stored MP4.
- The resulting WAV still requires real format, duration, and decoding verification.

### Next

Run the command against one real upload, then inspect the resulting WAV with ffprobe.

## 2026-08-23 — Step 4.3

### Goal

Verify that application-level extraction produces one real, valid audio artifact ready for an ASR experiment.

### Changes

- No application code changed.
- Recorded the real extraction and media-verification results for video project 3.

### Decisions

- Accept the current mono, 16 kHz, 16-bit PCM WAV output as the initial ASR-ready format.
- Keep provider-specific format optimization deferred until one real ASR provider is selected and tested.

### Verification

- `video-projects:extract-audio 3` completed successfully and created `video-projects/3/audio.wav`.
- ffprobe reported `pcm_s16le`, signed 16-bit samples, 16,000 Hz, one channel, WAV format, and a positive duration of `7.825125` seconds.
- FFmpeg decoded the complete WAV to a null output without reporting an error and returned exit code 0.
- Listening quality has not been manually reported.

### Result

One real uploaded video now produces a valid, fully decodable audio file suitable for the first Georgian ASR proof of concept. Phase 4 is complete.

### Problems / Notes

- The extracted audio duration should be compared with provider behavior during the first ASR experiment; no issue is currently indicated.

### Next

Research a small number of current Georgian-capable ASR providers and recommend one for the first experiment.

## 2026-08-23 — Step 5.1

### Goal

Compare a small number of current Georgian-capable speech-recognition services and recommend one for the first real timestamped transcription experiment.

### Changes

- No application code or dependencies changed.
- Compared ElevenLabs Scribe v2, Google Cloud Speech-to-Text Chirp, and current OpenAI transcription models using official documentation.
- Recommended ElevenLabs Scribe v2 as the first experiment, pending approval and manual provider setup.

### Decisions

- Prefer ElevenLabs Scribe v2 for the first proof of concept because Georgian support, word-level start/end timestamps, speaker IDs, diarization, multilingual detection, and a simple multipart API are explicitly documented together.
- Submit our existing mono 16 kHz PCM WAV with Georgian specified where supported, while retaining verbatim output and avoiding optional rewriting features.
- Keep Google Chirp as a later benchmark: Georgian `ka-GE`, automatic punctuation, model adaptation, and word-level confidence are documented, but Georgian diarization is not listed and Google Cloud setup is heavier for this first experiment.
- Keep current OpenAI transcription models as a later benchmark: code-switching and diarization options are promising, but the reviewed current model pages do not establish as clear a Georgian word-timestamp contract as Scribe's response schema.

### Verification

- ElevenLabs officially lists Georgian and documents precise word timestamps, speaker diarization, language detection, PCM `s16le` 16 kHz mono input, files up to 5 GB, and a structured `words` response containing `text`, `start`, `end`, `type`, and `speaker_id`.
- ElevenLabs' current API pricing page lists Scribe v2 batch transcription at `$0.22` per audio hour, excluding taxes; optional keyterm prompting is an additional `$0.05` per hour.
- Google officially lists Georgian `ka-GE` for Chirp models in European regions and lists automatic punctuation, model adaptation, and word-level confidence for Chirp 2. Its current standard V2 rate begins at `$0.016` per minute.
- OpenAI officially describes GPT Transcribe as supporting keyword and multiple-language hints for multilingual audio and code-switching, and separately offers a diarization model; actual Georgian accuracy and word timing would still require an experiment.
- Provider marketing claims were not treated as proof of Georgian accuracy. No provider has yet been tested against our real Georgian audio.

### Result

ElevenLabs Scribe v2 is the recommended first ASR provider for experimentation, not a permanent provider commitment.

### Problems / Notes

- ElevenLabs logging defaults to enabled; its API documentation says zero-retention mode is limited to enterprise customers. Do not send sensitive media during the personal proof of concept without accepting that constraint.
- Published general multilingual claims do not establish Georgian-specific quality for names, sports terminology, accents, noise, or code-switching.
- Sources reviewed: `https://elevenlabs.io/docs/api-reference/speech-to-text/convert`, `https://elevenlabs.io/pricing/api`, `https://elevenlabs.io/speech-to-text`, `https://docs.cloud.google.com/speech-to-text/docs/speech-to-text-supported-languages`, `https://cloud.google.com/speech-to-text/pricing`, `https://developers.openai.com/api/docs/models/gpt-transcribe`, and `https://developers.openai.com/api/docs/models/gpt-4o-transcribe-diarize`.

### Next

After approval, provide the manual ElevenLabs account, API-key, and environment-variable setup checkpoint required by Phase 5.2.

## 2026-08-23 — Step 5.2

### Goal

Prepare an isolated local ASR runtime without adding Python dependencies to Laravel or paying a hosted provider.

### Changes

- Chose local faster-whisper for the first experiment after reviewing hosted-provider cost and privacy tradeoffs.
- Created the Python virtual environment outside the repository at `/home/lukachochua/.virtualenvs/georgian-captioner-asr` as a manual setup step.
- Installed faster-whisper 1.2.1 into that isolated environment.
- No application code or Composer/npm dependencies changed.

### Decisions

- Start the real experiment with multilingual Whisper `medium`, CPU `int8`, and word timestamps.
- Keep ElevenLabs Scribe v2 as a later quality benchmark rather than the first provider.
- Do not design the Laravel/Python integration boundary until real Georgian output and local runtime performance are known.

### Verification

- The environment uses the already-installed Python 3.12 runtime with `venv` isolation.
- `import faster_whisper` succeeded and reported version `1.2.1`.
- Importing `WhisperModel` succeeded and printed `faster-whisper ready`.
- The model itself has not yet been downloaded or run.

### Result

The machine is ready to run the first local Georgian timestamped-transcription experiment without an API account or key.

### Problems / Notes

- Model inference will be CPU-based because this machine has Intel integrated graphics and no NVIDIA CUDA GPU.
- The model download, runtime, Georgian accuracy, and timestamp quality remain unverified.

### Next

Download `medium` through the faster-whisper runtime and transcribe project 3's real WAV with CPU `int8` and word timestamps, preserving the raw result for inspection.

## 2026-08-23 — Step 5.3

### Goal

Run one real Georgian WAV through local faster-whisper and preserve enough raw output to evaluate text, timestamps, and performance before application integration.

### Changes

- Added the root-level `transcribe_local.py` experiment script.
- Fixed the experiment to multilingual Whisper `medium`, CPU `int8`, Georgian language `ka`, and word timestamps.
- Serialized experiment settings, runtime, language metadata, transcript text, segment metadata, and word timing/probability data to private JSON.
- Documented activation and execution commands in the README.
- Ignored generated Python bytecode directories.

### Decisions

- Keep this as an explicit experiment rather than connecting Laravel to Python prematurely.
- Preserve the output beside the private project media at `video-projects/{id}/transcription.raw.json`; do not commit media or transcription artifacts.
- Treat model-reported language probability as metadata, not evidence of transcription accuracy.

### Verification

- Python syntax compilation and command help completed successfully.
- The first run downloaded and loaded `medium`, then transcribed project 3's real `7.825125`-second WAV.
- Model download/load took `206.727` seconds; transcription took `94.924` seconds on CPU.
- The result reported Georgian `ka` with language probability `1.0` and preserved one segment containing nine timestamped words.
- The private JSON file is valid, readable, and 2,271 bytes.
- Several words have identical start and end timestamps, so the raw word timing is not yet acceptable without further evaluation.
- The transcript is `სე჎ე სე჈ე დიკიაე. სემიე სეეთე ეე გეიიე დეთე რრედრერ.` and appears linguistically implausible; the user has not yet compared it with the actual speech.
- A controlled repeat used newly uploaded project 4 with clearer sound: application inspection persisted `14,067` milliseconds and extraction produced a valid `13.885563`-second mono 16 kHz PCM WAV.
- With the model already cached, project 4 loaded in `2.972` seconds and took `201.320` seconds to transcribe on CPU.
- The second transcript still contained malformed or obsolete Georgian characters, mixed Latin fragments, and five zero-duration words among 14 words. Better recording volume therefore did not resolve the observed `medium` quality problem.

### Result

Local ASR and timestamp serialization work technically, but `medium` is slow and produced unusable Georgian text and timing on two recordings. It is rejected for application integration.

### Problems / Notes

- The initial model download used unauthenticated Hugging Face access and emitted only a rate-limit warning.
- Three words received zero-duration timestamps, which would violate future cue timing rules.
- A warm repeat run may separate model-download cost from normal model load and inference cost, but text quality should be evaluated first.

### Next

Decide whether to run one controlled `large-v3` experiment against project 4 before reconsidering hosted or Georgian-specialized alternatives.

## 2026-08-23 — Step 5.3b

### Goal

Determine whether generic Whisper `large-v3` materially improves Georgian text and word timing over the rejected `medium` model on the same clear recording.

### Changes

- Added an explicit `--model` choice to `transcribe_local.py`, limited to the two models under active evaluation.
- Documented the controlled `large-v3` command in the README.
- Removed the rejected 1.5 GB `medium` model cache at the user's request.
- Downloaded and ran `large-v3` with the same CPU `int8`, Georgian-language, and word-timestamp settings against project 4.

### Decisions

- Keep this result experimental; do not connect `large-v3` to Laravel.
- Treat CPU runtime and timestamp integrity as first-class acceptance criteria alongside transcript readability.
- Do not switch to a community Georgian fine-tune solely because it is language-specific; inspect its data, metrics, runtime format, and timestamp support first.

### Verification

- The `large-v3` cache occupies 2.9 GB; the previous `medium` cache is absent.
- Initial model download/load took `467.154` seconds.
- Transcribing project 4's `13.885563`-second WAV took `480.536` seconds on CPU `int8`.
- The result preserved two segments and 27 word entries from `1.26` through `13.86` seconds.
- Eleven word entries have identical start and end times, so timestamp integrity remains unacceptable.
- Text quality improved dramatically: the model recognized a Georgian counting sequence and greetings, but retained spelling errors, hallucinated text, and Unicode replacement characters.
- The Georgian Large v2 community fine-tune reviewed during the run reports self-evaluated Common Voice 11 Georgian WER of approximately `31.85%`, sparse intended-use documentation, and a Transformers/PyTorch rather than ready-made faster-whisper runtime.

### Result

Generic `large-v3` is substantially more capable in Georgian than `medium`, but its eight-minute CPU transcription time for fourteen seconds of audio and its invalid word timings make it unsuitable for direct personal-V1 integration on this machine.

### Problems / Notes

- A warm `large-v3` run would remove download cost but not the measured `480.536`-second inference cost.
- The exact spoken reference text is still needed to classify transcription errors objectively.
- `large-v3-turbo` may offer a better speed/quality balance, but Georgian quality is unproven.

### Next

Compare the transcript with the exact spoken Georgian, then decide whether one `large-v3-turbo` experiment is justified.

## 2026-08-23 — Step 5.3c

### Goal

Determine whether `large-v3-turbo` provides a practical local CPU speed/quality compromise for Georgian transcription.

### Changes

- Added `large-v3-turbo` as an explicit experiment-script model choice and documented its command in the README.
- Removed the 2.9 GB generic `large-v3` cache at the user's request while retaining its private JSON result.
- Downloaded and ran `large-v3-turbo` against project 4 with the same CPU `int8`, Georgian-language, and word-timestamp settings.

### Decisions

- Reject generic `large-v3-turbo` for the current Georgian workflow because speed improvement did not preserve usable text quality.
- Keep its 1.6 GB cache until explicitly asked to remove it.
- Stop treating progressively different generic Whisper sizes as likely solutions; investigate Georgian-specific local models next.

### Verification

- The generic `large-v3` cache is absent and can only be restored by downloading it again.
- The Turbo model download/load took `352.945` seconds; transcription took `85.550` seconds for `13.885563` seconds of audio.
- Turbo produced one segment with 14 timed words and no zero-duration entries from `2.08` through `13.86` seconds.
- The transcript was `ეერსს ლდს მიიც გაუივეს მის უირთრს მოირს მვირს განხოის ყიოუეს იყუოვთრყბი ნსსოს ოსთოს მხარ�`, which is unusable Georgian despite structurally better timestamps.
- The Turbo cache occupies 1.6 GB at `models--mobiuslabsgmbh--faster-whisper-large-v3-turbo`.

### Result

Turbo reduced inference time by approximately 82% relative to generic `large-v3`, but its Georgian text quality failed the core product requirement. It is not suitable for integration.

### Problems / Notes

- Even Turbo remained over six times slower than real time on this CPU.
- Language probability remained `1.0` despite unusable text, reinforcing that it cannot be treated as an accuracy score.

### Next

Evaluate Georgian-specific local ASR candidates before installing or downloading another model.

## 2026-08-23 — Step 5.4

### Goal

Determine what GeoCaption publicly discloses about its transcription pipeline and identify credible Georgian-specific local ASR candidates using documented data, metrics, licensing, runtime, and timestamp support.

### Changes

- No application code or dependencies changed.
- Reviewed GeoCaption's public website, blog, upload-page client bundle, and public API-route names without authenticating or probing private endpoints.
- Compared Georgian Whisper, Wav2Vec2, NVIDIA FastConformer, Meta Omnilingual ASR, and CPU-oriented conversions.
- Recommended NVIDIA `stt_ka_fastconformer_hybrid_large_pc` as the next controlled local model experiment.

### Decisions

- Treat GeoCaption's published accuracy and speed figures as product claims rather than independently verified benchmarks.
- Prefer the NVIDIA Georgian FastConformer checkpoint over the older Georgian Whisper Large v2 fine-tune because its documented Georgian data, WER, model size, and timestamp-capable NeMo architecture are substantially stronger.
- Do not install NeMo, ONNX Runtime, CrispASR, or another Python runtime until the exact experiment path is approved.
- Keep raw recognition and optional Georgian correction as separate stages; do not allow correction to invent caption timing.

### Verification

- GeoCaption publicly states that it uses Whisper Large v3 for recognition and describes a two-stage recognition plus Georgian grammar/punctuation correction system.
- GeoCaption's public assets identify Next.js, Supabase, `/api/upload`, and `/api/transcribe`, but do not disclose its compute provider, GPU, Whisper runtime, correction model, or alignment implementation.
- NVIDIA's official Georgian FastConformer is approximately 115M parameters, accepts mono 16 kHz WAV, uses a 1,024-token Georgian SentencePiece vocabulary, and is trained on approximately 163 hours from Common Voice 17 and FLEURS.
- NVIDIA reports greedy-decoding WER of `5.73%` on Common Voice 17 and `13.44%` on FLEURS; these are self-reported benchmark results and not evidence for our real media.
- NVIDIA NeMo documents word timestamp computation for FastConformer transducer/CTC models.
- The official checkpoint is CC BY 4.0. Community conversions include a PyTorch-free ONNX export and GGUF sizes of 219 MB F16, 130 MB Q8, and 82 MB Q4; conversion quality and timestamp workflow still require testing.
- The older Georgian Whisper Large v2 fine-tune reports approximately `31.85%` WER. The older Georgian Wav2Vec2 checkpoint shows plausible examples but has outdated runtime instructions and weaker published evidence.
- Meta Omnilingual ASR is actively maintained and Apache 2.0, but its recommended models and fairseq2 runtime are heavier and no Georgian-specific benchmark was located in this research pass.

### Result

NVIDIA Georgian FastConformer is the best-supported local candidate found for the next experiment. GeoCaption appears to obtain its product quality from generic Large v3 plus undisclosed infrastructure and post-correction rather than a publicly disclosed Georgian-specific model.

### Problems / Notes

- GeoCaption's claimed two-stage correction could improve spelling while also altering words; timing must remain grounded in ASR or alignment output.
- The lightest community FastConformer conversions are not NVIDIA-published artifacts even though they preserve the official weights.
- Sources reviewed include `https://www.geocaption.com/`, `https://www.geocaption.com/blog/kartuli-ai-subtitrebi-sruli-gaidi-2025`, `https://huggingface.co/nvidia/stt_ka_fastconformer_hybrid_large_pc`, `https://docs.nvidia.com/nemo-framework/user-guide/24.07/nemotoolkit/asr/intro.html`, `https://huggingface.co/OpenVoiceOS/stt_ka_fastconformer_hybrid_large_pc_onnx`, `https://huggingface.co/cstr/stt-ka-fastconformer-hybrid-ctc-large-GGUF`, and `https://github.com/facebookresearch/omnilingual-asr`.

### Next

Approve NVIDIA Georgian FastConformer as the next experiment, then select and set up the smallest runtime that can test both transcription quality and timestamp extraction.

## 2026-08-23 — Step 5.5

### Goal

Test NVIDIA's Georgian FastConformer through its native NeMo runtime and inspect real timestamp output before integrating any ASR runtime with Laravel.

### Changes

- Created a separate external NeMo environment with CPU-only PyTorch; no application dependency was added.
- Added `transcribe_nemo.py` as a controlled single-file experiment for `nvidia/stt_ka_fastconformer_hybrid_large_pc`.
- Documented the exact NeMo experiment command in the README.
- Removed the rejected 1.6 GB `large-v3-turbo` cache at the user's request.
- Preserved the private project 4 result as `transcription.nemo-fastconformer.raw.json`.

### Decisions

- Evaluate the official checkpoint through NeMo before considering its lighter community ONNX conversion.
- Keep the experiment outside Laravel until transcription quality is manually assessed.
- Preserve NeMo's native timestamp structures without normalizing them into the application's future internal format.

### Verification

- PyTorch `2.13.0+cpu` and NeMo ASR imported successfully on the Intel-only development laptop.
- The model restored successfully from the official NVIDIA Hugging Face checkpoint.
- Initial model download/load took `68.662` seconds; transcribing the `13.885563`-second project 4 WAV took `1.038` seconds on CPU.
- NeMo returned token timestep, character, word, and segment timestamp structures.
- All 17 word entries have positive duration and are ordered without overlap.
- The final timestamp ends at `13.92` seconds, approximately 34 milliseconds beyond the extracted WAV duration but within the source video's `14.067`-second playback duration.
- The transcript is `ერთი ორი, სამი, ოთხი, ხუთი ექვსი შვიდი რვა ცხრა ათი გამარჯობა გაგიმარჯოს, როგორ ხარმე. შელო მოხარმეც კარგა.`
- Script syntax compilation passed.
- Manual comparison found that the intended final phrase was `როგორ ხარ შენ? კარგად ვარ, შენ?`; the model returned `როგორ ხარმე. შელო მოხარმეც კარგა.`. The preceding counting and greeting were substantially more accurate.

### Result

The official Georgian FastConformer runs substantially faster than real time on this laptop after loading and produces structurally useful word and segment timestamps. It remains the leading local candidate, with further quality testing deferred until better representative audio is available.

### Problems / Notes

- This laptop has no NVIDIA GPU; the same experiment can later use CUDA on the user's RTX 4060 Ti PC after machine-specific PyTorch setup.
- NeMo emits informational training-configuration and missing-accelerator warnings during CPU inference; they did not prevent successful transcription.
- The first model download was unauthenticated and emitted a Hugging Face rate-limit warning.

### Next

Define the application's internal timestamp representation from the observed NeMo word output.

## 2026-08-23 — Step 6.1

### Goal

Choose a deterministic internal timestamp representation using the first real NeMo word output and the already persisted media duration.

### Changes

- Added the internal ASR timestamp decision to `DECISIONS.md`.
- Updated the immediate backlog to move from provider experimentation toward the minimum internal word representation.
- No application code, schema, raw transcription, or dependencies changed.

### Decisions

- Normalize provider word boundaries to rounded integer milliseconds named `start_ms` and `end_ms`.
- Require ordered, positive-duration intervals bounded by the known source-video duration.
- Clamp only small timestamp-granularity overruns beyond the video duration; reject other invalid timing during conversion.
- Preserve provider output unchanged and initially retain punctuation within word text.
- Defer confidence and speaker fields until real provider data justifies them.

### Verification

- Checked the rules against project 4's 17 NeMo word timestamps: all are ordered and positive-duration.
- The observed final boundary converts to `13,920` milliseconds and remains valid against the source video's persisted `14,067`-millisecond duration; the shorter extracted WAV must not truncate it.
- Documentation whitespace validation passed.
- No automated application test was required because this step records a design decision only.

### Result

The application now has an explicit provider-independent timestamp unit and minimum validation rules grounded in real Georgian ASR output.

### Problems / Notes

- The exact tolerance that distinguishes a harmless granularity overrun from invalid provider data should be defined alongside conversion code and tests, rather than guessed in this documentation-only step.
- Cue overlap and inclusive/exclusive playback boundaries are intentionally still undecided.

### Next

Propose the smallest internal timestamped-word shape before implementing conversion.

## 2026-08-23 — Step 6.2

### Goal

Define the smallest provider-independent representation for one timestamped transcription word without implementing storage or conversion prematurely.

### Changes

- Documented an immutable `TranscriptionWord` PHP value object containing only `text`, `startMs`, and `endMs`.
- Assigned single-word invariants to the value object and sequence/media-duration validation to the future converter.
- Updated the backlog to make class-location approval and implementation the next small step.
- No PHP class, schema, application behavior, dependency, or raw ASR output changed.

### Decisions

- Use a plain immutable PHP object rather than an Eloquent model, associative array, or third-party DTO package.
- Require non-empty text, a non-negative start, and an end strictly greater than the start.
- Do not include speaker, confidence, provider metadata, token data, character timing, or segment timing yet.
- Defer class namespace/location until implementation because the repository has no established value-object directory.

### Verification

- Checked the proposed fields against the 17 real NeMo word entries; each can be represented without provider-specific data.
- Confirmed collection ordering and duration clamping require context outside a single word and therefore do not belong in its constructor.
- Documentation whitespace validation passed.
- No automated application tests were required because this step defines the representation without implementing it.

### Result

The minimum internal word shape and its responsibility boundary are explicit and ready for a small test-driven implementation after approval.

### Problems / Notes

- A new class location will be necessary, and repository instructions require approval before creating a new base folder.
- Serialization is intentionally deferred until a real consumer requires it.

### Next

Approve a focused class location, then create and unit-test only the immutable `TranscriptionWord` value object.

## 2026-08-23 — Step 6.2b

### Goal

Implement only the approved immutable internal word representation and prove its local invariants with focused tests.

### Changes

- Added `App\ValueObjects\TranscriptionWord` with readonly `text`, `startMs`, and `endMs` properties.
- Added constructor validation for non-empty text, non-negative start time, and end time strictly after start time.
- Added focused Pest unit coverage for valid Georgian text, punctuation retention, every invalid boundary, whitespace-only text, and immutability.
- No provider conversion, persistence, serialization, or Laravel integration was added.

### Decisions

- Approved `app/ValueObjects` as the focused location for this invariant-driven domain value.
- Use `InvalidArgumentException` for invalid construction because these failures represent invalid caller-supplied values rather than recoverable provider or processing failures.

### Verification

- Pint completed successfully for changed PHP files.
- `tests/Unit/TranscriptionWordTest.php` passes: 8 tests and 16 assertions.
- PHPStan completed with zero errors after rerunning outside the restricted sandbox so its worker could bind a local ephemeral port.
- The first static-analysis attempt did not analyze code because the sandbox prevented that local socket; it was not an application failure.

### Result

The application now has a tested immutable representation for one normalized timestamped word, without coupling it to NeMo or database storage.

### Problems / Notes

- Collection ordering, media-duration clamping, and conversion failure behavior remain intentionally outside this value object.

### Next

Propose the smallest NeMo conversion boundary and explicitly define the acceptable timestamp-overrun tolerance before implementing it.

## 2026-08-23 — Step 6.3a

### Goal

Define the smallest provider conversion boundary and resolve timestamp tolerance and ordering behavior before writing conversion code.

### Changes

- Specified a single provider-specific `ConvertNemoTranscriptionWords` action that accepts decoded NeMo data plus known media duration and returns `TranscriptionWord` objects.
- Defined structural, numeric, timing, sequence, and duration validation behavior.
- Set a fixed 100-millisecond maximum duration-overrun tolerance.
- No converter code, fixture, persistence, command, or provider abstraction was added.

### Decisions

- Use an action consistent with the application's existing single-purpose operation boundaries.
- Base the 100-millisecond clamp tolerance on the observed 80-millisecond NeMo timestamp grid; reject larger overruns.
- Preserve provider ordering and reject decreasing start or end sequences instead of sorting silently.
- Allow overlapping word alignments for now; caption-cue overlap remains a later product decision.
- Fail the complete conversion on malformed input rather than returning partial data.

### Verification

- Confirmed the 100-millisecond tolerance is slightly larger than one 80-millisecond NeMo timestamp frame. Project 4 does not exercise the clamp when correctly bounded by its source-video duration.
- Checked the ordering rules against all 17 observed NeMo word entries; their starts and ends are nondecreasing.
- Confirmed that the converter can remain independent of model execution and database persistence.
- Documentation whitespace validation passed.

### Result

The NeMo conversion contract is explicit enough to implement and test without making further architectural or product decisions.

### Problems / Notes

- The selected 100-millisecond tolerance is evidence-based but has only one real recording behind it; later QA may justify revisiting it.
- Word overlap is accepted at the alignment layer but must not silently determine future cue overlap behavior.

### Next

Implement and unit-test only `ConvertNemoTranscriptionWords` against small in-memory NeMo-shaped arrays.

## 2026-08-23 — Step 6.3b

### Goal

Implement the approved NeMo word-conversion contract without adding file access, persistence, or a generic provider framework.

### Changes

- Added `App\Actions\ConvertNemoTranscriptionWords`.
- Converted finite numeric NeMo second boundaries into rounded integer milliseconds.
- Added strict response-shape, text, interval, duration, and sequence validation.
- Added the approved 100-millisecond duration-overrun clamp while preserving input order and permitting ordered word overlap.
- Added focused Pest unit tests using small in-memory NeMo-shaped arrays.

### Decisions

- Treat invalid media duration as caller misuse with `InvalidArgumentException`.
- Treat malformed or invalid NeMo output as `UnexpectedValueException` and fail the entire conversion.
- Accept only actual JSON numeric types for boundaries; numeric strings are rejected rather than coerced.

### Verification

- The converter and value-object unit suites pass: 26 tests and 42 assertions.
- Tests cover rounding, punctuation, a synthetic 34-millisecond clamp, permitted word overlap, non-positive duration, missing and malformed lists, invalid entries, non-finite and negative boundaries, reversed intervals, excessive overruns, and decreasing start/end sequences.
- Pint completed successfully for changed PHP files.
- PHPStan completed with zero errors.
- Documentation whitespace validation passed.

### Result

Decoded NeMo output can now be converted deterministically into validated provider-independent words without executing the model or touching storage.

### Problems / Notes

- Tests intentionally use minimal in-memory arrays. A representative saved fixture is the next evidence step before running conversion against the private project 4 artifact.
- Normalized words are not persisted or exposed to the frontend.

### Next

Add one minimal saved NeMo response fixture and prove it converts correctly without committing private media or the complete experimental response.

## 2026-08-23 — Step 6.4

### Goal

Prove the converter handles a realistic JSON-decoded NeMo response without committing private media or the complete project 4 experiment.

### Changes

- Added a small synthetic `tests/Fixtures/nemo-transcription.json` response containing representative NeMo metadata, token, word, character, and segment structures.
- Added a fixture-backed converter test covering real JSON decoding, ignored provider fields, Georgian punctuation, ordering, and duration clamping.
- No private recording, complete experimental transcript, model file, storage path, or user-specific content was added to the fixture.

### Decisions

- Keep one minimal committed NeMo fixture rather than using the full private experimental response as permanent test data.
- Include provider fields outside `timestamp.word` to prove the converter reads only its approved boundary.

### Verification

- The converter suite passes: 19 tests and 27 assertions.
- The converter plus value-object suites pass together: 27 tests and 43 assertions.
- The fixture's final `2,920`-millisecond word boundary correctly clamps to its synthetic `2,886`-millisecond media duration.
- Pint completed successfully for changed PHP files.
- PHPStan completed with zero errors.
- Documentation whitespace validation passed.

### Result

Phase 6 is complete: downstream code can consume validated Georgian timestamped words without knowing NeMo's response structure, and conversion is covered by both focused edge cases and a representative decoded fixture.

### Problems / Notes

- The application still has no normal workflow that invokes NeMo or associates raw transcription output with a `VideoProject`.
- Normalized words are intentionally not persisted yet.

### Next

Begin Phase 7.1 by proposing the smallest development-only path for displaying or debugging the ordered normalized word sequence from one real project result.

## 2026-08-23 — Step 7.1

### Goal

Display the ordered normalized words from one real `VideoProject` result without persisting data or generating caption cues.

### Changes

- Added the read-only `video-projects:inspect-transcription {videoProject}` Artisan command.
- The command resolves a positive project ID, requires persisted video duration, reads only the fixed private NeMo result path, decodes JSON strictly, converts it through `ConvertNemoTranscriptionWords`, and prints order, text, start milliseconds, and end milliseconds.
- Added command tests for successful table output, missing duration, missing result, invalid JSON, failed word conversion, missing project, and invalid project ID.
- Documented the command in the README.
- Corrected the timestamp boundary documentation to distinguish extracted WAV duration from the source-video playback duration.

### Decisions

- Use the persisted source-video `duration_ms` as the caption-alignment upper bound; do not truncate captions merely because the extracted audio stream is slightly shorter.
- Keep this command read-only and development-facing. It does not accept arbitrary paths, run NeMo, save normalized words, or generate cues.
- Retain the 100-millisecond clamp only as a defensive allowance for a genuine provider overrun beyond the video boundary.

### Verification

- The command feature suite passes: 7 tests and 21 assertions.
- The command, converter, and value-object suites pass together: 34 tests and 63 assertions.
- Pint completed successfully for changed PHP files.
- PHPStan completed with zero errors.
- Running `php artisan video-projects:inspect-transcription 4` succeeded against the real private result and displayed all 17 ordered normalized words.
- The real final word ends at `13,920` milliseconds, inside project 4's persisted `14,067`-millisecond video duration, so no clamp was applied.

### Result

The real project 4 NeMo output is now inspectable as validated internal words through a safe, read-only application command.

### Problems / Notes

- The displayed final phrase still reflects the known ASR error and is not treated as ground-truth text.
- Normalized words remain transient and are not stored or shown in the browser.

### Next

Propose the minimum caption-cue representation using the inspected real word sequence before implementing word grouping.

## 2026-08-23 — Step 7.3a

### Goal

Implement the minimum provider-independent representation for one generated caption cue without introducing grouping, persistence, editing, or styling.

### Changes

- Added immutable `App\ValueObjects\CaptionCue` with `order`, `text`, `startMs`, and `endMs`.
- Added constructor validation for positive one-based order, non-empty text, non-negative start, and end strictly after start.
- Added focused Pest unit coverage for valid Georgian cue text, all invalid local values, and readonly behavior.

### Decisions

- Use one-based cue order because it matches human-facing cue tables and subtitle conventions.
- Keep collection-level ordering, source-video bounds, cue overlap, grouping, and punctuation assembly outside the individual cue.
- Do not add persistence, editable identity, styling, speaker, confidence, or provider data yet.

### Verification

- The `CaptionCue` suite passes: 10 tests and 21 assertions.
- The caption-cue and transcription-word value-object suites pass together: 18 tests and 37 assertions.
- Pint completed successfully for changed PHP files.
- PHPStan completed with zero errors.
- Documentation whitespace validation passed.

### Result

The first deterministic cue generator now has a small, tested output type while storage and editor concerns remain deliberately deferred.

### Problems / Notes

- Consecutive cue ordering and overlap cannot be validated by one isolated cue and must be enforced by the generator or a future collection boundary.
- Cue text assembly for Georgian punctuation is not yet decided.

### Next

Propose the first deterministic word-to-cue grouping rules using the real project 4 word sequence, then implement only the approved algorithm.

## 2026-08-23 — Step 7.2a

### Goal

Define a transparent first word-to-cue grouping algorithm using the real project 4 sequence before implementing it.

### Changes

- Defined deterministic cue boundaries for strong punctuation, speech gaps, word count, Unicode character count, and cue duration.
- Defined exact text assembly, timing, ordering, single-oversized-word, and overlapping-input behavior.
- Recorded the four cues expected from project 4's current 17-word transcription.
- No grouping code, persistence, UI, or dependency was added.

### Decisions

- Split after `.`, `?`, `!`, or `…`.
- Split before a next-word gap of at least 800 milliseconds.
- Limit normal cues to 8 words, 42 Unicode characters, and 3,500 milliseconds.
- Join provider words with one space without rewriting punctuation.
- Reject overlapping word intervals in the first generator so it cannot emit ambiguous overlapping cues.
- Always emit a single oversized word rather than dropping it or producing an empty cue.

### Verification

- Applied the rules manually to all 17 normalized project 4 words.
- The expected result is four ordered, non-overlapping cues covering every word exactly once.
- The 800-millisecond gap separates `ცხრა` from `ათი`, while the observed shorter gaps remain eligible for grouping.
- The third cue ends at the recognized period and remains within the 3,500-millisecond duration limit.
- Documentation whitespace validation passed.

### Result

The first cue algorithm has explicit, testable inputs, thresholds, boundary priority, and expected real-project output.

### Problems / Notes

- The project 4 transcript contains known recognition errors, but its word timing remains useful for validating grouping behavior.
- ASR punctuation errors may create poor boundaries; the hard limits and later manual editing are the initial safeguards.
- Threshold tuning is deferred until captions can be viewed over playing video.

### Next

Implement and unit-test only the deterministic word-to-cue grouping action.

## 2026-08-23 — Step 7.2b

### Goal

Implement the approved deterministic word-to-cue rules and prove each boundary independently before connecting generation to project inspection.

### Changes

- Added `App\Actions\GenerateCaptionCues` with fixed first-pass thresholds for punctuation, speech gap, word count, Unicode character count, and cue duration.
- Added deterministic text assembly, consecutive one-based ordering, first/last word timing, single-oversized-word handling, and explicit overlapping-input rejection.
- Added focused Pest unit tests for the complete project 4 sequence and every approved boundary.
- No command integration, persistence, frontend behavior, configurable presets, or dependency was added.

### Decisions

- Return an empty cue list for an empty word list; this represents transcription with no consumable speech rather than an invalid cue.
- Keep the initial thresholds as private action constants until playback QA demonstrates a need for configuration.
- Trust the documented `list<TranscriptionWord>` method contract and avoid redundant runtime type checks; chronological overlap remains validated explicitly.

### Verification

- The generator produces exactly the four project 4 cues documented in Step 7.2a.
- Tests cover all four strong punctuation marks, the exact 800-millisecond gap boundary, the ninth-word split, multibyte-safe Georgian character limits, duration overflow, oversized single words, overlapping input rejection, and empty input.
- The generator and both related value-object suites pass: 30 tests and 60 assertions.
- Pint completed successfully for changed PHP files.
- PHPStan completed with zero errors after removing a redundant `instanceof` check identified as always true from the typed list contract.
- Documentation whitespace validation passed.

### Result

Validated transcription words can now become deterministic, ordered `CaptionCue` objects without provider knowledge, persistence, or an LLM.

### Problems / Notes

- The grouping thresholds have automated coverage but have not yet been manually evaluated over playing video.
- Invalid ASR punctuation can still cause an undesirable early cue boundary; manual editing and later evidence-based tuning remain necessary.

### Next

Extend the existing read-only project transcription command to display generated cues, then compare those boundaries manually before changing the algorithm.

## 2026-08-23 — Step 7.4

### Goal

Run deterministic cue generation against the real project 4 transcription and display the result without persistence or browser integration.

### Changes

- Injected `GenerateCaptionCues` into the existing read-only transcription inspection command.
- Added a second console table for generated cue order, text, start milliseconds, and end milliseconds.
- Extended the command feature test to verify fixture-backed cue generation and table output.
- Updated the README to state that the command displays both normalized words and generated cues.

### Decisions

- Keep word and cue tables together in the development command so the grouping result remains traceable to its source words.
- Do not persist cues or duplicate this loading logic in the project-page controller during this step.

### Verification

- The command feature suite passes with its expanded output: 7 tests and 26 assertions.
- The command, generator, and related value-object suites pass together: 37 tests and 86 assertions.
- Pint formatted the changed command successfully.
- PHPStan completed with zero errors.
- Running `php artisan video-projects:inspect-transcription 4` against the real private result displayed all 17 words followed by exactly the four expected cues from Step 7.2a.

### Result

Real Georgian ASR words now become inspectable caption cues through Laravel, validating the complete transient path from preserved NeMo JSON to deterministic cue output.

### Problems / Notes

- Cue timing and readability have not yet been manually compared with playing project 4 video.
- The command currently owns private result loading; browser display should first extract the smallest reusable application boundary rather than duplicate that logic.

### Next

Review the four cue boundaries against project 4 playback, then propose the smallest reusable boundary for a read-only cue table on the project page.

## 2026-08-23 — Step 7.4b

### Goal

Extract the private-result loading and transformation pipeline from the console command so a future project page can reuse it without duplicating provider or storage logic.

### Changes

- Added `App\Actions\LoadVideoProjectCaptionData` with constructor-injected word conversion and cue generation actions.
- Moved fixed private path resolution, duration requirement, file existence checks, strict JSON decoding, word normalization, and cue generation into the new action.
- Returned both transient normalized words and generated cues through a documented array shape.
- Refactored the inspection command to delegate to the action while preserving its output and clear failure messages.
- Added focused action tests for successful fixture loading, missing duration, missing private result, and invalid JSON.

### Decisions

- Keep loading strict: missing or malformed data throws rather than returning partial caption data.
- Keep the result transient and avoid a repository, provider interface, persistence schema, or arbitrary path parameter.
- Preserve both words and cues in the return value because the inspection command needs traceability and the pipeline should only convert once per load.

### Verification

- The new action and refactored command suites pass: 11 tests and 34 assertions.
- The full focused caption-data pipeline passes: 60 tests and 121 assertions.
- Pint completed successfully for changed PHP files.
- PHPStan completed with zero errors.
- Running `php artisan video-projects:inspect-transcription 4` after the refactor produced the same 17 normalized words and four caption cues as before.

### Result

Console and future HTTP callers now have one tested boundary for loading transient generated caption data from a project.

### Problems / Notes

- The strict action throws when a project has no transcription; the project page must translate that expected pre-transcription state into an explicit empty UI without treating malformed existing data as absent.
- Caption data is still generated on each load and is not editable or persistent.

### Next

Propose the smallest read-only project-page cue table and its explicit no-transcription state before wiring backend props to Vue.

## 2026-08-23 — Step 7.5a

### Goal

Provide transient generated cue data to the existing Inertia project page while distinguishing an absent transcription from malformed existing data.

### Changes

- Added fixed-path result-existence detection to `LoadVideoProjectCaptionData` without weakening its strict loading behavior.
- Injected the caption-data loader into `ShowVideoProjectController`.
- Added a root `cues` Inertia prop serialized as `order`, `text`, `start_ms`, and `end_ms`.
- Sent `cues: null` when no private NeMo result exists.
- Added endpoint tests for absent, valid, and malformed transcription states.
- No Vue template, TypeScript prop, styling, persistence, or raw provider JSON exposure changed.

### Decisions

- Use `null` to mean “no transcription result exists yet”; use an array, including a possible empty array, for successfully loaded generated cue data.
- Check only fixed-path result existence before invoking the loader. If a result exists, duration, JSON, normalization, and generation errors remain visible server failures.
- Serialize millisecond fields with snake-case names to match existing backend page props.

### Verification

- Project-page and caption-data action tests pass: 10 tests and 44 assertions.
- The complete focused backend caption pipeline passes: 66 tests and 157 assertions.
- Tests verify `cues: null` without a result, exact generated cue props with a valid fixture, and a server error for invalid existing JSON.
- Pint completed successfully for changed PHP files.
- PHPStan completed with zero errors.
- Inertia v3 endpoint assertion syntax was verified against the official current documentation and matches the repository's existing `AssertableInertia` convention.

### Result

The project-page response now carries safe transient generated cue data when available and an unambiguous absent state when transcription has not been produced.

### Problems / Notes

- The current Vue page does not declare or render the new prop yet, so there is no visible browser change in this step.
- Loading still regenerates cues from the private raw response on every project-page request.

### Next

Add the typed nullable cue prop and a basic read-only cue table below the native video, with an explicit “No transcription yet” message.

## 2026-08-23 — Step 7.5b

### Goal

Display transient generated caption cues on the existing project page without introducing editing, seeking, overlay, or persistence.

### Changes

- Added a typed nullable `CaptionCue[]` Inertia prop to the Vue project page.
- Added a read-only responsive table showing one-based cue order, Georgian caption text, and start/end seconds with millisecond precision.
- Added explicit UI states for no transcription result and a successfully loaded result that generates no cues.
- Preserved the existing native video player, metadata, navigation, and dark-mode styling.
- No frontend state library, component library, dependency, interaction, or caption overlay was added.

### Decisions

- Render `cues === null` as “No transcription yet.” and reserve an empty array for “No caption cues were generated.”
- Format integer milliseconds as fixed three-decimal seconds for human inspection while retaining integer values in page data.
- Keep the table in the existing page component until reuse or complexity justifies extraction.

### Verification

- Prettier formatted the changed Vue page successfully.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The project-page feature suite passes: 5 tests and 34 assertions.
- The build emitted an informational notice that optional `fontaine` optimized fallbacks are unavailable; no dependency was added because the feature builds successfully without it.
- Browser appearance and real cue timing have not yet been manually verified.

### Result

Projects with a valid private transcription now display generated Georgian caption cues directly beneath the source video, while untranscribed projects show an explicit empty state.

### Problems / Notes

- The table is read-only and cannot seek the video or show which cue is active.
- Cue text still contains known ASR errors from project 4.

### Next

Manually inspect project 4's table and timing, then begin Phase 8.1 by reading and displaying the native video's current playback time.

## 2026-08-23 — Step 8.1

### Goal

Read the native video element's current playback time into immediate Vue state and expose it during development without selecting or rendering an active cue.

### Changes

- Added a local Vue `ref` initialized to zero seconds.
- Added a typed native `timeupdate` handler that copies `HTMLVideoElement.currentTime` into that state.
- Added a compact playback-time readout beneath the native video with three-decimal precision and tabular numerals.
- No server round-trip, active-cue computation, overlay, seeking, editing, or dependency was added.

### Decisions

- Keep immediate playback state local to the project page using Vue's built-in `ref`.
- Retain browser-native `currentTime` in seconds at the video boundary; conversion to integer milliseconds belongs in the future active-cue selector call.
- Use the standard `timeupdate` event for the first milestone rather than animation frames or a custom player loop.

### Verification

- Prettier formatted the changed Vue page successfully.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The project-page feature suite passes: 5 tests and 34 assertions.
- The existing optional `fontaine` optimization notice remains informational and was not acted on.
- Actual playback-time updates and seeking have not yet been manually verified in a browser.

### Result

The Vue project page now owns immediate native playback time and exposes it visibly, establishing the input needed for active-caption selection.

### Problems / Notes

- Browser `timeupdate` frequency is implementation-dependent and is intentionally accepted for this first preview milestone.
- The readout is temporary development UI and does not yet highlight or display a cue.

### Next

Manually verify playback and seeking updates, then define and test active-cue selection using integer playback milliseconds.

## 2026-08-23 — Step 8.2a

### Goal

Implement and test active-cue selection as pure frontend logic before connecting it to Vue playback state or rendering an overlay.

### Changes

- Added a shared TypeScript `CaptionCue` interface and pure `findActiveCaptionCue` function.
- Implemented inclusive start and exclusive end matching with `null` for uncovered playback time.
- Added eight dependency-free TypeScript tests using Node's built-in test API.
- Added `npm run test:frontend` and documented it in the README.
- Enabled the already installed Node type definitions and TypeScript-extension imports needed by the native TypeScript test file.
- No Vue component, overlay, active-row styling, or npm dependency changed.

### Decisions

- Use the half-open interval `start_ms <= current_ms < end_ms` for browser active-cue selection.
- Return the first matching cue and rely on the backend cue generator's non-overlap guarantee.
- Use Node 22's built-in TypeScript type stripping and `node:test` rather than installing a frontend test framework for one pure function.

### Verification

- `npm run test:frontend` passes: 8 tests.
- Tests cover empty input, time before the first cue, inclusive start, time inside a cue, exclusive end, empty gaps, next-cue start, and exact adjacent-cue transition.
- Vue TypeScript checking completed with no errors after exposing existing Node types.
- ESLint completed with no errors after separating the type-only import.
- The Vite production build completed successfully.
- The optional `fontaine` optimization notice remains informational.

### Result

Active caption selection now has a tested, framework-independent boundary contract ready to consume Vue playback time.

### Problems / Notes

- The selector performs a linear search, which is the simplest correct approach for the current four cues; optimization requires evidence from much larger real projects.
- The selector is not yet imported by the project page.

### Next

Connect the selector to the page's local playback state, converting browser seconds to rounded integer milliseconds, without rendering the caption overlay yet.

## 2026-08-23 — Step 8.2b

### Goal

Connect the tested active-cue selector to native video playback state and expose its result without rendering a caption overlay.

### Changes

- Reused the shared `CaptionCue` type on the Vue project page instead of maintaining a duplicate interface.
- Converted native playback seconds to rounded integer milliseconds in a computed value.
- Derived the active cue with the tested `findActiveCaptionCue` function.
- Extended the development readout to show the active cue, `None` during uncovered time, or `Not available` when no transcription exists.
- No backend behavior, persistence, overlay styling, cue-table highlighting, or dependency changed.

### Decisions

- Keep playback milliseconds and the active cue as derived Vue state rather than independently mutable values.
- Round browser seconds to the nearest millisecond at the boundary where frontend playback time meets internal cue timestamps.
- Distinguish an absent transcription from a valid transcription with no cue at the current time in the development readout.

### Verification

- Prettier formatted the changed Vue page successfully.
- The frontend selector suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The project-page feature suite passes: 5 tests and 34 assertions.
- The existing optional `fontaine` optimization notice remains informational.
- Actual active-cue transitions have not yet been manually verified against browser playback.

### Result

Playing a project video now drives a computed active cue that is visible in the development readout beneath the video.

### Problems / Notes

- Caption text is not yet positioned over the video.
- The readout remains temporary development UI.

### Next

Manually verify active-cue transitions, then render the active cue directly over the video with one fixed default style.

## 2026-08-23 — Step 8.3

### Goal

Render the selected caption cue directly over native video playback using one fixed browser-preview style.

### Changes

- Wrapped the native video in a positioned, clipped container.
- Rendered the active cue as an HTML layer near the bottom of the video frame.
- Added one fixed readable treatment: centered white text, a translucent black background, modest text shadow, and responsive font sizing.
- Made the overlay ignore pointer events so it does not interfere with native video controls.
- No style controls, persistence, cue editing, custom player, rendering pipeline, or dependency was added.

### Decisions

- Keep live preview as native HTML video plus an HTML/CSS overlay.
- Place the initial caption above the native control area rather than attempting browser-specific control detection.
- Keep the fixed preview style intentionally simple until cue timing is manually validated.

### Verification

- Prettier formatted the changed Vue page successfully.
- The frontend active-cue selector suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The project-page feature suite passes: 5 tests and 34 assertions.
- The existing optional `fontaine` optimization notice remains informational.
- Actual overlay appearance, Georgian text rendering, and cue transitions have not yet been manually verified in a browser.

### Result

The currently active Georgian caption cue is now rendered directly over the playing video in the browser.

### Problems / Notes

- Native controls vary between browsers, so the fixed bottom offset must be checked manually.
- The style is a temporary coherent default and is not yet configurable or mapped to final video rendering.

### Next

Complete Phase 8.4 by manually verifying cue starts, cue ends, adjacent transitions, gaps, seeking, Georgian rendering, and control usability.

## 2026-08-23 — Step 8.3b

### Goal

Make the browser preview and its caption overlay follow the uploaded video's intrinsic aspect ratio, including portrait Reels/TikTok footage.

### Changes

- Removed the forced 16:9 aspect-ratio utility from the native video.
- Made the positioned preview container shrink-wrap the video's rendered width so the overlay matches the visible frame.
- Limited preview height to 75 percent of the viewport while retaining intrinsic aspect ratio and responsive width.
- Recorded wrapper-based fullscreen preview as a separate postponed improvement.
- No fullscreen implementation, cue editing, persistence, style control, or export rendering was added.

### Decisions

- Treat source aspect ratio as authoritative for browser preview.
- Keep this step limited to normal-page geometry; native video fullscreen and burned-in export are separate concerns.
- Preserve HTML/CSS overlay architecture for live preview.

### Verification

- Prettier completed successfully.
- The frontend active-cue selector suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The project-page feature suite passes: 5 tests and 34 assertions.
- The existing optional `fontaine` optimization notice remains informational.
- Portrait geometry and caption placement have not yet been manually verified in a browser.

### Result

The normal-page preview no longer imposes a 16:9 frame and should follow portrait, landscape, or square source dimensions.

### Problems / Notes

- Native fullscreen targets only the `<video>` element, so the sibling HTML overlay is not present there.
- Final exported captions will be rendered into the output video; browser fullscreen preview can instead fullscreen the shared wrapper in a later step.

### Next

Manually verify project 5's portrait layout, then complete Phase 8.4 and propose minimum persisted cue storage for text correction.

## 2026-08-23 — Step 9.1a

### Goal

Establish the approved minimum database structure needed to preserve human caption corrections.

### Changes

- Added the reversible `caption_cues` migration.
- Added the owning `video_project_id`, project-local `order`, cue `text`, integer `start_ms`, integer `end_ms`, and Laravel timestamps.
- Added cascading deletion with the owning video project.
- Added a unique constraint preventing duplicate cue order within one project.
- Kept text size and all presentation settings out of individual cue rows.
- No model, generated-cue persistence, editing endpoint, frontend form, or regeneration action was added.

### Decisions

- Preserve raw NeMo JSON as ASR evidence and make saved database cues the editable source of truth after initial generation.
- Persist generated cues only when a project has no saved cues.
- Require an explicit warned action for any future regeneration that could replace corrections.
- Store future text size and other caption presentation as project-level style rather than per-cue data.

### Verification

- Laravel Pint completed successfully.
- The complete Pest suite passes: 97 tests and 243 assertions.
- The migration applied, rolled back, and reapplied successfully without removing existing video projects.
- Laravel's database inspection reports all eight expected columns.
- SQLite reports the compound unique index and the cascading foreign key.
- The migration is currently applied in batch 4.
- No cue rows exist yet because persistence behavior is a separate next step.

### Result

The database can now hold ordered, timed, editable captions without modifying preserved ASR output.

### Problems / Notes

- Application code does not yet read or write `caption_cues`.
- Caption text remains read-only in the browser.

### Next

Add the minimal `CaptionCue` Eloquent model and relationship needed before persisting generated cues.

## 2026-08-23 — Step 9.1b

### Goal

Add the minimum Eloquent representation and relationships for persisted caption cues.

### Changes

- Added `App\Models\CaptionCue` with fillable editable fields and integer casts for order and timing.
- Added the inverse `videoProject` relationship.
- Added an ordered `captionCues` relationship to `VideoProject`.
- Added focused feature tests for persistence, integer casts, relationship traversal, project-local ordering, and cascading deletion.
- Kept the transient generated representation as `App\ValueObjects\CaptionCue`; the namespaces distinguish it from the saved model.
- No generated cues were persisted and no page, endpoint, factory, seeder, or dependency changed.

### Decisions

- Set cue ownership through the project relationship rather than allowing `video_project_id` through mass assignment.
- Return a project's saved cues in their explicit `order` by default.
- Do not add a factory or seeder until reusable fixture states or demo data create a concrete need.

### Verification

- Laravel Pint formatted the changed PHP files successfully.
- The focused model suite passes: 2 tests and 13 assertions.
- The complete Pest suite passes: 99 tests and 256 assertions.
- `git diff --check` completed with no errors.
- No browser behavior changed in this backend-only step.

### Result

Laravel can now create, retrieve, order, and associate saved caption cues through `VideoProject` without using raw database queries.

### Problems / Notes

- Existing projects still have no saved cue rows.
- The project page still reads transient cues generated from NeMo JSON.

### Next

Add one tested application action that persists generated cues only when the project has no saved cues.

## 2026-08-23 — Step 9.1c

### Goal

Persist one generated cue set atomically while protecting any existing saved captions from replacement.

### Changes

- Added `PersistGeneratedCaptionCues` as a single-purpose application action.
- Mapped generated `App\ValueObjects\CaptionCue` instances into saved `App\Models\CaptionCue` records.
- Wrapped the existence check and all inserts in one database transaction with a locked project lookup.
- Rejected empty generated sets instead of treating them as successful initialization.
- Refused to run when any saved cue already exists for the project.
- Added focused tests for successful mapping, empty-input rejection, and preservation of manually corrected text.
- Did not call the action from HTTP, console, page loading, or project 5.

### Decisions

- Fail explicitly on an existing saved cue set instead of returning it or silently overwriting it.
- Keep the persistence action separate from ASR loading so each boundary remains independently testable.
- Require a non-empty generated cue list because the current schema has no separate initialized-empty state.

### Verification

- Laravel Pint completed successfully.
- The focused persistence suite passes: 3 tests and 7 assertions.
- The complete Pest suite passes: 102 tests and 263 assertions.
- `git diff --check` completed with no errors.
- Project 5 still has no database cue rows because invoking the action is a separate step.

### Result

Application code can now save a generated cue set exactly once without risking silent replacement of later human corrections.

### Problems / Notes

- No application entry point invokes the action yet.
- The project page still reads transient cues from NeMo JSON.

### Next

Add a small development command that loads and persists generated cues for one explicitly selected video project.

## 2026-08-23 — Step 9.1d

### Goal

Expose the protected generated-cue persistence workflow for one explicitly selected development project.

### Changes

- Added `video-projects:persist-caption-cues {videoProject}`.
- Validated the project ID and reported missing projects or transcription prerequisites clearly.
- Composed the existing caption-data loader and protected persistence action without duplicating their logic.
- Reported the number of successfully saved cues.
- Added command tests for success, overwrite refusal, missing NeMo output, missing project, and invalid project ID.
- Documented the command and its non-overwrite behavior in the README.
- Ran the command once for project 5 and saved eight generated cues.

### Decisions

- Keep cue initialization explicit during the experimental local-ASR phase rather than writing during a normal GET page request.
- Treat a repeated command as an error so saved corrections remain protected.

### Verification

- Laravel Pint completed successfully.
- The focused command suite passes: 5 tests and 12 assertions.
- The complete Pest suite passes: 107 tests and 275 assertions.
- Artisan lists the command with its expected description.
- The first real project 5 invocation reported eight saved cues.
- A second real project 5 invocation failed because saved cues already exist.
- `git diff --check` completed with no errors.

### Result

Project 5 now has eight durable caption cues initialized from its preserved NeMo transcription, and rerunning generation cannot overwrite them.

### Problems / Notes

- The project page still reads transient NeMo-generated cues instead of the new saved rows.
- Caption text remains read-only in the browser.

### Next

Make the project page prefer ordered saved cues as its editable source of truth.

## 2026-08-23 — Step 9.1e

### Goal

Make durable saved cues the project page's caption source while retaining transient NeMo output for uninitialized projects.

### Changes

- Updated the project-page controller to query ordered saved cues first.
- Returned saved cue text and timing through the existing frontend prop shape.
- Kept transient NeMo loading only as the fallback when no saved cue exists.
- Added a precedence test using deliberately invalid raw NeMo JSON alongside a valid saved correction.
- No Vue component, route, form, database schema, or saved project 5 row changed.

### Decisions

- Do not inspect or parse raw ASR data once a project has saved cues.
- Preserve the existing frontend cue contract until the editing endpoint requires database cue identity.
- Keep uninitialized projects readable through the existing transient fallback during development.

### Verification

- Laravel Pint completed successfully.
- The project-page suite passes: 6 tests and 43 assertions.
- The complete Pest suite passes: 108 tests and 284 assertions.
- Vue TypeScript checking completed with no errors.
- The precedence test proves saved cues render even when the raw NeMo JSON is malformed.
- `git diff --check` completed with no errors.
- Project 5's unchanged saved text has not yet been visually distinguished from its identical generated source in the browser.

### Result

Project 5's eight saved database cues now drive its table, active-cue selection, and video overlay; raw NeMo output is no longer consulted for that project page.

### Problems / Notes

- Saved cue IDs are not yet exposed to Vue, so individual cues cannot be targeted for updates.
- Caption text remains read-only in the browser.

### Next

Add a validated backend endpoint that updates only the text of one cue belonging to the selected video project.

## 2026-08-23 — Step 9.1f

### Goal

Add a secure, validated backend boundary for correcting one saved caption cue's text.

### Changes

- Added a nested PATCH route for one project's saved caption cue.
- Enabled scoped route-model binding so cues from another project return not found.
- Added a Form Request that accepts required string text up to 500 characters.
- Added a single-action controller that updates only validated text and redirects to the project page.
- Added focused tests for valid Georgian correction, ignored timing/order input, invalid text, and cross-project access.
- No cue ID prop, Vue form, timing update, or project 5 text changed.

### Decisions

- Limit this endpoint to cue text; timing and structural edits remain separate operations with their own invariants.
- Use a 500-character safety limit while allowing corrections longer than the current automatic 42-character grouping target.
- Keep authorization open for personal V1 while enforcing project ownership through scoped binding; accounts and user policies remain Stage B.

### Verification

- Laravel Pint completed successfully.
- The focused endpoint suite passes: 6 tests and 28 assertions.
- The complete Pest suite passes: 114 tests and 312 assertions.
- Artisan lists the nested PATCH route with the expected name.
- Tests prove unrelated cue order and timing cannot be changed through this endpoint.
- Tests prove a cue from another project returns 404 and remains unchanged.
- `git diff --check` completed with no errors.
- No browser control exists yet, so manual editing has not been verified.

### Result

The backend can now safely persist one caption text correction without exposing timing fields or another project's cues.

### Problems / Notes

- The project-page cue props still omit database IDs.
- Users cannot invoke the endpoint from the current read-only table.

### Next

Expose saved cue IDs and add the first small cue-text editing control using the generated Wayfinder route.

## 2026-08-23 — Step 9.1g

### Goal

Allow one saved caption cue's text to be corrected from the project-page cue table.

### Changes

- Added nullable cue identity to the shared frontend cue contract.
- Exposed database IDs for saved cues and explicit `null` IDs for transient fallback cues.
- Added one Inertia `Form` per saved cue using the generated nested Wayfinder route.
- Added an accessible textarea, Save state, success feedback, isolated validation errors, and preserved scroll.
- Kept transient NeMo fallback cues read-only because they do not have durable database identity.
- Updated frontend selector fixtures and project-page prop tests for cue identity.
- No optimistic editing, autosave, timing edit, seeking, split, merge, or styling control was added.

### Decisions

- Submit explicit saves rather than autosaving each keystroke in the first correction workflow.
- Reload saved project props after success so the database remains authoritative for both table and overlay text.
- Isolate validation errors per cue form to prevent one invalid row from marking every row.

### Verification

- Wayfinder regenerated typed action, route, and form definitions successfully.
- The combined page and update endpoint suites pass: 12 tests and 71 assertions.
- The complete Pest suite passes: 114 tests and 312 assertions.
- The frontend active-cue suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors after correcting import order.
- The Vite production build completed successfully.
- The existing optional `fontaine` optimization notice remains informational.
- `git diff --check` completed with no errors.
- Browser editing, overlay refresh, and reload persistence have not yet been manually verified.

### Result

Every persisted project 5 cue now has a small text correction form whose successful save refreshes the table and video-overlay source from SQLite.

### Problems / Notes

- Changes appear in the overlay after Save, not on every keystroke.
- The table is horizontally scrollable on narrow screens because timing columns remain alongside the editor.

### Next

Manually correct one project 5 cue, play its interval, reload the page, and verify the corrected text remains in both the table and overlay.

## 2026-08-23 — Step 9.2

### Goal

Jump native video playback directly to a selected caption cue's start time.

### Changes

- Added a typed Vue reference to the native video element.
- Added local seek behavior that converts the cue's integer start milliseconds to browser seconds.
- Turned each cue number into an accessible button with an explicit seek label.
- Updated local playback state immediately after seeking so active-cue selection stays synchronized.
- Preserved the video's current paused or playing state; clicking a cue does not auto-play.
- No backend, database, cue selection, text persistence, timing edit, or dependency changed.

### Decisions

- Use an explicit cue-number button instead of making the entire row clickable, avoiding accidental seeks while editing text.
- Seek without auto-playing so the editor controls when playback begins.
- Keep seeking as immediate local Vue state with no server request.

### Verification

- Prettier completed successfully.
- The frontend active-cue suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The project-page suite passes: 6 tests and 43 assertions.
- The existing optional `fontaine` optimization notice remains informational.
- `git diff --check` completed with no errors.
- Actual browser seeking while paused and playing has not yet been manually verified.

### Result

Every caption cue can now seek the project video to its exact stored start time without a server round trip.

### Problems / Notes

- The seek control does not auto-play or scroll the video into view.
- Cue start and end times remain read-only.

### Next

Manually verify seeking on project 5, then add a validated backend boundary for editing one cue's start time.

## 2026-08-23 — Step 9.3

### Goal

Allow a saved cue's start time to be edited from the cue table while preserving core timing validity.

### Changes

- Added a project-scoped PATCH endpoint dedicated to cue start time.
- Added a Form Request requiring integer milliseconds at or above zero.
- Added validation requiring start time before cue end and no later than known video duration.
- Rejected timing edits when video duration is unknown.
- Added a single-action controller that updates only validated `start_ms`.
- Added a Wayfinder-backed per-cue number input and explicit Save control in the Start column.
- Added isolated errors, saving/success state, preserved scroll, and basic HTML timing limits.
- Added focused tests for valid updates, ignored unrelated fields, malformed input, timing boundaries, unknown duration, and cross-project access.
- Did not add end-time editing, overlap rules, automatic playback, split, or merge behavior.

### Decisions

- Keep integer milliseconds visible in the first timing editor so its value exactly matches internal storage.
- Give start time its own endpoint and form so text and timing validation remain independent.
- Defer cue-overlap policy until the complete timestamp-invariant step after end-time editing.

### Verification

- Wayfinder regenerated typed action, route, and form definitions successfully.
- Laravel Pint completed successfully.
- The focused start-time suite passes: 10 tests and 48 assertions.
- The complete Pest suite passes: 124 tests and 360 assertions.
- The frontend active-cue suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The nested start-time route is registered with the expected name.
- The existing optional `fontaine` optimization notice remains informational.
- `git diff --check` completed with no errors.
- Browser start-time editing and the resulting overlay boundary shift have not yet been manually verified.

### Result

Persisted cues can now have their start boundary corrected in integer milliseconds from the browser without exposing other cue fields.

### Problems / Notes

- The initial millisecond input is precise but not yet a friendly timestamp formatter.
- Editing a start can currently create overlap with the previous cue because overlap policy is deliberately not yet established.

### Next

Manually verify project 5 timing edits, then implement Phase 9.4 end-time editing before establishing complete timestamp and overlap invariants in Phase 9.5.

## 2026-08-23 — Step 9.4

### Goal

Allow a saved cue's end time to be edited from the cue table while preserving the timing boundaries already supported by the application.

### Changes

- Added a project-scoped PATCH endpoint dedicated to cue end time.
- Added a Form Request requiring positive integer milliseconds.
- Added validation requiring end time after cue start and no later than known video duration.
- Rejected timing edits when video duration is unknown.
- Added a single-action controller that updates only validated `end_ms`.
- Added a Wayfinder-backed per-cue number input and explicit Save control in the End column.
- Added isolated errors, saving/success state, preserved scroll, and matching HTML timing limits.
- Added focused tests for valid updates, ignored unrelated fields, malformed input, timing boundaries, unknown duration, and cross-project access.
- Did not add overlap rules, automatic playback, split, or merge behavior.

### Decisions

- Keep end time in integer milliseconds, matching start-time editing and internal storage.
- Give end time its own endpoint and form so each timing boundary has isolated validation and feedback.
- Continue allowing overlaps until Phase 9.5 establishes and documents one deliberate overlap policy.

### Verification

- Wayfinder regenerated typed action, route, and form definitions successfully.
- Laravel Pint completed successfully.
- The focused end-time suite passes: 11 tests and 53 assertions.
- The complete Pest suite passes: 135 tests and 413 assertions.
- The frontend active-cue suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The nested end-time route is registered with the expected name.
- The existing optional `fontaine` optimization notice remains informational.
- Actual browser end-time editing and the resulting overlay boundary shift have not yet been manually verified.

### Result

Persisted cues can now have either timing boundary corrected in integer milliseconds from the browser without exposing unrelated cue fields.

### Problems / Notes

- The millisecond inputs are precise but not yet friendly timestamp formatters.
- Editing an end can currently overlap the next cue because overlap policy is deliberately deferred.

### Next

Manually verify project 5 end-time edits, then establish complete timestamp invariants and cue-overlap policy in Phase 9.5.

## 2026-08-23 — Step 9.5

### Goal

Establish one deterministic timestamp and overlap policy before cue splitting and merging add more timing mutations.

### Changes

- Prevented start-time edits from moving a cue before the previous cue's end.
- Prevented end-time edits from moving a cue after the next cue's start.
- Kept exact touching boundaries valid and kept intentional gaps valid.
- Matched browser input limits to the neighboring cue constraints enforced by the backend.
- Added focused tests for accepted touching boundaries and rejected overlaps on both sides.
- Recorded the complete persisted-cue timing policy in `DECISIONS.md`.
- Did not change existing cue data, cue order, generation, splitting, merging, or rendering.

### Decisions

- Use cue order to identify temporal neighbors.
- Persist non-overlapping half-open intervals in integer milliseconds.
- Permit gaps and exact boundary transitions.

### Verification

- Laravel Pint completed successfully.
- The combined start/end timing suite passes: 25 tests and 117 assertions.
- The complete Pest suite passes: 139 tests and 429 assertions.
- The frontend active-cue suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The existing optional `fontaine` optimization notice remains informational.
- Browser validation against project 5 has not yet been manually verified.

### Result

Caption timing edits now preserve one ordered, non-overlapping timeline suitable for deterministic preview and future rendering.

### Problems / Notes

- HTML number-input limits improve immediate feedback, but the backend remains authoritative.
- Existing malformed or externally imported rows are not repaired automatically.

### Next

Manually verify boundary and overlap behavior on project 5, then define the exact behavior for splitting one cue in Phase 9.6.

## 2026-08-23 — Step 9.6

### Goal

Split one saved cue at a speech-relevant timestamp without losing text or violating timeline invariants.

### Changes

- Added a project-scoped POST endpoint for splitting a saved cue.
- Added validation requiring an integer playhead strictly inside the cue and at least two cue words.
- Added a transactional action that divides text at the central word boundary.
- Kept the original row as the first cue, created the second row at the next order, and shifted later orders safely from last to first.
- Preserved the original time range as two touching, non-overlapping intervals.
- Added a Wayfinder-backed “Split at playhead” control that activates only inside a splittable cue.
- Added focused tests covering the resulting text, timing, later ordering, malformed and boundary timestamps, one-word cues, and project scoping.
- Did not add manual text-boundary selection, merge behavior, styling, or export.

### Decisions

- Use browser playhead milliseconds for the timing boundary.
- Use a central whitespace-delimited word boundary as the initial text split.
- Give the first half the extra word for odd word counts.
- Keep the operation atomic because it updates multiple ordered rows.

### Verification

- Wayfinder regenerated typed action, route, and form definitions successfully.
- Laravel Pint completed successfully.
- The focused split suite passes: 11 tests and 81 assertions.
- The complete Pest suite passes: 150 tests and 510 assertions.
- The frontend active-cue suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The split route is registered with the expected project-scoped name.
- The existing optional `fontaine` optimization notice remains informational.
- Actual browser splitting against project 5 has not yet been manually verified.

### Result

A multi-word saved cue can now become two ordered, editable cues at the current video playhead without creating overlap or losing words.

### Problems / Notes

- The automatic text division is intentionally simple and may need immediate manual correction.
- Splitting normalizes whitespace between words.
- The control uses current playback time, so pausing at the intended speech boundary is recommended.

### Next

Manually verify splitting on project 5, then define and implement merging adjacent cues in Phase 9.7.

## 2026-08-23 — Step 9.7

### Goal

Merge one saved cue with its immediate next cue without leaving broken ordering or partial data.

### Changes

- Added a project-scoped POST endpoint for merging a cue with its next ordered cue.
- Added validation rejecting the operation for the final cue.
- Added a transactional action that joins trimmed texts with one space and extends the selected cue through the next cue's end.
- Deleted the consumed next cue and shifted all later orders down safely.
- Allowed an intentional merge to span any gap between the two original cues.
- Added a Wayfinder-backed “Merge with next” control, disabled for the final cue.
- Added focused tests for merged text and timing, deletion, reordered later cues, final-cue rejection, and project scoping.
- Did not add undo, confirmation, styling, or export.

### Decisions

- Keep the selected cue's ID, order, and start boundary.
- Consume only the immediate next cue in project order.
- Treat merging across a gap as an explicit choice to cover that gap.
- Keep the multi-row mutation atomic.

### Verification

- Wayfinder regenerated typed action, route, and form definitions successfully.
- Laravel Pint completed successfully.
- The focused merge suite passes: 3 tests and 21 assertions.
- The complete Pest suite passes: 153 tests and 531 assertions.
- The frontend active-cue suite passes: 8 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The merge route is registered with the expected project-scoped name.
- The existing optional `fontaine` optimization notice remains informational.
- Actual browser merging against project 5 has not yet been manually verified.

### Result

Saved cues now support text correction, seeking, timing correction, splitting, and adjacent merging while preserving the documented timeline invariants.

### Problems / Notes

- Merge is immediately destructive for the consumed row and has no undo control yet.
- A merged cue may become long and should be corrected or split again when needed.
- A former silent gap becomes captioned after merging.

### Next

Manually verify merging on project 5, then begin Phase 10.1 by defining one coherent default caption style before adding user controls.

## 2026-08-23 — Step 9.6b

### Goal

Explain why cue splitting is unavailable instead of presenting an unexplained disabled control.

### Changes

- Added cue-specific split guidance beside the disabled button.
- Explained when the playhead must move strictly inside the cue.
- Explained when the cue needs at least two words.
- Displayed the exact ready split millisecond when all requirements are satisfied.
- Associated the guidance with the split button for assistive technology.

### Decisions

- Keep the existing safe split rules and improve their visibility rather than weakening boundary validation.

### Verification

- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- Actual guidance clarity has not yet been manually verified.

### Result

The cue table now tells the user how to enable splitting and when the current playhead is ready.

### Problems / Notes

- Splitting remains a new operation rather than an exact undo of merge because merge does not preserve the consumed boundary.

### Next

Verify the message while attempting to split the recently merged project 5 cue.

## 2026-08-23 — Step 10.1

### Goal

Establish one coherent caption style as the baseline for incremental controls and eventual render mapping.

### Changes

- Added a typed default caption-style representation.
- Added a pure conversion from caption-style values to Vue CSS properties.
- Applied the default to the live browser overlay.
- Chose a Georgian-aware font fallback, 28 px bold white centered text, translucent black background, and a strong readability shadow.
- Kept layout structure separate and added no style persistence or controls.
- Expanded the frontend test command to cover caption cue logic and style behavior.

### Decisions

- Treat the default as explicit product data rather than an anonymous class list.
- Validate CSS preview and eventual subtitle rendering separately.
- Defer persistence until incremental controls prove the useful style shape.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 2 tests.
- The complete Pest suite passes: 153 tests and 531 assertions.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The existing optional `fontaine` optimization notice remains informational.
- Default-style appearance with real Georgian captions has not yet been manually verified.

### Result

Browser captions now render from one typed, tested default style ready for the first live style control.

### Problems / Notes

- The default is browser-local and not persisted.
- Arial availability and Georgian glyph appearance vary by operating system, with Noto Sans Georgian and system sans-serif as fallbacks.
- Browser CSS is not yet mapped to ASS/libass.

### Next

Manually inspect the default on landscape and vertical project video, then implement Phase 10.2 live font-size control.

## 2026-08-23 — Step 10.2

### Goal

Make caption font size adjustable with immediate feedback in the real video overlay.

### Changes

- Added a compact Caption style panel directly below the video preview.
- Added synchronized range and numeric controls from 12 to 72 pixels.
- Initialized local style state from the 28-pixel default.
- Applied size changes immediately through the existing typed CSS mapping.
- Clearly labeled the control as browser-preview-only and non-persistent.
- Added a focused test confirming a changed size maps to CSS without mutating the default.
- Moved unavailable split guidance from persistent row text into a compact native tooltip.
- Did not add style persistence, text color, font selection, or rendering mapping.

### Decisions

- Prove useful style controls in local Vue state before choosing database persistence.
- Use pixel values because they can later be compared explicitly with ASS/libass sizing.
- Keep the current range deliberately bounded for normal caption use.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 3 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The existing optional `fontaine` optimization notice remains informational.
- Browser interaction and perceived sizing on real videos have not yet been manually verified.

### Result

The project page now exposes caption styling directly below the video, beginning with an immediate font-size control.

### Problems / Notes

- Font size resets to 28 pixels on page reload.
- Browser pixels will require empirical mapping rather than assumed equivalence during ASS rendering work.

### Next

Manually verify the useful size range on project 5, then add Phase 10.3 live text-color control.

## 2026-08-23 — Steps 10.3–10.5 and 10.11a

### Goal

Complete the browser-preview controls that directly affect caption text presentation.

### Changes

- Added live text-color selection with the current hexadecimal value displayed.
- Added live bold and italic controls.
- Added Georgian-aware sans, system sans, and serif font-stack choices.
- Extended the typed style representation and CSS mapping with normal/italic font style.
- Kept the existing 12–72 pixel font-size controls in the same compact panel.
- Normalized direct font-size input to whole pixels within the supported range.
- Added focused coverage for font family, weight, italic style, and text-color mapping.
- Did not add background, position, outline, shadow, persistence, bundled fonts, or render mapping.

### Decisions

- Treat font family, size, bold, italic, and text color as the complete text-presentation group.
- Use system font stacks now instead of adding font packages before real Georgian browser and libass QA.
- Keep every control in immediate local Vue state until the style data shape is validated in practice.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 5 tests.
- The complete Pest suite passes: 153 tests and 531 assertions.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The existing optional `fontaine` optimization notice remains informational.
- Georgian glyph appearance for every font choice has not yet been manually verified.

### Result

The live browser overlay now supports font family, font size, bold, italic, and text color from one Caption style panel.

### Problems / Notes

- All text style choices reset on reload.
- The selected stack may resolve to different installed fonts on different machines.
- Bundled font selection and final-render equivalence remain unproven until Georgian font and ASS/libass QA.

### Next

Manually test every font choice with Georgian Mkhedruli text, then begin container styling with background color and opacity.

## 2026-08-23 — Steps 10.6–10.7

### Goal

Add independent live controls for the caption background color and opacity.

### Changes

- Separated background color and opacity in the typed browser caption style.
- Added a live background-color picker with its current hexadecimal value.
- Added synchronized range and numeric controls for background opacity from 0% to 100%.
- Normalized direct opacity input to a whole percentage within the supported range.
- Added pure CSS mapping from a six-digit hexadecimal color plus opacity to a browser color value.
- Added focused tests for independent background mapping and opacity normalization.
- Did not add style persistence, placement controls, outlines, shadows, or final-render mapping.

### Decisions

- Represent background color and opacity independently so transparency can change without losing the selected color.
- Keep these controls in immediate Vue state while the useful browser-preview style shape is still being established.
- Preserve black at 75% opacity as the default for caption readability.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 7 tests.
- The complete Pest suite passes: 153 tests and 531 assertions.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The existing optional `fontaine` optimization notice remains informational.
- Background appearance over real video has not yet been manually verified.

### Result

The live browser overlay now supports independently adjustable background color and opacity from the Caption style panel.

### Problems / Notes

- Both choices reset to black at 75% opacity on page reload.
- Low opacity can make captions difficult to read over complex footage.
- Browser CSS and final ASS/libass rendering still require separate visual comparison.

### Next

Manually verify background color and opacity on project 5, then add Phase 10.8 basic top, middle, and bottom placement.

## 2026-08-23 — Step 10.8 and caption-editor layout refinement

### Goal

Add the first useful caption-placement choices and make the generated-caption editor easier to scan and operate.

### Changes

- Added live top, middle, and bottom placement controls, with bottom retained as the default.
- Added a tested pure mapping from each placement to browser overlay layout.
- Kept bottom captions above native video controls.
- Widened the project workspace for the editing interface while preserving the video's intrinsic dimensions.
- Reduced the generated-caption table to four clear columns: number, caption, timing, and actions.
- Combined start and end controls in one labeled timing column.
- Preserved cue numbering as the control that seeks to the cue start.
- Moved split and merge into a compact, accessible per-cue actions menu.
- Preserved all existing text, timing, split, and merge forms and validation behavior.
- Did not add vertical offset, persistence, or final-render placement mapping.

### Decisions

- Keep caption text and timing visible because they are the primary correction workflow.
- Put less frequent structural operations behind a native disclosure menu rather than displaying large action buttons in every row.
- Use three coarse placement choices before adding fine vertical offset.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 8 tests.
- The complete Pest suite passes: 153 tests and 531 assertions.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The Vite production build completed successfully.
- The existing optional `fontaine` optimization notice remains informational.
- Placement and editor interactions have not yet been manually verified in a browser.

### Result

Captions can be previewed at the top, middle, or bottom of the video, and generated cues now use a cleaner editing layout with compact structural actions.

### Problems / Notes

- Placement resets to bottom on reload.
- The actions menu uses the browser's native disclosure behavior and should be checked at narrow viewport widths.
- Final rendered placement must later be compared against the browser preview.

### Next

Manually verify all three placements and the reorganized cue controls on project 5, then add Phase 10.9 vertical offset.

## 2026-08-23 — Step 10.9 and anchored cue actions

### Goal

Allow fine vertical positioning across the video and prevent the caption actions trigger from moving when its menu opens.

### Changes

- Replaced the three placement buttons with one continuous 0–100 range control.
- Labeled 0, 50, and 100 conceptually as Top, Middle, and Bottom while allowing every whole percentage between them.
- Positioned the caption smoothly along a safe vertical track within the video.
- Preserved space at the top and above native video controls at the bottom.
- Added normalization and focused tests for invalid, out-of-range, and fractional position values.
- Anchored each actions menu absolutely to a fixed-width three-dot trigger so opening it no longer changes the table layout or trigger position.
- Did not persist the position or implement final-render mapping.

### Decisions

- Store browser-preview vertical position as an integer percentage because it is understandable, format-independent, and suitable for later rendering experiments.
- Let Top, Middle, and Bottom act as slider landmarks rather than limiting users to three positions.
- Overlay the actions menu without changing row dimensions.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 9 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- Browser movement and menu anchoring have not yet been manually verified.

### Result

The caption can now move continuously from the top to the bottom of the video, and opening cue actions no longer intentionally reflows the table.

### Problems / Notes

- Vertical position resets to Bottom on reload.
- The overlaid actions menu should be checked near the table's bottom and at narrow viewport widths for clipping.
- Final ASS/libass positioning still requires a separate mapping and visual comparison.

### Next

Manually verify smooth caption movement and actions-menu stability on project 5, then evaluate outline and shadow controls.

## 2026-08-23 — Step 10.10 and position-control relocation

### Goal

Keep vertical positioning visible beside the preview and add useful outline and shadow controls that can plausibly map to final rendering.

### Changes

- Moved the vertical-position slider directly below the video and above playback diagnostics.
- Added a live outline-color picker.
- Added a live outline-width slider from 0 through 4 pixels in half-pixel steps.
- Added a live shadow toggle using the established default shadow.
- Extended the typed caption style and browser CSS mapping with outline color and width.
- Added focused tests for outline/shadow mapping and outline-width normalization.
- Did not add style persistence or ASS/libass mapping.

### Decisions

- Keep the position control visually adjacent to the video because it requires continuous preview feedback.
- Start outline width at 0 pixels so this step does not silently change the established default appearance.
- Use a simple shadow toggle before exposing offsets, blur, or color that may not map consistently to final rendering.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 11 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- Browser outline and shadow appearance have not yet been manually verified.

### Result

The position slider remains visible immediately below the preview, and captions now support live outline color/width plus shadow visibility.

### Problems / Notes

- These controls reset on page reload.
- Browser text stroke is vendor-prefixed and must be visually compared with ASS/libass outline rendering later.
- Shadow currently uses one fixed preset rather than exposing low-value parameters prematurely.

### Next

Manually verify the relocated slider, several outline widths, and shadow toggle on project 5, then add horizontal alignment for multi-line captions.

## 2026-08-23 — Caption text alignment

### Goal

Complete the original V1 caption-alignment requirement with the smallest useful browser-preview control.

### Changes

- Added left, center, and right text-alignment choices.
- Kept center alignment as the established default.
- Added a narrow alignment type and shared option list to the typed caption-style boundary.
- Connected alignment to immediate Vue preview state and existing CSS mapping.
- Added focused tests for all three supported values.
- Did not add horizontal caption-box positioning or style persistence.

### Decisions

- Treat alignment as line alignment inside a potentially multi-line caption box.
- Keep horizontal box placement centered because moving the entire caption box is a separate feature not required by the current styling scope.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 12 tests.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- Alignment appearance on a real multi-line Georgian caption has not yet been manually verified.

### Result

The browser preview now supports left, center, and right alignment for wrapped or explicitly multi-line caption text.

### Problems / Notes

- Alignment is not visually distinguishable on a single short line whose box fits its contents.
- The setting resets to center on reload until style persistence is implemented.

### Next

Manually verify alignment on a multi-line cue, then propose the minimum project-level caption-style persistence representation before changing the database.

## 2026-08-23 — Caption style persistence migration

### Goal

Create only the approved storage location for one project-level caption style without yet changing model or frontend behavior.

### Changes

- Added a focused migration with nullable `caption_style` JSON on `video_projects`.
- Added a reversible rollback that drops only `caption_style`.
- Did not add a model cast, defaults, validation, endpoint, or frontend persistence.

### Decisions

- Store one normalized style object directly on its video project.
- Keep the column nullable so existing projects use application defaults without a data backfill.
- Do not index the style because V1 does not query or sort projects by individual style values.

### Verification

- The migration applied successfully to the existing SQLite database.
- The migration rolled back successfully.
- The migration reapplied successfully after rollback.
- Database schema inspection confirms nullable `caption_style`; SQLite represents Laravel's JSON column as text.
- Laravel Pint completed successfully.
- The complete Pest suite passes: 153 tests and 531 assertions.

### Result

The database can now hold one optional caption-style configuration per video project, but application code does not read or write it yet.

### Problems / Notes

- The database does not validate individual JSON keys; application validation is required before writes are introduced.
- Existing projects currently contain null and therefore continue using browser defaults.

### Next

Add the `VideoProject` JSON cast and tested default-style resolution as the next isolated step.

## 2026-08-23 — Caption style model boundary

### Goal

Teach the `VideoProject` model to cast stored style JSON and resolve existing null styles to one complete canonical default.

### Changes

- Added `caption_style` to the model's fillable attributes and array casts.
- Defined the complete normalized project-level default using product values rather than CSS strings.
- Added `resolvedCaptionStyle()` to return the stored configuration or the complete default without writing defaults into existing rows.
- Added focused tests for null default resolution and a stored JSON round trip.
- Did not pass style data to Vue, add validation, add an endpoint, or add a Save style button.

### Decisions

- Keep the canonical persisted default on the backend model because Laravel will validate, store, and later supply this configuration to both preview and rendering paths.
- Preserve null in the database for untouched projects while resolving it at the application boundary.

### Verification

- Laravel Pint completed successfully.
- Focused model tests pass: 4 tests and 8 assertions.
- The complete Pest suite passes: 155 tests and 535 assertions.
- PHPStan completed but reports 28 existing issues in older cue relationship and action typing; it reports no caption-style cast or resolver issue.
- Frontend receipt of the resolved style has not yet been implemented or tested.

### Result

Laravel now reads saved caption-style JSON as an array and provides a complete default for every existing unstyled project.

### Problems / Notes

- Backend product keys still need an explicit mapping to the frontend style representation.
- The PHP and TypeScript defaults temporarily exist on opposite sides of an unimplemented prop boundary and must be reconciled in the next step.
- There is still no Save style button.

### Next

Pass the resolved caption style to the Vue project page and initialize every local preview control from that prop.

## 2026-08-23 — Caption style read path

### Goal

Initialize the browser preview from Laravel's resolved project style instead of independent hardcoded frontend control defaults.

### Changes

- Added the resolved `captionStyle` prop to the video-project Inertia response.
- Defined the normalized persisted-style TypeScript shape, including constrained font and alignment keys.
- Added one pure mapping from product settings to browser style values and font stacks.
- Initialized font, size, emphasis, colors, opacity, alignment, position, outline, and shadow controls from the server prop.
- Added endpoint coverage for both default and stored style props.
- Added frontend coverage for the complete product-settings-to-browser-style mapping.
- Did not add validation, a write endpoint, or a Save style button.

### Decisions

- Keep persisted product settings separate from browser CSS strings.
- Resolve the font key to an existing Georgian-aware browser stack at the frontend mapping boundary.
- Treat numeric zero as `0` across the JSON boundary rather than preserving a PHP-only `0.0` distinction.

### Verification

- Focused model and project-page tests pass: 11 tests and 62 assertions.
- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 13 tests.
- Laravel Pint completed successfully.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- A real stored non-default style has not yet been manually loaded because no write UI exists.

### Result

Every preview control now starts from the project's resolved backend configuration, so the read side of style persistence is complete.

### Problems / Notes

- Existing projects still resolve to defaults because no style write path exists yet.
- Browser controls can change local state but cannot save it.

### Next

Add a strictly validated, project-scoped endpoint that replaces the complete caption-style configuration.

## 2026-08-23 — Caption style write path and Save action

### Goal

Persist the complete live caption style on explicit user request and restore it through the established project read path.

### Changes

- Added a project-scoped PATCH route and invokable caption-style update controller.
- Added a dedicated Form Request validating every required style field, supported key, numeric range, color format, boolean, and outline half-pixel step.
- Replaced the complete project style with validated values only; unexpected input is ignored.
- Added a typed Wayfinder route for the Vue write request.
- Added an explicit Save style button with processing, success, and validation-error feedback.
- Kept preview updates immediate without sending requests as controls move.
- Added reverse browser-font-family-to-product-key mapping for persistence.
- Added exhaustive endpoint datasets plus focused frontend mapping coverage.

### Decisions

- Save the complete configuration atomically rather than supporting partial per-control writes.
- Require an explicit save to avoid a request for every slider movement.
- Keep personal V1 authorization open consistently with the current single-user application; authentication and policies remain Stage B concerns.

### Verification

- Caption-style endpoint tests pass: 20 tests and 94 assertions.
- Combined caption-style endpoint and project-page tests pass: 27 tests and 148 assertions.
- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 14 tests.
- Laravel Pint completed successfully.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- Manual browser save and full-reload persistence have not yet been verified.

### Result

The application now has a complete caption-style persistence loop: load, live preview, explicit validated save, and reload from project storage.

### Problems / Notes

- The UI intentionally shows one general validation message because its controls already constrain normal browser input to accepted values.
- Presets and final-render mapping are not implemented.

### Next

Manually save a distinctive style on project 5, fully reload the page, and confirm every setting is restored before adding the first small style preset.

## 2026-08-23 — Phase 11.1: Clean caption style preset

### Goal

Add the first reusable caption style without changing the established explicit-save behavior.

### Changes

- Added one complete, typed Clean preset covering every persisted caption-style field.
- Added a Clean button that updates all style controls and the video preview together.
- Kept preset application browser-local until Save style is clicked.
- Corrected the style-panel explanation now that styles can be persisted.
- Added frontend coverage for the preset's complete configuration and browser-style mapping.

### Decisions

- Represent a preset as product configuration rather than browser CSS so it can use the existing validation, persistence, preview, and future render boundaries.
- Add only one preset and avoid a generalized preset catalog until a second preset establishes a concrete repeated pattern.
- Do not auto-save preset selection, allowing a user to preview it without overwriting the stored custom style.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 15 tests.
- All 176 Laravel tests pass with 640 assertions.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The production frontend build completed successfully.
- Browser application, explicit save, and reload behavior for the Clean preset have not yet been manually verified.

### Result

The style editor now provides one coherent Clean starting point that can be previewed immediately and persisted through the existing Save style action.

### Problems / Notes

- Applying Clean intentionally replaces all unsaved values currently shown in the style controls.
- Only the Clean preset exists; Social, News, and Minimal remain unimplemented.

### Next

Manually verify the Clean preset on project 5, then add one Social preset as the next incremental Phase 11 step.

## 2026-08-23 — Phase 11.2: Social caption style preset

### Goal

Add one visibly distinct preset intended for high-impact captions on vertical social video.

### Changes

- Added a complete, typed Social preset beside the existing Clean preset.
- Configured larger bold system-sans text, a subtle background, a two-pixel outline, shadow, and slightly raised lower placement.
- Added the Social preset button to the existing live-preview and explicit-save flow.
- Added frontend coverage proving the full Social configuration and its difference from Clean.

### Decisions

- Optimize Social for legibility over varied vertical footage while keeping every setting reproducible by the current product configuration.
- Keep preset selection local until Save style is clicked, consistently with Clean and manual style editing.
- Retain simple explicit preset constants and buttons; two presets do not yet justify a separate catalog or component abstraction.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 16 tests.
- All 176 Laravel tests pass with 640 assertions.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The production frontend build completed successfully.
- Social preset appearance, save, and reload behavior have not yet been manually verified on real footage.

### Result

The editor now offers Clean and Social starting points, both previewable immediately and persistable with the same explicit Save style action.

### Problems / Notes

- The chosen Social values are an initial product default and may need adjustment after viewing representative Georgian captions on project 5.
- News and Minimal presets remain unimplemented.

### Next

Compare Clean and Social on project 5, then add one News preset if Social is visually acceptable.

## 2026-08-23 — Phase 11.3–11.4: Complete caption preset set

### Goal

Complete the planned V1 preset set and make the currently matching preset visible in the editor.

### Changes

- Added a News preset with bold Georgian-aware sans text, an opaque red background, left alignment, and lower placement.
- Added a Minimal preset with smaller regular text, no visible background, a modest outline, and shadow.
- Consolidated Clean, Social, News, and Minimal into one typed preset catalog.
- Rendered preset controls from the catalog and added a selected visual state with `aria-pressed` semantics.
- Added automatic Custom status when any live style value no longer exactly matches a preset.
- Added frontend tests for the complete catalog, distinct News and Minimal characteristics, preset matching, and Custom detection.

### Decisions

- Use exact complete-configuration matching for selection so the UI describes the actual live style rather than merely remembering the last clicked button.
- Introduce the small shared catalog now that four presets need identical labels, keys, configurations, and controls.
- Keep all preset values inside the already validated and persistable style capabilities so future renderer evaluation has a finite mapping target.

### Verification

- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 18 tests.
- All 176 Laravel tests pass with 640 assertions.
- Vue TypeScript checking completed with no errors.
- ESLint completed with no errors.
- The production frontend build completed successfully.
- Real-video appearance and selected/Custom interaction have not yet been manually verified.

### Result

Phase 11 now provides all four planned reusable presets, immediate selection feedback, Custom detection, and explicit persistence through Save style.

### Problems / Notes

- Preset values are initial V1 choices and can be tuned after real-footage review without changing the configuration architecture.
- Final export does not yet map or render these settings.

### Next

Manually review all presets on project 5, then evaluate ASS/libass against the actual cue and style requirements before implementing export.

## 2026-08-23 — Phase 12.1: ASS/libass evaluation

### Goal

Determine whether the installed FFmpeg/libass stack can support Georgian final-caption rendering and identify mismatches with the browser style model before writing export code.

### Changes

- Inspected FFmpeg 6.1.1 and confirmed both `ass` and `subtitles` filters are enabled through libass.
- Confirmed the renderer uses libass 0.17.1 with FreeType, FriBidi, HarfBuzz complex shaping, and Fontconfig.
- Confirmed local Noto Sans Georgian and Noto Serif Georgian font families and weight variants are available.
- Inspected project 5's real 368×640 media, saved millisecond cues, and persisted style configuration.
- Generated a temporary four-style ASS probe outside the repository and rendered four 368×640 PNG frames.
- Visually inspected Clean, Social, News, and Minimal Georgian output and recorded the durable renderer decision and fidelity limits.
- Added no application rendering code, dependencies, or permanent probe assets.

### Decisions

- Use ASS/libass as the first V1 final-caption rendering direction.
- Keep product style values independent of ASS syntax and introduce a tested conversion boundary.
- Require controlled Georgian font availability and explicit timestamp, scale, color, alignment, and position mappings.
- Treat rounded backgrounds and simultaneous independent box/outline styling as known preview-fidelity limitations requiring approximation or a later layering experiment.

### Verification

- FFmpeg reports `--enable-libass` and exposes the `ass` and `subtitles` video filters.
- The probe rendered four valid 368×640 PNG frames with no decode or missing-glyph errors.
- Libass selected Noto Sans Georgian Bold and Regular and reported HarfBuzz complex shaping.
- Georgian Mkhedruli text was visually present and readable in all four rendered probe frames.
- No automated application tests were added because this step changed documentation only and used a disposable environment probe.
- No real project MP4 has yet been rendered with captions.

### Result

ASS/libass is suitable for the first V1 export path, with explicit, bounded mapping work needed to manage browser-preview differences.

### Problems / Notes

- The initial News probe demonstrated that ASS opaque-box behavior is not a direct CSS background mapping when outline and shadow values are zero.
- Font size and vertical placement must be based on an explicit ASS PlayRes rather than copied blindly from browser CSS pixels.
- Font availability must be verified again on the RTX 4060 Ti machine or any later deployment environment.

### Next

Generate one ASS file from actual saved caption cues using one default style, without rendering a final MP4 yet.

## 2026-08-23 — Phase 12.2: Generate ASS from saved cues and styles

### Goal

Implement every concrete mapping identified by the ASS/libass evaluation and generate one real subtitle file without rendering a final MP4.

### Changes

- Added a pure ASS content generator for ordered saved caption cues and complete resolved project styles.
- Added deterministic nearest-centisecond timestamp conversion.
- Added a normalized 640-unit-high ASS canvas whose width follows the source video aspect ratio.
- Mapped Georgian-aware sans, system-sans, and serif product keys to explicit Noto Georgian render fonts.
- Mapped RGB hex colors to ASS BGR colors and percentage opacity to inverse ASS alpha.
- Mapped bold, italic, font size, text alignment, arbitrary vertical position, outline width/color, and shadow.
- Added safe ASS escaping for backslashes, braces, and line breaks.
- Added a separate lower box layer when background opacity is nonzero, preserving an independent text outline and shadow while approximating the browser's rounded box.
- Added a project action that safely probes source dimensions and writes `video-projects/{id}/captions.ass` on the project's private disk.
- Added the protected `video-projects:generate-ass` Artisan command and documented it in the README.
- Made resolved persisted style output explicitly typed and resilient to malformed stored scalar fields.

### Decisions

- Normalize ASS coordinates to 640 units high and derive width from source aspect ratio so style proportions remain stable across resolutions.
- Round cue boundaries to the nearest centisecond, preserving shared boundaries deterministically.
- Use horizontal anchor and vertical-band-aware ASS alignment with explicit positions to approximate browser placement across the full percentage range.
- Use layered box and text events for independent background and glyph styling; rounded corners remain an accepted approximation.
- Refuse generation when the private source file or saved cues are absent and reject malformed ffprobe dimensions.

### Verification

- ASS content, project-file generation, and command tests pass: 18 tests with 52 assertions.
- The full application suite passes: 195 tests with 693 assertions.
- Focused PHPStan analysis passes with zero errors for all new production files and the touched video-project model.
- Laravel Pint completed successfully.
- The command generated project 5's real private `captions.ass` from nine ordered saved cues and its persisted custom style.
- Libass parsed the generated file as 18 layered events, selected Noto Sans Georgian Bold through Fontconfig, and rendered a real project 5 frame with readable Georgian text and the persisted translucent blue background.
- A final captioned MP4 has not yet been rendered.

### Result

The application can now convert real saved Georgian cues and the complete persisted caption style into a private, libass-readable ASS subtitle file.

### Problems / Notes

- ASS background corners remain square rather than matching the browser's rounded CSS box.
- Font availability still needs verification on every machine used for rendering.
- The repository-wide PHPStan command reports 28 older relationship and Pest typing findings outside this step; focused analysis of the new production boundary passes.

### Next

Render one sample project MP4 with the generated ASS file and compare its timing and appearance with the browser preview.

## 2026-08-23 — Phase 12.3: Render one captioned MP4

### Goal

Move the manually verified FFmpeg render command into a safe, tested application boundary and render one real project output.

### Changes

- Added a single-purpose captioned-video render action that regenerates ASS before every export.
- Added fixed FFmpeg argument-array construction without shell interpolation.
- Configured H.264 `libx264` video at CRF 18 with the medium preset, copied optional source audio, and enabled MP4 fast start.
- Rendered to `captioned.rendering.mp4`, verified a non-empty result, and only then replaced `captioned.mp4`.
- Added failed-render cleanup while preserving any earlier completed export.
- Added the protected `video-projects:render` Artisan command.
- Documented the render command and private output path in the README.
- Added focused action and command coverage for success, exact FFmpeg arguments, invalid IDs, missing projects, partial failures, and missing output.

### Decisions

- Keep this step synchronous and command-driven until real application usage demonstrates the need for a queue.
- Copy audio rather than re-encode it because caption burning changes only the video stream.
- Keep output paths generated and project-controlled; no user input enters the FFmpeg filter or filesystem targets.
- Preserve the last completed export when FFmpeg fails.

### Verification

- Focused render action and command coverage passes: 8 tests with 27 assertions.
- The full application suite passes: 203 tests with 720 assertions.
- Focused PHPStan analysis passes with zero errors for the new render production files.
- Laravel Pint completed successfully.
- `video-projects:render 5` produced a real 1,564,904-byte private MP4.
- ffprobe reports a 17.466667-second MP4 containing H.264 video and AAC audio.
- FFmpeg decoded the entire rendered output with no errors.
- The new application-rendered MP4 has not yet been manually compared side-by-side with the browser preview.

### Result

The backend can now regenerate current ASS subtitles and safely render a complete captioned MP4 without modifying the source upload or sacrificing an older export on failure.

### Problems / Notes

- Rendering remains accessible only through Artisan; there is no browser Export button or download route yet.
- Processing status is not persisted because this synchronous proof is fast on the current short sample.
- Preview-versus-render fidelity still needs explicit manual comparison.

### Next

Compare project 5's browser preview and rendered MP4 for timing, line breaks, position, font, and size before exposing rendering through the application UI.

## 2026-08-23 — Phase 12.4–12.6: Compare output and expose browser export

### Goal

Record concrete project 5 preview/render fidelity and make the verified renderer usable from the project page with private download access.

### Changes

- Compared the project 5 persisted style and browser coordinate formulas with the generated ASS and real rendered frame.
- Added a project-scoped POST controller invoking the existing safe render action.
- Converted render failures into an inline Inertia form error while still reporting the underlying exception.
- Added a project-scoped private download controller with a generated filename and no-store caching.
- Added `hasCaptionedVideo` to the project page without exposing storage paths.
- Added an Export captioned video panel below playback diagnostics.
- Added synchronous processing feedback, disabled export for transient unsaved cues, completed feedback, Export again behavior, and Download MP4 access.
- Used typed Wayfinder controller actions for both render submission and download URL generation.
- Added route, render-controller, download, and page-prop coverage.
- Made the saved-cue page prop explicitly return a list while touching its static-analysis boundary.

### Decisions

- Keep browser rendering synchronous for this first application-facing proof because the short real sample completes quickly and no queue problem has yet been demonstrated.
- Expose completed media only through a project-bound private download route, never through a raw storage path.
- Require saved cues before export; transient transcription output is not an editable or renderable source of truth.
- Keep the existing completed export available until another render succeeds.

### Verification

- All 210 Laravel tests pass with 750 assertions.
- The active-cue frontend suite passes: 8 tests.
- The caption-style frontend suite passes: 18 tests.
- Focused PHPStan analysis passes with zero errors for the three touched page/export controllers.
- Vue TypeScript checking and ESLint complete with no errors.
- The production frontend build completes successfully.
- Laravel Pint and diff checks pass.
- Project 5 cue boundaries differ from browser millisecond timing by at most five milliseconds after deterministic ASS rounding.
- Project 5's 88% browser position and ASS mapping both resolve to normalized y=516 on its 368×640 frame.
- The real render carries the saved 19px bold Georgian-aware font, white text, 28% blue background, centered alignment, and shadow.
- The rendered Georgian text is readable and audio/video duration remains aligned.
- Browser clicking, processing feedback, and download have not yet been manually verified.

### Result

The personal V1 editor can now render its current saved Georgian captions and style from the browser, then expose the completed private MP4 for download.

### Problems / Notes

- ASS background corners remain square and padding differs slightly from the rounded browser overlay.
- Browser fallback and explicit Noto rendering can produce small glyph-metric and line-wrap differences.
- Long videos block the current HTTP request; persistent status or a queue should be introduced only after measuring this behavior with representative media.
- An older completed download can remain available after edits until the user exports again; the UI explicitly presents Export again.

### Next

Manually exercise Export MP4 and Download MP4 on project 5, then propose the minimum Phase 12.7 persistent render-status representation.

## 2026-08-23 — Phase 12.7a: Render-status migration

### Goal

Add only the approved persistent fields needed for a minimal render lifecycle without changing model or rendering behavior yet.

### Changes

- Added nullable `render_status`, `render_error`, and `rendered_at` columns to `video_projects`.
- Added a complete rollback dropping all three columns.
- Applied the migration to the development SQLite database without backfilling or changing existing projects.
- Added export-quality investigation and a possible small preset set to the immediate backlog because final video quality is part of personal V1 success.

### Decisions

- Use `null` status for projects that have never requested a render.
- Reserve pending, processing, completed, and failed lifecycle values for application logic in the next step.
- Store safe failure information only; raw FFmpeg diagnostics and filesystem paths must not enter user-facing status data.
- Do not add an index because personal V1 does not query or aggregate projects by render status.
- Do not add output paths, job IDs, progress, attempts, or queue infrastructure.

### Verification

- All 210 Laravel tests pass with 750 assertions against the migrated test schema.
- Laravel Pint completes successfully.
- A disposable SQLite database completed migration up, rollback, and up again successfully.
- The development SQLite database applied the migration successfully as batch 6.
- Existing project 5 media and its completed export were not modified.
- Model casts and render lifecycle transitions are not yet implemented.

### Result

The database can now persist the minimum render lifecycle while existing projects remain in an explicit never-requested null state.

### Problems / Notes

- The fields are intentionally inert until the next model/action step.
- The reported export quality difference has not yet been measured; adding arbitrary encoder controls before inspection would make the UI harder to understand without proving the right fix.

### Next

Add model casts and tested pending, processing, completed, and failed transitions, then inspect source-versus-export quality characteristics before proposing user choices.

## 2026-08-23 — Phase 12.7b: Render lifecycle behavior

### Goal

Make the approved render-status fields meaningful throughout one synchronous export attempt.

### Changes

- Added the string-backed `VideoRenderStatus` enum for pending, processing, completed, and failed states.
- Added enum and datetime casts plus render lifecycle fields to `VideoProject`.
- Made each export record pending and processing before media work, completed with a timestamp after finalization, or failed with a safe application message.
- Extended failure handling to include ASS-generation failures as well as FFmpeg and file-finalization failures.
- Reused the action's safe failure message in the browser controller.
- Added model-cast, successful-render, FFmpeg-failure, unusable-output, and pre-FFmpeg failure coverage.

### Decisions

- Keep pending as an explicit, short-lived synchronous transition so the same state model remains usable if rendering later moves to a queue.
- Clear the previous safe error when a new attempt begins.
- Preserve an older completed MP4 and its `rendered_at` value when a re-export fails.
- Do not persist raw FFmpeg diagnostics, output paths, progress, attempts, or job identifiers.

### Verification

- The focused render-action, render-controller, and model suites pass: 13 tests with 48 assertions.
- The complete Laravel suite passes: 212 tests with 766 assertions.
- Laravel Pint completes successfully.
- Browser display of the persisted status has not yet been implemented or manually verified.

### Result

Every application render attempt now leaves a durable, typed success or failure state while retaining a previously usable export on failure.

### Problems / Notes

- Because rendering remains synchronous, pending and processing are normally transient and are not yet visible after the redirect.
- A queue and progress reporting remain postponed until representative long-video behavior demonstrates the need.

### Next

Expose the persisted lifecycle and last successful render time on the project page, then inspect source-versus-export quality before proposing quality presets.
