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
                <input type="submit" value="Вывести данные по выбранным субъектам" class="btn btn-primary"
                       @click.prevent="onSubmitClick">
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
            document.addEventListener('click', () => {
                this.focusedBlock = ''
            })
            let data = {data: {}}
            let fetchedData = (await this.sendRequest(data))
            if (fetchedData.errors) {
                this.waiting_for_response = false
                this.presenting_results = false

            } else {
                this.variants.area = this.markAsMatchedAll(fetchedData)
                this.waiting_for_response = false
            }
        },
        methods: {
            markAsMatchedAll(data) {
                return data.map((item) => {
                    item.matches = true
                    return item
                })
            },
            async onElemSelection(elem) {
                this.waiting_for_response = true
                this.focusedBlock = '';
                let fetchedData = {};
                let blockName = elem.blockName
                let value = elem.value
                let data = {};
                switch (blockName) {
                    case 'area':
                        this.prevIsDoneForDistrict = true
                        this.selected.area = value
                        data = {
                            data: {
                                parent_subject: value,
                                get_districts: true
                            }
                        }
                        fetchedData = (await this.sendRequest(data))
                        if (fetchedData.errors) {
                            this.waiting_for_response = false
                            this.presenting_results = false
                        } else {
                            this.variants.district = fetchedData
                            this.waiting_for_response = false
                        }
                        break
                    case 'district':
                        this.prevIsDoneForCity = true
                        this.selected.district = value

                        data = {
                            data: {
                                parent_subject: value,
                                get_cities: true
                            }
                        }
                        fetchedData = (await this.sendRequest(data))
                        if (fetchedData.errors) {
                            this.waiting_for_response = false
                            this.presenting_results = false
                        } else {
                            this.variants.city = fetchedData
                            this.waiting_for_response = false
                        }
                        break
                    case 'city':
                        this.prevIsDoneForStreet = true
                        this.selected.city = value
                        data = {
                            data: {
                                parent_subject: value,
                                get_streets: true
                            }
                        }
                        fetchedData = (await this.sendRequest(data))
                        if (fetchedData.errors) {
                            this.waiting_for_response = false
                            this.presenting_results = false
                        } else {
                            this.variants.street = fetchedData
                            this.waiting_for_response = false
                        }
                        break
                    case 'street':
                        this.prevIsDoneForHouse = true
                        this.selected.street = value
                        data = {
                            data: {
                                parent_subject: value,
                                get_houses: true
                            }
                        }
                        fetchedData = (await this.sendRequest(data))
                        if (fetchedData.errors) {
                            this.waiting_for_response = false
                            this.presenting_results = false
                        } else {
                            this.variants.house = fetchedData
                            this.waiting_for_response = false
                        }
                        break
                    case 'house':
                        this.selected.house = value
                        this.waiting_for_response = false
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
            onSubmitClick() {
                if (!this.selected.area) {
                    return
                }
                this.waiting_for_response = true
                this.dataToPresent = this.buildDataToPresent()
                this.waiting_for_response = false
                this.presenting_results = true
            },
            buildDataToPresent() {
                let chain = ''
                let lastSelected = this.selected.area
                if (this.selected.district) {
                    chain += `${this.selected.area.SOCR} ${this.selected.area.NAME}`
                    lastSelected = this.selected.district
                }
                if (this.selected.city) {
                    chain += ` -> ${this.selected.district.SOCR} ${this.selected.district.NAME}`
                    lastSelected = this.selected.city
                }
                if (this.selected.street) {
                    chain += ` -> ${this.selected.city.SOCR} ${this.selected.city.NAME}`
                    lastSelected = this.selected.street
                }
                if (this.selected.house) {
                    chain += ` -> ${this.selected.street.SOCR} ${this.selected.street.NAME}`
                    lastSelected = this.selected.house
                }
                return [{NAME_CHAIN: chain, ...lastSelected}]
            },
            newQuery() {
                this.presenting_results = false
                this.area = ''
                this.district = ''
                this.city = ''
                this.street = ''
                this.house = ''
                this.prevIsDoneForArea = true
                this.prevIsDoneForDistrict = false
                this.prevIsDoneForCity = false
                this.prevIsDoneForStreet = false
                this.prevIsDoneForHouse = false
                this.variants = {
                    area: this.variants.area,
                    district: [],
                    city: [],
                    street: [],
                    house: [],
                };
                this.selected = {
                    area: undefined,
                    district: undefined,
                    city: undefined,
                    street: undefined,
                    house: undefined
                };
            },
            selectVariants(blockName) {
                switch (blockName) {
                    case 'area':
                        this.variants.area = this.variants.area.map((elem) => {
                            elem.matches = elem.NAME.toLowerCase().indexOf(this.area.toLowerCase()) !== -1
                            return elem
                        })
                        break
                    case 'district':
                        this.variants.district = this.variants.district.map((elem) => {
                            elem.matches = elem.NAME.toLowerCase().indexOf(this.district.toLowerCase()) !== -1
                            return elem
                        })
                        break
                    case 'city':
                        this.variants.city = this.variants.city.map((elem) => {
                            elem.matches = elem.NAME.toLowerCase().indexOf(this.city.toLowerCase()) !== -1
                            return elem
                        })
                        break
                    case 'street':
                        this.variants.street = this.variants.street.map((elem) => {
                            elem.matches = elem.NAME.toLowerCase().indexOf(this.street.toLowerCase()) !== -1
                            return elem
                        })
                        break
                    case 'house':
                        this.variants.house = this.variants.house.map((elem) => {
                            elem.matches = elem.NAME.toLowerCase().indexOf(this.house.toLowerCase()) !== -1
                            return elem
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