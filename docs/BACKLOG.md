# Current

- Propose the smallest development-only way to inspect the ordered normalized words for one `VideoProject`.

# Next

- Inspect the normalized words before designing cue generation.
- Define the minimum caption-cue representation after inspecting the real normalized sequence.

# Later

- Upload, store, display, and play one Georgian MP4.
- Inspect uploaded media with ffprobe and extract ASR-ready audio with FFmpeg.
- Select and test one Georgian-capable ASR provider using real audio and timestamp output.
- Define an internal timestamped-word representation after inspecting real provider output.
- Generate deterministic caption cues and display them over native browser video playback.
- Add cue correction, timing adjustment, splitting, merging, and incremental caption styling.
- Evaluate ASS/libass and FFmpeg for final rendered MP4 export.
- Build a representative Georgian QA set covering names, sports terminology, code-switching, varied speakers, and difficult audio.
- Benchmark alternative Georgian ASR providers only after the complete workflow works.
- Repeat the NeMo experiment with improved audio and benchmark it on the RTX 4060 Ti machine when useful.
- Benchmark Google Cloud Speech-to-Text Chirp and current OpenAI transcription models after the first complete provider workflow works.
- Consider custom Georgian dictionaries, proper-name correction, speaker labels, transcript search, smart cue segmentation, reusable presets, alternate aspect-ratio previews, subtitle export, transcription export, and batch processing after evidence justifies them.
- Consider accounts, subscriptions, billing, storage quotas, collaboration, commercial infrastructure, and production scaling only after personal V1 is demonstrably useful.

# Done

- Phase 0.1: Inspected and recorded the existing repository baseline without changing it.
- Phase 0.2: Created the documentation foundation.
- Phase 0.3: Evaluated the starter scaffold and retained dormant infrastructure deliberately.
- Phase 0.4: Replaced the promotional Laravel screen with the minimal Georgian Captioner shell.
- Phase 0: Established a documented, deliberately minimal Laravel/Inertia/Vue foundation.
- Phase 1.1: Approved the minimum `VideoProject` domain and schema representation.
- Phase 1.2: Added and reversibly verified the `video_projects` migration.
- Phase 1.3: Added and tested the minimal `VideoProject` Eloquent model.
- Phase 1: Established the minimum domain representation for one video project.
- Phase 2.1: Added a native MP4 selection interface without submission behavior.
- Phase 2.2: Added and tested server-side MP4 validation with a 500 MB development limit.
- Phase 2.3: Stored validated MP4 uploads privately using generated paths.
- Phase 2.4: Persisted uploaded-video metadata and added orphan-file cleanup when persistence fails.
- Phase 2.5: Redirected successful uploads to a page displaying safe video-project metadata.
- Phase 2.6: Added controlled, range-capable private MP4 delivery and native browser playback.
- Phase 2: Verified upload, private storage, metadata persistence, project display, native playback, and seeking with a real MP4.
- Phase 3.1: Confirmed FFmpeg and ffprobe 6.1.1 are already installed; Phase 3.2 installation is unnecessary.
- Phase 3.3: Inspected a real uploaded MP4 and confirmed duration plus H.264 video and AAC audio streams.
- Phase 3.4a: Added nullable integer-millisecond duration storage and model support without altering existing rows.
- Phase 3.4b: Added and tested a single-purpose action that validates ffprobe output and persists duration.
- Phase 3.4c: Added a tested Artisan command for inspecting one `VideoProject` by ID.
- Phase 3.4d: Displayed persisted duration, including an explicit uninspected state, on the project page.
- Phase 4.1: Chose one single-purpose action as the minimal FFmpeg audio-extraction boundary.
- Phase 4.2a: Added tested private extraction of mono 16 kHz PCM WAV audio with failed-output cleanup.
- Phase 4.2b: Added a tested Artisan command for extracting audio from one `VideoProject` by ID.
- Phase 4.3: Verified a real extracted WAV's codec, sample rate, channel count, duration, and complete decoding.
- Phase 4: Produced and verified one valid ASR-ready audio file from a real uploaded video.
- Phase 5.1: Compared current Georgian-capable ASR options and recommended ElevenLabs Scribe v2 for the first experiment.
- Phase 5.1b: Chose local faster-whisper as the first experiment to prioritize privacy and avoid per-minute API costs.
- Phase 5.2: Created an isolated external Python environment and verified faster-whisper 1.2.1 imports successfully.
- Phase 5.3: Ran and preserved the first local Georgian transcription with `medium`, CPU `int8`, and word timestamps.
- Phase 5.3b: Compared generic `large-v3` on the same clearer recording and measured its quality, timing, and CPU cost.
- Phase 5.3c: Tested `large-v3-turbo`; inference improved substantially but Georgian text quality remained unusable.
- Phase 5.4: Researched GeoCaption's disclosed pipeline and credible Georgian-specific local ASR models; recommended NVIDIA Georgian FastConformer.
- Phase 5.5: Ran NVIDIA Georgian FastConformer through native NeMo and preserved word, segment, character, and token timestamps.
- Phase 6.1: Chose validated integer milliseconds as the internal ASR timestamp representation.
- Phase 6.2: Chose the minimum immutable internal `TranscriptionWord` representation without adding persistence.
- Phase 6.2b: Implemented and unit-tested the immutable `TranscriptionWord` value object.
- Phase 6.3a: Defined the strict NeMo word-conversion boundary and one-frame duration tolerance.
- Phase 6.3b: Implemented and unit-tested strict NeMo word conversion.
- Phase 6.4: Added a representative NeMo JSON fixture and verified conversion independently of private media.
- Phase 6: Established and tested a provider-independent timestamped-word format plus NeMo conversion.

# Rejected / Out of Scope

- Recreating the Laravel application or reinstalling the configured Vue stack.
- Early SaaS billing, subscriptions, organizations, teams, complex permissions, and multi-user collaboration.
- Premature Redis, microservices, distributed workers, Kubernetes, and infrastructure for unproven scale.
- Generic multilingual, translation, social publishing, marketing-site, and public API work during personal V1.
- General-purpose video editing, timeline editing, stickers, motion tracking, advanced animation, and Canva- or CapCut-like features.
- Using an LLM to guess caption timestamps or making generative rewriting part of the core transcription pipeline.
