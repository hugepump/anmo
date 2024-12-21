<!-- 页面头部 -->
<template>
    <div class="lb-header">
        <div class="lb-left">
            <div class="logo">
                <img src="" alt="">
            </div>
            <div v-if="isIndex" class="admin-title">
                <span>龙兵科技有限公司</span>
                <el-tag type="danger" effect="dark" :disable-transitions='true' size="small">体验版</el-tag>
            </div>
            <div v-else class="menu-title">{{$t('menu.' + title)}}</div>
        </div>
        <div class="lb-right">
            <el-avatar shape="square" size="small" :src="avatar"></el-avatar>
            <!-- 用户名下拉菜单 -->
            <el-dropdown class="user-name" trigger="hover" @command="handleCommand">
                <span class="el-dropdown-link">
                    <span>龙兵科技有限公司</span>
                    <i class="el-icon-caret-bottom"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item  command="loginout">退出登录</el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      avatar: require('../assets/logo.png'),
      isIndex: true,
      title: ''
    }
  },
  created () {
    this.handleTitle(this.$route.meta.title)
  },
  methods: {
    handleTitle (title) {
      this.isIndex = !title
      this.title = title
    },
    handleCommand () {
      console.log('用户退出')
    }
  },
  watch: {
    $route: {
      handler (val, oldVal) {
        this.handleTitle(val.meta.title)
      },
      // 深度观察监听
      deep: true
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-header{
        width: 100%;
        height: 70px;
        border-bottom: 1px solid #EEEEEE;
        background: #FFFFFF;
        display:flex;
        justify-content: space-between;
        align-items: center;
        .lb-left{
            display: flex;
            height: 70px;
            .logo{
                width: 120px;
                height: 70px;
                background: #273543;
                display: flex;
                justify-content: center;
                align-items: center;
                img{
                    width: 34px;
                    height: 34px;
                    border-radius: 50%;
                    background: #fff;
                    border: 1px solid #fff;
                }
            }
            .admin-title{
                padding: 0 32px;
                display: flex;
                align-items: center;
                span{
                    margin-right: 5px;
                }
            }
            .menu-title {
                height: 70px;
                width: 160px;
                border-right: 1px solid #eeeeee;
                height: 100%;
                display: flex;
                font-size: 16px;
                color: #101010;
                align-items: center;
                justify-content: center;
            }
        }
        .lb-right{
            display:flex;
            justify-content: center;
            align-items: center;
            padding: 0 30px;
            cursor: pointer;
            .el-dropdown-link{
                cursor: pointer;
            }
            .el-dropdown-menu__item{
                text-align: center;
            }
            span{
                margin: 0 5px 0 10px;
            }
        }
    }
</style>
