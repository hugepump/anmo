<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'App'
  // created () {
  //   this.$api.getTest().then(res => {
  //     console.log(res)
  //   })
  // }
}
</script>

<style lang='scss'>
@import url('./style/reset.css');
@import url('./style/icon.css');
</style>
