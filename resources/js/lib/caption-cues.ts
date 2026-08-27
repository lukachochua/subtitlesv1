export interface CaptionWord {
    order: number;
    text: string;
    start_ms: number;
    end_ms: number;
}

export interface CaptionCue {
    id: number | null;
    order: number;
    text: string;
    start_ms: number;
    end_ms: number;
    words: CaptionWord[];
}

export const findActiveCaptionCue = (
    cues: readonly CaptionCue[],
    currentTimeMs: number,
): CaptionCue | null =>
    cues.find(
        (cue) => cue.start_ms <= currentTimeMs && currentTimeMs < cue.end_ms,
    ) ?? null;

export const findActiveCaptionWord = (
    cue: CaptionCue | null,
    currentTimeMs: number,
): CaptionWord | null =>
    cue?.words.find(
        (word) => word.start_ms <= currentTimeMs && currentTimeMs < word.end_ms,
    ) ?? null;
