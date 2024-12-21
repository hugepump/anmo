const state = {
  currentIndex: 0
}
const getters = {
  currentIndex: state => {
    return state.currentIndex
  }
}
const mutations = {
  setCurrentIndex (state, index = 0) {
    state.currentIndex = index
  }
}
export default {
  state,
  getters,
  mutations
}
