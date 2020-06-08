<template>
    <div class="carousel-item" :class="{active:isFirst}">
        <div class="content">
            <template v-if="withChain">
                <div class="row-presenter name-chain">
                    <div class="name">Полный путь :</div>
                    <div v-if="content.SOCR!=='ДОМ'" class="value">{{`${content.NAME_CHAIN}, ${content.FULL_SOCR}
                        ${content.NAME}`}}
                    </div>
                    <div v-else class="value">{{`${content.NAME_CHAIN}, дом ${content.NAME}`}}</div>
                </div>
            </template>
            <template v-else-if="content.NAME">
                <div class="row-presenter name">
                    <div class="name">Полный путь :</div>
                    <div class="value">{{content.FULL_SOCR}} {{content.NAME}}</div>
                </div>
            </template>
<!--            <template v-if="withChain">-->
<!--                <div class="row-presenter name-chain">-->
<!--                    <div class="name">Полный путь :</div>-->
<!--                    <div v-if="content.SOCR!=='ДОМ'" class="value">{{`${content.NAME_CHAIN}, ${content.SOCR}-->
<!--                        ${content.NAME}`}}-->
<!--                    </div>-->
<!--                    <div v-else class="value">{{`${content.NAME_CHAIN}, ${content.NAME}`}}</div>-->
<!--                </div>-->
<!--            </template>-->
<!--            <template v-else-if="content.NAME">-->
<!--                <div class="row-presenter name">-->
<!--                    <div class="name">Название :</div>-->
<!--                    <div class="value">{{content.NAME}}</div>-->
<!--                </div>-->
<!--            </template>-->
<!--            <template v-if="content.SOCR">-->
<!--                <div class="row-presenter socr">-->
<!--                    <div class="name">Сокращение :</div>-->
<!--                    <div class="value">{{content.SOCR}}</div>-->
<!--                </div>-->
<!--            </template>-->
<!--            <template v-if="content.FULL_SOCR">-->
<!--                <div class="row-presenter full_socr">-->
<!--                    <div class="name">Расшифровка сокращения :</div>-->
<!--                    <div class="value">{{content.FULL_SOCR}}</div>-->
<!--                </div>-->
<!--            </template>-->
<!--            <template v-if="content.CODE">-->
<!--                <div class="row-presenter code">-->
<!--                    <div class="name">Код КЛАДР :</div>-->
<!--                    <div class="value">{{content.CODE}}</div>-->
<!--                </div>-->
<!--            </template>-->
<!--            <template v-if="content.INDEX">-->
<!--                <div class="row-presenter index">-->
<!--                    <div class="name">Почтовый индекс :</div>-->
<!--                    <div class="value">{{content.INDEX}}</div>-->
<!--                </div>-->
<!--            </template>-->
<!--            <template v-if="content.GNINMB">-->
<!--                <div class="row-presenter gninmb">-->
<!--                    <div class="name">Код налоговой :</div>-->
<!--                    <div class="value">{{content.GNINMB}}</div>-->
<!--                </div>-->
<!--            </template>-->
<!--            <template v-if="content.OCATD">-->
<!--                <div class="row-presenter ocatd">-->
<!--                    <div class="name">Код ОКАТО :</div>-->
<!--                    <div class="value">{{content.OCATD}}</div>-->
<!--                </div>-->
<!--            </template>-->
        </div>
    </div>
</template>

<script>
    export default {
        name: "ResultPresenter",
        props: {
            content: {
                required: true,
                type: Object
            },
            isFirst: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            withChain() {
                return this.content.NAME_CHAIN && this.content.SOCR && this.content.NAME
            }
        },
    }
</script>

<style lang="scss" scoped>
    .content {
        width: 100%;
        height: 15rem;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
    }

    .name-chain {
        /*font-size: 1rem;*/
    }

    .row-presenter {
        display: flex;
        justify-content: space-between;
    }
</style>