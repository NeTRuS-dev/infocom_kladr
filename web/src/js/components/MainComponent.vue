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
            <div class="w-100 d-flex justify-content-center">
                <button class="btn btn-primary" @click="onSubmitClick">Найти</button>
            </div>
        </form>
        <loading-spinner v-if="waiting_for_response"></loading-spinner>
        <results-main-presenter v-if="presenting_results" :results-to-present="dataToPresent"></results-main-presenter>
    </div>
</template>

<script>
    import LoadingSpinner from "@/js/components/LoadingSpinner";
    import FormGroupComponent from "@/js/components/FormGroupComponent";
    import ResultsMainPresenter from "@/js/components/ResultsMainPresenter";
    import {ajaxUrl} from "@/js/config";

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
                dataToPresent: [],
            }
        },
        methods: {
            onSubmitClick() {
                this.waiting_for_response = true
                this.sendRequest()
            },
            async sendRequest() {

                let data = {
                    area: this.area,
                    district: this.district,
                    city: this.city,
                    street: this.street,
                    house: this.house,
                }
                let response = await fetch(ajaxUrl, {
                    method: "POST",
                    mode: 'cors',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                let fetchedData = await response.json()
                console.log(fetchedData)
                if (fetchedData.errors) {
                    this.errors = fetchedData.errors
                    this.waiting_for_response = false
                    this.presenting_results = false

                } else {
                    this.dataToPresent = fetchedData
                    this.waiting_for_response = false
                    this.presenting_results = true
                }
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
    .main {
        width: 40%;
        margin-left: auto;
        margin-right: auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        height: auto;

        form {
            margin-top: 10%;
            width: 100%;
        }
    }
</style>