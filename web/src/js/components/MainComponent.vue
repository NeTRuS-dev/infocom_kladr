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
                    holder="Введите район">Район
            </form-group-component>
            <form-group-component
                    @focus-changed="changeFocus"
                    :focused-block="focusedBlock"
                    :previous-done="selected.area!==undefined"
                    error-message="Выберите область"
                    v-model="city"
                    @need-to-recalc-variants="selectVariants"
                    :variants-to-choose="variants.city"
                    @elem-selected="onElemSelection"
                    :selected-value="selected.city"
                    block-name="city"
                    holder="Введите город">Город
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
                    :previous-done="selected.street!==undefined"
                    error-message="Выберите улицу"
                    v-model="house"
                    :selected-value="{}"
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
                showError: false,
                preventFocusChange: false,
                focusedBlock: '',
                area: '',
                district: '',
                city: '',
                street: '',
                house: '',
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
                }
            },
            city(newVal, oldVal) {
                if (newVal.length < oldVal.length) {
                    this.selected.city = undefined
                    this.selected.street = undefined
                    this.selected.house = undefined
                    this.street = '';
                    this.house = '';
                }
            },
            street(newVal, oldVal) {
                if (newVal.length < oldVal.length) {
                    this.selected.street = undefined
                    this.selected.house = undefined
                    this.house = '';
                }
            },
        },
        methods: {
            markAsMatchedAll(data) {
                return data.map((item) => {
                    item.matches = true
                    return item
                })
            },
            changeFocus(event) {
                if (this.preventFocusChange) {
                    this.preventFocusChange = false
                    return
                }
                this.showError = false
                this.focusedBlock = event
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
                        this.selected.area = value
                        this.selected.district = undefined
                        this.selected.city = undefined
                        this.selected.street = undefined
                        this.selected.house = undefined
                        this.district = '';
                        this.city = '';
                        this.street = '';
                        this.house = '';
                        break
                    case 'district':
                        this.selected.district = value
                        this.selected.city = undefined
                        this.selected.street = undefined
                        this.selected.house = undefined
                        this.city = '';
                        this.street = '';
                        this.house = '';
                        break
                    case 'city':
                        this.selected.city = value
                        this.selected.street = undefined
                        this.selected.house = undefined
                        this.street = '';
                        this.house = '';
                        break
                    case 'street':
                        this.selected.street = value
                        this.selected.house = undefined
                        this.house = '';
                        break
                }
                this.preventFocusChange = true
                if (!this.selected.area || this.selected.street) {
                    this.waiting_for_response = false
                    return
                }
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
                fetchedData = (await this.sendRequest(data))
                if (fetchedData.errors) {
                    this.waiting_for_response = false
                    this.presenting_results = false
                } else {
                    this.setNewVariants(fetchedData)
                    this.waiting_for_response = false
                }
            },
            setNewVariants(newData) {
                if (newData.district) {
                    this.variants.district = this.markAsMatchedAll(newData.district)
                }
                if (newData.city) {
                    this.variants.city = this.markAsMatchedAll(newData.city)
                }
                if (newData.street) {
                    this.variants.street = this.markAsMatchedAll(newData.street)
                }
            },
            async sendRequest(data) {
                this.preventFocusChange = false
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
                if (!this.selected.area) {
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
                let fetchedData = (await this.sendRequest(data))
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
                this.presenting_results = false
                this.area = ''
                this.district = ''
                this.city = ''
                this.street = ''
                this.house = ''
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