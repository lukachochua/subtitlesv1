import assert from 'node:assert/strict';
import test from 'node:test';

import {
    captionStyleToCss,
    captionVerticalPositionToCss,
    CAPTION_FONT_OPTIONS,
    DEFAULT_CAPTION_STYLE,
    normalizeCaptionBackgroundOpacityPercent,
    normalizeCaptionFontSize,
    normalizeCaptionOutlineWidth,
    normalizeCaptionVerticalPositionPercent,
} from './caption-style.ts';

test('maps vertical caption percentages to overlay CSS', () => {
    assert.deepEqual(captionVerticalPositionToCss(0), {
        position: 'absolute',
        left: '50%',
        top: 'calc(1rem + (100% - 4.5rem) * 0)',
        transform: 'translate(-50%, -0%)',
    });
    assert.deepEqual(captionVerticalPositionToCss(50), {
        position: 'absolute',
        left: '50%',
        top: 'calc(1rem + (100% - 4.5rem) * 0.5)',
        transform: 'translate(-50%, -50%)',
    });
    assert.deepEqual(captionVerticalPositionToCss(100), {
        position: 'absolute',
        left: '50%',
        top: 'calc(1rem + (100% - 4.5rem) * 1)',
        transform: 'translate(-50%, -100%)',
    });
});

test('normalizes caption vertical position percentages', () => {
    assert.equal(normalizeCaptionVerticalPositionPercent(Number.NaN), 100);
    assert.equal(normalizeCaptionVerticalPositionPercent(-1), 0);
    assert.equal(normalizeCaptionVerticalPositionPercent(49.6), 50);
    assert.equal(normalizeCaptionVerticalPositionPercent(101), 100);
});

test('defines one coherent default caption style', () => {
    assert.deepEqual(DEFAULT_CAPTION_STYLE, {
        fontFamily: 'Arial, "Noto Sans Georgian", sans-serif',
        fontSizePx: 28,
        fontWeight: 700,
        fontStyle: 'normal',
        lineHeight: 1.25,
        textColor: '#ffffff',
        backgroundColor: '#000000',
        backgroundOpacity: 0.75,
        outlineColor: '#000000',
        outlineWidthPx: 0,
        textAlign: 'center',
        textShadow: '0 1px 2px rgb(0 0 0 / 0.9)',
    });
});

test('maps the default caption style to browser CSS', () => {
    assert.deepEqual(captionStyleToCss(DEFAULT_CAPTION_STYLE), {
        fontFamily: 'Arial, "Noto Sans Georgian", sans-serif',
        fontSize: '28px',
        fontWeight: 700,
        fontStyle: 'normal',
        lineHeight: 1.25,
        color: '#ffffff',
        backgroundColor: 'rgb(0 0 0 / 0.75)',
        WebkitTextStroke: '0px #000000',
        textAlign: 'center',
        textShadow: '0 1px 2px rgb(0 0 0 / 0.9)',
    });
});

test('maps a changed font size without changing the default', () => {
    const css = captionStyleToCss({
        ...DEFAULT_CAPTION_STYLE,
        fontSizePx: 44,
    });

    assert.equal(css.fontSize, '44px');
    assert.equal(DEFAULT_CAPTION_STYLE.fontSizePx, 28);
});

test('maps font family, weight, italic, and text color changes', () => {
    const css = captionStyleToCss({
        ...DEFAULT_CAPTION_STYLE,
        fontFamily: CAPTION_FONT_OPTIONS[2].value,
        fontWeight: 400,
        fontStyle: 'italic',
        textColor: '#facc15',
    });

    assert.equal(css.fontFamily, '"Noto Serif Georgian", Georgia, serif');
    assert.equal(css.fontWeight, 400);
    assert.equal(css.fontStyle, 'italic');
    assert.equal(css.color, '#facc15');
});

test('normalizes caption font sizes to supported whole pixels', () => {
    assert.equal(normalizeCaptionFontSize(Number.NaN), 28);
    assert.equal(normalizeCaptionFontSize(11), 12);
    assert.equal(normalizeCaptionFontSize(28.6), 29);
    assert.equal(normalizeCaptionFontSize(73), 72);
});

test('maps background color and opacity independently', () => {
    const css = captionStyleToCss({
        ...DEFAULT_CAPTION_STYLE,
        backgroundColor: '#1d4ed8',
        backgroundOpacity: 0.4,
    });

    assert.equal(css.backgroundColor, 'rgb(29 78 216 / 0.4)');
});

test('normalizes caption background opacity percentages', () => {
    assert.equal(normalizeCaptionBackgroundOpacityPercent(Number.NaN), 75);
    assert.equal(normalizeCaptionBackgroundOpacityPercent(-1), 0);
    assert.equal(normalizeCaptionBackgroundOpacityPercent(48.6), 49);
    assert.equal(normalizeCaptionBackgroundOpacityPercent(101), 100);
});

test('maps outline and shadow styles independently', () => {
    const css = captionStyleToCss({
        ...DEFAULT_CAPTION_STYLE,
        outlineColor: '#ef4444',
        outlineWidthPx: 2.5,
        textShadow: 'none',
    });

    assert.equal(css.WebkitTextStroke, '2.5px #ef4444');
    assert.equal(css.textShadow, 'none');
});

test('normalizes outline width to supported half pixels', () => {
    assert.equal(normalizeCaptionOutlineWidth(Number.NaN), 0);
    assert.equal(normalizeCaptionOutlineWidth(-1), 0);
    assert.equal(normalizeCaptionOutlineWidth(1.26), 1.5);
    assert.equal(normalizeCaptionOutlineWidth(5), 4);
});
