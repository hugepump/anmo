(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["technician-pages-credit"],{"083e":function(t,i,e){"use strict";e("6a54");var a=e("f5bd").default;Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0,e("c223");var n=a(e("2634")),s=a(e("2fdc")),c=a(e("9b1b")),r=e("8f59"),l={components:{},data:function(){return{isLoad:!1,orderType:{1:["iconweiguijilu","收入积累","完成一笔主服务订单","#000"],2:["iconweiguijilu","收入积累","完成一笔加钟订单","#000"],3:["iconqinheli","勤奋值","","#000"],4:["iconweiguijilu","收入积累","促成一笔复购单","#000"],5:["iconshourujilei","亲和力","收到一个好评","#000"],6:["iconqinfenzhi","违规记录","用户退单一笔","#E93B2E"],7:["iconqinfenzhi","违规记录","".concat(this.$t("action.attendantName"),"拒单一笔"),"#E93B2E"],8:["iconqinfenzhi","违规记录","用户差评一个","#E93B2E"]},param:{page:1},list:{data:[]},loading:!0}},computed:(0,r.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(){this.initIndex()},destroyed:function(){this.$util.back()},onPullDownRefresh:function(){uni.showNavigationBarLoading(),this.initRefresh(),uni.stopPullDownRefresh()},onReachBottom:function(){this.list.current_page>=this.list.last_page||this.loading||(this.param.page=this.param.page+1,this.loading=!0,this.getList())},methods:(0,c.default)((0,c.default)({},(0,r.mapMutations)([])),{},{initIndex:function(){var t=arguments,i=this;return(0,s.default)((0,n.default)().mark((function e(){var a;return(0,n.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return a=t.length>0&&void 0!==t[0]&&t[0],e.next=3,i.getList();case 3:i.$util.setNavigationBarColor({bg:i.primaryColor}),i.isLoad=!0,i.$util.hideAll(),a||i.$jweixin.hideOptionMenu();case 7:case"end":return e.stop()}}),e)})))()},initRefresh:function(){this.param.page=1,this.initIndex(!0)},getList:function(t){var i=this;return(0,s.default)((0,n.default)().mark((function e(){var a,s,c;return(0,n.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t&&(i.param.page=1,i.list.data=[],uni.pageScrollTo({scrollTop:0})),a=i.list,i.activeIndex,s=i.$util.deepCopy(i.param),e.next=5,i.$api.technician.getCreditValueData(s);case 5:c=e.sent,1==i.param.page||(c.data=a.data.concat(c.data)),i.list=c,i.loading=!1,i.$util.hideAll();case 9:case"end":return e.stop()}}),e)})))()}})};i.default=l},4925:function(t,i,e){var a=e("c86c");i=a(!1),i.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.technician-credit-score .credit-score-count[data-v-43c909e9]{width:%?750?%;height:%?664?%;top:0;border-radius:0 0 %?18?% %?18?%}.technician-credit-score .credit-score-count .credit-bg[data-v-43c909e9]{width:100%;height:100%;top:0}.technician-credit-score .credit-score-count .score-bg[data-v-43c909e9]{width:%?298?%;height:%?296?%}.technician-credit-score .credit-score-count .score-bg .score-text[data-v-43c909e9]{font-size:%?50?%;height:%?54?%}.technician-credit-score .credit-score-count .rule-btn[data-v-43c909e9]{top:%?38?%;right:0;width:%?145?%;height:%?54?%;border-radius:%?54?% 0 0 %?54?%;background:hsla(0,0%,100%,.2);z-index:2}.technician-credit-score .credit-score-count .iconfont[data-v-43c909e9]{font-size:%?46?%}.technician-credit-score .count-list .count-bg[data-v-43c909e9]{width:100%;height:%?188?%;top:0;left:0;opacity:.2;border-radius:%?20?% %?20?% 0 0}.technician-credit-score .count-list .line[data-v-43c909e9]{width:%?6?%;height:%?28?%}.technician-credit-score .count-list .title[data-v-43c909e9]{width:%?190?%;height:%?40?%}.technician-credit-score .count-list .title .iconfont[data-v-43c909e9]{font-size:%?24?%;margin-top:%?2?%}.technician-credit-score .count-list .text[data-v-43c909e9]{width:calc(100% - %?190?%)}.technician-credit-score .count-list .icon-info[data-v-43c909e9]{width:%?80?%;height:%?80?%}.technician-credit-score .count-list .icon-info .iconfont[data-v-43c909e9]{font-size:%?40?%}',""]),t.exports=i},"5b3e":function(t,i,e){"use strict";e.r(i);var a=e("083e"),n=e.n(a);for(var s in a)["default"].indexOf(s)<0&&function(t){e.d(i,t,(function(){return a[t]}))}(s);i["default"]=n.a},a3d9:function(t,i,e){"use strict";e.r(i);var a=e("defa"),n=e("5b3e");for(var s in n)["default"].indexOf(s)<0&&function(t){e.d(i,t,(function(){return n[t]}))}(s);e("db0b");var c=e("828b"),r=Object(c["a"])(n["default"],a["b"],a["c"],!1,null,"43c909e9",null,!1,a["a"],void 0);i["default"]=r.exports},db0b:function(t,i,e){"use strict";var a=e("fceb"),n=e.n(a);n.a},defa:function(t,i,e){"use strict";e.d(i,"b",(function(){return a})),e.d(i,"c",(function(){return n})),e.d(i,"a",(function(){}));var a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.isLoad?e("v-uni-view",{staticClass:"technician-credit-score",style:{background:t.pageColor}},[e("v-uni-view",{staticClass:"credit-score-count f-desc c-base abs",style:{background:t.primaryColor}},[e("v-uni-image",{staticClass:"credit-bg abs",attrs:{src:"https://lbqny.migugu.com/admin/anmo/mine/credit-bg.png"}}),e("v-uni-view",{staticClass:"rule-btn flex-center f-desc c-base abs",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.$refs.show_rule_item.open()}}},[t._v("规则说明")]),e("v-uni-view",{staticClass:"flex-center abs",staticStyle:{width:"100%",height:"580rpx"}},[e("v-uni-view",{staticClass:"score-bg rel flex-center flex-column"},[e("v-uni-image",{staticClass:"score-bg abs",attrs:{src:"https://lbqny.migugu.com/admin/anmo/mine/credit-score-bg.png"}}),e("v-uni-view",{staticClass:"score-text text-bold mb-sm"},[t._v(t._s(t.list.credit_value))]),e("v-uni-view",{staticClass:"mb-md"},[t._v("信用分")])],1)],1),e("v-uni-view",{staticClass:"credit-bg"},[e("v-uni-view",{staticClass:"flex-center flex-column"},[e("v-uni-view",{staticClass:"space-md"}),e("i",{staticClass:"iconfont iconweiguijilu mb-sm"}),e("v-uni-view",[t._v("收入积累")])],1),e("v-uni-view",{staticClass:"flex-between mt-md",staticStyle:{height:"296rpx"}},[e("v-uni-view",{staticClass:"flex-center flex-column",staticStyle:{width:"44%"}},[e("i",{staticClass:"iconfont iconshourujilei mb-sm"}),e("v-uni-view",[t._v("亲和力")])],1),e("v-uni-view",{staticClass:"flex-center flex-column",staticStyle:{width:"44%"}},[e("i",{staticClass:"iconfont iconqinheli mb-sm"}),e("v-uni-view",[t._v("勤奋值")])],1)],1),e("v-uni-view",{staticClass:"flex-center flex-column mt-md",staticStyle:{height:"120rpx"}},[e("i",{staticClass:"iconfont iconqinfenzhi mb-sm"}),e("v-uni-view",[t._v("违规记录")])],1)],1)],1),e("v-uni-view",{staticClass:"rel"},[e("v-uni-view",{staticStyle:{height:"566rpx"}}),e("v-uni-view",{staticClass:"count-list fill-base mt-md ml-md mr-md radius-20 rel"},[e("v-uni-view",{staticClass:"count-bg abs",style:{background:"linear-gradient(180deg, "+t.primaryColor+" 0%, rgba(255,255,255,0) 100%)"}}),e("v-uni-view",{staticClass:"flex-y-center pd-lg f-mini-title c-title text-bold"},[e("v-uni-view",{staticClass:"line radius mr-md",style:{background:t.primaryColor}}),t._v("四维数据解读")],1),e("v-uni-view",{staticClass:"pd-lg"},[e("v-uni-view",{staticClass:"flex-warp f-paragraph"},[e("v-uni-view",{staticClass:"title flex-y-center"},[e("i",{staticClass:"iconfont iconxulie mr-sm"}),e("v-uni-view",{staticClass:"c-title text-bold"},[t._v("收入积累")])],1),e("v-uni-view",{staticClass:"text c-paragraph"},[t._v("包含主订单、加钟订单、复购订单的金额值")])],1),e("v-uni-view",{staticClass:"flex-warp f-paragraph mt-lg"},[e("v-uni-view",{staticClass:"title flex-y-center"},[e("i",{staticClass:"iconfont iconxulie mr-sm"}),e("v-uni-view",{staticClass:"c-title text-bold"},[t._v("勤奋值")])],1),e("v-uni-view",{staticClass:"text c-paragraph"},[t._v("服务时长分钟数积累")])],1),e("v-uni-view",{staticClass:"flex-warp f-paragraph mt-lg"},[e("v-uni-view",{staticClass:"title flex-y-center"},[e("i",{staticClass:"iconfont iconxulie mr-sm"}),e("v-uni-view",{staticClass:"c-title text-bold"},[t._v("亲和力")])],1),e("v-uni-view",{staticClass:"text c-paragraph"},[t._v("用户的好评数量累计")])],1),e("v-uni-view",{staticClass:"flex-warp f-paragraph mt-lg"},[e("v-uni-view",{staticClass:"title flex-y-center"},[e("i",{staticClass:"iconfont iconxulie mr-sm"}),e("v-uni-view",{staticClass:"c-title text-bold"},[t._v("违规记录")])],1),e("v-uni-view",{staticClass:"text c-paragraph"},[t._v("包含退单、拒单行为和差评记录")])],1)],1)],1),e("v-uni-view",{staticClass:"count-list fill-base mt-md ml-md mr-md radius-20 rel"},[e("v-uni-view",{staticClass:"count-bg abs",style:{background:"linear-gradient(180deg, "+t.primaryColor+" 0%, rgba(255,255,255,0) 100%)"}}),e("v-uni-view",{staticClass:"flex-y-center pd-lg f-mini-title c-title text-bold"},[e("v-uni-view",{staticClass:"line radius mr-md",style:{background:t.primaryColor}}),t._v("信用分记录")],1),t._l(t.list.data,(function(i,a){return e("v-uni-view",{key:a,staticClass:"flex-warp pd-lg"},[e("v-uni-view",{staticClass:"icon-info flex-center radius",style:{background:"违规记录"===t.orderType[i.type][1]?t.orderType[i.type][3]:t.primaryColor}},[e("i",{staticClass:"iconfont c-base",class:t.orderType[i.type][0]})]),e("v-uni-view",{staticClass:"flex-1 ml-md"},[e("v-uni-view",{staticClass:"flex-between"},[e("v-uni-view",[e("v-uni-view",{staticClass:"f-mini-title c-title text-bold",staticStyle:{"line-height":"34rpx"}},[t._v(t._s(t.orderType[i.type][1]))]),e("v-uni-view",{staticClass:"f-desc c-caption mt-sm"},[t._v(t._s(3===i.type?"完成一笔服务订单，增加时长"+i.order_price+"分钟":t.orderType[i.type][2]))])],1),e("v-uni-view",{staticClass:"f-title text-bold ml-lg",style:{color:t.orderType[i.type][3]}},[t._v(t._s("违规记录"===t.orderType[i.type][1]?"":"+")+t._s(i.value))])],1),e("v-uni-view",{staticClass:"f-desc c-caption",staticStyle:{"margin-top":"5rpx"}},[t._v(t._s(i.create_time))])],1)],1)})),t.loading?e("load-more",{attrs:{noMore:t.list.current_page>=t.list.last_page&&t.list.data.length>0,loading:t.loading}}):t._e(),!t.loading&&t.list.data.length<=0&&1==t.list.current_page?e("abnor"):t._e()],2)],1),e("v-uni-view",{staticClass:"space-footer"}),e("uni-popup",{ref:"show_rule_item",attrs:{type:"center",maskClick:!1}},[e("v-uni-view",{staticClass:"common-popup-content fill-base pd-lg radius-34",staticStyle:{width:"700rpx"}},[e("v-uni-view",{staticClass:"title"},[t._v("规则说明")]),e("v-uni-view",{staticClass:"f-desc c-title mt-lg",staticStyle:{width:"100%","max-height":"55vh",overflow:"auto"}},[e("v-uni-text",{staticStyle:{"word-break":"break-all"},attrs:{decode:"emsp"}},[t._v(t._s(t.list.config.text))])],1),e("v-uni-view",{staticClass:"button"},[e("v-uni-view",{staticClass:"item-child c-base",style:{background:t.primaryColor,color:"#fff"},on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.$refs.show_rule_item.close()}}},[t._v("知道了")])],1)],1)],1)],1):t._e()},n=[]},fceb:function(t,i,e){var a=e("4925");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("967d").default;n("fce4897e",a,!0,{sourceMap:!1,shadowMode:!1})}}]);