<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 108 108"
        :class="className"
        v-bind="$attrs"
        role="img"
        aria-label="SmartGuard"
    >
        <path
            fill="#102A18"
            d="M20 14h68a10 10 0 0 1 10 10v60a10 10 0 0 1-10 10H20a10 10 0 0 1-10-10V24a10 10 0 0 1 10-10Z"
        />
        <path
            fill="#00C853"
            d="m54 21 26 10v19c0 18-10.9 31.8-26 38-15.1-6.2-26-20-26-38V31l26-10Z"
        />
        <path
            fill="#07120B"
            d="m54 29 18 7v14c0 12.4-7.1 22.5-18 27.7C43.1 72.5 36 62.4 36 50V36l18-7Z"
        />
        <path
            fill="#00C853"
            fill-rule="evenodd"
            d="M54 38a11 11 0 1 0 .1 0H54Zm0 6a5 5 0 1 1-.1 0h.1Z"
            clip-rule="evenodd"
        />
        <path fill="#00C853" d="M51 56h6v14h-6z" />
        <path fill="#A7FFBC" d="M45 81h18v5H45z" />
        <path
            fill="#A7FFBC"
            d="m62 42-7 11h8l-15 21 4-15h-7l6-17h11Z"
            opacity=".95"
        />
    </svg>
</template>
