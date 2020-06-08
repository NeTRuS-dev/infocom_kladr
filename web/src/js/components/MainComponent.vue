<template>
    <div class="main">
        <form @submit.prevent v-if="showForm" autocomplete="off">
            <form-group-component
                    @focus-changed="changeFocus"
                    :focused-block="focusedBlock"
                    :previous-done="!showError"
                    error-message="Выберите область"
                    v-model="area"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.area"
                    @elem-selected="onElemSelection"
                    :selected-value="selected.area"
                    block-name="area"
                    holder="Введите область">Область
            </form-group-component>
            <form-group-component
                    @focus-changed="changeFocus"
                    :focused-block="focusedBlock"
                    :previous-done="selected.area!==undefined"
                    error-message="Выберите область"
                    v-model="district"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.district"
                    @elem-selected="onElemSelection"
                    :selected-value="selected.district"
                    block-name="district"
                    holder="Введите район">Регион / район
            </form-group-component>
            <form-group-component
                    @focus-changed="changeFocus"
                    :focused-block="focusedBlock"
                    :previous-done="true"
                    error-message="Выберите область"
                    v-model="city"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.city"
                    @elem-selected="onElemSelection"
                    :selected-value="selected.city"
                    block-name="city"
                    holder="Введите город">Город / н. п.
            </form-group-component>
            <form-group-component
                    @focus-changed="changeFocus"
                    :focused-block="focusedBlock"
                    :previous-done="selected.district!==undefined||selected.city!==undefined"
                    error-message="Выберите район или город"
                    v-model="street"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.street"
                    @elem-selected="onElemSelection"
                    block-name="street"
                    :selected-value="selected.street"
                    holder="Введите улицу">Улица
            </form-group-component>
            <form-group-component
                    @focus-changed="changeFocus"
                    :focused-block="focusedBlock"
                    :previous-done="(selected.street!==undefined||selected.city!==undefined)&&houseFound"
                    :error-message="(selected.street!==undefined||selected.city!==undefined)?'Дом не найден':'Выберите улицу или город'"
                    v-model="house"
                    :selected-value="{}"
                    block-name="house"
                    holder="Введите номер дома">Дом
            </form-group-component>
            <div class="w-100 d-flex justify-content-center mt-4">
                <input type="submit" value="Вывести данные по выбранным субъектам" class="btn btn-primary mr-5"
                       @click.prevent.stop="onSubmitClick">
                <button class="btn btn-info ml-5" @click="newQuery">Сбросить форму</button>
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
    import {checkHouseUrl, fullRequestUrl, initUrl, searchUrl} from "@/js/config";

    export default {
        name: "MainComponent",
        data() {
            return {
                showError: false,
                preventFocusChange: false,
                focusedBlock: '',
                area: '',
                district: '',
                city: '',
                street: '',
                house: '',
                houseFound: true,
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
                bigCities: [],
                waiting_for_response: false,
                presenting_results: false,
                dataToPresent: [],
                isCooldown: false
            }
        },
        async mounted() {
            document.addEventListener('click', () => {
                this.focusedBlock = ''
            })
            let data = {data: {}}
            let fetchedData = (await this.sendRequest(data, initUrl))
            if (fetchedData.errors) {
                this.presenting_results = false
            } else {
                this.variants.area = fetchedData.area
                this.variants.city = fetchedData.city
                this.bigCities = fetchedData.city
            }
        },
        watch: {
            area(newVal, oldVal) {
                if (newVal.length < oldVal.length) {
                    this.selected.area = undefined
                    this.selected.district = undefined
                    this.selected.city = undefined
                    this.selected.street = undefined
                    this.selected.house = undefined
                    this.district = '';
                    this.city = '';
                    this.street = '';
                    this.house = '';
                    this.variants.city = this.bigCities
                    this.houseFound = true
                }
            },
            district(newVal, oldVal) {
                if (newVal.length < oldVal.length) {
                    this.selected.district = undefined
                    this.selected.city = undefined
                    this.selected.street = undefined
                    this.selected.house = undefined
                    this.city = '';
                    this.street = '';
                    this.house = '';
                    this.variants.city = this.bigCities
                    this.houseFound = true
                }
            },
            city(newVal, oldVal) {
                if (newVal.length < oldVal.length) {
                    this.selected.city = undefined
                    this.selected.street = undefined
                    this.selected.house = undefined
                    this.street = '';
                    this.house = '';
                    this.houseFound = true
                }
            },
            street(newVal, oldVal) {
                if (newVal.length < oldVal.length) {
                    this.selected.street = undefined
                    this.selected.house = undefined
                    this.house = '';
                    this.houseFound = true
                }
            },
            house() {
                this.checkHouse()
            }
        },
        methods: {
            async checkHouse() {
                if (this.house === '') {
                    this.houseFound = true
                }
                if (this.isCooldown || (!this.selected.street && !this.selected.city) || this.house === '') return;
                let fetchedData, data
                data = {
                    data: {
                        checking_house: this.house,
                    }
                }
                if (this.selected.street) {
                    data.data.selected_street = this.selected.street
                } else {
                    data.data.selected_city = this.selected.city
                }
                fetchedData = (await this.sendRequest(data, checkHouseUrl))
                this.houseFound = fetchedData
                this.isCooldown = true;
                setTimeout(() => this.isCooldown = false, 800);
            },
            changeFocus(event) {
                if (this.preventFocusChange) {
                    this.preventFocusChange = false
                    this.focusedBlock = ''
                    return
                }
                this.showError = false
                this.focusedBlock = event
            },
            async onElemSelection(elem) {
                this.preventFocusChange = true
                this.focusedBlock = ''
                let {blockName, value} = elem
                switch (blockName) {
                    case 'area':
                        this.selected.area = value
                        this.selected.district = undefined
                        this.selected.city = undefined
                        this.selected.street = undefined
                        this.selected.house = undefined
                        this.area = value.NAME;
                        this.district = '';
                        this.city = '';
                        this.street = '';
                        this.house = '';
                        this.variants.district = []
                        this.variants.city = []
                        this.variants.street = []
                        break
                    case 'district':
                        this.selected.district = value
                        this.selected.city = undefined
                        this.selected.street = undefined
                        this.selected.house = undefined
                        this.district = value.NAME;
                        this.city = '';
                        this.street = '';
                        this.house = '';
                        this.variants.city = []
                        this.variants.street = []
                        break
                    case 'city':
                        this.selected.city = value
                        this.selected.street = undefined
                        this.selected.house = undefined
                        this.city = value.NAME;
                        this.street = '';
                        this.house = '';
                        this.variants.street = []
                        break
                    case 'street':
                        this.street = value.NAME;
                        this.selected.street = value
                        this.selected.house = undefined
                        this.house = '';
                        break
                }
                await this.checkHouse()
                if (!this.selected.area && !this.bigCities.includes(this.selected.city) || this.selected.street) {
                    return
                }
                await this.getNewVariants()

            },
            async getNewVariants() {
                let fetchedData, data
                data = {
                    data: {
                        selected_area: this.selected.area,
                    }
                }
                if (this.selected.district !== undefined) {
                    data.data.selected_district = this.selected.district
                }
                if (this.selected.city !== undefined) {
                    data.data.selected_city = this.selected.city
                }
                fetchedData = (await this.sendRequest(data, searchUrl))
                if (fetchedData.errors) {
                    this.presenting_results = false
                } else {
                    this.setNewVariants(fetchedData)
                }
            },
            setNewVariants(newData) {
                if (newData.district) {
                    if (this.district !== '') {
                        this.variants.district = newData.district
                        this.selectVariants('district')
                    } else {
                        this.variants.district = newData.district
                    }
                }
                if (newData.city) {
                    if (this.city !== '') {
                        this.variants.city = newData.city
                        this.selectVariants('city')
                    } else {
                        this.variants.city = newData.city
                    }
                }
                if (newData.street) {
                    if (this.street !== '') {
                        this.variants.street = newData.street
                        this.selectVariants('street')
                    } else {
                        this.variants.street = newData.street
                    }
                }
            },
            async sendRequest(data, url) {
                let response = await fetch(url, {
                    method: "POST",
                    mode: 'cors',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                if (!response.ok) {
                    console.log('На сервере произошла ошибка ' + response.status)
                }
                return (await response.json())

            },
            async onSubmitClick() {
                if (!this.selected.area && !this.bigCities.includes(this.selected.city)) {
                    this.focusedBlock = 'area'
                    this.showError = true
                    return
                }
                this.preventFocusChange = false
                this.waiting_for_response = true
                let data = {
                    data: {
                        get_full_response: true,
                        selected_area: this.selected.area,
                    }
                }
                if (this.selected.district !== undefined) {
                    data.data.selected_district = this.selected.district
                }
                if (this.selected.city !== undefined) {
                    data.data.selected_city = this.selected.city
                }
                if (this.selected.street !== undefined) {
                    data.data.selected_street = this.selected.street
                }
                if (this.house !== undefined && this.house !== '') {
                    data.data.selected_house = this.house
                }
                let fetchedData = (await this.sendRequest(data, fullRequestUrl))
                if (fetchedData.errors) {
                    this.presenting_results = false
                } else {
                    this.dataToPresent = this.buildDataToPresent(fetchedData)
                    this.presenting_results = true
                }
                this.waiting_for_response = false
            },
            buildDataToPresent(lastLevelData) {
                return lastLevelData
            },
            newQuery() {
                this.preventFocusChange = false
                this.presenting_results = false
                this.area = ''
                this.district = ''
                this.city = ''
                this.street = ''
                this.house = ''
                this.variants = {
                    area: this.variants.area,
                    district: [],
                    city: this.bigCities,
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
            width: 50%;
        }
    }
</style>