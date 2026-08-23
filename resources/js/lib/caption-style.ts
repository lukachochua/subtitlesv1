import type { CSSProperties } from 'vue';

export type CaptionTextAlignment = 'left' | 'center' | 'right';

export interface CaptionStyle {
    fontFamily: string;
    fontSizePx: number;
    fontWeight: number;
    fontStyle: 'normal' | 'italic';
    lineHeight: number;
    textColor: string;
    backgroundColor: string;
    backgroundOpacity: number;
    outlineColor: string;
    outlineWidthPx: number;
    textAlign: CaptionTextAlignment;
    textShadow: string;
}

export const CAPTION_VERTICAL_POSITION_DEFAULT_PERCENT = 100;

export const CAPTION_TEXT_ALIGNMENT_OPTIONS: ReadonlyArray<{
    label: string;
    value: CaptionTextAlignment;
}> = Object.freeze([
    { label: 'Left', value: 'left' },
    { label: 'Center', value: 'center' },
    { label: 'Right', value: 'right' },
]);

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
export const CAPTION_BACKGROUND_OPACITY_DEFAULT_PERCENT = 75;
export const CAPTION_OUTLINE_WIDTH_MAX_PX = 4;

export const normalizeCaptionFontSize = (fontSizePx: number): number =>
    Number.isFinite(fontSizePx)
        ? Math.min(
              CAPTION_FONT_SIZE_MAX_PX,
              Math.max(CAPTION_FONT_SIZE_MIN_PX, Math.round(fontSizePx)),
          )
        : CAPTION_FONT_SIZE_DEFAULT_PX;

export const normalizeCaptionBackgroundOpacityPercent = (
    opacityPercent: number,
): number =>
    Number.isFinite(opacityPercent)
        ? Math.min(100, Math.max(0, Math.round(opacityPercent)))
        : CAPTION_BACKGROUND_OPACITY_DEFAULT_PERCENT;

export const normalizeCaptionVerticalPositionPercent = (
    positionPercent: number,
): number =>
    Number.isFinite(positionPercent)
        ? Math.min(100, Math.max(0, Math.round(positionPercent)))
        : CAPTION_VERTICAL_POSITION_DEFAULT_PERCENT;

export const normalizeCaptionOutlineWidth = (outlineWidthPx: number): number =>
    Number.isFinite(outlineWidthPx)
        ? Math.min(
              CAPTION_OUTLINE_WIDTH_MAX_PX,
              Math.max(0, Math.round(outlineWidthPx * 2) / 2),
          )
        : 0;

const colorWithOpacity = (hexColor: string, opacity: number): string => {
    const match = /^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hexColor);

    if (match === null) {
        throw new Error(`Invalid caption color: ${hexColor}`);
    }

    const [, red, green, blue] = match;
    const normalizedOpacity = Math.min(1, Math.max(0, opacity));

    return `rgb(${Number.parseInt(red, 16)} ${Number.parseInt(green, 16)} ${Number.parseInt(blue, 16)} / ${normalizedOpacity})`;
};

export const DEFAULT_CAPTION_STYLE: Readonly<CaptionStyle> = Object.freeze({
    fontFamily: 'Arial, "Noto Sans Georgian", sans-serif',
    fontSizePx: CAPTION_FONT_SIZE_DEFAULT_PX,
    fontWeight: 700,
    fontStyle: 'normal',
    lineHeight: 1.25,
    textColor: '#ffffff',
    backgroundColor: '#000000',
    backgroundOpacity: CAPTION_BACKGROUND_OPACITY_DEFAULT_PERCENT / 100,
    outlineColor: '#000000',
    outlineWidthPx: 0,
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
    backgroundColor: colorWithOpacity(
        style.backgroundColor,
        style.backgroundOpacity,
    ),
    WebkitTextStroke: `${style.outlineWidthPx}px ${style.outlineColor}`,
    textAlign: style.textAlign,
    textShadow: style.textShadow,
});

export const captionVerticalPositionToCss = (
    positionPercent: number,
): CSSProperties => {
    const normalizedPosition =
        normalizeCaptionVerticalPositionPercent(positionPercent);

    return {
        position: 'absolute',
        left: '50%',
        top: `calc(1rem + (100% - 4.5rem) * ${normalizedPosition / 100})`,
        transform: `translate(-50%, -${normalizedPosition}%)`,
    };
};
