<template>
    <div class="main">
        <form @submit.prevent v-if="showForm">
            <form-group-component
                    @focus-changed="focusedBlock=$event"
                    :focused-block="focusedBlock"
                    :previous-done="prevIsDoneForArea"
                    v-model="area"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.area"
                    @elem-selected="onElemSelection"
                    block-name="area"
                    holder="Введите область">Область
            </form-group-component>
            <form-group-component
                    @focus-changed="focusedBlock=$event"
                    :focused-block="focusedBlock"
                    :previous-done="prevIsDoneForDistrict"
                    v-model="district"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.district"
                    @elem-selected="onElemSelection"
                    block-name="district"
                    holder="Введите район">Район
            </form-group-component>
            <form-group-component
                    @focus-changed="focusedBlock=$event"
                    :focused-block="focusedBlock"
                    :previous-done="prevIsDoneForCity"
                    v-model="city"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.city"
                    @elem-selected="onElemSelection"
                    block-name="city"
                    holder="Введите город">Город
            </form-group-component>
            <form-group-component
                    @focus-changed="focusedBlock=$event"
                    :focused-block="focusedBlock"
                    :previous-done="prevIsDoneForStreet"
                    v-model="street"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.street"
                    @elem-selected="onElemSelection"
                    block-name="street"
                    holder="Введите улицу">Улица
            </form-group-component>
            <form-group-component
                    @focus-changed="focusedBlock=$event"
                    :focused-block="focusedBlock"
                    :previous-done="prevIsDoneForHouse"
                    v-model="house"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.house"
                    @elem-selected="onElemSelection"
                    block-name="house"
                    holder="Введите номер дома">Дом
            </form-group-component>
            <div class="w-100 d-flex justify-content-center">
                <input type="submit" value="Найти" class="btn btn-primary" @click.prevent="onSubmitClick">
            </div>
        </form>
        <loading-spinner v-if="waiting_for_response"></loading-spinner>
        <template v-if="presenting_results">
            <results-main-presenter
                    :results-to-present="dataToPresent"></results-main-presenter>
            <button class="btn btn-info" @click="newQuery">Новый запрос</button>
        </template>
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
                focusedBlock: '',
                area: '',
                district: '',
                city: '',
                street: '',
                house: '',
                prevIsDoneForArea: true,
                prevIsDoneForDistrict: false,
                prevIsDoneForCity: false,
                prevIsDoneForStreet: false,
                prevIsDoneForHouse: false,
                variants: {
                    area: [],
                    district: [],
                    city: [],
                    street: [],
                    house: [],
                },
                selected: {
                    area: undefined,
                    district: undefined,
                    city: undefined,
                    street: undefined,
                    house: undefined,
                },
                waiting_for_response: false,
                presenting_results: false,
                dataToPresent: [],
            }
        },
        async created() {
            this.waiting_for_response = true
            let data = {}
            let fetchedData = (await this.sendRequest(data))
            if (fetchedData.errors) {
                this.waiting_for_response = false
                this.presenting_results = false

            } else {
                this.variants.area = fetchedData
                this.waiting_for_response = false
            }
        },
        methods: {
            async onElemSelection(data) {
                this.focusedBlock = '';
                let blockName = data.blockName
                let value = data.value
                switch (blockName) {
                    case 'area':
                        this.prevIsDoneForDistrict = true
                        this.selected.area = value
                        break
                    case 'district':
                        this.prevIsDoneForCity = true
                        this.selected.district = value

                        break
                    case 'city':
                        this.prevIsDoneForStreet = true
                        this.selected.city = value

                        break
                    case 'street':
                        this.prevIsDoneForHouse = true
                        this.selected.street = value
                        break
                    case 'house':
                        this.selected.house = value
                        break
                }
            },
            async sendRequest(data) {
                let response = await fetch(ajaxUrl, {
                    method: "POST",
                    mode: 'cors',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                if (!response.ok) {
                    console.log('На сервере произошла ошибка ' + response.status)
                    this.waiting_for_response = false
                    this.presenting_results = false
                }
                return (await response.json())

            },
            async onSubmitClick() {
                this.waiting_for_response = true
                let data = {
                    area: this.area,
                    district: this.district,
                    city: this.city,
                    street: this.street,
                    house: this.house,
                }
                let fetchedData = (await this.sendRequest(data))
                if (fetchedData.errors) {
                    this.waiting_for_response = false
                    this.presenting_results = false

                } else {
                    this.dataToPresent = fetchedData
                    this.waiting_for_response = false
                    this.presenting_results = true
                }
            },
            newQuery() {
                this.presenting_results = false
                this.area = ''
                this.district = ''
                this.city = ''
                this.street = ''
                this.house = ''
            },
            selectVariants(blockName) {
                switch (blockName) {
                    case 'area':
                        this.variants.area = this.variants.area.filter((elem) => {
                            return elem.NAME.toLowerCase().indexOf(this.area.toLowerCase()) === -1
                        })
                        break
                    case 'district':
                        this.variants.district = this.variants.district.filter((elem) => {
                            return elem.NAME.toLowerCase().indexOf(this.district.toLowerCase()) === -1
                        })
                        break
                    case 'city':
                        this.variants.city = this.variants.city.filter((elem) => {
                            return elem.NAME.toLowerCase().indexOf(this.city.toLowerCase()) === -1
                        })
                        break
                    case 'street':
                        this.variants.street = this.variants.street.filter((elem) => {
                            return elem.NAME.toLowerCase().indexOf(this.street.toLowerCase()) === -1
                        })
                        break
                    case 'house':
                        this.variants.house = this.variants.house.filter((elem) => {
                            return elem.NAME.toLowerCase().indexOf(this.house.toLowerCase()) === -1
                        })
                        break
                }
            },
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
        width: 80%;
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
            width: 50%;
        }
    }
</style>