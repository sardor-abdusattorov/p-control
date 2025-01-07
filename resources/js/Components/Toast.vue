<template>
    <transition name="slide-fade">
        <div v-if="flash.success && isVisible" class="absolute top-4 right-4 w-8/12 md:w-6/12 lg:w-3/12 z-[100]">
            <div class="flex p-4 justify-between items-center bg-green-600 rounded-lg">
                <div>
                    <CheckCircleIcon class="h-8 w-8 text-white" fill="currentColor" />
                </div>
                <div class="mx-3 text-sm font-medium text-white" v-html="flash.success"></div>
                <button @click="closeToast" type="button" class="ml-auto bg-white/20 text-white rounded-lg focus:ring-2 focus:ring-white/50 p-1.5 hover:bg-white/30 h-8 w-8">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </div>
    </transition>

    <transition name="slide-fade">
        <div v-if="flash.info && isVisible" class="absolute top-4 right-4 w-8/12 md:w-6/12 lg:w-3/12 z-[100]">
            <div class="flex p-4 justify-between items-center bg-primary rounded-lg">
                <div>
                    <InformationCircleIcon class="h-8 w-8 text-white" fill="currentColor" />
                </div>
                <div class="mx-3 text-sm font-medium text-white" v-html="flash.info"></div>
                <button @click="closeToast" type="button" class="ml-auto bg-white/20 text-white rounded-lg focus:ring-2 focus:ring-white/50 p-1.5 hover:bg-white/30 h-8 w-8">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </div>
    </transition>

    <transition name="slide-fade">
        <div v-if="flash.warning && isVisible" class="absolute top-4 right-4 w-8/12 md:w-6/12 lg:w-3/12 z-[100]">
            <div class="flex p-4 justify-between items-center bg-amber-600 rounded-lg">
                <div>
                    <ExclamationTriangleIcon class="h-8 w-8 text-white" fill="currentColor" />
                </div>
                <div class="mx-3 text-sm font-medium text-white" v-html="flash.warning"></div>
                <button @click="closeToast" type="button" class="ml-auto bg-white/20 text-white rounded-lg focus:ring-2 focus:ring-white/50 p-1.5 hover:bg-white/30 h-8 w-8">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </div>
    </transition>

    <transition name="slide-fade">
        <div v-if="flash.error && isVisible" class="absolute top-4 right-4 w-8/12 md:w-6/12 lg:w-3/12 z-[100]">
            <div class="flex p-4 justify-between items-center bg-red-600 rounded-lg">
                <div>
                    <ExclamationCircleIcon class="h-8 w-8 text-white" fill="currentColor" />
                </div>
                <div class="mx-3 text-sm font-medium text-white" v-html="flash.error"></div>
                <button @click="closeToast" type="button" class="ml-auto bg-white/20 text-white rounded-lg focus:ring-2 focus:ring-white/50 p-1.5 hover:bg-white/30 h-8 w-8">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </div>
    </transition>
</template>

<script>
import { XMarkIcon, CheckCircleIcon, ExclamationCircleIcon, InformationCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid';

export default {
    components: {
        XMarkIcon,
        CheckCircleIcon,
        ExclamationCircleIcon,
        InformationCircleIcon,
        ExclamationTriangleIcon,
    },
    props: {
        flash: Object,
    },
    data() {
        return {
            isVisible: false,
            timeout: null,
        };
    },
    methods: {
        showToast() {
            this.isVisible = true;
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
            this.timeout = setTimeout(() => {
                this.isVisible = false;
            }, 3000);
        },
        closeToast() {
            this.isVisible = false;
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
        },
    },
    watch: {
        flash: {
            deep: true,
            immediate: true,
            handler(newVal) {
                if (newVal && (newVal.success || newVal.info || newVal.warning || newVal.error)) {
                    this.showToast();
                }
            },
        },
    },
}
</script>

<style>
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
    transition: all 0.8s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(10px);
    opacity: 0;
}
</style>
