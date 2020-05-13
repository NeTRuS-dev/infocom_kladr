<template>
    <div class="main">
        <form @submit.prevent v-if="showForm">
            <form-group-component
                    :error="errors.area"
                    v-model="area"
                    block-name="area"
                    holder="Введите область">Область
            </form-group-component>
            <form-group-component
                    :error="errors.district"
                    v-model="district"
                    block-name="area"
                    holder="Введите район">Район
            </form-group-component>
            <form-group-component
                    :error="errors.city"
                    v-model="city"
                    block-name="city"
                    holder="Введите город">Город
            </form-group-component>
            <form-group-component
                    :error="errors.street"
                    v-model="street"
                    block-name="street"
                    holder="Введите улицу">Улица
            </form-group-component>
            <form-group-component
                    :error="errors.house"
                    v-model="house"
                    block-name="house"
                    holder="Введите номер дома">Дом
            </form-group-component>
            <button class="btn btn-primary" @click="onSubmitClick">Найти</button>
        </form>
        <loading-spinner v-if="waiting_for_response"></loading-spinner>
        <results-main-presenter v-if="presenting_results"></results-main-presenter>
    </div>
</template>

<script>
    import LoadingSpinner from "@/js/components/LoadingSpinner";
    import FormGroupComponent from "@/js/components/FormGroupComponent";
    import ResultsMainPresenter from "@/js/components/ResultsMainPresenter";

    export default {
        name: "MainComponent",
        data() {
            return {
                area: '',
                district: '',
                city: '',
                street: '',
                house: '',
                errors: {
                    area: '',
                    district: '',
                    city: '',
                    street: '',
                    house: '',
                },
                waiting_for_response: false,
                presenting_results: false,
            }
        },
        methods: {
            onSubmitClick() {
                this.waiting_for_response = true;
                //request
                //if !errors
                this.waiting_for_response = false;
                this.presenting_results = true;
            }
        },
        computed: {
            showForm() {
                return !this.waiting_for_response && !this.presenting_results
            },
        },
        components: {ResultsMainPresenter, FormGroupComponent, LoadingSpinner},
    }
</script>

<style lang="scss" scoped>

</style>