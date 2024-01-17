<template>
    <div class="tooltip absolute -top-2 left-1/2 transform -translate-x-1/2 -translate-y-full hidden group-hover:block max-w-sm">
        <div class="tooltip-text rounded-lg shadow-lg overflow-hidden bg-black/75 px-2 py-1.5">
            <slot></slot>
        </div>
    </div>
</template>

<style scoped>
.tooltip .tooltip-text::after {
    content: " ";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: rgba(0,0,0,0.75) transparent transparent transparent;
}
</style>