import assert from 'node:assert/strict';
import test from 'node:test';

import { findActiveCaptionCue } from './caption-cues.ts';
import type { CaptionCue } from './caption-cues.ts';

const cues: CaptionCue[] = [
    {
        id: null,
        order: 1,
        text: 'ერთი ორი',
        start_ms: 1000,
        end_ms: 2000,
    },
    {
        id: null,
        order: 2,
        text: 'სამი ოთხი',
        start_ms: 2500,
        end_ms: 3500,
    },
];

test('returns no active cue when the cue list is empty', () => {
    assert.equal(findActiveCaptionCue([], 1000), null);
});

test('returns no active cue before the first cue', () => {
    assert.equal(findActiveCaptionCue(cues, 999), null);
});

test('includes the cue start boundary', () => {
    assert.equal(findActiveCaptionCue(cues, 1000), cues[0]);
});

test('returns the cue during its interval', () => {
    assert.equal(findActiveCaptionCue(cues, 1500), cues[0]);
});

test('excludes the cue end boundary', () => {
    assert.equal(findActiveCaptionCue(cues, 2000), null);
});

test('returns no active cue during a gap', () => {
    assert.equal(findActiveCaptionCue(cues, 2250), null);
});

test('selects the next cue at its start boundary', () => {
    assert.equal(findActiveCaptionCue(cues, 2500), cues[1]);
});

test('transitions directly when one cue starts as the previous cue ends', () => {
    const adjacentCues: CaptionCue[] = [
        { id: null, order: 1, text: 'ერთი', start_ms: 0, end_ms: 1000 },
        { id: null, order: 2, text: 'ორი', start_ms: 1000, end_ms: 2000 },
    ];

    assert.equal(findActiveCaptionCue(adjacentCues, 1000), adjacentCues[1]);
});
