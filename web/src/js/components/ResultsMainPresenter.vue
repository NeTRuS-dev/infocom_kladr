<template>
    <div id="results" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner" role="listbox">
            <result-presenter v-for="(result, index) in resultsToPresent" :key="result.CODE" :content="result"
                              :is-first="index===0"></result-presenter>
        </div>
        <a class="carousel-control-prev" href="#results" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#results" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

</template>

<script>
    import ResultPresenter from "@/js/components/ResultPresenter";

    export default {
        name: "ResultsMainPresenter",
        components: {ResultPresenter},
        props: {
            resultsToPresent: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                firstSet: false,
            }
        },
        computed: {},
        methods: {}
    }
</script>

<style lang="scss" scoped>
    .carousel {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
</style>