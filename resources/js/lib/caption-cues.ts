export interface CaptionCue {
    order: number;
    text: string;
    start_ms: number;
    end_ms: number;
}

export const findActiveCaptionCue = (
    cues: readonly CaptionCue[],
    currentTimeMs: number,
): CaptionCue | null =>
    cues.find(
        (cue) => cue.start_ms <= currentTimeMs && currentTimeMs < cue.end_ms,
    ) ?? null;
