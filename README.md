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
