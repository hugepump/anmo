<template>
<!-- 概况 -->
    <div>
        {{key}}
        <lb-switch v-model='val' @change="handleSwitchVal" opType='view'></lb-switch>
        <!-- <lb-switch v-model='val1' @change="handleSwitchVal" opType='test'></lb-switch>
        <lb-switch v-model='val2' @change="handleSwitchVal" opType='add'></lb-switch> -->
    </div>
</template>

<script>
export default {
  data () {
    return {
      val: true,
      val1: false,
      val2: true,
      width: 80,
      key: '概况页面'
    }
  },
  methods: {
    handleSwitchVal (val) {
      this.val = val
    }
  },
  watch: {
    val (vals) {
      console.log(vals)
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
