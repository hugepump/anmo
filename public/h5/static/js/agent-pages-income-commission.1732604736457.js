(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["agent-pages-income-commission"],{1343:function(t,e,i){"use strict";var a=i("f5cc6"),n=i.n(a);n.a},"58e2":function(t,e,i){"use strict";i.r(e);var a=i("9380"),n=i.n(a);for(var s in a)["default"].indexOf(s)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(s);e["default"]=n.a},9380:function(t,e,i){"use strict";i("6a54");var a=i("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("c223");var n=a(i("9b1b")),s=a(i("2634")),c=a(i("2fdc")),o=i("8f59"),r=a(i("ca81")),l=a(i("36a0")),u={components:{wPicker:l.default},data:function(){return{isLoad:!1,popupHeight:"",curDay:"",curMonth:"",startYear:"",showKey:"",showDate:!1,time_text:"",prev_time:{activeIndex:0,month:"",start_time:"",end_time:""},check_time:{activeIndex:0,month:"",start_time:"",end_time:""},activeIndex:0,tabList:[{id:1,title:"月份选择"},{id:2,title:"自定义时间"}],typeIndex:0,typeList:[{title:"全部",id:0},{title:"未到账",id:1},{title:"已到账",id:2}],statusType:["","未到账","已到账"],cityType:["","城市","区县","省"],param:{page:1},list:{data:[]},loading:!0}},computed:(0,o.mapState)({}),onLoad:function(){var t=this;return(0,c.default)((0,s.default)().mark((function e(){var i;return(0,s.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return i=new Date(Math.ceil((new Date).getTime())),t.curDay=t.$util.formatTime(i,"YY-M-D"),t.curMonth=t.$util.formatTime(i,"YY-M"),t.startYear=t.$util.formatTime(i,"YY"),e.next=6,t.initIndex();case 6:t.isLoad=!0;case 7:case"end":return e.stop()}}),e)})))()},onPullDownRefresh:function(){uni.showNavigationBarLoading(),this.initRefresh(),uni.stopPullDownRefresh()},onReachBottom:function(){this.list.current_page>=this.list.last_page||this.loading||(this.param.page=this.param.page+1,this.loading=!0,this.getList())},methods:(0,n.default)((0,n.default)({},(0,o.mapMutations)([])),{},{initIndex:function(){var t=arguments,e=this;return(0,c.default)((0,s.default)().mark((function i(){var a;return(0,s.default)().wrap((function(i){while(1)switch(i.prev=i.next){case 0:return a=t.length>0&&void 0!==t[0]&&t[0],i.next=3,e.getList();case 3:e.$util.setNavigationBarColor({bg:e.primaryColor}),a||e.$jweixin.hideOptionMenu();case 5:case"end":return i.stop()}}),i)})))()},initRefresh:function(){this.param.page=1,this.initIndex(!0)},getList:function(t){var e=this;return(0,c.default)((0,s.default)().mark((function i(){var a,n,c,o,r,l,u,v,m,d,p;return(0,s.default)().wrap((function(i){while(1)switch(i.prev=i.next){case 0:return t&&(e.param.page=1,e.list.data=[],uni.pageScrollTo({scrollTop:0})),a=e.list,n=e.activeIndex,c=e.typeIndex,o=e.typeList,r=e.$util.deepCopy(e.param),l=o[c].id,r.status=l,u=e.$util.deepCopy(e.check_time),v=u.month,m=u.start_time,d=u.end_time,0==n&&(r.month=v?e.$util.DateToUnix("".concat(v,"-01")):""),1==n&&(r.start_time=m&&d?e.$util.DateToUnix(m):"",r.end_time=m&&d?e.$util.DateToUnix(d)+86400-1:""),i.next=10,e.$api.agent.commList(r);case 10:p=i.sent,1==e.param.page||(p.data=a.data.concat(p.data)),e.list=p,e.loading=!1,e.$util.hideAll();case 14:case"end":return i.stop()}}),i)})))()},initFixHeight:function(t){this.popupHeight=t},handerTabChange:function(t){this.activeIndex=t,this.check_time.activeIndex=t},handerTypeChange:function(t){this.typeIndex=t,this.getList(1),this.$refs.show_status_item.close()},toChangeType:function(t){this.active=t},toShowTimePopup:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0;this.check_time=this.$util.deepCopy(this.prev_time),e&&0!=e&&(this.activeIndex=0,this.curMonth=e,this.check_time.month=e),this.activeIndex=this.check_time.activeIndex,this.$refs.show_choose_time.open()},toReset:function(){var t=this.activeIndex;0!=t?(this.check_time.start_time="",this.check_time.end_time=""):this.check_time.month=""},toClose:function(){var t=this;return(0,c.default)((0,s.default)().mark((function e(){var i,a,n,c,o;return(0,s.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:i=t.activeIndex,a=t.check_time,n=a.month,c=a.start_time,o=a.end_time,(0!=i||n)&&(1!=i||c&&o)||(1!=i||c&&o||(t.activeIndex=0,t.check_time.activeIndex=0,t.check_time.month=""),t.prev_time=t.$util.deepCopy(t.check_time)),(0==i&&n||1==i&&c&&o)&&(t.check_time=t.$util.deepCopy(t.prev_time)),t.$refs.show_choose_time.close(),t.getList(1);case 6:case"end":return e.stop()}}),e)})))()},toShowTime:function(t){var e=this.activeIndex;if(1!=e||"end_time"!=t||this.check_time.start_time){var i=this.check_time[t];i&&("month"==t?this.curMonth=i:this.curDay=i),this.showKey=t,this.showDate=!0}else this.$util.showToast({title:"请选择开始时间"})},onConfirm:function(t){var e=this;return(0,c.default)((0,s.default)().mark((function i(){var a,n,c,o,r,l,u,v,m,d,p,h;return(0,s.default)().wrap((function(i){while(1)switch(i.prev=i.next){case 0:if(a=e.check_time,n=a.start_time,c=a.end_time,o=e.showKey,r=e.activeIndex,l=void 0===r?0:r,u=e.$util.DateToUnix("month"==o?"".concat(t.result,"-01"):t.result),v=n?e.$util.DateToUnix(n):0,m=c?e.$util.DateToUnix(c):0,d=e.$util.formatTime(new Date(Math.ceil((new Date).getTime())),"YY-M-D"),p=e.$util.DateToUnix(d)+1,h={month:"开始月份",start_time:"开始时间",end_time:"结束时间"},!(u>p)){i.next=11;break}return e.$util.showToast({title:"".concat(h[o],"不能选择未来时间哦")}),i.abrupt("return");case 11:if(1!=l||!("start_time"==o&&m&&m<e.$util.DateToUnix(t.result)||"end_time"==o&&v&&v>e.$util.DateToUnix(t.result))){i.next=14;break}return e.$util.showToast({title:"结束时间不能小于开始时间"}),i.abrupt("return");case 14:e.check_time[o]=t.result;case 15:case"end":return i.stop()}}),i)})))()},toConfirm:function(){var t=this;return(0,c.default)((0,s.default)().mark((function e(){var i,a,n,c,o,r,l,u,v,m,d;return(0,s.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(i=t.check_time,a=i.month,n=void 0===a?"":a,c=i.start_time,o=void 0===c?"":c,r=i.end_time,l=void 0===r?"":r,u=t.activeIndex,v=void 0===u?0:u,m=1e3*t.$util.DateToUnix(o),d=1e3*t.$util.DateToUnix(l),0!=v||n){e.next=7;break}return t.$util.showToast({title:"请选择开始月份"}),e.abrupt("return");case 7:if(1!=v||o&&l&&!(d-m>31536e6)){e.next=10;break}return t.$util.showToast({title:o?l?"查询时间跨度最长为一年哦":"请选择结束时间":"请选择开始时间"}),e.abrupt("return");case 10:t.check_time.activeIndex=v,t.prev_time=t.$util.deepCopy(t.check_time),t.$refs.show_choose_time.close(),t.getList(1);case 14:case"end":return e.stop()}}),e)})))()}}),filters:{handleTimeText:function(t,e){var i="请选择",a=t.activeIndex,n=t.month,s=t.start_time,c=t.end_time;0==a&&n&&(i=r.default.formatTime(1e3*r.default.DateToUnix("".concat(n,"-01")),"YY年M月"));var o=1==e?"YY.M.D":"YY年M月D日";return 1==a&&s&&c&&(i=r.default.formatTime(1e3*r.default.DateToUnix(s),o)+" - "+r.default.formatTime(1e3*r.default.DateToUnix(c),o)),i}}};e.default=u},a0f9:function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return a}));var a={wPicker:i("36a0").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.isLoad?i("v-uni-view",{staticClass:"agent-income-commission",style:{background:t.pageColor}},[i("fixed",{attrs:{zIndex:"997"},on:{height:function(e){arguments[0]=e=t.$handleEvent(e),t.initFixHeight.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"record-search-info pd-md c-base",style:{background:t.pageColor}},[i("v-uni-view",{staticClass:"record-info radius-32",style:{background:t.primaryColor}},[i("v-uni-view",{staticClass:"search-item flex-between ml-lg mr-lg f-desc b-1px-b"},[i("v-uni-view",[t._v("查询时间")]),i("v-uni-view",{staticClass:"flex-y-center",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toShowTimePopup(e)}}},[t._v(t._s(t._f("handleTimeText")(t.prev_time,1))),i("i",{staticClass:"iconfont icon-right"})])],1),i("v-uni-view",{staticClass:"search-item flex-between ml-lg mr-lg f-desc"},[i("v-uni-view",[t._v("入账状态")]),i("v-uni-view",{staticClass:"flex-y-center",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.$refs.show_status_item.open()}}},[t._v(t._s(t.typeList[t.typeIndex].title)),i("i",{staticClass:"iconfont icon-right"})])],1)],1)],1)],1),t._l(t.list.data,(function(e,a){return[0==t.prev_time.activeIndex&&(0==a||a>0&&e.month!=t.list.data[a-1].month)?i("v-uni-view",{key:a+"_0",staticClass:"count-item pl-md pr-md",style:{background:t.pageColor},on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.toShowTimePopup(i,e.month)}}},[i("veiw",{staticClass:"title flex-y-center"},[i("v-uni-view",{staticClass:"f-title c-title text-bold"},[t._v(t._s(e.month))]),i("i",{staticClass:"iconfont iconxia"})],1),i("v-uni-view",{staticClass:"f-caption c-caption"},[t._v("获得总提成¥"+t._s(e.total_cash)+"（"+t._s(t.cityType[e.my_city_type])+"代理）")])],1):t._e(),1==t.prev_time.activeIndex&&0==a?i("v-uni-view",{key:a+"_1",staticClass:"count-item pl-md pr-md",style:{background:t.pageColor},on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.toShowTimePopup(i,e.month)}}},[i("veiw",{staticClass:"title flex-y-center"},[i("v-uni-view",{staticClass:"f-title c-title text-bold"},[t._v(t._s(t._f("handleTimeText")(t.prev_time,2)))]),i("i",{staticClass:"iconfont iconxia"})],1),i("v-uni-view",{staticClass:"f-caption c-caption"},[t._v("获得总提成¥"+t._s(e.total_cash)+"（"+t._s(t.cityType[e.my_city_type])+"代理）")])],1):t._e(),i("v-uni-view",{key:a+"_2",staticClass:"list-item fill-base",class:[{"mt-md":0!==a}]},[i("v-uni-view",{staticClass:"flex-between pt-lg pb-lg ml-lg mr-lg b-1px-b"},[i("v-uni-view",{staticClass:"flex-y-center f-caption c-caption"},[t._v(t._s(t.$t("action.attendantName"))),i("v-uni-view",{staticClass:"f-paragraph c-title text-bold ml-md max-380 ellipsis"},[t._v(t._s(e.coach_info?e.coach_info.coach_name:"-"))])],1),i("v-uni-view",{staticClass:"f-paragraph text-bold",style:{color:2==e.status?t.primaryColor:t.subColor}},[t._v(t._s(t.statusType[e.status]))])],1),i("v-uni-view",{staticClass:"pd-lg f-caption"},[[19,20].includes(e.type)?t._e():i("v-uni-view",{staticClass:"flex-warp"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v("创建时间:")]),i("v-uni-view",{staticClass:"c-title"},[t._v(t._s(e.create_time))])],1),i("v-uni-view",{staticClass:"flex-warp mt-md"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v("服务时间:")]),i("v-uni-view",{staticClass:"c-title"},[t._v(t._s(e.start_time))])],1),i("v-uni-view",{staticClass:"flex-warp mt-md"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v("项目:")]),i("v-uni-view",{staticClass:"c-title",staticStyle:{"max-width":"450rpx"}},t._l(e.order_goods,(function(e,a){return i("v-uni-view",{key:a,class:[{"mt-md":0!=a}]},[t._v(t._s(e.goods_name))])})),1)],1),i("v-uni-view",{staticClass:"flex-warp mt-md"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v("订单实际支付价格:")]),i("v-uni-view",{staticClass:"flex-y-center c-title"},[i("v-uni-view",{staticClass:"mr-sm"},[t._v("¥"+t._s(e.pay_price))]),1*e.true_car_price>0?[t._v("(含车费¥"+t._s(e.true_car_price)+")")]:t._e()],2)],1),[19,20].includes(e.type)?[i("v-uni-view",{staticClass:"flex-warp mt-md"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v("退款金额:")]),i("v-uni-view",{staticClass:"flex-y-center c-title"},[i("v-uni-view",{staticClass:"mr-sm"},[t._v("¥"+t._s(e.refund_price))])],1)],1),i("v-uni-view",{staticClass:"flex-warp mt-lg pt-md"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v(t._s(19==e.type?"空单费分成":"退款手续费分成")+":")]),i("v-uni-view",{staticClass:"c-title",style:{color:t.primaryColor}},[t._v("¥"+t._s(e.cash))])],1)]:[i("v-uni-view",{staticClass:"flex-warp mt-lg pt-md"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v(t._s(t.$t("action.attendantName"))+"分成:")]),i("v-uni-view",{staticClass:"c-title",style:{color:t.primaryColor}},[t._v("¥"+t._s(e.coach_cash))])],1),t._l(e.admin_cash_list,(function(e,a){return i("v-uni-view",{key:a,staticClass:"flex-warp mt-md"},[i("v-uni-view",{staticClass:"item-text c-paragraph"},[t._v(t._s(t.cityType[e.city_type])+"代理分成:")]),i("v-uni-view",{staticClass:"c-title",style:{color:t.primaryColor}},[t._v("¥"+t._s(e.cash))])],1)}))]],2)],1)]})),t.loading?i("load-more",{attrs:{noMore:t.list.current_page>=t.list.last_page&&t.list.data.length>0,loading:t.loading}}):t._e(),!t.loading&&t.list.data.length<=0&&1==t.list.current_page?i("abnor",{attrs:{percent:"calc(100vh - 240rpx - calc(30rpx + env(safe-area-inset-bottom) / 2))"}}):t._e(),i("v-uni-view",{staticClass:"space-footer"}),i("uni-popup",{ref:"show_choose_time",attrs:{type:"bottom",maskClick:!1}},[i("v-uni-view",{staticClass:"popup-choose-time fill-base"},[i("v-uni-view",{staticClass:"pl-lg pr-lg"},[i("v-uni-view",{staticClass:"flex-between b-1px-b"},[i("tab",{attrs:{list:t.tabList,activeIndex:1*t.activeIndex,activeColor:t.primaryColor,height:"100rpx"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.handerTabChange.apply(void 0,arguments)}}}),i("i",{staticClass:"iconfont icon-close",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toClose.apply(void 0,arguments)}}})],1),i("v-uni-view",{staticClass:"flex-center",staticStyle:{height:"80rpx"}},[i("v-uni-view",{staticClass:"flex-1 flex-y-baseline"},[i("v-uni-view",{staticClass:"f-paragraph c-title text-bold"},[t._v(t._s(0==t.activeIndex?"选择月份":"自定义时间"))]),1==t.activeIndex?i("v-uni-view",{staticClass:"f-caption c-warning ml-sm"},[t._v("最长可查找时间跨度一年的交易")]):t._e()],1),0==t.activeIndex&&t.check_time.month||1==t.activeIndex&&(t.check_time.start_time||t.check_time.end_time)?i("v-uni-view",{staticClass:"f-paragraph",staticStyle:{color:"#5A677E"},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toReset.apply(void 0,arguments)}}},[t._v("清除")]):t._e()],1),0==t.activeIndex?i("v-uni-view",{staticClass:"flex-center pb-lg"},[i("v-uni-view",{staticClass:"item-child flex-center flex-column",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toShowTime("month")}}},[i("v-uni-view",[t._v("开始月份")]),i("v-uni-view",{staticClass:"mt-sm",style:{color:t.check_time.month?t.primaryColor:"#999"}},[t._v(t._s(t.check_time.month||"选择月份"))])],1)],1):t._e(),1==t.activeIndex?i("v-uni-view",{staticClass:"flex-center pb-lg"},[i("v-uni-view",{staticClass:"item-child flex-center flex-column",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toShowTime("start_time")}}},[i("v-uni-view",[t._v("开始时间")]),i("v-uni-view",{staticClass:"mt-sm",style:{color:t.check_time.start_time?t.primaryColor:"#999"}},[t._v(t._s(t.check_time.start_time||"选择时间"))])],1),i("v-uni-view",{staticClass:"item-child flex-center flex-column  b-1px-l",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toShowTime("end_time")}}},[i("v-uni-view",[t._v("结束时间")]),i("v-uni-view",{staticClass:"mt-sm",style:{color:t.check_time.end_time?t.primaryColor:"#999"}},[t._v(t._s(t.check_time.end_time||"选择时间"))])],1)],1):t._e()],1),i("v-uni-view",{staticClass:"flex-center flex-column fill-body"},[i("v-uni-view",{staticClass:"space-lg"}),i("v-uni-view",{staticClass:"confirm-btn flex-center f-title c-base radius-16",style:{background:t.primaryColor},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.toConfirm.apply(void 0,arguments)}}},[t._v("确定")]),i("v-uni-view",{staticClass:"space-lg"}),i("v-uni-view",{staticClass:"space-safe"})],1)],1)],1),i("w-picker",{ref:"day",attrs:{mode:"date",startYear:1*t.startYear-10,endYear:t.startYear,value:0==t.activeIndex?t.curMonth:t.curDay,current:!1,fields:0==t.activeIndex?"month":"day","disabled-after":!1,themeColor:t.primaryColor,visible:t.showDate},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.onConfirm(e)},"update:visible":function(e){arguments[0]=e=t.$handleEvent(e),t.showDate=e}}}),i("uni-popup",{ref:"show_status_item",attrs:{type:"bottom",custom:!0}},[i("v-uni-view",{staticClass:"popup-status pd-lg fill-base"},t._l(t.typeList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"flex-center f-paragraph mb-lg",class:[{"mt-lg":0==a}],style:{color:t.typeIndex==a?t.primaryColor:""},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.handerTypeChange(a)}}},[t._v(t._s(e.title))])})),1)],1)],2):t._e()},s=[]},b465:function(t,e,i){"use strict";i.r(e);var a=i("a0f9"),n=i("58e2");for(var s in n)["default"].indexOf(s)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(s);i("1343");var c=i("828b"),o=Object(c["a"])(n["default"],a["b"],a["c"],!1,null,"281cf50c",null,!1,a["a"],void 0);e["default"]=o.exports},d9f1:function(t,e,i){var a=i("c86c");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.agent-income-commission .record-search-info[data-v-281cf50c]{width:%?750?%}.agent-income-commission .record-search-info .record-info[data-v-281cf50c]{width:%?710?%}.agent-income-commission .record-search-info .record-info .search-item[data-v-281cf50c]{height:%?100?%}.agent-income-commission .record-search-info .record-info .search-item .text[data-v-281cf50c]{color:#3d2c1b}.agent-income-commission .record-search-info .record-info .search-item .iconfont[data-v-281cf50c]{font-size:%?26?%}.agent-income-commission .count-item[data-v-281cf50c]{height:%?120?%}.agent-income-commission .count-item .title[data-v-281cf50c]{padding-top:%?18?%}.agent-income-commission .count-item .title .iconfont[data-v-281cf50c]{font-size:%?16?%}.agent-income-commission .list-item .item-text[data-v-281cf50c]{width:%?240?%}.agent-income-commission .popup-choose-time[data-v-281cf50c]{width:%?750?%;border-radius:%?30?% %?30?% 0 0}.agent-income-commission .popup-choose-time .icon-close[data-v-281cf50c]{color:#a8aeb8;font-size:%?40?%}.agent-income-commission .popup-choose-time .item-child[data-v-281cf50c]{width:50%}.agent-income-commission .popup-choose-time .confirm-btn[data-v-281cf50c]{width:%?670?%;height:%?100?%}.agent-income-commission .popup-status[data-v-281cf50c]{width:%?750?%;height:%?360?%;border-radius:%?30?% %?30?% 0 0}',""]),t.exports=e},f5cc6:function(t,e,i){var a=i("d9f1");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("967d").default;n("558f478b",a,!0,{sourceMap:!1,shadowMode:!1})}}]);