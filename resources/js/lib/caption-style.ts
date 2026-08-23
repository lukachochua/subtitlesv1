import type { CSSProperties } from 'vue';

export interface CaptionStyle {
    fontFamily: string;
    fontSizePx: number;
    fontWeight: number;
    fontStyle: 'normal' | 'italic';
    lineHeight: number;
    textColor: string;
    backgroundColor: string;
    textAlign: CSSProperties['textAlign'];
    textShadow: string;
}

export const CAPTION_FONT_OPTIONS = Object.freeze([
    {
        label: 'Georgian sans',
        value: 'Arial, "Noto Sans Georgian", sans-serif',
    },
    {
        label: 'System sans',
        value: 'system-ui, "Noto Sans Georgian", sans-serif',
    },
    {
        label: 'Georgian serif',
        value: '"Noto Serif Georgian", Georgia, serif',
    },
]);

export const CAPTION_FONT_SIZE_MIN_PX = 12;
export const CAPTION_FONT_SIZE_MAX_PX = 72;
export const CAPTION_FONT_SIZE_DEFAULT_PX = 28;

export const normalizeCaptionFontSize = (fontSizePx: number): number =>
    Number.isFinite(fontSizePx)
        ? Math.min(
              CAPTION_FONT_SIZE_MAX_PX,
              Math.max(CAPTION_FONT_SIZE_MIN_PX, Math.round(fontSizePx)),
          )
        : CAPTION_FONT_SIZE_DEFAULT_PX;

export const DEFAULT_CAPTION_STYLE: Readonly<CaptionStyle> = Object.freeze({
    fontFamily: 'Arial, "Noto Sans Georgian", sans-serif',
    fontSizePx: CAPTION_FONT_SIZE_DEFAULT_PX,
    fontWeight: 700,
    fontStyle: 'normal',
    lineHeight: 1.25,
    textColor: '#ffffff',
    backgroundColor: 'rgb(0 0 0 / 0.75)',
    textAlign: 'center',
    textShadow: '0 1px 2px rgb(0 0 0 / 0.9)',
});

export const captionStyleToCss = (style: CaptionStyle): CSSProperties => ({
    fontFamily: style.fontFamily,
    fontSize: `${style.fontSizePx}px`,
    fontWeight: style.fontWeight,
    fontStyle: style.fontStyle,
    lineHeight: style.lineHeight,
    color: style.textColor,
    backgroundColor: style.backgroundColor,
    textAlign: style.textAlign,
    textShadow: style.textShadow,
});
