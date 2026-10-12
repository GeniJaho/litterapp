<script setup>
import { computed, ref } from "vue";
import { Deferred, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Tooltip from "@/Components/Tooltip.vue";
import { Line, Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip as ChartTooltip,
    Legend,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Filler,
} from "chart.js";

ChartJS.register(
    Title,
    ChartTooltip,
    Legend,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Filler,
);

const props = defineProps({
    period: {
        type: String,
        required: true,
    },
    availablePeriods: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        default: null,
    },
});

const loading = ref(false);

const selectPeriod = (key) => {
    if (key === props.period || loading.value || props.stats === null) {
        return;
    }

    router.reload({
        only: ["period", "stats"],
        data: { period: key },
        preserveScroll: true,
        preserveState: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    });
};

const refreshStats = () => {
    if (loading.value || props.stats === null) {
        return;
    }

    router.post(
        route("dashboard.refresh"),
        { period: props.period },
        {
            preserveScroll: true,
            onStart: () => (loading.value = true),
            onFinish: () => (loading.value = false),
        },
    );
};

const formatNumber = (value) => Number(value ?? 0).toLocaleString();

const periodLabel = computed(
    () =>
        props.availablePeriods.find((period) => period.key === props.period)
            ?.label ?? "",
);

const metrics = computed(() => [
    { label: "Photos", value: props.stats.totals.photos, hint: "uploaded" },
    { label: "Items", value: props.stats.totals.items, hint: "total quantity" },
    { label: "Tags", value: props.stats.totals.tags, hint: "applied to items" },
]);

const tickColor = "#94A3B8";
const gridColor = "rgba(148, 163, 184, 0.15)";
const colors = {
    photos: "#06B6D4",
    items: "#6366F1",
    disposal: ["#06B6D4", "#10B981", "#F59E0B"],
};

const activityData = computed(() => ({
    labels: props.stats.charts.activity.labels,
    datasets: [
        {
            label: "Photos",
            data: props.stats.charts.activity.photos,
            borderColor: colors.photos,
            backgroundColor: "rgba(6, 182, 212, 0.15)",
            tension: 0.35,
            fill: true,
            pointRadius: 2,
        },
        {
            label: "Items",
            data: props.stats.charts.activity.items,
            borderColor: colors.items,
            backgroundColor: "rgba(99, 102, 241, 0.12)",
            tension: 0.35,
            fill: true,
            pointRadius: 2,
        },
    ],
}));

const activityOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: "index", intersect: false },
    plugins: {
        legend: { labels: { color: tickColor, usePointStyle: true } },
    },
    scales: {
        x: { ticks: { color: tickColor, maxRotation: 0, autoSkip: true }, grid: { color: gridColor } },
        y: { beginAtZero: true, ticks: { color: tickColor, precision: 0 }, grid: { color: gridColor } },
    },
};

const topItemsData = computed(() => ({
    labels: props.stats.charts.top_items.labels,
    datasets: [
        {
            label: "Items",
            data: props.stats.charts.top_items.quantities,
            backgroundColor: colors.photos,
            borderRadius: 4,
            maxBarThickness: 28,
        },
    ],
}));

const topItemsOptions = {
    indexAxis: "y",
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { beginAtZero: true, ticks: { color: tickColor, precision: 0 }, grid: { color: gridColor } },
        y: { ticks: { color: tickColor }, grid: { display: false } },
    },
};

const disposalData = computed(() => ({
    labels: ["Picked up", "Recycled", "Deposit"],
    datasets: [
        {
            label: "Items",
            data: [
                props.stats.charts.disposal.picked_up,
                props.stats.charts.disposal.recycled,
                props.stats.charts.disposal.deposit,
            ],
            backgroundColor: colors.disposal,
            borderRadius: 4,
            maxBarThickness: 56,
        },
    ],
}));

const disposalOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { ticks: { color: tickColor }, grid: { display: false } },
        y: { beginAtZero: true, ticks: { color: tickColor, precision: 0 }, grid: { color: gridColor } },
    },
};

const hasActivity = computed(
    () =>
        props.stats.charts.activity.labels.length > 0 &&
        [
            ...props.stats.charts.activity.photos,
            ...props.stats.charts.activity.items,
        ].some((value) => value > 0),
);

const hasTopItems = computed(() => props.stats.charts.top_items.labels.length > 0);

const hasDisposal = computed(() => {
    const disposal = props.stats.charts.disposal;

    return disposal.picked_up + disposal.recycled + disposal.deposit > 0;
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="flex flex-col gap-4 px-4 sm:flex-row sm:items-center sm:justify-between sm:px-0">
                    <p class="text-gray-600 dark:text-gray-300">
                        Your litter-picking activity at a glance.
                    </p>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            :disabled="loading || stats === null"
                            class="group relative flex h-8 w-8 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            @click="refreshStats"
                        >
                            <Tooltip>
                                <span class="block w-48 text-center text-xs text-white">
                                    These figures refresh on their own every hour. Click to update them now.
                                </span>
                            </Tooltip>
                            <i class="fas fa-arrows-rotate"></i>
                            <span class="sr-only">Refresh stats</span>
                        </button>

                        <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-700">
                            <button
                                v-for="option in availablePeriods"
                                :key="option.key"
                                type="button"
                                :disabled="loading || stats === null"
                                class="rounded-md px-3 py-1.5 text-sm font-semibold transition focus:outline-none disabled:cursor-not-allowed"
                                :class="option.key === period
                                    ? 'bg-darkBlue text-white shadow dark:bg-mainWhite dark:text-darkBlue'
                                    : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'"
                                @click="selectPeriod(option.key)"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <Deferred data="stats">
                    <template #fallback>
                        <div class="space-y-6 animate-pulse">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div
                                    v-for="placeholder in 3"
                                    :key="placeholder"
                                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                                >
                                    <div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-700" />
                                    <div class="mt-3 h-9 w-32 rounded bg-gray-200 dark:bg-gray-700" />
                                    <div class="mt-2 h-3 w-28 rounded bg-gray-200 dark:bg-gray-700" />
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                                <div class="h-4 w-44 rounded bg-gray-200 dark:bg-gray-700" />
                                <div class="mt-3 h-9 w-24 rounded bg-gray-200 dark:bg-gray-700" />
                            </div>

                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                                <div class="h-4 w-36 rounded bg-gray-200 dark:bg-gray-700" />
                                <div class="mt-4 h-72 rounded bg-gray-100 dark:bg-gray-700/50" />
                            </div>

                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div
                                    v-for="placeholder in 2"
                                    :key="placeholder"
                                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                                >
                                    <div class="h-4 w-36 rounded bg-gray-200 dark:bg-gray-700" />
                                    <div class="mt-4 h-72 rounded bg-gray-100 dark:bg-gray-700/50" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <div
                        class="space-y-6 transition-opacity duration-200"
                        :class="{ 'pointer-events-none opacity-50': loading }"
                    >
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div
                                v-for="metric in metrics"
                                :key="metric.label"
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                            >
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ metric.label }}
                                </p>
                                <p class="mt-2 text-4xl font-bold text-darkBlue dark:text-mainWhite">
                                    {{ formatNumber(metric.value) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ periodLabel }} · {{ metric.hint }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        Photos still needing tags
                                    </p>
                                    <p class="mt-2 text-4xl font-bold text-darkBlue dark:text-mainWhite">
                                        {{ formatNumber(stats.needs_tagging) }}
                                    </p>
                                </div>

                                <Link
                                    v-if="stats.needs_tagging > 0"
                                    :href="route('my-photos')"
                                    class="inline-flex items-center justify-center rounded-md bg-darkBlue px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-mainWhite dark:text-darkBlue dark:hover:bg-white"
                                >
                                    Tag them
                                </Link>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                                Activity over time
                            </h3>
                            <div class="mt-4 h-72">
                                <Line v-if="hasActivity" :data="activityData" :options="activityOptions" />
                                <p
                                    v-else
                                    class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500"
                                >
                                    No activity for this period yet.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                                    Top item types
                                </h3>
                                <div class="mt-4 h-72">
                                    <Bar v-if="hasTopItems" :data="topItemsData" :options="topItemsOptions" />
                                    <p
                                        v-else
                                        class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500"
                                    >
                                        No items tagged in this period yet.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                                    Disposal
                                </h3>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    Item quantities per flag — one item can count in more than one bar.
                                </p>
                                <div class="mt-4 h-72">
                                    <Bar v-if="hasDisposal" :data="disposalData" :options="disposalOptions" />
                                    <p
                                        v-else
                                        class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500"
                                    >
                                        No disposal data for this period yet.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Deferred>
            </div>
        </div>
    </AppLayout>
</template>
