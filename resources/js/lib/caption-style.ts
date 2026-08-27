import type { CSSProperties } from 'vue';

export type CaptionTextAlignment = 'left' | 'center' | 'right';
export type CaptionActiveWordStyle = 'text' | 'background';
export type CaptionFont = 'georgian_sans' | 'system_sans' | 'georgian_serif';
export type CaptionStylePresetKey = 'clean' | 'social' | 'news' | 'minimal';

export interface CaptionStyleConfiguration {
    font: CaptionFont;
    font_size_px: number;
    bold: boolean;
    italic: boolean;
    text_color: string;
    background_color: string;
    background_opacity_percent: number;
    text_alignment: CaptionTextAlignment;
    vertical_position_percent: number;
    outline_color: string;
    outline_width_px: number;
    shadow: boolean;
    active_word_enabled: boolean;
    active_word_color: string;
    active_word_style: CaptionActiveWordStyle;
}

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

export const CAPTION_FONT_OPTIONS: ReadonlyArray<{
    key: CaptionFont;
    label: string;
    value: string;
}> = Object.freeze([
    {
        key: 'georgian_sans',
        label: 'Georgian sans',
        value: 'Arial, "Noto Sans Georgian", sans-serif',
    },
    {
        key: 'system_sans',
        label: 'System sans',
        value: 'system-ui, "Noto Sans Georgian", sans-serif',
    },
    {
        key: 'georgian_serif',
        label: 'Georgian serif',
        value: '"Noto Serif Georgian", Georgia, serif',
    },
]);

export const CAPTION_FONT_SIZE_MIN_PX = 12;
export const CAPTION_FONT_SIZE_MAX_PX = 72;
export const CAPTION_FONT_SIZE_DEFAULT_PX = 20;
export const CAPTION_ACTIVE_WORD_DEFAULT_COLOR = '#fde047';
export const CAPTION_BACKGROUND_OPACITY_DEFAULT_PERCENT = 75;
export const CAPTION_OUTLINE_WIDTH_MAX_PX = 4;

export const CLEAN_CAPTION_STYLE_PRESET: Readonly<CaptionStyleConfiguration> =
    Object.freeze({
        font: 'georgian_sans',
        font_size_px: CAPTION_FONT_SIZE_DEFAULT_PX,
        bold: true,
        italic: false,
        text_color: '#ffffff',
        background_color: '#000000',
        background_opacity_percent: CAPTION_BACKGROUND_OPACITY_DEFAULT_PERCENT,
        text_alignment: 'center',
        vertical_position_percent: CAPTION_VERTICAL_POSITION_DEFAULT_PERCENT,
        outline_color: '#000000',
        outline_width_px: 0,
        shadow: true,
        active_word_enabled: true,
        active_word_color: CAPTION_ACTIVE_WORD_DEFAULT_COLOR,
        active_word_style: 'text',
    });

export const SOCIAL_CAPTION_STYLE_PRESET: Readonly<CaptionStyleConfiguration> =
    Object.freeze({
        font: 'system_sans',
        font_size_px: 40,
        bold: true,
        italic: false,
        text_color: '#ffffff',
        background_color: '#000000',
        background_opacity_percent: 20,
        text_alignment: 'center',
        vertical_position_percent: 88,
        outline_color: '#000000',
        outline_width_px: 2,
        shadow: true,
        active_word_enabled: true,
        active_word_color: CAPTION_ACTIVE_WORD_DEFAULT_COLOR,
        active_word_style: 'text',
    });

export const NEWS_CAPTION_STYLE_PRESET: Readonly<CaptionStyleConfiguration> =
    Object.freeze({
        font: 'georgian_sans',
        font_size_px: 32,
        bold: true,
        italic: false,
        text_color: '#ffffff',
        background_color: '#991b1b',
        background_opacity_percent: 90,
        text_alignment: 'left',
        vertical_position_percent: 92,
        outline_color: '#000000',
        outline_width_px: 0,
        shadow: false,
        active_word_enabled: true,
        active_word_color: CAPTION_ACTIVE_WORD_DEFAULT_COLOR,
        active_word_style: 'text',
    });

export const MINIMAL_CAPTION_STYLE_PRESET: Readonly<CaptionStyleConfiguration> =
    Object.freeze({
        font: 'system_sans',
        font_size_px: 24,
        bold: false,
        italic: false,
        text_color: '#ffffff',
        background_color: '#000000',
        background_opacity_percent: 0,
        text_alignment: 'center',
        vertical_position_percent: 94,
        outline_color: '#000000',
        outline_width_px: 1,
        shadow: true,
        active_word_enabled: true,
        active_word_color: CAPTION_ACTIVE_WORD_DEFAULT_COLOR,
        active_word_style: 'text',
    });

export const CAPTION_STYLE_PRESETS: ReadonlyArray<{
    key: CaptionStylePresetKey;
    label: string;
    configuration: Readonly<CaptionStyleConfiguration>;
}> = Object.freeze([
    {
        key: 'clean',
        label: 'Clean',
        configuration: CLEAN_CAPTION_STYLE_PRESET,
    },
    {
        key: 'social',
        label: 'Social',
        configuration: SOCIAL_CAPTION_STYLE_PRESET,
    },
    {
        key: 'news',
        label: 'News',
        configuration: NEWS_CAPTION_STYLE_PRESET,
    },
    {
        key: 'minimal',
        label: 'Minimal',
        configuration: MINIMAL_CAPTION_STYLE_PRESET,
    },
]);

export const findCaptionStylePresetKey = (
    configuration: CaptionStyleConfiguration,
): CaptionStylePresetKey | null =>
    CAPTION_STYLE_PRESETS.find((preset) =>
        Object.entries(preset.configuration).every(
            ([key, value]) =>
                configuration[key as keyof CaptionStyleConfiguration] === value,
        ),
    )?.key ?? null;

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

export const captionStyleConfigurationToBrowserStyle = (
    configuration: CaptionStyleConfiguration,
): CaptionStyle => {
    const font = CAPTION_FONT_OPTIONS.find(
        (option) => option.key === configuration.font,
    );

    if (font === undefined) {
        throw new Error(`Unsupported caption font: ${configuration.font}`);
    }

    return {
        ...DEFAULT_CAPTION_STYLE,
        fontFamily: font.value,
        fontSizePx: configuration.font_size_px,
        fontWeight: configuration.bold ? 700 : 400,
        fontStyle: configuration.italic ? 'italic' : 'normal',
        textColor: configuration.text_color,
        backgroundColor: configuration.background_color,
        backgroundOpacity: configuration.background_opacity_percent / 100,
        outlineColor: configuration.outline_color,
        outlineWidthPx: configuration.outline_width_px,
        textAlign: configuration.text_alignment,
        textShadow: configuration.shadow
            ? DEFAULT_CAPTION_STYLE.textShadow
            : 'none',
    };
};

export const captionFontKeyFromFamily = (fontFamily: string): CaptionFont => {
    const font = CAPTION_FONT_OPTIONS.find(
        (option) => option.value === fontFamily,
    );

    if (font === undefined) {
        throw new Error(`Unsupported caption font family: ${fontFamily}`);
    }

    return font.key;
};

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
