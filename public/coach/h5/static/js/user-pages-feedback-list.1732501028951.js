(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-feedback-list"],{"110c":function(t,e,a){"use strict";var i=a("53f0"),n=a.n(i);n.a},"1ace":function(t,e,a){"use strict";a.r(e);var i=a("abd9"),n=a.n(i);for(var s in i)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(s);e["default"]=n.a},"53f0":function(t,e,a){var i=a("fd32");i.__esModule&&(i=i.default),"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("967d").default;n("6c1ff079",i,!0,{sourceMap:!1,shadowMode:!1})},"6ee0":function(t,e,a){"use strict";a.r(e);var i=a("9d61"),n=a("1ace");for(var s in n)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(s);a("110c");var r=a("828b"),o=Object(r["a"])(n["default"],i["b"],i["c"],!1,null,"41eb2dee",null,!1,i["a"],void 0);e["default"]=o.exports},"9d61":function(t,e,a){"use strict";a.d(e,"b",(function(){return i})),a.d(e,"c",(function(){return n})),a.d(e,"a",(function(){}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",{style:{background:t.pageColor}},[a("fixed",[a("tab",{attrs:{isLine:!0,list:t.tabList,activeIndex:1*t.activeIndex,color:"#9D9D9D",activeColor:t.primaryColor,width:100/t.tabList.length+"%",height:"100rpx",numberType:2},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.handerTabChange.apply(void 0,arguments)}}})],1),a("v-uni-view",{staticClass:"pd-md"},t._l(t.list.data,(function(e,i){return a("v-uni-view",{key:i,staticClass:"pl-lg pr-lg pb-lg fill-base radius-16 mb-md rel item-box",on:{click:function(a){a.stopPropagation(),arguments[0]=a=t.$handleEvent(a),t.$util.goUrl({url:"/user/pages/feedback/detail?id="+e.id})}}},[a("v-uni-view",{staticClass:"abs type-name text-center flex-center"},[a("v-uni-view",{staticClass:"taye-name-bg",style:{backgroundColor:t.primaryColor}}),a("v-uni-text",{staticClass:"c-base f-caption",staticStyle:{"z-index":"9"},style:{color:t.primaryColor}},[t._v(t._s(e.type_name))])],1),a("v-uni-view",{staticClass:"item-nav flex-between "},[a("v-uni-text",{staticClass:"f-paragraph text-bold max-450 ellipsis"},[t._v("订单号 "+t._s(e.order_code||"无"))]),a("v-uni-text",{style:{color:1==e.status?t.primaryColor:"#999"}},[t._v(t._s(1==e.status?"未处理":"已处理"))])],1),a("v-uni-view",{staticClass:"item-cont radius-16 pd-lg"},[a("v-uni-view",{staticClass:"f-paragraph text-bold pb-md"},[t._v("反馈内容")]),a("v-uni-view",{staticClass:"c-paragraph ellipsis-2",staticStyle:{"white-space":"pre-wrap"}},[t._v(t._s(e.content))])],1),a("v-uni-view",{staticClass:"flex pt-lg",staticStyle:{"justify-content":"flex-end"}},[a("v-uni-view",{staticClass:"reply f-desc c-base text-center",style:{backgroundColor:t.primaryColor}},[t._v("查看详情")])],1)],1)})),1),t.loading?a("load-more",{attrs:{noMore:t.list.current_page>=t.list.last_page&&t.list.data.length>0,loading:t.loading}}):t._e(),!t.loading&&t.list.data.length<=0&&1==t.list.current_page?a("abnor",{attrs:{isCenter:!0}}):t._e(),a("v-uni-view",{staticClass:"space-footer"})],1)},n=[]},abd9:function(t,e,a){"use strict";a("6a54");var i=a("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("c223");var n=i(a("2634")),s=i(a("2fdc")),r=a("8f59"),o={data:function(){return{tabList:[{title:"全部反馈",sort:"top desc",sign:1},{title:"未处理",sort:"price",sign:0,number:0},{title:"已处理",sort:"total_sale",sign:0}],activeIndex:0,loading:!0,isLoad:!1,param:{page:1,limit:10,status:0},list:{data:[]}}},computed:(0,r.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(){this.$util.setNavigationBarColor({bg:this.primaryColor}),this.initIndex()},onPullDownRefresh:function(){uni.showNavigationBarLoading(),this.initRefresh(),uni.stopPullDownRefresh()},onReachBottom:function(){this.list.current_page>=this.list.last_page||this.loading||(this.param.page=this.param.page+1,this.loading=!0,this.getList())},methods:{initIndex:function(){var t=arguments,e=this;return(0,s.default)((0,n.default)().mark((function a(){var i;return(0,n.default)().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return i=t.length>0&&void 0!==t[0]&&t[0],a.next=3,e.getList();case 3:i||e.$jweixin.hideOptionMenu();case 4:case"end":return a.stop()}}),a)})))()},initRefresh:function(){this.param.page=1,this.initIndex(!0)},handerTabChange:function(t){this.activeIndex=t,this.param.status=t,this.param.page=1,this.getList()},getList:function(){var t=this;return(0,s.default)((0,n.default)().mark((function e(){var a,i,s;return(0,n.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return a=t.list,i=t.param,e.next=3,t.$api.mine.listFeedback(i);case 3:s=e.sent,1==t.param.page||(s.data=a.data.concat(s.data)),t.list=s,t.tabList[1].number=s.wait,t.isLoad=!0,t.loading=!1,t.$util.hideAll();case 9:case"end":return e.stop()}}),e)})))()}}};e.default=o},fd32:function(t,e,a){var i=a("c86c");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.item-nav[data-v-41eb2dee]{height:%?96?%}.item-box[data-v-41eb2dee]{padding-top:%?80?%}.item-cont[data-v-41eb2dee]{background-color:#f7f8fa;min-height:%?218?%}.type-name[data-v-41eb2dee]{top:0;left:0;width:%?146?%;height:%?47?%;line-height:%?47?%;overflow:hidden;border-bottom-right-radius:%?16?%;border-top-left-radius:%?16?%}.type-name .taye-name-bg[data-v-41eb2dee]{position:absolute;width:100%;height:100%;left:0;top:0;opacity:.1}.reply[data-v-41eb2dee]{width:%?140?%;height:%?56?%;line-height:%?56?%;border-radius:%?56?%}',""]),t.exports=e}}]);