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

## Decision: Normalize ASR word timestamps to integer milliseconds

### Context

The first native NVIDIA NeMo Georgian FastConformer result returned floating-point seconds for words, characters, and segments. Its 17 word intervals were ordered and had positive duration, but the final word ended at `13.92` seconds while ffprobe measured the extracted audio as `13.885563` seconds. Downstream cue generation and editing need deterministic comparisons against media duration without depending on provider-specific floating-point values.

### Decision

Represent normalized ASR word boundaries as integer `start_ms` and `end_ms` values. Convert provider seconds by multiplying by 1,000 and rounding to the nearest integer. Require ordered words with `start_ms >= 0` and `end_ms > start_ms`. When a provider boundary exceeds the known audio duration only because of timestamp granularity, clamp it to `duration_ms`; otherwise treat invalid ordering or duration as conversion failure. Preserve the original provider response unchanged alongside normalized data.

Initially retain punctuation as part of the recognized word text. Do not add speaker or confidence fields until a real provider result supplies meaningful values.

### Reason

Integer milliseconds match the existing video-duration representation and are precise enough for browser caption timing. Explicit validation prevents malformed provider timestamps from silently entering cue generation, while preserving raw output allows later conversion improvements without another transcription request.

### Consequences

- Provider adapters must convert and validate timestamps before downstream code consumes them.
- The known media duration is the upper bound for normalized word timestamps.
- Small model-boundary overruns, such as the observed 34 milliseconds, can be normalized deterministically.
- Cue boundary inclusivity, overlap policy, and cue-specific validation remain separate decisions for the cue-generation and editing phases.
- NeMo token, character, and segment timestamps remain available in raw experimental output but are not part of the first internal word representation.

## Decision: Represent one normalized transcription word as an immutable value object

### Context

Downstream cue generation needs provider-independent word text and timing. The application has no existing DTO or value-object convention, and the first NeMo result does not provide justified speaker or confidence values. Passing unstructured provider arrays onward would make timestamp invariants implicit, while persisting words now would couple an experimental provider result to an unproven database design.

### Decision

Represent one normalized word with a small immutable PHP value object named `TranscriptionWord` containing exactly:

```text
text: string
startMs: int
endMs: int
```

The object will enforce non-empty text, `startMs >= 0`, and `endMs > startMs`. A separate future NeMo conversion operation will enforce collection-level rules: source order, non-overlap if required by the observed provider output, and `endMs <= durationMs` after permitted boundary clamping.

Do not make this an Eloquent model, persist it, add a DTO package, or include provider metadata, speaker, confidence, token, character, or segment fields yet.

### Reason

An immutable value object makes the core word invariants explicit and independently testable without introducing storage or a third-party abstraction. Keeping collection validation in the converter avoids giving one word responsibility for neighboring words or media duration it does not know.

### Consequences

- Provider-specific code must produce `TranscriptionWord` instances before cue-generation code consumes words.
- The value object can later expose an array representation if persistence or frontend transfer requires it; that behavior is not needed yet.
- Adding speaker or confidence data requires evidence from a selected provider and a deliberate extension or related representation.
- The approved class location is `app/ValueObjects/TranscriptionWord.php`; future value objects should use this folder only when they represent similarly invariant-driven domain values.

## Decision: Convert NeMo words through one strict application action

### Context

The preserved NeMo result exposes words at `timestamp.word`, with text plus floating-point `start` and `end` seconds. Its timestamp grid advances in 80-millisecond increments, and its final word ends 34 milliseconds beyond the ffprobe-derived audio duration. Conversion must tolerate model granularity without silently repairing materially invalid provider data. The application currently uses single-purpose actions for media operations and has no justified generic ASR-provider framework.

### Decision

Use one provider-specific action named `ConvertNemoTranscriptionWords` in `app/Actions`. It will accept decoded NeMo response data and the known integer `durationMs`, and return an ordered list of `TranscriptionWord` objects.

The converter will:

- Require a non-empty `timestamp.word` list.
- Require every entry to contain non-empty string `word` plus finite numeric `start` and `end` seconds.
- Convert seconds by multiplying by 1,000 and rounding to the nearest integer.
- Preserve input order; never sort provider output silently.
- Require nondecreasing start times and nondecreasing end times across the sequence.
- Allow word intervals to overlap at this stage because alignment overlap is not the same as caption-cue overlap.
- Clamp a boundary beyond `durationMs` only when the overrun is at most 100 milliseconds.
- Reject negative boundaries, reversed intervals, larger duration overruns, malformed structures, and invalid sequence ordering.
- Construct `TranscriptionWord` objects so their local invariants remain enforced in one place.

The 100-millisecond tolerance is a deliberate upper bound slightly larger than one observed 80-millisecond NeMo timestamp frame. It is not a general correction allowance and must not grow automatically for longer media.

### Reason

A provider-specific action is the smallest boundary that isolates NeMo's response shape and can be tested without running the model. Strict validation prevents corrupt timing from reaching cue generation, while the one-frame tolerance handles the real rounding artifact already observed. Allowing word overlap avoids prematurely imposing the future cue-overlap policy on lower-level alignment data.

### Consequences

- Conversion failures must be explicit rather than returning a partial word list.
- Raw NeMo output remains preserved for diagnosis and future re-conversion.
- A different ASR provider may receive its own small converter after real output exists; no shared provider interface is justified yet.
- Cue generation must later decide how overlapping word alignment contributes to non-overlapping caption cues.
- The tolerance should change only if further real NeMo output demonstrates that one model frame is insufficient.
## Decision: Isolate ffprobe duration inspection in one application action

### Context

The application must safely execute ffprobe, validate its output, and persist overall duration without coupling media inspection to an HTTP request, queue, or provider framework prematurely.

### Decision

Use `App\Actions\InspectVideoProject` as the single operation boundary. It resolves the model's recorded private file, invokes `/usr/bin/ffprobe` synchronously through Laravel's Process facade with array arguments and a 30-second timeout, requires positive container duration plus video and audio streams, then persists rounded integer milliseconds.

### Reason

The action keeps one domain operation independently testable while Laravel's Process facade provides safe argument handling, timeouts, fakes, and stray-process prevention without another dependency or custom wrapper.

### Consequences

- Callers can invoke one operation without knowing ffprobe syntax or response structure.
- Invalid files, failed processes, and malformed metadata leave `duration_ms` unchanged.
- `/usr/bin/ffprobe` is currently a development-machine assumption and may need configuration when deployment requirements exist.
- The synchronous boundary is appropriate for this small inspection; longer processing may later justify a queue based on observed request duration.

## Decision: Extract one deterministic ASR-ready WAV through an application action

### Context

The application needs one audio artifact from an uploaded video before testing a real Georgian ASR provider. This operation must not expose filesystem paths to user input or introduce a queue, media framework, or provider abstraction prematurely.

### Decision

Use `App\Actions\ExtractVideoProjectAudio` as the single extraction boundary. It invokes `/usr/bin/ffmpeg` synchronously through Laravel's Process facade with array arguments and a 120-second timeout. It writes mono, 16 kHz, 16-bit PCM WAV audio to the project's private `video-projects/{id}/audio.wav` path, replacing any earlier artifact and deleting partial output on failure.

### Reason

Uncompressed mono PCM is simple to inspect and broadly usable for initial ASR experiments. A deterministic, application-controlled path makes repeated development runs understandable without trusting uploaded filenames or adding database state before it is needed.

### Consequences

- The source video and extracted audio remain on the project's configured private disk.
- Successful extraction currently returns the relative audio path but does not persist it.
- The action checks that output exists and is non-empty; media-format and duration verification remain a separate next step.
- WAV files are larger than compressed audio, and the exact provider input format may be revisited after selecting and testing one ASR provider.
- `/usr/bin/ffmpeg` and synchronous execution are current personal-V1 assumptions that may need configuration or queued execution when real processing evidence justifies it.

## Decision: Test local faster-whisper before a hosted ASR provider

### Context

Hosted Georgian-capable providers offer explicit timestamp APIs but introduce per-minute cost, external media transfer, credentials, and provider retention policies. The personal-V1 development machine has an Intel Core Ultra 5 125U, 16 GB RAM, and no NVIDIA CUDA GPU.

### Decision

Use faster-whisper as the first real Georgian ASR experiment. Start with the multilingual `medium` model on CPU using `int8` computation and request word-level timestamps. Keep its Python virtual environment outside the Laravel repository. Do not integrate the runtime into Laravel until real output and performance have been inspected.

### Reason

This path avoids usage fees and keeps test media local while still providing a practical word-timestamp experiment on the available CPU. Starting with `medium` limits download, memory, and processing cost before evidence justifies trying `large-v3`.

### Consequences

- The first model download and inference use local bandwidth, disk, memory, CPU time, and power rather than a metered ASR API.
- Speaker diarization is not included in the initial experiment.
- Georgian transcription quality and timing accuracy remain unproven until tested against real audio.
- ElevenLabs Scribe v2, Google Chirp, OpenAI transcription models, Meta Omnilingual ASR, and larger Whisper models remain later benchmarks rather than current integrations.
- A future application boundary must treat the Python runtime as a separate media-processing concern instead of adding Python dependencies to PHP.
