(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-shield"],{"0bf4":function(t,e,a){"use strict";var i=a("0e3b"),n=a.n(i);n.a},"0e3b":function(t,e,a){var i=a("2611");i.__esModule&&(i=i.default),"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("967d").default;n("7f48dd91",i,!0,{sourceMap:!1,shadowMode:!1})},"0f4d":function(t,e,a){"use strict";a("6a54");var i=a("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("bf0f"),a("18f7"),a("de6c"),a("c223"),a("dd2b");var n=i(a("2634")),r=i(a("2fdc")),s=i(a("9b1b")),o=a("8f59"),l={data:function(){return{isLoad:!1,tabList:[{id:2,title:"不喜欢Ta"},{id:1,title:"不想看Ta的作品"}],activeIndex:0,loading:!0,param:{page:1},list:{data:[]},lockTap:!1}},computed:(0,o.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(){this.$util.setNavigationBarColor({bg:this.primaryColor}),this.initIndex()},onPullDownRefresh:function(){uni.showNavigationBarLoading(),this.initRefresh(),uni.stopPullDownRefresh()},onReachBottom:function(){this.list.current_page>=this.list.last_page||this.loading||(this.param.page=this.param.page+1,this.loading=!0,this.getList())},methods:(0,s.default)((0,s.default)((0,s.default)({},(0,o.mapActions)(["getConfigInfo"])),(0,o.mapMutations)(["updateUserItem"])),{},{initIndex:function(){var t=arguments,e=this;return(0,r.default)((0,n.default)().mark((function a(){var i;return(0,n.default)().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return i=t.length>0&&void 0!==t[0]&&t[0],a.next=3,Promise.all([e.getConfigInfo(),e.getList()]);case 3:e.isLoad=!0,i||e.$jweixin.hideOptionMenu();case 5:case"end":return a.stop()}}),a)})))()},handerTabChange:function(t){this.activeIndex=t,this.param.page=1,this.getList()},initRefresh:function(){this.param.page=1,this.initIndex(!0)},getList:function(){var t=this;return(0,r.default)((0,n.default)().mark((function e(){var a,i,r,s,o,l;return(0,n.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return a=t.list,i=t.param,r=t.tabList,s=t.activeIndex,o=r[s].id,i.type=o,e.next=5,t.$api.mine.shieldCoachList(i);case 5:l=e.sent,1==t.param.page||(l.data=a.data.concat(l.data)),t.list=l,t.loading=!1,t.$util.hideAll();case 9:case"end":return e.stop()}}),e)})))()},toRemove:function(t){var e=this;return(0,r.default)((0,n.default)().mark((function a(){var i,r;return(0,n.default)().wrap((function(a){while(1)switch(a.prev=a.next){case 0:if(i=e.list.data[t].id,r=e.tabList[e.activeIndex].id,!e.lockTap){a.next=4;break}return a.abrupt("return");case 4:return e.lockTap=!0,e.$util.showLoading(),a.prev=6,a.next=9,e.$api.mine.shieldCoachDel({id:i});case 9:e.$util.hideAll(),e.$util.showToast({title:"移除成功"}),e.list.data.splice(t,1),e.lockTap=!1,e.updateUserItem({key:"haveShieldOper",val:r}),a.next=19;break;case 16:a.prev=16,a.t0=a["catch"](6),setTimeout((function(){e.lockTap=!1,e.$util.hideAll()}),2e3);case 19:case"end":return a.stop()}}),a,null,[[6,16]])})))()}})};e.default=l},2611:function(t,e,a){var i=a("c86c");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.user-shield .list-item .avatar[data-v-6b9f3da2]{width:%?124?%;height:%?124?%}.user-shield .list-item .text[data-v-6b9f3da2]{color:#4d4d4d;margin-top:%?6?%}.user-shield .list-item .remove-btn[data-v-6b9f3da2]{width:%?146?%;height:%?56?%;-webkit-transform:rotate(1turn);transform:rotate(1turn)}',""]),t.exports=e},3695:function(t,e,a){"use strict";a.d(e,"b",(function(){return i})),a.d(e,"c",(function(){return n})),a.d(e,"a",(function(){}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return t.isLoad?a("v-uni-view",{staticClass:"user-shield",style:{background:t.pageColor,paddingTop:t.configInfo.plugAuth.dynamic?"":"1rpx"}},[t.configInfo.plugAuth.dynamic?a("fixed",[a("tab",{attrs:{list:t.tabList,activeIndex:1*t.activeIndex,activeColor:t.primaryColor,width:"50%",height:"100rpx",color:"#999999"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.handerTabChange.apply(void 0,arguments)}}})],1):t._e(),t._l(t.list.data,(function(e,i){return a("v-uni-view",{key:i,staticClass:"list-item flex-center fill-base mt-md ml-md mr-md pd-lg radius-16"},[a("v-uni-image",{staticClass:"avatar radius",attrs:{mode:"aspectFill",src:e.work_img}}),a("v-uni-view",{staticClass:"flex-1 flex-between ml-md"},[a("v-uni-view",{staticClass:"f-title c-black text-bold max-300 ellipsis"},[t._v(t._s(e.coach_name))]),a("v-uni-view",{staticClass:"remove-btn flex-center f-desc radius",style:{color:t.primaryColor,border:"1rpx solid "+t.primaryColor},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toRemove(i)}}},[t._v("移除列表")])],1)],1)})),t.loading?a("load-more",{attrs:{noMore:t.list.current_page>=t.list.last_page&&t.list.data.length>0,loading:t.loading}}):t._e(),!t.loading&&t.list.data.length<=0&&1==t.list.current_page?a("abnor",{attrs:{type:"USER",isCenter:!0}}):t._e(),a("v-uni-view",{staticClass:"space-footer"})],2):t._e()},n=[]},"900c":function(t,e,a){"use strict";a.r(e);var i=a("3695"),n=a("9081");for(var r in n)["default"].indexOf(r)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(r);a("0bf4");var s=a("828b"),o=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"6b9f3da2",null,!1,i["a"],void 0);e["default"]=o.exports},9081:function(t,e,a){"use strict";a.r(e);var i=a("0f4d"),n=a.n(i);for(var r in i)["default"].indexOf(r)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(r);e["default"]=n.a}}]);