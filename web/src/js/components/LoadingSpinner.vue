<template>
    <div class="spinner-border text-info" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</template>

<script>
    export default {
        name: "LoadingSpinner"
    }
</script>

<style lang="scss" scoped>
    div {
        width: 4rem;
        height: 4rem;
    }
</style>