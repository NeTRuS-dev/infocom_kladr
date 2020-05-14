<template>
    <div class="carousel-item" :class="{active:isFirst}">
        <div class="content">
            <template v-if="withChain">
                <div class="row-presenter name-chain">
                    <div class="name">Полный путь : </div>
                    <div class="value">{{`${content.NAME_CHAIN} -> ${content.NAME}`}}</div>
                </div>
            </template>
            <template v-else-if="content.NAME">
                <div class="row-presenter name">
                    <div class="name">Название : </div>
                    <div class="value">{{content.NAME}}</div>
                </div>
            </template>
            <template v-if="content.SOCR">
                <div class="row-presenter socr">
                    <div class="name">Сокращение : </div>
                    <div class="value">{{content.SOCR}}</div>
                </div>
            </template>
            <template v-if="content.CODE">
                <div class="row-presenter code">
                    <div class="name">Код КЛАДР : </div>
                    <div class="value">{{content.CODE}}</div>
                </div>
            </template>
            <template v-if="content.INDEX">
                <div class="row-presenter index">
                    <div class="name">Почтовый ндекс : </div>
                    <div class="value">{{content.INDEX}}</div>
                </div>
            </template>
            <div class="row-presenter gninmb">
                <div class="name">Код налоговой : </div>
                <div class="value">{{content.GNINMB}}</div>
            </div>
            <div class="row-presenter ocatd">
                <div class="name">Код ОКАТО : </div>
                <div class="value">{{content.OCATD}}</div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ResultPresenter",
        props: {
            content: {
                required: true,
                type: Array
            },
            isFirst: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            withChain() {
                return this.content.NAME_CHAIN && this.content.NAME
            }
        },
    }
</script>

<style lang="scss" scoped>
    .content {
        width: 100%;
        height: 70%;
    }

    .row-presenter {
        display: flex;
        justify-content: space-between;
    }
</style>