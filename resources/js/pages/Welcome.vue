<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-stone-50 px-6 py-16 text-stone-950 dark:bg-stone-950 dark:text-stone-50"
    >
        <Head title="Georgian Captioner" />

        <main class="w-full max-w-2xl">
            <p
                class="text-sm font-semibold tracking-widest text-red-700 uppercase dark:text-red-400"
            >
                ქართული ვიდეოს სუბტიტრები
            </p>

            <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">
                Georgian Captioner
            </h1>

            <p
                class="mt-6 max-w-xl text-lg leading-8 text-stone-600 dark:text-stone-300"
            >
                Turn Georgian speech into accurately timed captions, correct
                them directly against the video, and export a finished captioned
                MP4.
            </p>

            <section
                class="mt-10 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-800 dark:bg-stone-900"
                aria-labelledby="current-stage"
            >
                <h2
                    id="current-stage"
                    class="text-sm font-medium text-stone-500 dark:text-stone-400"
                >
                    Current stage
                </h2>

                <p class="mt-2 text-lg font-medium">
                    The application foundation is ready.
                </p>

                <p class="mt-2 leading-7 text-stone-600 dark:text-stone-300">
                    The next milestone is representing one video project before
                    adding upload behavior.
                </p>
            </section>
        </main>
    </div>
</template>
