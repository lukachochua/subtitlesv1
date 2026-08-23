<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { CaptionCue } from '@/lib/caption-cues';
import { findActiveCaptionCue } from '@/lib/caption-cues';
import { home } from '@/routes';
import { media as videoProjectMedia } from '@/routes/video-projects';
import { update as updateCaptionCue } from '@/routes/video-projects/caption-cues';
import { update as updateCaptionCueEndTime } from '@/routes/video-projects/caption-cues/end-time';
import { store as splitCaptionCue } from '@/routes/video-projects/caption-cues/split';
import { update as updateCaptionCueStartTime } from '@/routes/video-projects/caption-cues/start-time';

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
}>();

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

const canSplitCueAtPlayhead = (cue: CaptionCue): boolean =>
    currentTimeMilliseconds.value > cue.start_ms &&
    currentTimeMilliseconds.value < cue.end_ms &&
    cue.text.trim().split(/\s+/u).length >= 2;

const videoElement = ref<HTMLVideoElement | null>(null);
const currentTimeSeconds = ref(0);
const currentTimeMilliseconds = computed(() =>
    Math.round(currentTimeSeconds.value * 1_000),
);
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
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-stone-50 px-6 py-16 text-stone-950 dark:bg-stone-950 dark:text-stone-50"
    >
        <Head :title="videoProject.original_filename" />

        <main class="w-full max-w-2xl">
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
                        class="pointer-events-none absolute inset-x-4 bottom-14 flex justify-center"
                    >
                        <p
                            class="max-w-[90%] rounded-md bg-black/75 px-3 py-1.5 text-center text-base leading-snug font-semibold wrap-break-word whitespace-pre-wrap text-white [text-shadow:0_1px_2px_rgb(0_0_0_/_0.9)] sm:text-xl"
                        >
                            {{ activeCue.text }}
                        </p>
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
                                    Cue
                                </th>
                                <th scope="col" class="px-6 py-3 font-semibold">
                                    Caption
                                </th>
                                <th scope="col" class="px-6 py-3 font-semibold">
                                    Start
                                </th>
                                <th scope="col" class="px-6 py-3 font-semibold">
                                    End
                                </th>
                                <th scope="col" class="px-6 py-3 font-semibold">
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
                                <td class="min-w-80 px-6 py-4 leading-6">
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
                                <td
                                    class="px-6 py-4 font-mono whitespace-nowrap"
                                >
                                    <Form
                                        v-if="cue.id !== null"
                                        v-bind="
                                            updateCaptionCueStartTime.form({
                                                videoProject: videoProject.id,
                                                captionCue: cue.id,
                                            })
                                        "
                                        :error-bag="`caption-cue-start-time-${cue.id}`"
                                        :options="{ preserveScroll: true }"
                                        set-defaults-on-success
                                        class="grid min-w-44 gap-2"
                                        #default="{
                                            errors,
                                            processing,
                                            recentlySuccessful,
                                        }"
                                    >
                                        <label
                                            :for="`caption-cue-${cue.id}-start-ms`"
                                            class="sr-only"
                                        >
                                            Caption cue {{ cue.order }} start
                                            time in milliseconds
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input
                                                :id="`caption-cue-${cue.id}-start-ms`"
                                                name="start_ms"
                                                type="number"
                                                :value="cue.start_ms"
                                                :min="
                                                    getCueStartMinimum(cueIndex)
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
                                        {{ formatCueTime(cue.start_ms) }}
                                    </template>
                                </td>
                                <td
                                    class="px-6 py-4 font-mono whitespace-nowrap"
                                >
                                    <Form
                                        v-if="cue.id !== null"
                                        v-bind="
                                            updateCaptionCueEndTime.form({
                                                videoProject: videoProject.id,
                                                captionCue: cue.id,
                                            })
                                        "
                                        :error-bag="`caption-cue-end-time-${cue.id}`"
                                        :options="{ preserveScroll: true }"
                                        set-defaults-on-success
                                        class="grid min-w-44 gap-2"
                                        #default="{
                                            errors,
                                            processing,
                                            recentlySuccessful,
                                        }"
                                    >
                                        <label
                                            :for="`caption-cue-${cue.id}-end-ms`"
                                            class="sr-only"
                                        >
                                            Caption cue {{ cue.order }} end time
                                            in milliseconds
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input
                                                :id="`caption-cue-${cue.id}-end-ms`"
                                                name="end_ms"
                                                type="number"
                                                :value="cue.end_ms"
                                                :min="cue.start_ms + 1"
                                                :max="
                                                    getCueEndMaximum(cueIndex)
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
                                        {{ formatCueTime(cue.end_ms) }}
                                    </template>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <Form
                                        v-if="cue.id !== null"
                                        v-bind="
                                            splitCaptionCue.form({
                                                videoProject: videoProject.id,
                                                captionCue: cue.id,
                                            })
                                        "
                                        :error-bag="`caption-cue-split-${cue.id}`"
                                        :options="{ preserveScroll: true }"
                                        class="grid min-w-44 gap-2"
                                        #default="{ errors, processing }"
                                    >
                                        <input
                                            type="hidden"
                                            name="split_ms"
                                            :value="currentTimeMilliseconds"
                                        />
                                        <button
                                            type="submit"
                                            :disabled="
                                                processing ||
                                                !canSplitCueAtPlayhead(cue)
                                            "
                                            class="rounded-md border border-stone-300 px-3 py-1.5 text-xs font-semibold whitespace-nowrap text-stone-700 hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800 dark:focus-visible:ring-red-400"
                                        >
                                            {{
                                                processing
                                                    ? 'Splitting…'
                                                    : 'Split at playhead'
                                            }}
                                        </button>
                                        <p
                                            class="text-xs text-stone-500 dark:text-stone-400"
                                        >
                                            {{ currentTimeMilliseconds }} ms
                                        </p>
                                        <p
                                            v-if="errors.split_ms"
                                            class="max-w-52 text-xs whitespace-normal text-red-700 dark:text-red-400"
                                        >
                                            {{ errors.split_ms }}
                                        </p>
                                    </Form>
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
