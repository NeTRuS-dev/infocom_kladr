<template>
    <div class="form-group">
        <span v-if="shouldBeShown" class="text-danger d-block">{{errorMessage}}</span>
        <label :for="blockName">
            <slot></slot>
        </label>

        <input type="text" class="form-control" :class="{error:showErrorBorder}" :id="blockName" :name="blockName"
               @click.stop="$emit('focus-changed', blockName)"
               autocomplete="off"
               :value="input_value"
               @input="onInput($event)"
               :placeholder="holder">
        <ul @click.stop="$emit('focus-changed', blockName)" class="list-group" v-show="isOnFocus">
            <li class="list-group-item"
                v-for="(variant, index) in matchesVariants" :key="index"
                @click.prevent="elemSelectionEvent(variant)">
                {{variant.SOCR}} {{variant.NAME}}
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        name: "FormGroupComponent",
        model: {
            event: 'data-changed',
            prop: 'input_value'
        },
        props: {
            errorMessage: String,
            input_value: String,
            previousDone: Boolean,
            blockName: String,
            holder: String,
            variantsToChoose: Array,
            focusedBlock: String,
            selectedValue: Object,
        },
        data() {
            return {}
        },
        methods: {
            onInput(event) {
                this.$emit('data-changed', event.target.value)
                this.$emit('need-to-recalc-variants', this.blockName)
                this.$emit('focus-changed', this.blockName)
            },
            elemSelectionEvent(value) {
                this.$emit('data-changed', value.NAME)
                this.$emit('elem-selected', {
                    blockName: this.blockName,
                    value: value
                })

            }
        },
        computed: {
            matchesVariants() {
                if (this.variantsToChoose) {
                    return this.variantsToChoose.filter((elem) => {
                        return elem.matches === true
                    })
                } else {
                    return []
                }
            },
            isOnFocus() {
                return ((this.blockName === this.focusedBlock) && this.variantsToChoose !== undefined && this.variantsToChoose.length > 0)
            },
            shouldBeShown() {
                return (!this.previousDone && this.isOnFocus)
            },
            showErrorBorder() {
                return this.shouldBeShown || (this.isOnFocus === false && this.input_value && !this.selectedValue)
            }
        }
    }
</script>

<style lang="scss" scoped>
    .error {
        border-color: red;
    }

    .form-group {
        text-align: center;
        min-width: 70%;
    }

    ul {
        position: absolute;
        list-style: none;
        max-width: 40vw;
        max-height: 40vh;
        overflow: auto;

        li {
            &:hover {
                cursor: pointer;
                font-size: 1.15rem;
                z-index: 2;
                color: #fff;
                background-color: #007bff;
                border-color: #007bff;
            }
        }
    }
</style>