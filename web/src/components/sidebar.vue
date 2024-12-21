<!-- 右侧边栏 -->
<template>
    <div class="lb-sidebar">
        <div class="menu">
            <ul class="menu-top">
                <router-link
                v-for="(item,index) in routes"
                tag='li'
                :key='index'
                active-class="menu-active"
                :to="item.path"
                >
                <i class="iconfont" :class="item.meta.icon"></i>
                {{$t('menu.' + item.meta.menuName)}}
                </router-link>
            </ul>
            <!-- <ul>
                <router-link
                v-for="(item,index) in routes"
                tag='li'
                :key='index'
                active-class="menu-active"
                :to="item.path"
                >{{item.meta.menuName}}</router-link>
            </ul> -->
        </div>
        <div v-if="subnav.length > 0" class="submenu">
            <el-collapse
            v-for="(item, index) in subnav"
            :key="index"
            v-model="activeNames"
            >
                <el-collapse-item :title="$t('menu.' + item.name)" :name='index'>
                    <div
                    class="item"
                    v-for="(items, indexs) in item.url"
                    :key="indexs">
                    <router-link
                    tag='span'
                    active-class="el-collapse-item-active"
                    :to="items.url">{{$t('menu.' + items.name)}}</router-link>
                    </div>
                </el-collapse-item>
            </el-collapse>
        </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      routes: [], // 路由表
      subnav: [], // 二级菜单表
      activeNames: [] // 二级菜单展开的配置
    }
  },
  created () {
    this.handleRoute()
    this.handleSubnav(this.$route.name)
  },
  methods: {
    /**
     * @method 处理路由表，渲染到侧边栏
     */
    handleRoute () {
      let {routes} = this.$store.getters // JSON.parse(localStorage.getItem('routes'))
      this.routes = routes.filter(item => {
        if (!item.hidden) {
          return item
        }
      })
    },
    /**
     * @method 处理二级菜单导航
     */
    handleSubnav (name) {
      let {routes} = this
      for (let i = 0, len = routes.length; i < len; i++) {
        let children = routes[i].children
        for (let j = 0, l = children.length; j < l; j++) {
          if (children[j].name === name) {
            this.subnav = routes[i].meta.subNavName || []
            this.openSubnav()
            break
          }
        }
      }
    },
    /**
     * @method 展开二级菜单
     */
    openSubnav () {
      let arr = []
      this.subnav.forEach((item, index) => {
        arr.push(index)
      })
      this.activeNames = arr
    }
  },
  watch: {
    $route: {
      handler (val, oldVal) {
        this.handleSubnav(val.name)
      },
      // 深度观察监听
      deep: true
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-sidebar{
        display: flex;
        .menu{
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 120px;
            height: calc(100vh - 70px - 50px);
            background: #273543;
            .menu-top{
                width: 100%;
                color:#cccccc;
                font-size: 14px;
                text-align: center;
                line-height: 50px;
                li{
                    width: 100%;
                    height: 50px;
                    border-top: 1px solid #cccccc;;
                    cursor: pointer;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    i{
                      margin-right: 10px;
                    }
                    &:hover{
                      color: #09f;
                    }
                }
                .menu-active{
                    background: #fff;
                    color: #273543;
                    &:hover{
                      color: #273543;
                    }
                }
            }
        }
        .submenu{
            width: 159px;
            background: #fff;
            padding: 0 15px;
            .el-collapse{
                border-top: 1px;
                .item{
                  cursor: pointer;
                  text-align: center;
                  &:hover{
                    span{
                      color: #09f;
                    }
                    .el-collapse-item-active{
                      color: #273543;
                    }
                  }
                  .el-collapse-item-active{
                    width: 100%;
                    display: inline-block;
                    background: #F0F0F0;
                    border-radius: 2px;
                    text-align: center;
                  }
                }
            }
        }
    }
</style>
