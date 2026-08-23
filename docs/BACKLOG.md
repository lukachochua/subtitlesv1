# Current

- Phase 0.3: Evaluate the generated starter code, its references, and whether retaining it helps personal V1. Do not remove anything without approval.

# Next

- After approved cleanup, establish the minimal Georgian Captioner application shell.
- Propose the minimum domain representation for one uploaded video before changing the database.

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

# Rejected / Out of Scope

- Recreating the Laravel application or reinstalling the configured Vue stack.
- Early SaaS billing, subscriptions, organizations, teams, complex permissions, and multi-user collaboration.
- Premature Redis, microservices, distributed workers, Kubernetes, and infrastructure for unproven scale.
- Generic multilingual, translation, social publishing, marketing-site, and public API work during personal V1.
- General-purpose video editing, timeline editing, stickers, motion tracking, advanced animation, and Canva- or CapCut-like features.
- Using an LLM to guess caption timestamps or making generative rewriting part of the core transcription pipeline.
