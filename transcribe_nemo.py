#!/usr/bin/env python3

import argparse
import json
from pathlib import Path
from time import perf_counter
from typing import Any

import torch
from nemo.collections.asr.models import EncDecHybridRNNTCTCBPEModel


MODEL_NAME = "nvidia/stt_ka_fastconformer_hybrid_large_pc"


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Run one NVIDIA NeMo Georgian FastConformer experiment.",
    )
    parser.add_argument("audio", type=Path, help="Path to a mono 16 kHz WAV file")
    parser.add_argument("output", type=Path, help="Path for the raw JSON result")

    return parser.parse_args()


def make_json_safe(value: Any) -> Any:
    if isinstance(value, torch.Tensor):
        return value.detach().cpu().tolist()

    if isinstance(value, dict):
        return {key: make_json_safe(item) for key, item in value.items()}

    if isinstance(value, (list, tuple)):
        return [make_json_safe(item) for item in value]

    return value


def main() -> None:
    arguments = parse_arguments()
    audio_path = arguments.audio.resolve()
    output_path = arguments.output.resolve()

    if not audio_path.is_file():
        raise SystemExit(f"Audio file does not exist: {audio_path}")

    device = torch.device("cuda" if torch.cuda.is_available() else "cpu")

    model_started_at = perf_counter()
    model = EncDecHybridRNNTCTCBPEModel.from_pretrained(model_name=MODEL_NAME)
    model = model.to(device)
    model_load_seconds = perf_counter() - model_started_at

    transcription_started_at = perf_counter()
    hypotheses = model.transcribe(
        [str(audio_path)],
        batch_size=1,
        return_hypotheses=True,
        timestamps=True,
    )
    transcription_seconds = perf_counter() - transcription_started_at

    hypothesis = hypotheses[0]
    result = {
        "experiment": {
            "model": MODEL_NAME,
            "device": device.type,
            "timestamps_requested": True,
            "model_load_seconds": round(model_load_seconds, 3),
            "transcription_seconds": round(transcription_seconds, 3),
        },
        "text": hypothesis.text,
        "score": make_json_safe(hypothesis.score),
        "timestamp": make_json_safe(hypothesis.timestamp),
    }

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(
        json.dumps(result, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

    print(f"Transcript: {hypothesis.text}")
    timestamp_keys = (
        list(hypothesis.timestamp) if isinstance(hypothesis.timestamp, dict) else []
    )
    print(f"Timestamp keys: {timestamp_keys}")
    print(f"Model load: {model_load_seconds:.3f} seconds")
    print(f"Transcription: {transcription_seconds:.3f} seconds")
    print(f"Raw result: {output_path}")


if __name__ == "__main__":
    main()
