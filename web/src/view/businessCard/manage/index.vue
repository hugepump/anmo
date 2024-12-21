<template>
<!-- 名片 -->
    <div class="lb-bcard-manage">
      <top-nav @changNav='handleNav'></top-nav>
      <keep-alive>
        <cardMange v-if="navSwitch === 0"/>
        <defaultContent v-if="navSwitch === 1"/>
      </keep-alive>
    </div>
</template>

<script>
import cardMange from './subPage/cardMange'
import defaultContent from './subPage/defaultContent'
export default {
  data () {
    return {
      navSwitch: -1
    }
  },
  components: {
    cardMange,
    defaultContent
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
  .lb-bcard-manage{
    width: 100%;
  }
</style>
