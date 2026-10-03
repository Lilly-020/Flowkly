<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import { store } from '@/routes/requests';

const props = defineProps<{
    types: { value: string; label: string }[];
    tiUsers: { id: number; name: string }[];
}>();

const form = useForm({
    title: '',
    description: '',
    type: '',
    assigned_to: '',
    images: [] as File[],
});

const previews = computed(() =>
    form.images.map((file) => ({
        name: file.name,
        url: URL.createObjectURL(file),
    })),
);

const fileInput = ref<HTMLInputElement | null>(null);

const onFilesSelected = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const files = input.files ? Array.from(input.files) : [];

    form.images = [...form.images, ...files].slice(0, 5);
    input.value = '';
};

const removeImage = (index: number) => {
    form.images = form.images.filter((_, i) => i !== index);
};

const submit = () => {
    form.post(store().url, {
        forceFormData: true,
    });
};
</script>

<template>
    <LateralBar>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Nova solicitação
        </h1>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Descreva o que está acontecendo e para quem devemos encaminhar.
        </p>

        <form
            class="mt-6 max-w-2xl space-y-5 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            @submit.prevent="submit"
        >
            <div>
                <label
                    for="title"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Título
                </label>

                <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    required
                    placeholder="Ex: Computador não liga"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />

                <p
                    v-if="form.errors.title"
                    class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                >
                    {{ form.errors.title }}
                </p>
            </div>

            <div>
                <label
                    for="type"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Tipo
                </label>

                <select
                    id="type"
                    v-model="form.type"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="" disabled>Selecione o tipo</option>
                    <option
                        v-for="type in props.types"
                        :key="type.value"
                        :value="type.value"
                    >
                        {{ type.label }}
                    </option>
                </select>

                <p
                    v-if="form.errors.type"
                    class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                >
                    {{ form.errors.type }}
                </p>
            </div>

            <div>
                <label
                    for="assigned_to"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Encaminhar para (TI)
                </label>

                <select
                    id="assigned_to"
                    v-model="form.assigned_to"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="" disabled>Selecione uma pessoa</option>
                    <option
                        v-for="person in props.tiUsers"
                        :key="person.id"
                        :value="person.id"
                    >
                        {{ person.name }}
                    </option>
                </select>

                <p
                    v-if="form.errors.assigned_to"
                    class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                >
                    {{ form.errors.assigned_to }}
                </p>
            </div>

            <div>
                <label
                    for="description"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Descrição
                </label>

                <textarea
                    id="description"
                    v-model="form.description"
                    rows="5"
                    required
                    placeholder="Explique com detalhes o que está acontecendo"
                    class="w-full resize-none rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />

                <p
                    v-if="form.errors.description"
                    class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                >
                    {{ form.errors.description }}
                </p>
            </div>

            <div>
                <span
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Imagens (opcional, até 5)
                </span>

                <div class="flex flex-wrap gap-3">
                    <div
                        v-for="(preview, index) in previews"
                        :key="preview.url"
                        class="group relative h-20 w-20 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
                    >
                        <img
                            :src="preview.url"
                            :alt="preview.name"
                            class="h-full w-full object-cover"
                        />

                        <button
                            type="button"
                            class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100"
                            aria-label="Remover imagem"
                            @click="removeImage(index)"
                        >
                            <svg
                                class="h-3 w-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <button
                        v-if="form.images.length < 5"
                        type="button"
                        class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-gray-300 text-gray-400 hover:border-indigo-400 hover:text-indigo-500 dark:border-gray-700"
                        @click="fileInput?.click()"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 5v14M5 12h14"
                            />
                        </svg>
                        <span class="text-xs">Adicionar</span>
                    </button>
                </div>

                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    multiple
                    class="hidden"
                    @change="onFilesSelected"
                />

                <p
                    v-if="form.errors.images"
                    class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                >
                    {{ form.errors.images }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
            >
                {{ form.processing ? 'Enviando...' : 'Criar solicitação' }}
            </button>
        </form>
    </LateralBar>
</template>
