(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-address-list"],{"100d":function(t,e,a){var i=a("c86c");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.user-address-list .address-icon[data-v-9968d970]{width:%?64?%;height:%?64?%}.user-address-list .address-icon .iconfont[data-v-9968d970]{font-size:%?38?%}.user-address-list .address-info[data-v-9968d970]{max-width:%?600?%}.user-address-list .username[data-v-9968d970]{font-size:%?30?%}.user-address-list .oper-info[data-v-9968d970]{height:%?80?%}',""]),t.exports=e},"590b":function(t,e,a){"use strict";a("6a54");var i=a("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("c223"),a("4626"),a("dd2b");var n=i(a("5de6")),s=i(a("2634")),r=i(a("2fdc")),o=i(a("9b1b")),c=a("8f59"),d={components:{},data:function(){return{options:{},check_id:0,param:{page:1},list:{data:[]},loading:!0,lockTap:!1}},computed:(0,c.mapState)({haveOperItem:function(t){return t.order.haveOperItem},technicianParam:function(t){return t.technician.param}}),onLoad:function(t){var e=t.check,a=void 0===e?0:e,i=t.type,n=void 0===i?0:i;if(t.check=1*a,t.type=1*n,this.options=t,1==a){var s=this.$util.getPage(-1).orderInfo.address_info,r=void 0===s?{id:0}:s,o=r.id;this.check_id=o}this.updateOrderItem({key:"haveOperItem",val:!1}),this.initIndex()},destroyed:function(){var t=this.options.check,e=void 0===t?0:t,a=this.haveOperItem;1==e&&a&&(this.$util.getPage(-1).orderInfo.address_info={},this.$util.back())},onPullDownRefresh:function(){uni.showNavigationBarLoading(),this.initRefresh(),uni.stopPullDownRefresh()},onReachBottom:function(){this.list.current_page>=this.list.last_page||this.loading||(this.param.page=this.param.page+1,this.loading=!0,this.getList())},methods:(0,o.default)((0,o.default)({},(0,c.mapMutations)(["updateUserItem","updateTechnicianItem","updateOrderItem"])),{},{initIndex:function(){var t=arguments,e=this;return(0,r.default)((0,s.default)().mark((function a(){var i;return(0,s.default)().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return i=t.length>0&&void 0!==t[0]&&t[0],a.next=3,e.getList();case 3:e.$util.setNavigationBarColor({bg:e.primaryColor}),i||e.$jweixin.hideOptionMenu();case 5:case"end":return a.stop()}}),a)})))()},initRefresh:function(){this.param.page=1,this.initIndex(!0)},getList:function(){var t=this;return(0,r.default)((0,s.default)().mark((function e(){var a,i,n;return(0,s.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return a=t.list,i=t.param,e.next=3,t.$api.mine.addressList(i);case 3:n=e.sent,1==t.param.page||(n.data=a.data.concat(n.data)),t.list=n,t.loading=!1,t.$util.hideAll();case 7:case"end":return e.stop()}}),e)})))()},toUpdateItem:function(t){var e=this;return(0,r.default)((0,s.default)().mark((function a(){var i,n,r,o,c,d,l,u,p;return(0,s.default)().wrap((function(a){while(1)switch(a.prev=a.next){case 0:if(i=e.list.data[t],n=e.options.check,r=void 0===n?0:n,!r){a.next=7;break}1==r&&(e.$util.getPage(-1).orderInfo.address_info=i,e.$util.back(),e.$util.goUrl({url:1,openType:"navigateBack"})),2==r&&(o=e.$util.pick(i,["lat","lng","address","address_info"]),e.updateUserItem({key:"location",val:o}),c=getCurrentPages(),d=c.length,l="",d>1&&(l=c[c.length-2].route),["pages/technician","user/pages/choose-technician"].includes(l)?(e.$util.getPage(-1).toChooseLocation(1),e.$util.goUrl({url:1,openType:"navigateBack"})):(e.updateUserItem({key:"city_info",val:Object.assign({},e.city_info,{id:0})}),e.updateUserItem({key:"changeAddr",val:!0}),e.$util.goUrl({url:["/pages/technician","/user/pages/choose-technician"][e.options.type],openType:"reLaunch"}))),a.next=11;break;case 7:return u=i.id,p=i.status,a.next=10,e.$api.mine.addressUpdate({id:u,status:0==p?1:0});case 10:e.initRefresh();case 11:case"end":return a.stop()}}),a)})))()},toDel:function(t){var e=this;return(0,r.default)((0,s.default)().mark((function a(){var i,r,o,c,d;return(0,s.default)().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return a.next=2,uni.showModal({title:"提示",content:"请确认是否要删除此数据?"});case 2:if(i=a.sent,r=(0,n.default)(i,2),r[0],o=r[1].confirm,o){a.next=8;break}return a.abrupt("return");case 8:if(c=e.list.data[t].id,d=e.check_id,!e.lockTap){a.next=12;break}return a.abrupt("return");case 12:return e.lockTap=!0,e.$util.showLoading(),a.prev=14,a.next=17,e.$api.mine.addressDel({id:c});case 17:e.$util.hideAll(),e.$util.showToast({title:"删除成功"}),e.list.data.splice(t,1),e.lockTap=!1,c==d&&e.updateOrderItem({key:"haveOperItem",val:!0}),a.next=27;break;case 24:a.prev=24,a.t0=a["catch"](14),setTimeout((function(){e.lockTap=!1,e.$util.hideAll()}),2e3);case 27:case"end":return a.stop()}}),a,null,[[14,24]])})))()},goDetail:function(t){var e=this.list.data[t].id,a="/user/pages/address/edit?id=".concat(e);this.$util.goUrl({url:a})}})};e.default=d},8599:function(t,e,a){"use strict";a.r(e);var i=a("e90f"),n=a("8d54");for(var s in n)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(s);a("ba58");var r=a("828b"),o=Object(r["a"])(n["default"],i["b"],i["c"],!1,null,"9968d970",null,!1,i["a"],void 0);e["default"]=o.exports},"8d54":function(t,e,a){"use strict";a.r(e);var i=a("590b"),n=a.n(i);for(var s in i)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(s);e["default"]=n.a},"8f07":function(t,e,a){var i=a("100d");i.__esModule&&(i=i.default),"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("967d").default;n("14765ed4",i,!0,{sourceMap:!1,shadowMode:!1})},ba58:function(t,e,a){"use strict";var i=a("8f07"),n=a.n(i);n.a},e90f:function(t,e,a){"use strict";a.d(e,"b",(function(){return i})),a.d(e,"c",(function(){return n})),a.d(e,"a",(function(){}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",{staticClass:"user-address-list pt-md",style:{background:t.pageColor}},[t._l(t.list.data,(function(e,i){return a("v-uni-view",{key:i,staticClass:"item-child pl-lg pr-lg fill-base radius-16",class:[{"mt-md":0!=i}],on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toUpdateItem(i)}}},[a("v-uni-view",{staticClass:"flex-warp pt-lg pb-lg b-1px-b"},[a("v-uni-view",{staticClass:"address-icon flex-center c-base radius",style:{background:"linear-gradient(to right, "+t.subColor+", "+t.primaryColor+")"}},[a("i",{staticClass:"iconfont iconjuli"})]),a("v-uni-view",{staticClass:"address-info flex-1 ml-md"},[a("v-uni-view",{staticClass:"flex-y-baseline username c-title text-bold"},[t._v(t._s(e.user_name)),a("v-uni-view",{staticClass:"ml-md f-desc c-caption"},[t._v(t._s(e.mobile))])],1),a("v-uni-view",{staticClass:"f-desc c-title"},[t._v(t._s(e.address+" "+e.address_info))])],1)],1),a("v-uni-view",{staticClass:"oper-info f-paragraph c-caption flex-between"},[a("v-uni-view",{staticClass:"flex-y-center",style:{color:t.options.check&&e.id==t.check_id||!t.options.check&&1==e.status?t.primaryColor:""}},[a("i",{staticClass:"iconfont icon-xuanze mr-sm",class:[{"icon-xuanze-fill":t.options.check&&e.id==t.check_id||!t.options.check&&1==e.status}],style:{color:t.options.check&&e.id==t.check_id||!t.options.check&&1==e.status?t.primaryColor:""}}),t.options.check?[t._v(t._s(e.id==t.check_id?"当前选择地址":"点击选择"))]:[t._v(t._s(1==e.status?"默认地址":"设为默认"))]],2),a("v-uni-view",{staticClass:"flex-center"},[a("v-uni-view",{staticClass:"pl-lg pr-lg",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toDel(i)}}},[t._v("删除")]),a("v-uni-view",{staticClass:"pl-lg",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.goDetail(i)}}},[t._v("编辑")])],1)],1)],1)})),t.loading?a("load-more",{attrs:{noMore:t.list.current_page>=t.list.last_page&&t.list.data.length>0,loading:t.loading}}):t._e(),!t.loading&&t.list.data.length<=0&&1==t.list.current_page?a("abnor",{attrs:{tip:[{text:"您还没有添加地址哦~",color:0}],percent:"calc(100vh - calc(200rpx + env(safe-area-inset-bottom) / 2))"}}):t._e(),a("v-uni-view",{staticClass:"space-max-footer"}),a("fix-bottom-button",{attrs:{text:[{text:"添加新地址",type:"confirm"}],bgColor:"#fff"},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.$util.goUrl({url:"/user/pages/address/edit"})}}})],2)},n=[]}}]);