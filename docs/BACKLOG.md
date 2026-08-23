# Current

- Phase 2.6: Serve and play the stored MP4 with the native browser video element.

# Next

- Manually verify native playback with a real Georgian MP4.

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
- Consider custom Georgian dictionaries, proper-name correction, speaker labels, transcript search, smart cue segmentation, reusable presets, alternate aspect-ratio previews, subtitle export, transcription export, local/private ASR, and batch processing after evidence justifies them.
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

# Rejected / Out of Scope

- Recreating the Laravel application or reinstalling the configured Vue stack.
- Early SaaS billing, subscriptions, organizations, teams, complex permissions, and multi-user collaboration.
- Premature Redis, microservices, distributed workers, Kubernetes, and infrastructure for unproven scale.
- Generic multilingual, translation, social publishing, marketing-site, and public API work during personal V1.
- General-purpose video editing, timeline editing, stickers, motion tracking, advanced animation, and Canva- or CapCut-like features.
- Using an LLM to guess caption timestamps or making generative rewriting part of the core transcription pipeline.
