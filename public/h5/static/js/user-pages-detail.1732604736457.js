(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-detail"],{"29f0":function(t,e,i){"use strict";i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return n})),i.d(e,"a",(function(){}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.detail.id?i("v-uni-view",{staticClass:"pages-home rel",style:{background:t.pageColor}},[t.options.pid?i("v-uni-view",{staticClass:"abs",class:[{"back-user-ios":t.configInfo.isIos},{"back-user-android":!t.configInfo.isIos}],staticStyle:{"margin-top":"25rpx","z-index":"1"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.$util.goUrl({url:"/pages/service",openType:"reLaunch"})}}},[i("v-uni-view",{staticClass:"iconshouye iconfont"}),i("v-uni-view",{staticClass:"back-user_text"},[t._v("回到首页")])],1):t._e(),[i("banner",{attrs:{list:t.detail.imgs,margin:0,autoplay:!0,height:667,indicatorActiveColor:t.primaryColor}}),i("v-uni-view",{staticClass:"d-info radius-16 rel ml-md mr-md fill-base rel"},[i("v-uni-image",{staticClass:"abs d-bg radius-16",attrs:{src:"https://lbqny.migugu.com/admin/industry/service/detail-01.png",mode:"aspectFill"}}),i("v-uni-view",{staticClass:"rel flex-between"},[i("v-uni-view",{staticClass:"f-md-title text-bold max-500 ellipsis"},[t._v(t._s(t.detail.title))]),i("v-uni-view",{staticClass:"f-icontext c-paragraph"},[t._v("已售"+t._s(t._f("handleFormatNum")(t.detail.total_sale)))])],1),i("v-uni-view",{staticClass:"flex-y-center rel pt-sm"},[i("v-uni-view",{staticClass:"flex mr-sm"},[i("v-uni-view",{staticClass:"flex-y-baseline"},[i("v-uni-text",{staticClass:"c-warning f-caption"},[t._v("¥")]),i("v-uni-text",{staticClass:"f-big-title c-warning text-bold"},[t._v(t._s(t.detail.price))])],1),t.detail.show_unit?i("v-uni-text",{staticClass:"f-mini-title c-warning",staticStyle:{"padding-top":"15rpx"}},[t._v("/"+t._s(t.detail.show_unit))]):t._e()],1),i("v-uni-view",{staticClass:"flex-center f-icontext d-label d-label-time",staticStyle:{color:"#FFEEB9"}},[t._v(t._s(t.detail.time_long)+"分钟")])],1),t.detail.position_data.length>0?i("v-uni-view",{staticClass:"rel pt-md flex-y-center"},[i("v-uni-text",{staticClass:"f-caption c-paragraph mr-sm d-label-titel"},[t._v("服务部位")]),i("v-uni-view",{staticClass:"flex-warp flex-1"},[t._l(t.detail.position_data,(function(e,a){return[i("v-uni-view",{key:a+"_0",staticClass:"d-label-item mr-sm flex-center f-caption mt-sm mb-sm",style:{color:t.primaryColor,border:"1px solid "+t.primaryColor}},[t._v(t._s(e.title))])]}))],2)],1):t._e()],1),t.detail.guarantee_data.length>0?i("v-uni-view",{staticClass:"radius-16 fill-base d-guarantee pt-lg pb-lg pl-md pr-md mt-md ml-md mr-md"},[i("v-uni-image",{staticClass:"guarantee-nav",attrs:{src:"https://lbqny.migugu.com/admin/industry/service/guarantee-nav.png",mode:"aspectFill"}}),i("v-uni-scroll-view",{staticClass:"mt-md",staticStyle:{width:"670rpx","white-space":"nowrap"},attrs:{"scroll-x":!0}},t._l(t.detail.guarantee_data,(function(e,a){return i("v-uni-view",{key:a,staticClass:"mr-md",staticStyle:{display:"inline-block"}},[i("v-uni-view",{staticClass:"flex-y-center d-guarantee-item"},[i("v-uni-view",{},[i("v-uni-view",{staticClass:"flex-y-center"},[i("v-uni-view",{staticClass:"flex-center guarantee-item-icon rel"},[i("i",{staticClass:"iconfont iconicon-gx ",staticStyle:{"font-size":"7px"},style:{color:t.primaryColor}})]),i("v-uni-view",{staticClass:"f-paragraph text-bold pl-sm",style:{color:t.primaryColor}},[t._v(t._s(e.title))])],1),i("v-uni-view",{staticClass:"pt-sm f-caption",staticStyle:{color:"#6F6F6F"}},[t._v(t._s(e.sub_title))])],1)],1)],1)})),1)],1):t._e(),i("v-uni-view",{staticClass:"ml-md mr-md mt-md fill-base pb-lg radius-16",staticStyle:{overflow:"hidden"}},[i("v-uni-view",{staticClass:"tab-list flex-center"},[t._l(t.tabList,(function(e,a){return[i("v-uni-view",{key:a+"_0",staticClass:"tab-item flex-1 flex-center rel",class:[{"text-bold":a==t.activeIndex}],on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.handerTabChange(a)}}},[i("v-uni-text",{staticClass:"rel tab-item-title"},[t._v(t._s(e.title))]),i("v-uni-image",{staticClass:"abs",class:"tab-item-line"+(a+1),style:{opacity:a==t.activeIndex?.8:0},attrs:{src:t.tabLine[a],mode:"aspectFill"}})],1)]}))],2),i("v-uni-view",{staticClass:"space-sm"}),i("v-uni-view",{staticClass:"fill-base f-paragraph c-desc ml-lg mr-lg mt-lg radius-16",staticStyle:{"text-align":"justify"}},[i("parser",{attrs:{html:t.detail[t.rule[t.tabList[t.activeIndex].id]],"show-with-animation":!0,"lazy-load":!0},on:{linkpress:function(e){arguments[0]=e=t.$handleEvent(e),t.linkpress.apply(void 0,arguments)}}},[t._v("加载中...")])],1)],1)],i("v-uni-view",{staticClass:"space-max-footer"}),i("fix-bottom-button",{attrs:{text:[{type:"confirm",text:t.options.coach_id?"立即下单":"选择"+t.$t("action.attendantName"),isAuth:!0}],bgColor:"#fff"},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.toConfirm.apply(void 0,arguments)}}})],2):t._e()},n=[]},3409:function(t,e,i){"use strict";i.r(e);var a=i("29f0"),n=i("6575");for(var s in n)["default"].indexOf(s)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(s);i("6ee5");var r=i("828b"),o=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"6f6d24b5",null,!1,a["a"],void 0);e["default"]=o.exports},"57f7":function(t,e,i){var a=i("c86c");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.pages-home .list-item .item-btn[data-v-6f6d24b5]{width:%?129?%;height:%?54?%}.pages-home .d-info[data-v-6f6d24b5]{padding:%?36?% %?30?% %?40?% %?30?%;margin-top:%?-117?%;overflow:hidden}.pages-home .d-info .d-bg[data-v-6f6d24b5]{width:100%;height:%?207?%;top:0;left:0}.pages-home .d-info .d-label[data-v-6f6d24b5]{padding:2px %?11?%;border-radius:%?4?%}.pages-home .d-info .d-label-titel[data-v-6f6d24b5]{height:%?38?%;line-height:%?38?%}.pages-home .d-info .d-label-item[data-v-6f6d24b5]{min-width:%?84?%;height:%?38?%;border-radius:%?4?%;padding:2px;line-height:%?38?%}.pages-home .d-info .d-label-time[data-v-6f6d24b5]{min-width:%?94?%;height:%?34?%;padding:0 3px;border-radius:%?4?%;background:linear-gradient(270deg,#4c545a,#282b34)}.pages-home .d-store .d-store-cover[data-v-6f6d24b5]{width:%?102?%;height:%?102?%;border-radius:%?8?%;margin-right:%?16?%}.pages-home .d-store .d-store-label[data-v-6f6d24b5]{min-width:%?84?%;height:%?38?%;border-radius:%?4?%;padding:0 2px}.pages-home .d-store .phone-icon[data-v-6f6d24b5]{width:%?44?%;height:%?44?%;background:#f4f4f4;border-radius:%?14?%;margin-bottom:2px}.pages-home .d-store .iconyduixingxingshixin[data-v-6f6d24b5]{background-image:-webkit-linear-gradient(#ff6617,#f7a31c);font-size:12px}.pages-home .d-store .d-store-opinion[data-v-6f6d24b5],\n.pages-home .d-store .d-store-taking[data-v-6f6d24b5]{padding:1px 4px;color:#7d422c;border-radius:%?8?%;background-color:#ffeedc;margin-right:%?16?%}.pages-home .d-store .d-store-service[data-v-6f6d24b5]{padding:1px 4px;color:#ff4c20;background:#ffeeeb;border-radius:%?8?%}.pages-home .d-guarantee[data-v-6f6d24b5]{background:linear-gradient(177deg,#effdfb,#fff 10%,#fff)}.pages-home .d-guarantee .guarantee-nav[data-v-6f6d24b5]{width:%?455?%;height:%?41?%}.pages-home .d-guarantee .d-guarantee-icon[data-v-6f6d24b5]{width:%?40?%;height:%?49?%}.pages-home .d-guarantee .d-guarantee-item[data-v-6f6d24b5]{min-width:%?245?%;height:%?121?%;background:#effcff;border-radius:%?8?%;padding:0 %?20?%}.pages-home .d-guarantee .guarantee-item-icon[data-v-6f6d24b5]{width:%?24?%;height:%?24?%;border:1px solid #333;border-radius:%?24?%}.pages-home .d-guarantee .guarantee-item-icon .iconicon-gx[data-v-6f6d24b5]{padding-top:1px}.pages-home .tab-list[data-v-6f6d24b5]{height:%?78?%}.pages-home .tab-list .tab-item-title[data-v-6f6d24b5]{z-index:1}.pages-home .tab-list .tab-item-line1[data-v-6f6d24b5]{width:%?242?%;height:%?78?%}.pages-home .tab-list .tab-item-line2[data-v-6f6d24b5]{width:%?282?%;height:%?78?%}.pages-home .tab-list .tab-item-line3[data-v-6f6d24b5]{width:%?244?%;height:%?78?%}',""]),t.exports=e},6575:function(t,e,i){"use strict";i.r(e);var a=i("d2af"),n=i.n(a);for(var s in a)["default"].indexOf(s)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(s);e["default"]=n.a},"6ee5":function(t,e,i){"use strict";var a=i("7b32"),n=i.n(a);n.a},"7b32":function(t,e,i){var a=i("57f7");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("967d").default;n("6327d2ae",a,!0,{sourceMap:!1,shadowMode:!1})},d2af:function(t,e,i){"use strict";i("6a54");var a=i("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("c223"),i("d4b5"),i("aa9c"),i("4626"),i("5ac7"),i("e966");var n=a(i("9b1b")),s=a(i("2634")),r=a(i("2fdc")),o=i("8f59"),d=a(i("c57d")),c=a(i("0812")),l={components:{parser:c.default},data:function(){return{options:{},activeIndex:0,tabList:[],tabLine:["https://lbqny.migugu.com/admin/industry/service/nav-left.png","https://lbqny.migugu.com/admin/industry/service/nav-center.png","https://lbqny.migugu.com/admin/industry/service/nav-right.png"],rule:{0:"introduce",1:"explain",2:"notice"},detail:{}}},computed:(0,o.mapState)({configInfo:function(t){return t.config.configInfo},isGzhLogin:function(t){return t.user.isGzhLogin}}),onLoad:function(t){var e=this;return(0,r.default)((0,s.default)().mark((function i(){var a,n,r,o,d,c;return(0,s.default)().wrap((function(i){while(1)switch(i.prev=i.next){case 0:return a=t.pid,n=void 0===a?0:a,r=t.store_id,o=void 0===r?0:r,d=t.coach_id,c=void 0===d?0:d,t.pid=1*n,t.store_id=1*o,t.coach_id=1*c,e.options=t,e.$util.showLoading(),i.next=8,e.initIndex();case 8:e.scanRecordId&&e.updateScanRecord();case 9:case"end":return i.stop()}}),i)})))()},destroyed:function(){this.updateUserItem({key:"appShare",val:!0})},onPullDownRefresh:function(){uni.showNavigationBarLoading(),this.initRefresh(),uni.stopPullDownRefresh()},onShareAppMessage:function(t){var e=this.userInfo.id,i=void 0===e?0:e,a=this.detail,n=a.id,s=a.title,r=a.cover,o="/user/pages/detail?pid=".concat(i,"&id=").concat(n);return this.$util.log(o),{title:s,imageUrl:r,path:o}},methods:(0,n.default)((0,n.default)((0,n.default)({},(0,o.mapActions)(["getConfigInfo","getUserInfo","addScanRecord","updateScanRecord"])),(0,o.mapMutations)(["updateUserItem"])),{},{initIndex:function(){var t=arguments,e=this;return(0,r.default)((0,s.default)().mark((function i(){var a,n,r,o,d,c;return(0,s.default)().wrap((function(i){while(1)switch(i.prev=i.next){case 0:if(a=t.length>0&&void 0!==t[0]&&t[0],n=e.options.pid,r=void 0===n?0:n,o=e.scanRecordId,o=e.$util.getQueryString("code"),r&&!o&&e.addScanRecord({type:2,qr_id:r,is_qr:0}),d=e.userInfo.id,c=void 0===d?0:d,!(!e.configInfo.id||a||r&&o&&!c)){i.next=9;break}return i.next=9,e.getConfigInfo();case 9:if(!r||o||c){i.next=12;break}return i.next=12,e.getUserInfo();case 12:if(!r||o||e.userInfo.id){i.next=14;break}return i.abrupt("return");case 14:return i.next=16,e.getDetail();case 16:e.$util.setNavigationBarColor({bg:e.primaryColor}),e.$jweixin.initJssdk((function(){e.toAppShare()}));case 18:case"end":return i.stop()}}),i)})))()},initRefresh:function(){this.initIndex(!0)},toAppShare:function(){var t=this.userInfo.id,e=void 0===t?0:t,i=this.detail,a=i.id,n=i.title,s=i.cover,r=d.default.siteroot,o=r.split("/index.php")[0],c="".concat(o,"/h5/#/user/pages/detail?id=").concat(a,"&pid=").concat(e);this.$jweixin.showOptionMenu(),this.$jweixin.shareAppMessage(n,"",c,s),this.$jweixin.shareTimelineMessage(n,c,s)},toTransImg:function(t){var e=this,i=this.$util.DateToUnix(this.$util.formatTime(new Date,"YY-M-D h:m:s"));uni.downloadFile({url:t.imageUrl,success:function(a){plus.zip.compressImage({src:a.tempFilePath,dst:"_doc/".concat(i,".jpg"),format:"jpg"},(function(a){plus.zip.compressImage({src:a.target,dst:"_doc/".concat(i,"-img.jpg"),quality:50},(function(i){t.imageUrl=i.target||"/static/img/logo.png",e.sharePage(t)}),(function(i){t.imageUrl="/static/img/logo.png",e.sharePage(t)}))}))}})},sharePage:function(t){this.$util.hideAll();var e=t.href,i=t.title,a=t.summary,n=t.imageUrl;uni.share({provider:"weixin",scene:"WXSceneSession",type:0,href:e,title:i,summary:a,imageUrl:n,success:function(t){console.log("success:"+JSON.stringify(t))},fail:function(t){console.log("fail:"+JSON.stringify(t))}})},getDetail:function(){var t=this;return(0,r.default)((0,s.default)().mark((function e(){var i,a,n,r;return(0,s.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return i=t.options.id,e.next=3,t.$api.service.serviceInfo({id:i});case 3:a=e.sent,n=a.explain,t.detail=a,r=[{title:"项目介绍",id:0}],n&&n.length>0&&r.push({title:"禁忌说明",id:1}),t.tabList=r.concat([{title:"下单须知",id:2}]),t.$util.hideAll();case 10:case"end":return e.stop()}}),e)})))()},handerTabChange:function(t){this.activeIndex=t},linkpress:function(t){},toConfirm:function(){var t=this.options,e=t.id,i=t.coach_id,a=void 0===i?0:i,n=t.store_id,s=void 0===n?0:n;if(a)this.toOrder();else{var r=this.detail.member_info,o=r.can_buy,d=r.title;if(o)this.$util.goUrl({url:"/user/pages/choose-technician?id=".concat(e,"&store_id=").concat(s)});else{var c=d?d.includes("会员")?d:"".concat(d,"会员"):"会员";this.showToast({title:"您还不是".concat(c)})}}},toOrder:function(t){var e=this;return(0,r.default)((0,s.default)().mark((function t(){var i,a,n,r,o,d,c;return(0,s.default)().wrap((function(t){while(1)switch(t.prev=t.next){case 0:if(i=e.options,a=i.id,n=i.coach_id,r=void 0===n?0:n,o=i.is_work,d=void 0===o?0:o,d){t.next=4;break}return e.$util.showToast({title:"该".concat(e.$t("action.attendantName"),"未上班")}),t.abrupt("return");case 4:if(!e.lockTap){t.next=6;break}return t.abrupt("return");case 6:return e.lockTap=!0,t.prev=7,t.next=10,e.$api.order.addCar({service_id:a,coach_id:r,num:1,is_top:1,coach_service:1});case 10:e.lockTap=!1,c="/user/pages/order?id=".concat(r,"&ser_id=").concat(a),e.$util.goUrl({url:c}),t.next=18;break;case 15:t.prev=15,t.t0=t["catch"](7),e.lockTap=!1;case 18:case"end":return t.stop()}}),t,null,[[7,15]])})))()}}),filters:{handleFormatNum:function(t){var e=t;if(t>9999){var i=t%1e4,a=parseInt(t/1e4);e="".concat(a,i?"w+":"w")}return e}}};e.default=l}}]);