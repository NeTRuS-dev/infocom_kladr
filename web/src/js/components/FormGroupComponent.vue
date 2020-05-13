<template>
    <div class="form-group">
        <span v-if="error" class="text-danger d-block">{{error}}</span>
        <label :for="blockName">
            <slot></slot>
        </label>
        <input type="text" class="form-control" :class="{error:error}" :id="blockName"
               :value="input_value"
               @input="$emit('data-changed', $event.target.value)"
               :placeholder="holder">
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
            input_value: String,
            error: String,
            blockName: String,
            holder: String,
        },
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
</style>