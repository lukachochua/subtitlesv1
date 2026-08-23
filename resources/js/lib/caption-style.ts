import type { CSSProperties } from 'vue';

export interface CaptionStyle {
    fontFamily: string;
    fontSizePx: number;
    fontWeight: number;
    lineHeight: number;
    textColor: string;
    backgroundColor: string;
    textAlign: CSSProperties['textAlign'];
    textShadow: string;
}

export const DEFAULT_CAPTION_STYLE: Readonly<CaptionStyle> = Object.freeze({
    fontFamily: 'Arial, "Noto Sans Georgian", sans-serif',
    fontSizePx: 28,
    fontWeight: 700,
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
    lineHeight: style.lineHeight,
    color: style.textColor,
    backgroundColor: style.backgroundColor,
    textAlign: style.textAlign,
    textShadow: style.textShadow,
});
