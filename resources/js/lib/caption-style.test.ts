import assert from 'node:assert/strict';
import test from 'node:test';

import { captionStyleToCss, DEFAULT_CAPTION_STYLE } from './caption-style.ts';

test('defines one coherent default caption style', () => {
    assert.deepEqual(DEFAULT_CAPTION_STYLE, {
        fontFamily: 'Arial, "Noto Sans Georgian", sans-serif',
        fontSizePx: 28,
        fontWeight: 700,
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
