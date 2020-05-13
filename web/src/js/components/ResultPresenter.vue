<template>
    <div class="carousel-item" :class="{active:isFirst}">
        <div class="content">
            {{content}}
            <div class="name-chain"></div>
            <div class="name"></div>
            <div class="socr"></div>
            <div class="code"></div>
            <div class="index"></div>
            <div class="gninmb"></div>
            <div class="ocatd"></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ResultPresenter",
        props: {
            content: {
                required: true,
                type: Array
            },
            isFirst: {
                type: Boolean,
                default: false
            }
        }
    }
</script>

<style lang="scss" scoped>
    .content {
        width: 100%;
        height: 100%;
    }
</style>