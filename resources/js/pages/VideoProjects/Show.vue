<script setup lang="ts">
import { Form, Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { CaptionCue } from '@/lib/caption-cues';
import { findActiveCaptionCue } from '@/lib/caption-cues';
import {
    captionFontKeyFromFamily,
    captionStyleConfigurationToBrowserStyle,
    captionStyleToCss,
    captionVerticalPositionToCss,
    CAPTION_FONT_OPTIONS,
    CAPTION_FONT_SIZE_MAX_PX,
    CAPTION_FONT_SIZE_MIN_PX,
    CAPTION_OUTLINE_WIDTH_MAX_PX,
    CAPTION_TEXT_ALIGNMENT_OPTIONS,
    DEFAULT_CAPTION_STYLE,
    normalizeCaptionBackgroundOpacityPercent,
    normalizeCaptionFontSize,
    normalizeCaptionOutlineWidth,
    normalizeCaptionVerticalPositionPercent,
} from '@/lib/caption-style';
import type { CaptionStyleConfiguration } from '@/lib/caption-style';
import { home } from '@/routes';
import { media as videoProjectMedia } from '@/routes/video-projects';
import { update as updateCaptionCue } from '@/routes/video-projects/caption-cues';
import { update as updateCaptionCueEndTime } from '@/routes/video-projects/caption-cues/end-time';
import { store as mergeCaptionCueWithNext } from '@/routes/video-projects/caption-cues/merge-next';
import { store as splitCaptionCue } from '@/routes/video-projects/caption-cues/split';
import { update as updateCaptionCueStartTime } from '@/routes/video-projects/caption-cues/start-time';
import { update as updateCaptionStyle } from '@/routes/video-projects/caption-style';

interface VideoProject {
    id: number;
    original_filename: string;
    mime_type: string;
    size_bytes: number;
    duration_ms: number | null;
}

const props = defineProps<{
    videoProject: VideoProject;
    cues: CaptionCue[] | null;
    captionStyle: CaptionStyleConfiguration;
}>();

const initialCaptionStyle = captionStyleConfigurationToBrowserStyle(
    props.captionStyle,
);

const formattedSize = new Intl.NumberFormat('en', {
    style: 'unit',
    unit: 'megabyte',
    unitDisplay: 'short',
    maximumFractionDigits: 2,
}).format(props.videoProject.size_bytes / 1_000_000);

const formattedDuration =
    props.videoProject.duration_ms === null
        ? 'Not inspected'
        : `${(props.videoProject.duration_ms / 1_000).toFixed(3)} seconds`;

const formatCueTime = (milliseconds: number): string =>
    `${(milliseconds / 1_000).toFixed(3)} s`;

const getCueStartMinimum = (cueIndex: number): number =>
    props.cues?.[cueIndex - 1]?.end_ms ?? 0;

const getCueEndMaximum = (cueIndex: number): number | undefined =>
    props.cues?.[cueIndex + 1]?.start_ms ??
    props.videoProject.duration_ms ??
    undefined;

const getSplitUnavailableReason = (cue: CaptionCue): string | null => {
    if (cue.text.trim().split(/\s+/u).length < 2) {
        return 'Add at least two words before splitting this cue.';
    }

    if (
        currentTimeMilliseconds.value <= cue.start_ms ||
        currentTimeMilliseconds.value >= cue.end_ms
    ) {
        return 'Pause the playhead inside this cue to split it.';
    }

    return null;
};

const canSplitCueAtPlayhead = (cue: CaptionCue): boolean =>
    getSplitUnavailableReason(cue) === null;

const videoElement = ref<HTMLVideoElement | null>(null);
const currentTimeSeconds = ref(0);
const currentTimeMilliseconds = computed(() =>
    Math.round(currentTimeSeconds.value * 1_000),
);
const captionFontSizePx = ref(initialCaptionStyle.fontSizePx);
const captionFontFamily = ref(initialCaptionStyle.fontFamily);
const captionTextColor = ref(initialCaptionStyle.textColor);
const captionBackgroundColor = ref(initialCaptionStyle.backgroundColor);
const captionBackgroundOpacityPercent = ref(
    Math.round(initialCaptionStyle.backgroundOpacity * 100),
);
const captionOutlineColor = ref(initialCaptionStyle.outlineColor);
const captionOutlineWidthPx = ref(initialCaptionStyle.outlineWidthPx);
const captionHasShadow = ref(props.captionStyle.shadow);
const captionTextAlignment = ref(initialCaptionStyle.textAlign);
const captionVerticalPositionPercent = ref(
    props.captionStyle.vertical_position_percent,
);
const captionIsBold = ref(props.captionStyle.bold);
const captionIsItalic = ref(props.captionStyle.italic);
const captionPreviewStyle = computed(() => ({
    ...DEFAULT_CAPTION_STYLE,
    fontFamily: captionFontFamily.value,
    fontSizePx: captionFontSizePx.value,
    fontWeight: captionIsBold.value ? 700 : 400,
    fontStyle: captionIsItalic.value
        ? ('italic' as const)
        : ('normal' as const),
    textColor: captionTextColor.value,
    backgroundColor: captionBackgroundColor.value,
    backgroundOpacity: captionBackgroundOpacityPercent.value / 100,
    outlineColor: captionOutlineColor.value,
    outlineWidthPx: captionOutlineWidthPx.value,
    textShadow: captionHasShadow.value
        ? DEFAULT_CAPTION_STYLE.textShadow
        : 'none',
    textAlign: captionTextAlignment.value,
}));
const captionStyleForm = useForm<CaptionStyleConfiguration>(props.captionStyle);
const activeCue = computed(() =>
    props.cues === null
        ? null
        : findActiveCaptionCue(props.cues, currentTimeMilliseconds.value),
);

const updateCurrentTime = (event: Event): void => {
    currentTimeSeconds.value = (
        event.currentTarget as HTMLVideoElement
    ).currentTime;
};

const seekToCue = (cue: CaptionCue): void => {
    if (videoElement.value === null) {
        return;
    }

    const cueStartSeconds = cue.start_ms / 1_000;

    videoElement.value.currentTime = cueStartSeconds;
    currentTimeSeconds.value = cueStartSeconds;
};

const updateCaptionFontSize = (event: Event): void => {
    const input = event.currentTarget as HTMLInputElement;

    captionFontSizePx.value = normalizeCaptionFontSize(input.valueAsNumber);
    input.value = captionFontSizePx.value.toString();
};

const updateCaptionBackgroundOpacity = (event: Event): void => {
    const input = event.currentTarget as HTMLInputElement;

    captionBackgroundOpacityPercent.value =
        normalizeCaptionBackgroundOpacityPercent(input.valueAsNumber);
    input.value = captionBackgroundOpacityPercent.value.toString();
};

const updateCaptionVerticalPosition = (event: Event): void => {
    const input = event.currentTarget as HTMLInputElement;

    captionVerticalPositionPercent.value =
        normalizeCaptionVerticalPositionPercent(input.valueAsNumber);
    input.value = captionVerticalPositionPercent.value.toString();
};

const updateCaptionOutlineWidth = (event: Event): void => {
    const input = event.currentTarget as HTMLInputElement;

    captionOutlineWidthPx.value = normalizeCaptionOutlineWidth(
        input.valueAsNumber,
    );
    input.value = captionOutlineWidthPx.value.toString();
};

const currentCaptionStyleConfiguration = (): CaptionStyleConfiguration => ({
    font: captionFontKeyFromFamily(captionFontFamily.value),
    font_size_px: captionFontSizePx.value,
    bold: captionIsBold.value,
    italic: captionIsItalic.value,
    text_color: captionTextColor.value,
    background_color: captionBackgroundColor.value,
    background_opacity_percent: captionBackgroundOpacityPercent.value,
    text_alignment: captionTextAlignment.value,
    vertical_position_percent: captionVerticalPositionPercent.value,
    outline_color: captionOutlineColor.value,
    outline_width_px: captionOutlineWidthPx.value,
    shadow: captionHasShadow.value,
});

const saveCaptionStyle = (): void => {
    captionStyleForm
        .transform(() => currentCaptionStyleConfiguration())
        .patch(
            updateCaptionStyle.url({ videoProject: props.videoProject.id }),
            {
                preserveScroll: true,
            },
        );
};
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-stone-50 px-6 py-16 text-stone-950 dark:bg-stone-950 dark:text-stone-50"
    >
        <Head :title="videoProject.original_filename" />

        <main class="w-full max-w-5xl">
            <p
                class="text-sm font-semibold tracking-widest text-red-700 uppercase dark:text-red-400"
            >
                Video project
            </p>

            <h1
                class="mt-4 text-3xl font-semibold tracking-tight wrap-break-word sm:text-4xl"
            >
                {{ videoProject.original_filename }}
            </h1>

            <section
                class="mt-10 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-800 dark:bg-stone-900"
                aria-labelledby="video-details"
            >
                <h2 id="video-details" class="text-xl font-semibold">
                    Uploaded video
                </h2>

                <div
                    class="relative mx-auto mt-6 w-fit max-w-full overflow-hidden rounded-xl bg-black"
                >
                    <video
                        ref="videoElement"
                        controls
                        preload="metadata"
                        :src="videoProjectMedia.url(videoProject.id)"
                        class="block max-h-[75vh] w-auto max-w-full bg-black"
                        @timeupdate="updateCurrentTime"
                    >
                        Your browser does not support HTML video playback.
                    </video>

                    <div
                        v-if="activeCue"
                        class="pointer-events-none absolute inset-0"
                    >
                        <p
                            class="w-max max-w-[90%] rounded-md px-3 py-1.5 wrap-break-word whitespace-pre-wrap"
                            :style="[
                                captionStyleToCss(captionPreviewStyle),
                                captionVerticalPositionToCss(
                                    captionVerticalPositionPercent,
                                ),
                            ]"
                        >
                            {{ activeCue.text }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-3 rounded-lg border border-stone-200 bg-white px-3 py-3 dark:border-stone-700 dark:bg-stone-900"
                >
                    <div class="flex items-center justify-between gap-3">
                        <label
                            for="caption-vertical-position"
                            class="text-sm font-medium text-stone-700 dark:text-stone-200"
                        >
                            Vertical position
                        </label>
                        <output
                            for="caption-vertical-position"
                            class="font-mono text-sm tabular-nums"
                        >
                            {{ captionVerticalPositionPercent }}%
                        </output>
                    </div>
                    <input
                        id="caption-vertical-position"
                        v-model.number="captionVerticalPositionPercent"
                        type="range"
                        min="0"
                        max="100"
                        step="1"
                        class="mt-2 w-full accent-red-700 dark:accent-red-500"
                        @change="updateCaptionVerticalPosition"
                    />
                    <div
                        class="flex justify-between text-xs font-medium text-stone-500 dark:text-stone-400"
                        aria-hidden="true"
                    >
                        <span>Top</span>
                        <span>Middle</span>
                        <span>Bottom</span>
                    </div>
                </div>

                <div
                    class="mt-3 grid gap-2 rounded-lg bg-stone-100 px-3 py-2 text-sm dark:bg-stone-950"
                >
                    <div class="flex items-center justify-between gap-4">
                        <span
                            class="font-medium text-stone-600 dark:text-stone-300"
                        >
                            Playback time
                        </span>
                        <output class="font-mono tabular-nums">
                            {{ currentTimeSeconds.toFixed(3) }} s
                        </output>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <span
                            class="shrink-0 font-medium text-stone-600 dark:text-stone-300"
                        >
                            Active cue
                        </span>
                        <output class="text-right leading-5">
                            <template v-if="cues === null">
                                Not available
                            </template>
                            <template v-else-if="activeCue">
                                #{{ activeCue.order }} {{ activeCue.text }}
                            </template>
                            <template v-else>None</template>
                        </output>
                    </div>
                </div>

                <div
                    class="mt-4 rounded-lg border border-stone-200 p-4 dark:border-stone-700"
                    aria-labelledby="caption-style-heading"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3
                                id="caption-style-heading"
                                class="font-semibold"
                            >
                                Caption style
                            </h3>
                            <p
                                class="mt-1 text-xs text-stone-500 dark:text-stone-400"
                            >
                                Browser preview only; resets when this page is
                                reloaded.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                v-if="captionStyleForm.recentlySuccessful"
                                class="text-xs font-medium text-emerald-700 dark:text-emerald-400"
                            >
                                Saved
                            </span>
                            <button
                                type="button"
                                :disabled="captionStyleForm.processing"
                                class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 dark:bg-red-600 dark:hover:bg-red-500 dark:focus-visible:ring-red-400 dark:focus-visible:ring-offset-stone-900"
                                @click="saveCaptionStyle"
                            >
                                {{
                                    captionStyleForm.processing
                                        ? 'Saving…'
                                        : 'Save style'
                                }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4">
                        <p
                            v-if="captionStyleForm.hasErrors"
                            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950 dark:text-red-300"
                        >
                            Style could not be saved. Review the selected values
                            and try again.
                        </p>
                        <div>
                            <label
                                for="caption-font-family"
                                class="block text-sm font-medium text-stone-700 dark:text-stone-200"
                            >
                                Font
                            </label>
                            <select
                                id="caption-font-family"
                                v-model="captionFontFamily"
                                class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-50 dark:focus-visible:ring-red-400"
                            >
                                <option
                                    v-for="font in CAPTION_FONT_OPTIONS"
                                    :key="font.value"
                                    :value="font.value"
                                >
                                    {{ font.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="caption-font-size"
                                class="block text-sm font-medium text-stone-700 dark:text-stone-200"
                            >
                                Font size
                            </label>
                            <div
                                class="mt-2 grid grid-cols-[minmax(0,1fr)_5rem] items-center gap-3"
                            >
                                <input
                                    id="caption-font-size"
                                    v-model.number="captionFontSizePx"
                                    type="range"
                                    :min="CAPTION_FONT_SIZE_MIN_PX"
                                    :max="CAPTION_FONT_SIZE_MAX_PX"
                                    step="1"
                                    class="w-full accent-red-700 dark:accent-red-500"
                                />
                                <input
                                    :value="captionFontSizePx"
                                    type="number"
                                    :min="CAPTION_FONT_SIZE_MIN_PX"
                                    :max="CAPTION_FONT_SIZE_MAX_PX"
                                    step="1"
                                    aria-label="Caption font size in pixels"
                                    class="w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 font-mono text-sm text-stone-950 tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-50 dark:focus-visible:ring-red-400"
                                    @change="updateCaptionFontSize"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="caption-text-color"
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-200"
                                >
                                    Text color
                                </label>
                                <div class="mt-2 flex items-center gap-3">
                                    <input
                                        id="caption-text-color"
                                        v-model="captionTextColor"
                                        type="color"
                                        class="h-10 w-14 cursor-pointer rounded-lg border border-stone-300 bg-white p-1 dark:border-stone-700 dark:bg-stone-950"
                                    />
                                    <output
                                        for="caption-text-color"
                                        class="font-mono text-sm uppercase"
                                    >
                                        {{ captionTextColor }}
                                    </output>
                                </div>
                            </div>

                            <fieldset>
                                <legend
                                    class="text-sm font-medium text-stone-700 dark:text-stone-200"
                                >
                                    Emphasis
                                </legend>
                                <div class="mt-3 flex flex-wrap gap-4">
                                    <label
                                        class="flex cursor-pointer items-center gap-2 text-sm"
                                    >
                                        <input
                                            v-model="captionIsBold"
                                            type="checkbox"
                                            class="size-4 accent-red-700 dark:accent-red-500"
                                        />
                                        <span class="font-bold">Bold</span>
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-2 text-sm"
                                    >
                                        <input
                                            v-model="captionIsItalic"
                                            type="checkbox"
                                            class="size-4 accent-red-700 dark:accent-red-500"
                                        />
                                        <span class="italic">Italic</span>
                                    </label>
                                </div>
                            </fieldset>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="caption-background-color"
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-200"
                                >
                                    Background color
                                </label>
                                <div class="mt-2 flex items-center gap-3">
                                    <input
                                        id="caption-background-color"
                                        v-model="captionBackgroundColor"
                                        type="color"
                                        class="h-10 w-14 cursor-pointer rounded-lg border border-stone-300 bg-white p-1 dark:border-stone-700 dark:bg-stone-950"
                                    />
                                    <output
                                        for="caption-background-color"
                                        class="font-mono text-sm uppercase"
                                    >
                                        {{ captionBackgroundColor }}
                                    </output>
                                </div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <label
                                        for="caption-background-opacity"
                                        class="text-sm font-medium text-stone-700 dark:text-stone-200"
                                    >
                                        Background opacity
                                    </label>
                                    <output
                                        for="caption-background-opacity"
                                        class="font-mono text-sm tabular-nums"
                                    >
                                        {{ captionBackgroundOpacityPercent }}%
                                    </output>
                                </div>
                                <div
                                    class="mt-2 grid grid-cols-[minmax(0,1fr)_5rem] items-center gap-3"
                                >
                                    <input
                                        id="caption-background-opacity"
                                        v-model.number="
                                            captionBackgroundOpacityPercent
                                        "
                                        type="range"
                                        min="0"
                                        max="100"
                                        step="1"
                                        class="w-full accent-red-700 dark:accent-red-500"
                                    />
                                    <input
                                        :value="captionBackgroundOpacityPercent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="1"
                                        aria-label="Caption background opacity percentage"
                                        class="w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 font-mono text-sm text-stone-950 tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-50 dark:focus-visible:ring-red-400"
                                        @change="updateCaptionBackgroundOpacity"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="caption-outline-color"
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-200"
                                >
                                    Outline color
                                </label>
                                <div class="mt-2 flex items-center gap-3">
                                    <input
                                        id="caption-outline-color"
                                        v-model="captionOutlineColor"
                                        type="color"
                                        class="h-10 w-14 cursor-pointer rounded-lg border border-stone-300 bg-white p-1 dark:border-stone-700 dark:bg-stone-950"
                                    />
                                    <output
                                        for="caption-outline-color"
                                        class="font-mono text-sm uppercase"
                                    >
                                        {{ captionOutlineColor }}
                                    </output>
                                </div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <label
                                        for="caption-outline-width"
                                        class="text-sm font-medium text-stone-700 dark:text-stone-200"
                                    >
                                        Outline width
                                    </label>
                                    <output
                                        for="caption-outline-width"
                                        class="font-mono text-sm tabular-nums"
                                    >
                                        {{ captionOutlineWidthPx }} px
                                    </output>
                                </div>
                                <input
                                    id="caption-outline-width"
                                    v-model.number="captionOutlineWidthPx"
                                    type="range"
                                    min="0"
                                    :max="CAPTION_OUTLINE_WIDTH_MAX_PX"
                                    step="0.5"
                                    class="mt-2 w-full accent-red-700 dark:accent-red-500"
                                    @change="updateCaptionOutlineWidth"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <fieldset>
                                <legend
                                    class="text-sm font-medium text-stone-700 dark:text-stone-200"
                                >
                                    Text alignment
                                </legend>
                                <div
                                    class="mt-2 grid grid-cols-3 rounded-lg border border-stone-300 p-1 dark:border-stone-700"
                                >
                                    <label
                                        v-for="alignmentOption in CAPTION_TEXT_ALIGNMENT_OPTIONS"
                                        :key="alignmentOption.value"
                                        class="cursor-pointer"
                                    >
                                        <input
                                            v-model="captionTextAlignment"
                                            type="radio"
                                            name="caption-text-alignment"
                                            :value="alignmentOption.value"
                                            class="peer sr-only"
                                        />
                                        <span
                                            class="block rounded-md px-2 py-2 text-center text-xs font-semibold text-stone-600 peer-checked:bg-red-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-red-600 peer-focus-visible:ring-offset-2 dark:text-stone-300 dark:peer-checked:bg-red-600 dark:peer-focus-visible:ring-red-400 dark:peer-focus-visible:ring-offset-stone-900"
                                        >
                                            {{ alignmentOption.label }}
                                        </span>
                                    </label>
                                </div>
                            </fieldset>

                            <label
                                class="flex cursor-pointer items-center gap-2 self-end py-2 text-sm font-medium text-stone-700 dark:text-stone-200"
                            >
                                <input
                                    v-model="captionHasShadow"
                                    type="checkbox"
                                    class="size-4 accent-red-700 dark:accent-red-500"
                                />
                                Shadow
                            </label>
                        </div>
                    </div>
                </div>

                <dl class="mt-6 grid gap-5 sm:grid-cols-3">
                    <div class="flex flex-col gap-1">
                        <dt
                            class="text-sm font-medium text-stone-500 dark:text-stone-400"
                        >
                            File type
                        </dt>
                        <dd>{{ videoProject.mime_type }}</dd>
                    </div>

                    <div class="flex flex-col gap-1">
                        <dt
                            class="text-sm font-medium text-stone-500 dark:text-stone-400"
                        >
                            File size
                        </dt>
                        <dd>{{ formattedSize }}</dd>
                    </div>

                    <div class="flex flex-col gap-1">
                        <dt
                            class="text-sm font-medium text-stone-500 dark:text-stone-400"
                        >
                            Duration
                        </dt>
                        <dd>{{ formattedDuration }}</dd>
                    </div>
                </dl>
            </section>

            <section
                class="mt-6 rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-stone-800 dark:bg-stone-900"
                aria-labelledby="generated-captions"
            >
                <div class="p-6">
                    <h2 id="generated-captions" class="text-xl font-semibold">
                        Generated captions
                    </h2>

                    <p
                        v-if="cues === null"
                        class="mt-3 text-sm text-stone-600 dark:text-stone-300"
                    >
                        No transcription yet.
                    </p>

                    <p
                        v-else-if="cues.length === 0"
                        class="mt-3 text-sm text-stone-600 dark:text-stone-300"
                    >
                        No caption cues were generated.
                    </p>
                </div>

                <div
                    v-if="cues !== null && cues.length > 0"
                    class="overflow-x-auto"
                >
                    <table class="min-w-full border-collapse text-left text-sm">
                        <thead
                            class="border-y border-stone-200 bg-stone-50 text-xs tracking-wide text-stone-500 uppercase dark:border-stone-800 dark:bg-stone-950 dark:text-stone-400"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3 font-semibold">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-3 font-semibold">
                                    Caption
                                </th>
                                <th scope="col" class="px-6 py-3 font-semibold">
                                    Timing
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-right font-semibold"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-stone-200 dark:divide-stone-800"
                        >
                            <tr
                                v-for="(cue, cueIndex) in cues"
                                :key="cue.id ?? cue.order"
                            >
                                <td
                                    class="px-6 py-4 font-medium whitespace-nowrap text-stone-500 dark:text-stone-400"
                                >
                                    <button
                                        type="button"
                                        :aria-label="`Seek video to caption cue ${cue.order}`"
                                        class="rounded-md px-2 py-1 font-semibold text-red-700 hover:bg-red-50 hover:text-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:text-red-400 dark:hover:bg-red-950 dark:hover:text-red-300 dark:focus-visible:ring-red-400"
                                        @click="seekToCue(cue)"
                                    >
                                        #{{ cue.order }}
                                    </button>
                                </td>
                                <td
                                    class="min-w-72 px-6 py-4 align-top leading-6"
                                >
                                    <Form
                                        v-if="cue.id !== null"
                                        v-bind="
                                            updateCaptionCue.form({
                                                videoProject: videoProject.id,
                                                captionCue: cue.id,
                                            })
                                        "
                                        :error-bag="`caption-cue-${cue.id}`"
                                        :options="{ preserveScroll: true }"
                                        set-defaults-on-success
                                        class="grid gap-2"
                                        #default="{
                                            errors,
                                            processing,
                                            recentlySuccessful,
                                        }"
                                    >
                                        <label
                                            :for="`caption-cue-${cue.id}-text`"
                                            class="sr-only"
                                        >
                                            Caption cue {{ cue.order }} text
                                        </label>
                                        <textarea
                                            :id="`caption-cue-${cue.id}-text`"
                                            name="text"
                                            :value="cue.text"
                                            rows="2"
                                            maxlength="500"
                                            :disabled="processing"
                                            class="w-full resize-y rounded-lg border border-stone-300 bg-white px-3 py-2 leading-6 text-stone-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-wait disabled:opacity-60 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-50 dark:focus-visible:ring-red-400"
                                        />
                                        <div class="flex items-center gap-3">
                                            <button
                                                type="submit"
                                                :disabled="processing"
                                                class="rounded-md bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 dark:bg-red-600 dark:hover:bg-red-500 dark:focus-visible:ring-red-400 dark:focus-visible:ring-offset-stone-900"
                                            >
                                                {{
                                                    processing
                                                        ? 'Saving…'
                                                        : 'Save text'
                                                }}
                                            </button>
                                            <span
                                                v-if="recentlySuccessful"
                                                class="text-xs font-medium text-emerald-700 dark:text-emerald-400"
                                            >
                                                Saved
                                            </span>
                                        </div>
                                        <p
                                            v-if="errors.text"
                                            class="text-xs text-red-700 dark:text-red-400"
                                        >
                                            {{ errors.text }}
                                        </p>
                                    </Form>
                                    <template v-else>
                                        {{ cue.text }}
                                    </template>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="grid min-w-52 gap-3">
                                        <Form
                                            v-if="cue.id !== null"
                                            v-bind="
                                                updateCaptionCueStartTime.form({
                                                    videoProject:
                                                        videoProject.id,
                                                    captionCue: cue.id,
                                                })
                                            "
                                            :error-bag="`caption-cue-start-time-${cue.id}`"
                                            :options="{ preserveScroll: true }"
                                            set-defaults-on-success
                                            class="grid gap-1.5"
                                            #default="{
                                                errors,
                                                processing,
                                                recentlySuccessful,
                                            }"
                                        >
                                            <label
                                                :for="`caption-cue-${cue.id}-start-ms`"
                                                class="font-sans text-xs font-semibold text-stone-500 dark:text-stone-400"
                                            >
                                                Start
                                            </label>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <input
                                                    :id="`caption-cue-${cue.id}-start-ms`"
                                                    name="start_ms"
                                                    type="number"
                                                    :value="cue.start_ms"
                                                    :min="
                                                        getCueStartMinimum(
                                                            cueIndex,
                                                        )
                                                    "
                                                    :max="cue.end_ms - 1"
                                                    step="1"
                                                    inputmode="numeric"
                                                    :disabled="processing"
                                                    class="w-24 rounded-lg border border-stone-300 bg-white px-2 py-1.5 font-mono text-stone-950 tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-wait disabled:opacity-60 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-50 dark:focus-visible:ring-red-400"
                                                />
                                                <span
                                                    class="text-xs text-stone-500 dark:text-stone-400"
                                                >
                                                    ms
                                                </span>
                                                <button
                                                    type="submit"
                                                    :disabled="processing"
                                                    class="rounded-md border border-stone-300 px-2 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-wait disabled:opacity-60 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800 dark:focus-visible:ring-red-400"
                                                >
                                                    {{
                                                        processing
                                                            ? 'Saving…'
                                                            : 'Save'
                                                    }}
                                                </button>
                                            </div>
                                            <span
                                                v-if="recentlySuccessful"
                                                class="font-sans text-xs font-medium text-emerald-700 dark:text-emerald-400"
                                            >
                                                Saved
                                            </span>
                                            <p
                                                v-if="errors.start_ms"
                                                class="max-w-52 font-sans text-xs whitespace-normal text-red-700 dark:text-red-400"
                                            >
                                                {{ errors.start_ms }}
                                            </p>
                                        </Form>
                                        <template v-else>
                                            <span class="font-mono">
                                                Start:
                                                {{
                                                    formatCueTime(cue.start_ms)
                                                }}
                                            </span>
                                        </template>
                                        <Form
                                            v-if="cue.id !== null"
                                            v-bind="
                                                updateCaptionCueEndTime.form({
                                                    videoProject:
                                                        videoProject.id,
                                                    captionCue: cue.id,
                                                })
                                            "
                                            :error-bag="`caption-cue-end-time-${cue.id}`"
                                            :options="{ preserveScroll: true }"
                                            set-defaults-on-success
                                            class="grid gap-1.5"
                                            #default="{
                                                errors,
                                                processing,
                                                recentlySuccessful,
                                            }"
                                        >
                                            <label
                                                :for="`caption-cue-${cue.id}-end-ms`"
                                                class="font-sans text-xs font-semibold text-stone-500 dark:text-stone-400"
                                            >
                                                End
                                            </label>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <input
                                                    :id="`caption-cue-${cue.id}-end-ms`"
                                                    name="end_ms"
                                                    type="number"
                                                    :value="cue.end_ms"
                                                    :min="cue.start_ms + 1"
                                                    :max="
                                                        getCueEndMaximum(
                                                            cueIndex,
                                                        )
                                                    "
                                                    step="1"
                                                    inputmode="numeric"
                                                    :disabled="processing"
                                                    class="w-24 rounded-lg border border-stone-300 bg-white px-2 py-1.5 font-mono text-stone-950 tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-wait disabled:opacity-60 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-50 dark:focus-visible:ring-red-400"
                                                />
                                                <span
                                                    class="text-xs text-stone-500 dark:text-stone-400"
                                                >
                                                    ms
                                                </span>
                                                <button
                                                    type="submit"
                                                    :disabled="processing"
                                                    class="rounded-md border border-stone-300 px-2 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-wait disabled:opacity-60 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800 dark:focus-visible:ring-red-400"
                                                >
                                                    {{
                                                        processing
                                                            ? 'Saving…'
                                                            : 'Save'
                                                    }}
                                                </button>
                                            </div>
                                            <span
                                                v-if="recentlySuccessful"
                                                class="font-sans text-xs font-medium text-emerald-700 dark:text-emerald-400"
                                            >
                                                Saved
                                            </span>
                                            <p
                                                v-if="errors.end_ms"
                                                class="max-w-52 font-sans text-xs whitespace-normal text-red-700 dark:text-red-400"
                                            >
                                                {{ errors.end_ms }}
                                            </p>
                                        </Form>
                                        <template v-else>
                                            <span class="font-mono">
                                                End:
                                                {{ formatCueTime(cue.end_ms) }}
                                            </span>
                                        </template>
                                    </div>
                                </td>
                                <td class="w-20 px-6 py-4 text-right align-top">
                                    <details
                                        class="group relative inline-block w-9 text-left"
                                    >
                                        <summary
                                            class="inline-flex size-9 cursor-pointer list-none items-center justify-center rounded-lg border border-stone-300 text-xl leading-none text-stone-600 hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:border-stone-700 dark:text-stone-300 dark:hover:bg-stone-800 dark:focus-visible:ring-red-400 [&::-webkit-details-marker]:hidden"
                                            :aria-label="`Open actions for caption cue ${cue.order}`"
                                            title="Caption actions"
                                        >
                                            ⋮
                                        </summary>
                                        <div
                                            class="absolute top-full right-0 z-20 mt-2 grid w-48 gap-2 rounded-lg border border-stone-200 bg-white p-2 shadow-lg dark:border-stone-700 dark:bg-stone-950"
                                        >
                                            <Form
                                                v-if="cue.id !== null"
                                                v-bind="
                                                    splitCaptionCue.form({
                                                        videoProject:
                                                            videoProject.id,
                                                        captionCue: cue.id,
                                                    })
                                                "
                                                :error-bag="`caption-cue-split-${cue.id}`"
                                                :options="{
                                                    preserveScroll: true,
                                                }"
                                                class="grid gap-2"
                                                #default="{
                                                    errors,
                                                    processing,
                                                }"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="split_ms"
                                                    :value="
                                                        currentTimeMilliseconds
                                                    "
                                                />
                                                <span
                                                    class="block"
                                                    :title="
                                                        getSplitUnavailableReason(
                                                            cue,
                                                        ) ??
                                                        `Split at ${currentTimeMilliseconds} ms`
                                                    "
                                                >
                                                    <button
                                                        type="submit"
                                                        :aria-label="
                                                            getSplitUnavailableReason(
                                                                cue,
                                                            ) ??
                                                            `Split cue at ${currentTimeMilliseconds} milliseconds`
                                                        "
                                                        :disabled="
                                                            processing ||
                                                            !canSplitCueAtPlayhead(
                                                                cue,
                                                            )
                                                        "
                                                        class="w-full rounded-md px-3 py-2 text-left text-xs font-semibold whitespace-nowrap text-stone-700 hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-stone-200 dark:hover:bg-stone-800 dark:focus-visible:ring-red-400"
                                                    >
                                                        {{
                                                            processing
                                                                ? 'Splitting…'
                                                                : 'Split at playhead'
                                                        }}
                                                    </button>
                                                </span>
                                                <p
                                                    v-if="errors.split_ms"
                                                    class="max-w-52 text-xs whitespace-normal text-red-700 dark:text-red-400"
                                                >
                                                    {{ errors.split_ms }}
                                                </p>
                                            </Form>
                                            <Form
                                                v-if="cue.id !== null"
                                                v-bind="
                                                    mergeCaptionCueWithNext.form(
                                                        {
                                                            videoProject:
                                                                videoProject.id,
                                                            captionCue: cue.id,
                                                        },
                                                    )
                                                "
                                                :error-bag="`caption-cue-merge-${cue.id}`"
                                                :options="{
                                                    preserveScroll: true,
                                                }"
                                                class="grid gap-2"
                                                #default="{
                                                    errors,
                                                    processing,
                                                }"
                                            >
                                                <button
                                                    type="submit"
                                                    :disabled="
                                                        processing ||
                                                        cueIndex ===
                                                            cues.length - 1
                                                    "
                                                    class="rounded-md px-3 py-2 text-left text-xs font-semibold whitespace-nowrap text-stone-700 hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-stone-200 dark:hover:bg-stone-800 dark:focus-visible:ring-red-400"
                                                >
                                                    {{
                                                        processing
                                                            ? 'Merging…'
                                                            : 'Merge with next'
                                                    }}
                                                </button>
                                                <p
                                                    v-if="errors.caption_cue"
                                                    class="max-w-52 text-xs whitespace-normal text-red-700 dark:text-red-400"
                                                >
                                                    {{ errors.caption_cue }}
                                                </p>
                                            </Form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <Link
                :href="home()"
                class="mt-6 inline-flex rounded-lg px-1 py-2 text-sm font-semibold text-red-700 underline-offset-4 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:text-red-400 dark:focus-visible:ring-red-400"
            >
                Upload another video
            </Link>
        </main>
    </div>
</template>
