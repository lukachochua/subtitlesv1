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

## Decision: Use ASS/libass for the first rendered-caption export

### Context

The application now stores integer-millisecond cues and complete project-level styles covering font, size, emphasis, colors, background opacity, alignment, vertical position, outline, and shadow. Final export needs a deterministic subtitle representation that FFmpeg can burn into MP4 while shaping Georgian text correctly.

The installed FFmpeg 6.1.1 build includes libass 0.17.1, Fontconfig, FreeType, FriBidi, and HarfBuzz. A 368×640 probe using Noto Sans Georgian rendered four Georgian samples successfully with complex shaping and selected the expected regular and bold font files.

### Decision

Use generated ASS subtitles with FFmpeg's libass-backed `ass` filter for the first V1 rendered export.

Keep ASS generation as an explicit application boundary. Map product settings into ASS deliberately rather than exposing raw ASS fields to the editor.

### Reason

ASS supports the core V1 requirements: precise cue timing, Georgian Unicode text, explicit fonts and sizes, bold and italic variants, text and background colors with opacity, alignment, vertical placement, outline, shadow, and line breaks. The required rendering stack is already installed on the current machine, so no dependency installation is needed now.

### Consequences

- Rendering must use a known Georgian-capable font rather than relying on browser CSS fallback stacks. Noto Sans Georgian and Noto Serif Georgian are available on the current machine; deployment machines must provide the chosen fonts or a controlled `fontsdir`.
- ASS timestamps use centiseconds, so integer-millisecond cue boundaries need a documented deterministic conversion and tests.
- CSS pixels do not map directly to source-video pixels. Font size, outline width, shadow, and vertical placement need a defined PlayRes-based mapping.
- ASS colors require BGR channel order and inverse alpha conversion.
- Browser left/center/right alignment and arbitrary vertical percentage require deliberate ASS alignment, margin, or position mapping.
- ASS cannot exactly reproduce the browser overlay's rounded background. A single ordinary ASS style also cannot always preserve an independently colored translucent box and glyph outline exactly; V1 may approximate this or use layered dialogue after a focused experiment.
- Browser and libass line wrapping may differ. Preview-versus-render comparison remains a required later step.
- The next step is limited to generating one ASS file from real cues with one default style; it does not yet render an MP4.

## Decision: Render the first V1 export as H.264 MP4 with copied source audio

### Context

Burning ASS captions requires video re-encoding, but the source audio does not need modification. Failed or interrupted media processing must not replace an earlier usable export with a partial file.

### Decision

Render with FFmpeg `libx264`, the `medium` preset, CRF 18, copied source audio when present, and `+faststart`. Write to a fixed private temporary MP4 and move it to `captioned.mp4` only after FFmpeg succeeds and the output is non-empty.

Regenerate ASS at the start of every render so the export always reflects the current saved cues and style.

### Reason

H.264 MP4 has broad playback compatibility. CRF 18 provides a high-quality V1 output, copying audio avoids unnecessary quality loss, and fast-start metadata improves playback behavior. A temporary output protects the last completed export from failed FFmpeg runs.

### Consequences

- Video rendering may take substantial time and currently runs synchronously from the Artisan command.
- The current one-hour process timeout is a development guard, not a final job-processing design.
- The first application-facing render action runs synchronously and exposes request-local processing feedback. Persistent processing status and a queue remain justified only when the next measured workflow step requires them.
- Re-rendering replaces the previous completed `captioned.mp4` only after a new usable temporary output exists.
- The source upload is never modified.

## Decision: Persist the minimum render lifecycle on the video project

### Context

Request-local button feedback disappears after navigation or failure, while longer videos may eventually require background processing. The application needs a durable lifecycle without introducing jobs, progress tracking, or separate render records prematurely.

### Decision

Store nullable `render_status`, `render_error`, and `rendered_at` fields directly on `video_projects`.

Use `null` for never requested and reserve `pending`, `processing`, `completed`, and `failed` as the render lifecycle states. Store only a safe application-level failure message, not raw FFmpeg output or filesystem paths.

### Reason

These fields are sufficient to restore meaningful UI state, record the last successful export time, and support a later queue boundary without committing to queue infrastructure now.

### Consequences

- The deterministic output path remains outside the database.
- Progress percentages, job IDs, attempts, separate render history, and processing timestamps remain unimplemented.
- `VideoRenderStatus` provides the four allowed application values, and the render action now persists each lifecycle transition.
- A new attempt clears the previous error; a failed re-export preserves both the prior completed MP4 and its last successful `rendered_at` timestamp.
- The project page receives a safe render-state object and combines it with request-local form processing feedback; polling is unnecessary while rendering remains synchronous.
- Existing projects retain `null` values and require no data backfill.

## Decision: Use three explicit H.264 export quality presets

### Context

Project 5's source retained 368×640, 30 fps, H.264 video and copied AAC audio, but the original CRF 18 export reduced video bitrate from about 701 kb/s to 644 kb/s and looked worse to the user.

### Decision

Offer High (CRF 14, slow preset), Balanced (CRF 18, medium), and Smaller (CRF 23, fast). Default new and previously unset projects to High and persist the last selected preset on the video project.

### Reason

These choices express the useful product trade-off without exposing encoder jargon in the UI. Burned captions require video re-encoding, so source stream copying is not possible.

### Consequences

- High produces larger files and slower renders but materially reduces visible generation loss.
- Audio continues to be copied unchanged.
- Resolution and frame rate remain inherited from the source.
- Advanced codec, bitrate, resolution, and arbitrary CRF controls remain out of scope.

## Decision: Invoke local NeMo directly from Laravel for personal V1

### Context

The proven Georgian NeMo script previously required separate shell commands and manual environment activation between upload and cue editing.

### Decision

Configure an absolute `NEMO_PYTHON_PATH` per processing machine and invoke `transcribe_nemo.py` through Laravel Process with array arguments and a one-hour timeout. One application action inspects media when needed, extracts audio, transcribes, validates timestamp output, converts cues, and persists them only when no edited cues exist.

### Reason

Direct executable invocation removes shell activation from the user workflow while reusing the tested local model and existing application boundaries without a new package or service.

### Consequences

- Each machine must install its own compatible NeMo environment and model cache.
- Laravel supplies an explicit conservative process `PATH`, including the conventional CUDA binary directory, so NeMo dependencies do not rely on an activated shell environment.
- Safe lifecycle fields record status while raw process details remain server-side.
- Saved or edited cues are never overwritten automatically.
- Processing remains synchronous until representative server timing justifies the existing database queue.

## Decision: Group caption styling behind a single category toolbar

### Context

Displaying every position, typography, background, outline, shadow, and preset control simultaneously overloaded the project page and made the video-editing workflow harder to scan.

### Decision

Keep one compact icon-and-label toolbar for Position, Text, Background, Effects, and Presets. Show at most one category panel at a time, allow the active category to close, and keep one persistent Save style action with an unsaved-state indicator.

### Reason

The categories match the user's editing intent while preserving immediate preview and reducing visual density. Visible labels keep the controls discoverable; tooltips supplement rather than replace them.

### Consequences

- Style values and persistence remain unchanged; this is an information-architecture refactor.
- Generate captions, cue correction, and export remain distinct workflow actions rather than style categories.
- A future desktop side panel or mobile bottom sheet can reuse the same category model if evidence justifies it.

## Decision: Organize the editor into three goal-oriented workspaces

### Context

Automatic generation, cue correction, styling, playback diagnostics, and exporting were all visible in one long project page. Even after styling was grouped internally, unrelated workflow stages still competed for attention.

### Decision

Keep the video visible and place three main workspace buttons beneath it: Captions, Style, and Export. Select Captions by default. Captions contains generation and cue editing, Style contains the existing five appearance categories, and Export contains quality, rendering, status, and download controls.

### Reason

The sequence matches the user's product journey—create and correct, style, then export—and avoids a vague Technical category that would combine unrelated beginning and ending operations.

### Consequences

- Only the active workspace controls are visible while the video preview remains stable.
- Caption count, unsaved-style state, and export availability appear as compact workspace indicators.
- Local style state survives workspace switching.
- File metadata remains visible as project context; development playback details appear only in Captions.

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

The first native NVIDIA NeMo Georgian FastConformer result returned floating-point seconds for words, characters, and segments. Its 17 word intervals were ordered and had positive duration. The extracted WAV ends at `13.885563` seconds, the final ASR word ends at `13.92` seconds, and the source video ends at `14.067` seconds. Downstream caption timing must use the source video as its playback boundary without depending on provider-specific floating-point values.

### Decision

Represent normalized ASR word boundaries as integer `start_ms` and `end_ms` values. Convert provider seconds by multiplying by 1,000 and rounding to the nearest integer. Require ordered words with `start_ms >= 0` and `end_ms > start_ms`. Bound caption-alignment timestamps by the known source-video `duration_ms`; a slightly shorter extracted audio stream must not truncate otherwise valid captions. Preserve the original provider response unchanged alongside normalized data.

Initially retain punctuation as part of the recognized word text. Do not add speaker or confidence fields until a real provider result supplies meaningful values.

### Reason

Integer milliseconds match the existing video-duration representation and are precise enough for browser caption timing. Explicit validation prevents malformed provider timestamps from silently entering cue generation, while preserving raw output allows later conversion improvements without another transcription request.

### Consequences

- Provider adapters must convert and validate timestamps before downstream code consumes them.
- The known media duration is the upper bound for normalized word timestamps.
- A small provider boundary overrun beyond the source video can be normalized deterministically within the separately approved tolerance.
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

The preserved NeMo result exposes words at `timestamp.word`, with text plus floating-point `start` and `end` seconds. Its timestamp grid advances in 80-millisecond increments. The final word is slightly beyond the extracted WAV duration but remains within the source-video duration used for browser playback. Conversion still needs a narrow defensive tolerance for a provider boundary that genuinely exceeds the video duration, without silently repairing materially invalid data. The application currently uses single-purpose actions for media operations and has no justified generic ASR-provider framework.

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

The 100-millisecond tolerance is a deliberate upper bound slightly larger than one observed 80-millisecond NeMo timestamp frame. It is defensive rather than exercised by project 4's video boundary, is not a general correction allowance, and must not grow automatically for longer media.

### Reason

A provider-specific action is the smallest boundary that isolates NeMo's response shape and can be tested without running the model. Strict validation prevents corrupt timing from reaching cue generation, while the one-frame tolerance handles the real rounding artifact already observed. Allowing word overlap avoids prematurely imposing the future cue-overlap policy on lower-level alignment data.

### Consequences

- Conversion failures must be explicit rather than returning a partial word list.
- Raw NeMo output remains preserved for diagnosis and future re-conversion.
- A different ASR provider may receive its own small converter after real output exists; no shared provider interface is justified yet.
- Cue generation must later decide how overlapping word alignment contributes to non-overlapping caption cues.
- The tolerance should change only if further real NeMo output demonstrates that one model frame is insufficient.

## Decision: Represent one generated caption cue as an immutable value object

### Context

Automatic cue generation needs a provider-independent output distinct from timestamped ASR words. A cue groups one or more words into the text and timing that will eventually appear over the video. Persistence, editing, styling, and grouping rules are not yet designed, but the minimum cue fields and local invariants are already required to implement the first deterministic grouping algorithm.

### Decision

Represent one generated cue with an immutable `App\ValueObjects\CaptionCue` containing exactly:

```text
order: int
text: string
startMs: int
endMs: int
```

Use one-based order values. The object enforces `order >= 1`, non-empty text, `startMs >= 0`, and `endMs > startMs`.

The future cue generator remains responsible for collection-level behavior: consecutive order values, source-video duration bounds, cue overlap policy, word grouping, punctuation spacing, and any maximum text or duration rules.

Do not make the cue an Eloquent model or add style, editing, provider, confidence, or speaker fields yet.

### Reason

The value object mirrors the established immutable `TranscriptionWord` boundary and makes the smallest cue independently valid without prematurely designing storage or editing. One-based order matches the human-facing cue table and subtitle conventions.

### Consequences

- Cue-generation code must return valid `CaptionCue` objects in deterministic order.
- The object can remain unchanged while grouping rules are iterated against real Georgian speech.
- Persistence and editable cue identity will require a separate schema decision when the editor needs saved cues.
- Styling remains project or cue presentation data and is not part of this first cue representation.

## Decision: Generate initial caption cues with deterministic bounded rules

### Context

Project 4 provides 17 ordered Georgian words with real NeMo timestamps. Personal V1 needs a transparent first grouping algorithm that produces readable cues without an LLM or Georgian-specific linguistic parser. The algorithm must be simple enough to test and revise after manual playback while preventing excessively long text or timing.

### Decision

Process normalized `TranscriptionWord` objects in their existing order and include every word exactly once. Begin a cue with the first available word and append following words until the earliest applicable boundary:

- End after a word whose text ends with strong punctuation: `.`, `?`, `!`, or `…`.
- End before the next word when the silent gap is at least 800 milliseconds.
- End before adding a word that would exceed 8 words.
- End before adding a word that would exceed 42 Unicode characters, counting one inserted space between words.
- End before adding a word that would make cue duration exceed 3,500 milliseconds.

Join word text with exactly one space. Because punctuation is retained on the recognized word, do not add or rewrite punctuation. Set cue start to its first word's `startMs`, end to its last word's `endMs`, and assign consecutive one-based order.

For this first algorithm, reject overlapping input word intervals rather than guessing how to create non-overlapping cues. A single word is still emitted even if it individually exceeds a text or duration limit; the generator must not drop it or create an empty cue.

### Reason

The limits are conservative starting values suitable for short social and interview captions, and every boundary is deterministic and explainable. An 800-millisecond gap separates the clear pause observed between project 4's counting groups without splitting its shorter pauses. Strong punctuation provides an obvious boundary, while word, character, and duration caps protect readability when punctuation is missing or inaccurate.

### Consequences

- Project 4 is expected to produce these four initial cues:

```text
1 | ერთი ორი, სამი, ოთხი, ხუთი                | 1680–4480
2 | ექვსი შვიდი რვა ცხრა                     | 4720–6880
3 | ათი გამარჯობა გაგიმარჯოს, როგორ ხარმე.   | 7680–11040
4 | შელო მოხარმეც კარგა.                     | 11600–13920
```

- Cue quality depends on ASR punctuation, but hard limits still bound unpunctuated speech.
- Unicode character counting must use a multibyte-safe operation so Georgian letters count as characters rather than UTF-8 bytes.
- These thresholds are initial product rules, not permanent truths; change them only after playback QA demonstrates a concrete problem.
- Line breaking, cue editing, persistence, and style layout remain separate later concerns.

## Decision: Load transient project caption data through one application action

### Context

The development command originally owned the fixed private NeMo result path, JSON decoding, word normalization, and cue generation. The project page will need the same transient cues for browser inspection, and duplicating that pipeline in a controller would create inconsistent error handling and grouping behavior before persistence exists.

### Decision

Use `App\Actions\LoadVideoProjectCaptionData` as the reusable read boundary. Given a `VideoProject`, it requires persisted video duration, reads only `video-projects/{id}/transcription.nemo-fastconformer.raw.json` from the project's recorded disk, decodes JSON strictly, converts words, generates cues, and returns:

```text
words: list<TranscriptionWord>
cues: list<CaptionCue>
```

Keep the result transient. Do not persist normalized words or cues, accept arbitrary paths, run NeMo, or hide malformed transcription data by returning partial results.

### Reason

One action keeps storage and transformation behavior reusable by console and HTTP callers while remaining smaller than a repository, provider interface, or transcription service layer. Returning both stages preserves development traceability and avoids rerunning conversion when a caller needs words and cues together.

### Consequences

- The inspection command delegates loading and transformation to the same boundary intended for the project page.
- Missing duration or result files fail explicitly; invalid JSON, word conversion, and cue generation also fail rather than returning incomplete captions.
- The project-page backend sends `cues: null` only when the fixed private result file is absent. When a result exists, strict loading runs and malformed data remains an error rather than appearing absent.
- Persistence will require a separate decision and will not silently replace this experimental raw-result boundary.

## Decision: Use half-open intervals for active caption cues

### Context

Browser playback provides a continuously changing `currentTime`, while caption cues have integer millisecond start and end boundaries. Active-cue selection must behave deterministically at exact boundaries, in empty gaps, and when one cue starts exactly as another ends.

### Decision

A cue is active when:

```text
start_ms <= current_ms < end_ms
```

Convert browser seconds to integer milliseconds before selection. Return no active cue when the list is empty, before the first cue, after a cue's exclusive end, or within an uncovered gap. When adjacent cues share a boundary, the next cue becomes active at that exact millisecond.

### Reason

Half-open intervals avoid displaying two adjacent cues at the same instant and give each exact boundary one unambiguous result. This matches common time-range handling and the product requirement that captions disappear at their end.

### Consequences

- Cue start is inclusive and cue end is exclusive throughout browser preview logic.
- Empty timeline regions render no caption.
- Editing and persistence validation must preserve this interpretation when they are introduced.
- Final rendering behavior should be compared with this browser rule when export work begins.

## Decision: Keep persisted caption cues ordered and non-overlapping

### Context

Caption timing edits can otherwise create overlapping intervals where browser preview and final rendering must choose between multiple active captions. The application already uses integer milliseconds, ordered cues, and half-open active intervals.

### Decision

Persisted cue timing follows these invariants:

```text
start_ms >= 0
end_ms > start_ms
end_ms <= video duration
previous end_ms <= current start_ms
current end_ms <= next start_ms
```

Video duration must be known before timing can be edited. Cue `order` identifies the previous and next cue. Adjacent cues may touch at one exact millisecond, and gaps between cues are allowed, but cues may not overlap.

Continue interpreting intervals as half-open: `start_ms <= current_ms < end_ms`.

### Reason

A no-overlap policy gives browser preview, editing, splitting, merging, and eventual subtitle rendering one deterministic timeline. Allowing touching boundaries supports immediate transitions, while allowing gaps preserves intentional silent sections.

### Consequences

- Start edits are constrained by the previous cue's end and the current cue's end.
- End edits are constrained by the current cue's start and the next cue's start.
- Browser number controls expose the same neighboring limits enforced by the backend.
- Future split, merge, generation, import, and regeneration operations must preserve these invariants.
- Existing rows are not rewritten or repaired automatically; malformed imported data will need explicit validation at its mutation boundary.

## Decision: Split cues at the current playhead and a central word boundary

### Context

A split operation must choose both a timestamp boundary and how to distribute the original caption text. Requiring several manual inputs would make a common correction slow, while duplicating or dropping text would make the result unsafe.

### Decision

Allow a saved cue to be split only when the browser playhead is strictly inside its interval and the cue contains at least two whitespace-delimited words.

Use the playhead's integer millisecond value as the new touching boundary. Keep the original cue as the first half and create a new immediately following cue as the second half. Divide text at the central word boundary, giving the first cue the extra word when the count is odd. Shift all later cue orders upward by one inside the same database transaction.

### Reason

The playhead provides a timing choice grounded in actual speech, while a deterministic word split produces an immediately editable starting point without losing any words. A transaction prevents partial order or interval changes.

### Consequences

- Splitting preserves the original cue's full time range as two non-overlapping half-open intervals.
- All original words remain in order, although whitespace is normalized to single spaces.
- The user may correct the two resulting texts with the existing text editor.
- One-word cues cannot be split until their text contains at least two words.
- Later cue orders change, while their text and timestamps remain unchanged.

## Decision: Merge a cue only with its immediate next cue

### Context

Merging must specify which row survives, how text and timing combine, and how project-local ordering changes. Cues may legally contain gaps, so merging can also change what appears during an uncovered interval.

### Decision

Expose “Merge with next” on every saved cue except the final cue. Keep the selected cue, append the immediate next cue's trimmed text with one separating space, extend the selected cue's end to the next cue's end, delete the next row, and decrement all later cue orders in one transaction.

Allow merging across a gap. The merged cue spans continuously from the selected cue's start through the next cue's end, including that former gap.

### Reason

A directional operation is unambiguous in a table and preserves the selected cue's identity and start boundary. Spanning a gap matches the user's explicit intent to group the two captions and can be refined using existing timing controls.

### Consequences

- Text and timing from two adjacent ordered rows become one editable row.
- The selected cue ID and start remain stable; the next cue is deleted.
- Later cues retain text and timing while their order decreases by one.
- Merging across silence makes the combined caption visible during the former gap.
- The last cue cannot be merged because it has no next cue.

## Decision: Start caption styling from one typed browser default

### Context

The browser overlay had a useful but anonymous collection of Tailwind classes. Incremental style controls and eventual ASS rendering need explicit values that can be inspected, tested, and mapped instead of treating visual defaults as incidental markup.

### Decision

Use one typed `DEFAULT_CAPTION_STYLE` for the initial browser preview:

```text
font family: Arial, Noto Sans Georgian, sans-serif
font size: 28 px
font weight: 700
font style: normal
line height: 1.25
text: white
background: black at 75% opacity
alignment: center
placement: bottom
outline: black at 0 px
text shadow: 0 1px 2px black at 90% opacity
```

Map that representation to Vue inline CSS through one pure function. Keep structural layout such as padding, rounded corners, maximum width, and bottom placement in the overlay markup until their individual style phases establish requirements.

### Reason

Named values create the smallest usable style boundary for live controls without adding persistence, a style model, or a generic theming system prematurely. Explicit Georgian-aware font fallback makes script support visible as a product constraint.

### Consequences

- Browser preview has one inspectable and testable default.
- Phase 10 controls can change font family, size, weight, style, text color, background color, background opacity, outline color/width, and shadow visibility in local state from this baseline.
- Background color and opacity remain separate style values so users can adjust transparency without changing the chosen color and later rendering can map both explicitly.
- Represent vertical position as an integer percentage from 0 (top) through 50 (middle) to 100 (bottom). Bottom remains the default, and the browser preview reserves space above native video controls.
- Represent outline width in half-pixel steps from 0 through 4 pixels and keep outline color independent. Keep the initial shadow as a boolean choice until real rendering comparison justifies more parameters.
- Limit initial text alignment to left, center, and right, with center as the default. This aligns lines within the caption box; it does not move the box horizontally across the video.
- Persistence remains undecided until useful controls exist and their data shape is proven.
- Final ASS/libass mapping must be validated separately; CSS values are not assumed to render identically.

## Decision: Persist one caption style configuration per video project

### Context

The validated browser controls now cover font, size, emphasis, colors, background opacity, alignment, vertical position, outline, and shadow. Keeping these values only in Vue state makes every choice disappear on reload and prevents final rendering from consuming the same project settings.

### Decision

Store one nullable `caption_style` JSON object directly on `video_projects`. Persist normalized product values such as a font key, integer percentages, booleans, color values, and constrained numeric sizes rather than browser CSS strings or ASS-specific values. A null value means the project uses the application's complete default style.

### Reason

Personal V1 has one shared caption style per project. A separate one-to-one table or many individual columns would add complexity without a current query, relationship, or reuse requirement. JSON keeps the configuration cohesive while its browser-to-render mapping is still being validated.

### Consequences

- Existing projects remain valid with a null style and resolve to application defaults.
- The `VideoProject` model owns the canonical normalized default and exposes `resolvedCaptionStyle()` without replacing the stored null value.
- Style updates must validate the complete supported shape because SQLite does not enforce individual JSON fields.
- Vue preview CSS and future ASS/libass values must be derived from normalized product settings.
- The Inertia page receives normalized product settings and performs one explicit mapping from font keys, booleans, percentages, and sizes to browser CSS-facing state.
- Preview changes stay immediate and local; one explicit Save style action replaces the complete validated project configuration instead of issuing requests during slider movement.
- Per-cue styling, reusable presets, and style versioning remain postponed until evidence requires them.

## Decision: Persist editable caption cues separately from raw ASR output

### Context

The project page currently regenerates transient cues from preserved NeMo JSON. A browser text correction would therefore disappear on reload, while later timing edits, splitting, merging, and export all require one durable edited representation. Regenerating from ASR must not silently destroy human corrections.

### Decision

Store editable cues in `caption_cues` with `video_project_id`, project-local `order`, `text`, `start_ms`, `end_ms`, and Laravel timestamps. Enforce unique order within each project and cascade cue deletion when its video project is deleted.

Generate and persist cues only when the project has no saved cues. After that first save, database cues are the editing, preview, and eventual export source of truth. Any future regeneration must be an explicit warned action rather than an automatic overwrite.

Keep caption presentation settings, including text size, outside individual cue rows. Introduce them later as project-level caption style settings so every cue previews and renders consistently.

### Reason

Separating immutable experimental ASR evidence from mutable caption output preserves recovery and comparison while allowing corrections to survive reloads. A single project-level style avoids duplicated values and separates transcript content from presentation.

### Consequences

- Raw NeMo JSON remains unchanged when users edit captions.
- Saved cues take precedence over transient regeneration on normal page loads.
- Cue text, timing, order, splitting, and merging can build on one small table.
- Text size and other style controls require a separate later persistence decision.
- Regeneration behavior needs an explicit user-facing workflow before it is exposed.

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
# Decision: Keep stable NeMo RNNT as the measured transcription control

## Context

Default RNNT currently outperforms tested NeMo beam, auxiliary CTC, and Whisper variants for Georgian. CTC also breaks the established word-timestamp contract.

## Decision

Keep `nvidia/stt_ka_fastconformer_hybrid_large_pc` on its normal RNNT decoder with timestamped hypotheses. Preserve raw NeMo JSON and measure every isolated experiment against it.

## Consequences

- Beam and CTC settings are absent from production transcription.
- Larger beam sizes and more Whisper cycling require new evidence.
- Experimental post-editing cannot overwrite raw ASR or automatically modify production captions.

# Decision: Measure Georgian transcription with a private manifest and deterministic scorer

## Context

Listening and visual comparison do not provide reproducible error counts or human-effort tracking.

## Decision

Use a versioned, development-only manifest referencing private audio, verified transcripts, and preserved NeMo JSON. Score Unicode-normalized, punctuation-free text with word and character edit distance, retaining Georgian word forms. Optionally record correction time and audio duration.

## Consequences

- Private media and results remain outside the repository.
- The normal test suite never invokes NeMo or paid APIs.
- Dataset revisions are explicit and the evaluation set must never become training data.
- Production promotion requires repeatable WER, CER, or correction-time improvement across representative clips.

# Decision: Restrict the first LLM post-editor to validated one-token replacements

## Context

A text-only model may fix morphology and spelling-like errors but cannot acoustically verify plausible facts or proper nouns. Structural edits would invalidate existing word timing.

## Decision

Accept only structured, zero-based 1→1 replacement operations whose index and original token match raw timed words. Reject empty, whitespace-containing, malformed, duplicate, or out-of-range operations, and preserve each word's timestamps.

## Consequences

- The deterministic validation/application layer can be tested without an API.
- Additions, deletions, splits, merges, and paraphrases are excluded from the first experiment.
- A provider integration remains isolated until a benchmark demonstrates net benefit with very few new errors.
