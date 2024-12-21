<!-- 公共组件 -->
<template>
    <div class="home">
        <lb-header></lb-header>
        <div class="container">
            <sidebar></sidebar>
            <!-- 内容区域 -->
            <div class="main">
                <transition name="slide-fade">
                    <router-view></router-view>
                </transition>
            </div>
            <ad></ad>
        </div>
        <lb-footer></lb-footer>
    </div>
</template>

<script>
import lbHeader from './header'
import lbFooter from './footer'
import sidebar from './sidebar'
import ad from './ad'
export default {
  name: 'Home',
  data () {
    return {
    }
  },
  components: {
    lbHeader,
    lbFooter,
    sidebar,
    ad
  }
}
</script>

<style lang="scss" scoped>
    .home{
        width: 100%;
        height: 100vh;
        .container{
            width: 100%;
            height: calc(100vh - 70px - 50px);
            display: flex;
            background: #F0F0F0;
            .main{
                flex: 1;
                overflow-x: hidden;
                margin: 20px 20px 0 20px;
                background: #fff;
                position: relative;
            }
        }
    }
    .slide-fade-enter-active {
    transition: all 1s ease;
    }
    .slide-fade-leave-active {
    transition: all 0s ease;
    }
    .slide-fade-enter, .slide-fade-leave-to {
    transform: translateX(20px);
    opacity: 0;
    }
</style>
