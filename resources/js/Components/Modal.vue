<script setup lang="ts">
withDefaults(
    defineProps<{
        open: boolean;
        maxWidth?: string;
    }>(),
    {
        maxWidth: 'max-w-md',
    },
);

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-150"
            leave-active-class="transition-opacity duration-100"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm"
                @click.self="emit('close')"
            >
                <Transition
                    appear
                    enter-active-class="transition duration-150"
                    enter-from-class="scale-95 opacity-0"
                    leave-active-class="transition duration-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div class="w-full" :class="maxWidth">
                        <slot />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
