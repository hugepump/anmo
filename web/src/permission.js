export default [
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
      menuName: 'Survey', // 一级菜单标题
      icon: 'icon-gaikuang' // 一级菜单的图标
    },
    children: [
      {
        path: 'index',
        name: 'Survey',
        component: '/survey/index',
        meta: {
          title: '', // 页面头部的标题
          isOnly: true, // 单独页面
          auth: ['view', 'add', 'edit', 'del', 'outport'], // 单独页面按钮操作权限
          pagePermission: []
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
      menuName: 'BusinessCard', // 一级菜单标题
      icon: 'icon-mingpian', // 一级菜单的图标
      subNavName: [
        {
          name: 'CardManage', // 二级菜单的下拉的标题
          url: [
            {
              name: 'CardManage', // 三级菜单的标题
              url: '/businessCard/manage' // 三级菜单的路由
            },
            {
              name: 'ImpressionLabel', // 三级菜单的标题
              url: '/businessCard/tag' // 三级菜单的路由
            }
          ]
        },
        {
          name: 'Cardset',
          url: [
            {
              name: 'MobileSet',
              url: '/businessCard/mobileSet'
            },
            {
              name: 'ClientSet',
              url: '/businessCard/clientSet'
            }
          ]
        }
      ]
    },
    children: [ // 子菜单路由表
      // 名片管理
      {
        path: 'manage',
        name: 'CardManage',
        component: '/businessCard/manage/index',
        meta: {
          title: 'Cardset', // 页面头部的标题
          isOnly: false, // 多页面
          auth: [],
          pagePermission: [ // 页面权限
            {
              title: 'Cardset',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            },
            {
              title: 'DefaultContent',
              index: 1,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 印象标签
      {
        path: 'tag',
        name: 'ImpressionLabel',
        component: '/businessCard/manage/tag',
        meta: {
          title: 'Cardset',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'ImpressionLabel',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 手机端创建设置
      {
        path: 'mobileSet',
        name: 'MobileSet',
        component: '/businessCard/set/mobileSet',
        meta: {
          title: 'Cardset',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'MobileSet',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 获客功能设置
      {
        path: 'clientSet',
        name: 'ClientSet',
        component: '/businessCard/set/clientSet',
        meta: {
          title: 'Cardset',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'Authorisation',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      }
    ]
  },
  // 商城
  {
    path: '/malls',
    component: 'Layout',
    redirect: '/malls/list',
    meta: { // 所有要展示在菜单里的路由都添加在这边
      menuName: 'Malls', // 一级菜单标题
      icon: 'icon-gouwudai', // 一级菜单的图标
      subNavName: [
        {
          name: 'MallsManage', // 二级菜单的下拉的标题
          url: [
            {
              name: 'GoodsList', // 三级菜单的标题
              url: '/malls/list' // 三级菜单的路由
            },
            {
              name: 'GoodsClassify', // 三级菜单的标题
              url: '/malls/classify' // 三级菜单的路由
            }
          ]
        },
        {
          name: 'OrderManage',
          url: [
            {
              name: 'OrderManage',
              url: '/malls/orderManage'
            },
            {
              name: 'RefundManage',
              url: '/malls/refundManage'
            }
          ]
        },
        {
          name: 'MallsSet',
          url: [
            {
              name: 'DealSet',
              url: '/malls/dealSet'
            },
            {
              name: 'VirtualPaymentSet',
              url: '/malls/virtualPayment'
            },
            {
              name: 'StaffChoiceGoods',
              url: '/malls/staffGoods'
            }
          ]
        }
      ]
    },
    children: [ // 子菜单路由表
      // 商品列表
      {
        path: 'list',
        name: 'GoodsList',
        component: '/malls/goods/list',
        meta: {
          title: 'MallsSet', // 页面头部的标题
          isOnly: false, // 多页面
          auth: [],
          pagePermission: [ // 页面权限
            {
              title: 'GoodsList',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 商品分类
      {
        path: 'classify',
        name: 'GoodsClassify',
        component: '/malls/goods/classify',
        meta: {
          title: 'MallsSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'GoodsClassify',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 订单管理
      {
        path: 'orderManage',
        name: 'OrderManage',
        component: '/malls/order/manage',
        meta: {
          title: 'MallsSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'OrderManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 退款管理
      {
        path: 'refundManage',
        name: 'RefundManage',
        component: '/malls/order/refund',
        meta: {
          title: 'MallsSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'RefundManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 交易设置
      {
        path: 'dealSet',
        name: 'DealSet',
        component: '/malls/set/deal',
        meta: {
          title: 'MallsSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'OrderOvertime',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            },
            {
              title: 'PickUpSet',
              index: 1,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 虚拟支付设置
      {
        path: 'virtualPayment',
        name: 'VirtualPaymentSet',
        component: '/malls/set/payment',
        meta: {
          title: 'MallsSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'VirtualPaymentSet',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      // 员工选择商品
      {
        path: 'staffGoods',
        name: 'StaffChoiceGoods',
        component: '/malls/set/staffGoods',
        meta: {
          title: 'MallsSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'StaffChoiceGoods',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      }
    ]
  },
  // 动态
  {
    path: '/dynamic',
    component: 'Layout',
    redirect: '/dynamic/manage',
    meta: {
      menuName: 'Dynamic',
      icon: 'icon-dongtai',
      subNavName: [
        {
          name: 'DynamicManage', // 二级菜单的下拉的标题
          url: [
            {
              name: 'DynamicManage', // 三级菜单的标题
              url: '/dynamic/manage' // 三级菜单的路由
            },
            {
              name: 'CommentManage', // 三级菜单的标题
              url: '/dynamic/comment' // 三级菜单的路由
            }
          ]
        }
      ]
    },
    children: [
      {
        path: 'manage',
        name: 'DynamicManage',
        component: '/dynamic/manage',
        meta: {
          title: 'DynamicSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'DynamicManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      },
      {
        path: 'comment',
        name: 'CommentManage',
        component: '/dynamic/comment',
        meta: {
          title: 'DynamicSet',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'CommentManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport'] // 按钮操作权限
            }
          ]
        }
      }
    ]
  },
  // 官网
  {
    path: '/website',
    component: 'Layout',
    redirect: '/website/proManage',
    meta: {
      menuName: 'Website',
      icon: 'icon-guanwang',
      subNavName: [
        {
          name: 'ProductManage', // 产品管理
          url: [
            {
              name: 'ProClassfiyManage',
              url: '/website/proManage'
            },
            {
              name: 'ProductList',
              url: '/website/proList'
            }
          ]
        },
        {
          name: 'NewsList', // 新闻列表
          url: [
            {
              name: 'NewsClassfiyManage',
              url: '/website/newsManage'
            },
            {
              name: 'NewsList',
              url: '/website/newsList'
            }
          ]
        },
        {
          name: 'CaseManage', // 案列管理
          url: [
            {
              name: 'CaseClassfiyManage',
              url: '/website/caseManage'
            },
            {
              name: 'CaseManage',
              url: '/website/case'
            }
          ]
        },
        {
          name: 'OtherFunctions', // 其他功能
          url: [
            {
              name: 'About',
              url: '/website/about'
            },
            {
              name: 'DevHistory',
              url: '/website/history'
            },
            {
              name: 'BusinessScope',
              url: '/website/business'
            },
            {
              name: 'Recruit',
              url: '/website/recruit'
            }
          ]
        }
      ]
    },
    children: [
      {
        path: 'proManage',
        name: 'ProClassfiyManage', // 产品分类管理
        component: '/website/product/manage',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'ProClassfiyManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'proList',
        name: 'ProductList', // 产品列表
        component: '/website/product/list',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'ProductList',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'newsManage',
        name: 'NewsClassfiyManage', // 新闻分类管理
        component: '/website/news/manage',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'NewsClassfiyManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'newsList',
        name: 'NewsList', // 新闻列表
        component: '/website/news/list',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'NewsList',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'caseManage',
        name: 'CaseClassfiyManage', // 案列分类管理
        component: '/website/case/manage',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'CaseClassfiyManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'case',
        name: 'CaseManage', // 案列管理
        component: '/website/case/case',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'CaseManage',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'about',
        name: 'About', // 其他关于我们
        component: '/website/other/about',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'About',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'history',
        name: 'DevHistory', // 其他发展历程
        component: '/website/other/history',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'DevHistory',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'business',
        name: 'BusinessScope', // 其他业务范围
        component: '/website/other/business',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'BusinessScope',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
        }
      },
      {
        path: 'recruit',
        name: 'Recruit', // 其他人才招聘
        component: '/website/other/recruit',
        meta: {
          title: 'WebsiteManage',
          isOnly: false,
          auth: [],
          pagePermission: [
            {
              title: 'Recruit',
              index: 0,
              auth: ['view', 'add', 'edit', 'del', 'outport']
            }
          ]
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
