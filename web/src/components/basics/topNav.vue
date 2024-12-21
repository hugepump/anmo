<template>
    <div class="lb-top-nav" v-if="nav.length">
      <div class="nav-item" v-if='nav.length === 1'>{{$t('menu.' + nav[0].title)}}</div>
      <div
      v-else-if="nav.length > 1"
      v-for="(item) in nav"
      class="nav-item"
      :class="activeNav === item.index && 'nav-item-active'"
      :key='item.index'
      @click="handleNav(item.index)"
      >{{$t('menu.' + item.title)}}</div>
    </div>
</template>

<script>
export default {
  props: {
    active: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      activeNav: this.active,
      nav: []
    }
  },
  methods: {
    handleNav (index) {
      if (this.activeNav === index) return
      this.activeNav = index
      this.$store.commit('setCurrentIndex', index)
      this.$emit('changNav', index)
    }
  },
  created () {
    let {pagePermission} = this.$route.meta
    if (pagePermission) {
      this.nav = pagePermission
      this.activeNav = this.nav[0].index
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-top-nav{
        width: 100%;
        height: 60px;
        border-bottom: 1px solid #E1E1E1;
        display: flex;
        align-items: center;
        padding: 0 10px;
        font-size: 14px;
        white-space: nowrap;
        .nav-item{
          height: 60px;
          padding: 0 20px;
          line-height: 60px;
          cursor: pointer;
          &::after{
            position: absolute;
            content: '';
            width: 0%;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            height: 0px;
            background: $themeColor;
            transform: all 0.3 linear;
          }
        }
        .nav-item-active{
          color: $themeColor;
          position: relative;
          &::after{
            width: 90%;
            height: 2px;
          }
        }

    }
</style>
