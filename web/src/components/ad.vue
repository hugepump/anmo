<!-- 左侧边栏广告 -->
<template>
    <div class="lb-ad" :class="!switchs ? 'ad-collapse' : ''">
      <div class="ad-main"></div>
        <div class="flod"  @click="handleFold">{{switchs ? '折叠' : '展开'}}</div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      key: '广告',
      switchs: false
    }
  },
  methods: {
    handleFold () {
      let {switchs} = this
      this.switchs = !switchs
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-ad{
        width: 220px;
        position: relative;
        transition: width 0.2s linear;
        margin-top: 20px;
        .ad-main{
          position: absolute;
          top: 0;
          left: 0;
          width: 220px;
          background: #fff;
          height: 100%;
          font-size: 14px;
          padding: 10px;
        }
        .flod{
            position: absolute;
            top: 0;
            bottom: 0;
            left: -20px;
            margin: auto;
            width: 20px;
            height: 80px;
            background: #BFBFBF;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
        }
    }
    .ad-collapse{
        width: 0px;
    }
</style>
