import type { CSSProperties } from 'vue';

export interface CaptionStyle {
    fontFamily: string;
    fontSizePx: number;
    fontWeight: number;
    fontStyle: 'normal' | 'italic';
    lineHeight: number;
    textColor: string;
    backgroundColor: string;
    backgroundOpacity: number;
    textAlign: CSSProperties['textAlign'];
    textShadow: string;
}

export type CaptionPlacement = 'top' | 'middle' | 'bottom';

export const CAPTION_PLACEMENT_OPTIONS: ReadonlyArray<{
    label: string;
    value: CaptionPlacement;
}> = Object.freeze([
    { label: 'Top', value: 'top' },
    { label: 'Middle', value: 'middle' },
    { label: 'Bottom', value: 'bottom' },
]);

export const DEFAULT_CAPTION_PLACEMENT: CaptionPlacement = 'bottom';

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
    textAlign: style.textAlign,
    textShadow: style.textShadow,
});

export const captionPlacementToCss = (
    placement: CaptionPlacement,
): CSSProperties => {
    if (placement === 'top') {
        return { alignItems: 'flex-start', paddingTop: '1rem' };
    }

    if (placement === 'middle') {
        return { alignItems: 'center' };
    }

    return { alignItems: 'flex-end', paddingBottom: '3.5rem' };
};
