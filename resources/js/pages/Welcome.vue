<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { store as storeVideoProject } from '@/routes/video-projects';
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
                aria-labelledby="upload-video"
            >
                <h2
                    id="upload-video"
                    class="text-xl font-semibold tracking-tight"
                >
                    Choose a video
                </h2>

                <p class="mt-2 leading-7 text-stone-600 dark:text-stone-300">
                    Select one Georgian-language MP4 from your computer. File
                    validation allows up to 500 MB. The source video will be
                    stored privately using a generated filename.
                </p>

                <Form
                    v-bind="storeVideoProject.form()"
                    reset-on-success
                    class="mt-6 flex flex-col gap-4"
                    #default="{ errors, processing, wasSuccessful }"
                >
                    <div class="flex flex-col gap-2">
                        <label for="video" class="text-sm font-medium">
                            MP4 video
                        </label>

                        <input
                            id="video"
                            name="video"
                            type="file"
                            accept="video/mp4,.mp4"
                            :disabled="processing"
                            class="block w-full rounded-lg border border-stone-300 bg-stone-50 text-sm text-stone-600 file:mr-4 file:border-0 file:border-r file:border-stone-300 file:bg-stone-100 file:px-4 file:py-3 file:font-medium file:text-stone-900 hover:file:bg-stone-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-300 dark:file:border-stone-700 dark:file:bg-stone-800 dark:file:text-stone-100 dark:hover:file:bg-stone-700 dark:focus-visible:ring-red-400 dark:focus-visible:ring-offset-stone-900"
                        />

                        <p
                            v-if="errors.video"
                            class="text-sm text-red-700 dark:text-red-400"
                        >
                            {{ errors.video }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full rounded-lg bg-red-700 px-4 py-3 text-sm font-semibold text-white hover:bg-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 sm:w-auto sm:self-start dark:bg-red-600 dark:hover:bg-red-500 dark:focus-visible:ring-red-400 dark:focus-visible:ring-offset-stone-900"
                    >
                        {{ processing ? 'Storing video…' : 'Store video' }}
                    </button>

                    <p
                        v-if="wasSuccessful"
                        class="text-sm font-medium text-emerald-700 dark:text-emerald-400"
                    >
                        Video stored on the private local disk.
                    </p>
                </Form>
            </section>
        </main>
    </div>
</template>
