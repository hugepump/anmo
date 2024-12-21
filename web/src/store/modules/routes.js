// import {api} from '@/api'
import router from '@/router'
import Layout from '@/components/layout'
const _import = require('../../router/_import_' + process.env.NODE_ENV)
const state = {
  isAuth: false,
  routes: []
}
const getters = {
  isAuth: state => {
    return state.isAuth
  },
  routes: state => {
    return state.routes
  }
}
const mutations = {
  saveRoutes (state, routes = []) {
    state.routes = routes
    state.isAuth = true
  }
}
const actions = {
  getUserPromission ({commit}, obj) {
    commit('saveRoutes', obj.routes)
    routerGo(obj.routes, obj)
    // 正式请求接口获取路由
    /* api.getRoutes().then(res => {
      commit('saveRoutes', res.data)
      routerGo(res.data, obj)
    })
    */
  }
}

export default {
  state,
  getters,
  mutations,
  actions
}

function routerGo (routes, obj) {
  let getRouter = filterAsyncRouter(routes) // 过滤路由
  router.options.routes.push(...getRouter)
  router.addRoutes(getRouter) // 动态添加路由
  localStorage.setItem('routes', JSON.stringify(getRouter))
  obj.next({...obj.to, replace: true})
}
function filterAsyncRouter (asyncRouterMap) { // 遍历后台传来的路由字符串，转换为组件对象
  const accessedRouters = asyncRouterMap.filter(route => {
    if (route.component) {
      if (route.component === 'Layout') { // Layout组件特殊处理
        route.component = Layout
      } else {
        route.component = _import(route.component)
      }
    }
    if (route.children && route.children.length) {
      route.children = filterAsyncRouter(route.children)
    }
    return true
  })
  return accessedRouters
}
