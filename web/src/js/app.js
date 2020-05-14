import '@/scss/styles.scss';
import 'bootstrap'
import Vue from 'vue';
import MainComponent from "@/js/components/MainComponent";

new Vue({
    el: '#app',
    template: `
        <main-component></main-component>`,
    components: {MainComponent}

});