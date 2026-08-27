---
paths:
  - 'app/{Actions,Models,ValueObjects}/**/*Caption*.php,resources/js/**/*caption*.{ts,vue}'
---

# Js

## Preserve word starts during caption corrections
Accurate preview timing is positional. Correcting a word's spelling must retain its original start_ms; its end_ms may only be clamped to the following word's start (or cue end). When exact word timings exist, cue text edits must keep the same word count because inserted or removed words have no reliable timestamp.
