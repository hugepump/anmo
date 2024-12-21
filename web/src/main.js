// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import ElementUI from 'element-ui'
import router from './router'
import 'element-ui/lib/theme-chalk/index.css'
import store from './store'
import {api} from './api' // 接口
import i18n from './i18n' // 语言包
import routes from './permission'
import './components/basics'
import reg from './utils/reg'

Vue.config.productionTip = false
Vue.use(ElementUI, { size: 'small', zIndex: 3000 })
Vue.prototype.$api = api
Vue.prototype.$reg = reg
router.beforeEach((to, from, next) => {
  if (!store.getters.isAuth) {
    store.dispatch('getUserPromission', {to, next, routes})
  } else {
    next()
  }
})

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  i18n,
  store,
  components: { App },
  template: '<App/>'
})
