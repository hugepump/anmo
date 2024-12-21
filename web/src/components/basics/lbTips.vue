<template>
    <div class="lb-tips">
        <i class="iconfont icon-warn"></i>
        <span>{{title}}</span>
    </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: ''
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-tips{
            width: 100%;
            background: #F4F4F4;
            height: 60px;
            line-height: 60px;
            padding: 0 0 0 30px;
            i{
              color: $themeColor;
            }
        }
</style>
