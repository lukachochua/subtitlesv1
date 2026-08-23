<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { home } from '@/routes';
import { media as videoProjectMedia } from '@/routes/video-projects';

interface VideoProject {
    id: number;
    original_filename: string;
    mime_type: string;
    size_bytes: number;
    duration_ms: number | null;
}

interface CaptionCue {
    order: number;
    text: string;
    start_ms: number;
    end_ms: number;
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

                <video
                    controls
                    preload="metadata"
                    :src="videoProjectMedia.url(videoProject.id)"
                    class="mt-6 aspect-video w-full rounded-xl bg-black"
                >
                    Your browser does not support HTML video playback.
                </video>

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
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-stone-200 dark:divide-stone-800"
                        >
                            <tr v-for="cue in cues" :key="cue.order">
                                <td
                                    class="px-6 py-4 font-medium whitespace-nowrap text-stone-500 dark:text-stone-400"
                                >
                                    {{ cue.order }}
                                </td>
                                <td class="px-6 py-4 leading-6">
                                    {{ cue.text }}
                                </td>
                                <td
                                    class="px-6 py-4 font-mono whitespace-nowrap"
                                >
                                    {{ formatCueTime(cue.start_ms) }}
                                </td>
                                <td
                                    class="px-6 py-4 font-mono whitespace-nowrap"
                                >
                                    {{ formatCueTime(cue.end_ms) }}
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
