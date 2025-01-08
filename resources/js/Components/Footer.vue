<script setup></script>
<template>
    <footer
        class="sticky dark:bg-slate-900 top-[100vh] bg-white border-t border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-300"
    >
        <div
            class="flex items-center justify-center sm:justify-start mx-auto p-4 sm:px-6 lg:px-8"
        >
            <p class="text-center">
                <span class="font-bold">©️ 2017 - {{ new Date().getFullYear() }}</span>
                {{lang().label.company_name}}
            </p>
        </div>
    </footer>
</template>
