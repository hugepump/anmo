<template>
    <div class="lb-container">
        <top-nav :nav='nav' @changNav='handleNav'></top-nav>
        <transition name="slide-fade">
            <router-view></router-view>
        </transition>
    </div>
</template>

<script>
export default {
  data () {
    return {
      nav: []
    }
  },
  created () {
    console.log(this.$route)
    this.nav = this.$route.meta.topMenu || []
  },
  methods: {
    handleNav (index) {}
  },
  watch: {
    $route: {
      deep: true,
      handler (val) {
        this.nav = val.meta.topMenu || []
        console.log(val)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-container{
        width: 100%;
        height: 500px;
        background: #f60;
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
    }
</style>
