#!/usr/bin/env python3

import argparse
import json
from pathlib import Path
from time import perf_counter

from faster_whisper import WhisperModel


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Run one local Georgian faster-whisper transcription experiment.",
    )
    parser.add_argument("audio", type=Path, help="Path to the extracted WAV file")
    parser.add_argument("output", type=Path, help="Path for the raw JSON result")
    parser.add_argument(
        "--model",
        choices=("medium", "large-v3", "large-v3-turbo"),
        default="medium",
        help="Whisper model to run (default: medium)",
    )

    return parser.parse_args()


def main() -> None:
    arguments = parse_arguments()
    audio_path = arguments.audio.resolve()
    output_path = arguments.output.resolve()

    if not audio_path.is_file():
        raise SystemExit(f"Audio file does not exist: {audio_path}")

    model_started_at = perf_counter()
    model = WhisperModel(arguments.model, device="cpu", compute_type="int8")
    model_load_seconds = perf_counter() - model_started_at

    transcription_started_at = perf_counter()
    segment_generator, transcription_info = model.transcribe(
        str(audio_path),
        language="ka",
        word_timestamps=True,
    )

    segments = []

    for segment in segment_generator:
        segments.append(
            {
                "id": segment.id,
                "seek": segment.seek,
                "start": segment.start,
                "end": segment.end,
                "text": segment.text,
                "temperature": segment.temperature,
                "avg_logprob": segment.avg_logprob,
                "compression_ratio": segment.compression_ratio,
                "no_speech_prob": segment.no_speech_prob,
                "words": [
                    {
                        "word": word.word,
                        "start": word.start,
                        "end": word.end,
                        "probability": word.probability,
                    }
                    for word in (segment.words or [])
                ],
            }
        )

    transcription_seconds = perf_counter() - transcription_started_at
    transcript = "".join(segment["text"] for segment in segments).strip()

    result = {
        "experiment": {
            "model": arguments.model,
            "device": "cpu",
            "compute_type": "int8",
            "requested_language": "ka",
            "word_timestamps": True,
            "model_load_seconds": round(model_load_seconds, 3),
            "transcription_seconds": round(transcription_seconds, 3),
        },
        "language": transcription_info.language,
        "language_probability": transcription_info.language_probability,
        "duration": transcription_info.duration,
        "duration_after_vad": transcription_info.duration_after_vad,
        "text": transcript,
        "segments": segments,
    }

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(
        json.dumps(result, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

    print(f"Transcript: {transcript}")
    print(f"Detected language: {transcription_info.language}")
    print(f"Language probability: {transcription_info.language_probability:.4f}")
    print(f"Model load: {model_load_seconds:.3f} seconds")
    print(f"Transcription: {transcription_seconds:.3f} seconds")
    print(f"Raw result: {output_path}")


if __name__ == "__main__":
    main()
