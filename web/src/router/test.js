const arr = [
  {
    path: '/',
    redirect: '/survey',
    hidden: true // 是否展示在侧边栏的菜单里
  },
  // 概览
  {
    path: '/survey',
    component: 'Layout',
    redirect: '/survey/index',
    meta: {
      menuName: '概览', // 侧边一级菜单标题
      icon: 'icon-gaikuang' // 侧边一级菜单的图标
    },
    children: [
      {
        path: 'index',
        name: 'Survey',
        component: '/survey/index',
        meta: {
          title: '', // 页面头部的标题
          permission: ['view', 'add', 'edit', 'del', 'outport']
        }
      }
    ]
  },
  // 名片
  {
    path: '/businessCard',
    component: 'Layout',
    redirect: '/businessCard/manage',
    meta: { // 所有要展示在菜单里的路由都添加在这边
      menuName: '名片', // 侧边一级菜单标题
      icon: 'icon-mingpian', // 侧边一级菜单的图标
      subNavName: [
        {
          name: '名片管理', // 二级菜单的下拉的标题
          url: [
            {
              name: '名片管理', // 二级菜单的子菜单的标题
              url: '/businessCard/manage' // 二级菜单的子菜单的路由
            },
            {
              name: '印象标签', // 二级菜单的子菜单的标题
              url: '/businessCard/tag' // 二级菜单的子菜单的路由
            }
          ]
        },
        {
          name: '名片设置',
          url: [
            {
              name: '手机端创建设置',
              url: '/businessCard/mobileSet'
            },
            {
              name: '获客功能设置',
              url: '/businessCard/clientSet'
            }
          ]
        }
      ]
    },
    children: [ // 子菜单路由表
      {
        path: 'manage',
        name: 'Manage',
        component: '/businessCard/manage',
        meta: {
          title: '名片设置', // 页面头部的标题
          permission: ['view', 'add', 'edit', 'del', 'outport'] // 操作按钮的权限
        }
      },
      {
        path: 'tag',
        name: 'Tag',
        component: '/businessCard/tag',
        meta: {
          title: '名片设置',
          permission: ['view', 'add', 'edit', 'del', 'outport']
        }
      },
      {
        path: 'mobileSet',
        name: 'MobileSet',
        component: '/businessCard/mobileSet',
        meta: {
          title: '名片设置',
          permission: ['view', 'add', 'edit', 'del', 'outport']
        }
      },
      {
        path: 'clientSet',
        name: 'ClientSet',
        component: '/businessCard/clientSet',
        meta: {
          title: '名片设置',
          permission: ['view', 'add', 'edit', 'del', 'outport']
        }
      }
    ]
  },
  {
    path: '*',
    redirect: '/404',
    hidden: true
  }
]
