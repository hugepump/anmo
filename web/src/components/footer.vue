<!-- 页面底部 -->
<template>
    <div class="lb-footer">
        {{key}}
    </div>
</template>

<script>
export default {
  data () {
    return {
      key: '技术支持'
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-footer{
        width: 100%;
        height: 49px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-top: 1px solid #ccc;
        color: #ccc;
        font-size: 12px;
    }
</style>
