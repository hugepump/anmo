import axios from 'axios'
import qs from 'qs'
// import router from '../router'
// api 模块化
import apis from './modules'

axios.defaults.timeout = 10000
axios.defaults.baseURL = process.env.NODE_ENV === 'development' ? 'http://47.100.26.121:85' : ''
axios.interceptors.request.use(config => {
  if (localStorage.getItem('token')) {
    config.headers = {
      'Authorization': localStorage.getItem('token'),
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  }
  return config
}, error => {
  return Promise.reject(error)
})
axios.interceptors.response.use(res => {
//   if (res.data.code === '401') { // token验证不合法或者没有
//     localStorage.removeItem('token') // 删除token
//     localStorage.removeItem('ms_username') // 删除用户名
//     router.push('/login')
//   }
  return res
}, err => {
  return Promise.resolve(err.response)
})

/**
 * 封装get方法
 * @param url
 * @param data
 * @returns {Promise}
 */

export function get (url, params = {}) {
  return new Promise((resolve, reject) => {
    axios.get(url, {
      params: params
    })
      .then(response => {
        resolve(response.data)
      })
      .catch(err => {
        reject(err)
      })
  })
}

/**
 * 封装post请求
 * @param url
 * @param data
 * @returns {Promise}
 */

export function post (url, data = {}) {
  return new Promise((resolve, reject) => {
    axios.post(url, qs.stringify(data))
      .then(response => {
        resolve(response.data)
      }, err => {
        reject(err)
      })
  })
}

/**
 * 封装patch请求
 * @param url
 * @param data
 * @returns {Promise}
 */

export function patch (url, data = {}) {
  return new Promise((resolve, reject) => {
    axios.patch(url, data)
      .then(response => {
        resolve(response.data)
      }, err => {
        reject(err)
      })
  })
}

/**
 * 封装put请求
 * @param url
 * @param data
 * @returns {Promise}
 */

export function put (url, data = {}) {
  return new Promise((resolve, reject) => {
    axios.put(url, data)
      .then(response => {
        resolve(response.data)
      }, err => {
        reject(err)
      })
  })
}
/**
 * @method 获取七牛token
 * @param url
 * @param data
 * @returns {Promise}
 */
export function getQiniuToken (url, data = {}) {
  return new Promise((resolve, reject) => {
    axios.get(url, data)
      .then(response => {
        resolve(response.data)
      }, err => {
        reject(err)
      })
  })
}

/**
* 获取数据的接口
*/
export const api = {
  ...apis
}
