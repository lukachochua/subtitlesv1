# Georgian Captioner

A Georgian-first application for turning uploaded video into timed, editable captions and rendered captioned video.

## Local Development

Start the Laravel and Vite development processes:

```bash
composer run dev
```

## Current Video Project Commands

Inspect an uploaded video's streams and persist its duration:

```bash
php artisan video-projects:inspect <project-id>
```

Extract private mono, 16 kHz, 16-bit PCM WAV audio for ASR experimentation:

```bash
php artisan video-projects:extract-audio <project-id>
```

The project ID is visible in the uploaded project's page URL. Extracted audio is stored privately and is not browser-accessible.

## Frontend Tests

Run the dependency-free TypeScript tests with Node's built-in test API:

```bash
npm run test:frontend
```

## Local Georgian ASR Experiment

The application invokes the NeMo environment directly; activating it with
`source` is not required for browser caption generation. Configure the absolute
Python executable path in `.env`:

```dotenv
NEMO_PYTHON_PATH=/home/your-user/.virtualenvs/georgian-captioner-nemo/bin/python
```

Laravel also supplies a conservative executable `PATH` to the NeMo child
process so native dependencies do not depend on an activated shell. Override
`NEMO_PROCESS_PATH` only when a machine keeps CUDA or other required tools in a
different location.

Each processing machine needs its own NeMo environment. The model downloads to
that machine's local cache on its first run. After configuration, upload an MP4
and use **Generate captions** on its project page. The application inspects the
video, extracts audio, runs NVIDIA Georgian FastConformer, and saves editable
caption cues in one operation.

The first ASR proof of concept uses faster-whisper 1.2.1 in an external Python virtual environment. Activate it before running the experiment:

```bash
source /home/lukachochua/.virtualenvs/georgian-captioner-asr/bin/activate
```

Transcribe an extracted WAV with multilingual Whisper `medium`, CPU `int8`, and word timestamps:

```bash
python transcribe_local.py \
  storage/app/private/video-projects/3/audio.wav \
  storage/app/private/video-projects/3/transcription.raw.json
```

The first run downloads the model into the local Hugging Face cache. Both the extracted WAV and raw transcription JSON remain in private application storage and must not be committed.

Run the controlled `large-v3` comparison with the same CPU `int8` and word-timestamp settings:

```bash
python transcribe_local.py \
  storage/app/private/video-projects/4/audio.wav \
  storage/app/private/video-projects/4/transcription.large-v3.raw.json \
  --model large-v3
```

The Whisper experiments, including `large-v3-turbo`, did not produce usable
Georgian transcription. The next
controlled experiment uses NVIDIA's Georgian FastConformer through its native
NeMo runtime. Activate the separate CPU environment:

```bash
source /home/lukachochua/.virtualenvs/georgian-captioner-nemo/bin/activate
```

Run it against project 4's extracted audio:

```bash
python transcribe_nemo.py \
  storage/app/private/video-projects/4/audio.wav \
  storage/app/private/video-projects/4/transcription.nemo-fastconformer.raw.json
```

The first run downloads the model into the local cache. The output preserves
the transcript and any native timestamp structures returned by NeMo. The script
uses CUDA automatically when the active PyTorch installation supports it;
otherwise, it runs on CPU.

Inspect one project's preserved NeMo result as normalized integer-millisecond
words and generated caption cues without modifying it:

```bash
php artisan video-projects:inspect-transcription <project-id>
```

The project must already have a persisted duration and a private result at
`video-projects/{id}/transcription.nemo-fastconformer.raw.json`.

Persist one project's generated cues as its editable caption source:

```bash
php artisan video-projects:persist-caption-cues <project-id>
```

This command succeeds only when the project has no saved cues. Repeating it
fails instead of replacing saved or manually corrected captions.

Generate a private ASS subtitle file from one project's saved cues and current
caption style:

```bash
php artisan video-projects:generate-ass <project-id>
```

The command inspects the source video's dimensions and writes
`storage/app/private/video-projects/{id}/captions.ass`. It does not render or
modify the source MP4.

Render one project's current saved cues and style into a private captioned MP4:

```bash
php artisan video-projects:render <project-id>
```

The command regenerates the ASS file and writes
`storage/app/private/video-projects/{id}/captioned.mp4`. The source upload is
never modified. A failed render removes its partial temporary file and preserves
the previous completed export.

The browser export form offers High quality (CRF 14/slow), Balanced (CRF
18/medium), and Smaller file (CRF 23/fast). Audio is copied without re-encoding;
video must be re-encoded to burn captions into its frames.
