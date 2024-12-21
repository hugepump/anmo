import Vue from 'vue'
import Vuex from 'vuex'
import routes from './modules/routes'
import operate from './modules/operate'

Vue.use(Vuex)

const store = new Vuex.Store({
  modules: {
    routes,
    operate
  },
  state: {
  },
  getters: {
  },
  mutations: {
  },
  actions: {}
})

export default store
