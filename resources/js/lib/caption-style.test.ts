import assert from 'node:assert/strict';
import test from 'node:test';

import {
    captionStyleToCss,
    CAPTION_FONT_OPTIONS,
    DEFAULT_CAPTION_STYLE,
    normalizeCaptionFontSize,
} from './caption-style.ts';

test('defines one coherent default caption style', () => {
    assert.deepEqual(DEFAULT_CAPTION_STYLE, {
        fontFamily: 'Arial, "Noto Sans Georgian", sans-serif',
        fontSizePx: 28,
        fontWeight: 700,
        fontStyle: 'normal',
        lineHeight: 1.25,
        textColor: '#ffffff',
        backgroundColor: 'rgb(0 0 0 / 0.75)',
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
