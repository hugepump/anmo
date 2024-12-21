<template>
    <div class="lb-deal-set">
        <top-nav @changNav='handleNav'/>
        <div class="page-main">
          <keep-alive>
            <over-time v-if="navSwitch === 0"></over-time>
            <pick-up v-else-if="navSwitch === 1"></pick-up>
          </keep-alive>
        </div>
    </div>
</template>

<script>
import OverTime from './subpage/overTime'
import PickUp from './subpage/pickUp'
export default {
  data () {
    return {
      key: '商城',
      navSwitch: -1
    }
  },
  components: {
    OverTime,
    PickUp
  },
  created () {
    let routes = this.$route.meta.pagePermission
    this.navSwitch = routes[0].index
  },
  methods: {
    handleNav (index) {
      this.navSwitch = index
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-deal-set{
    width: 100%;
    .page-main{
      padding: 20px;
    }
  }
</style>
