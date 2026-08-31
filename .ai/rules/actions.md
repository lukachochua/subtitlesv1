---
paths:
  - '{transcribe_nemo.py,app/Actions/*Transcription*.php,app/Actions/ApplyTranscriptionCorrections.php,docs/TRANSCRIPTION_QUALITY.md}'
---

# Actions

## Preserve RNNT control and raw ASR
Keep the Georgian FastConformer production path on its default timestamped RNNT decoder. Evaluate post-processing separately, never overwrite raw NeMo JSON/timed words, and restrict the first LLM experiment to validated 1-to-1 token replacements that preserve timestamps.
