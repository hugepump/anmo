(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["technician-pages-shop-detail"],{"0981":function(t,e,n){var i=n("c86c");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.shop .shop-swiper[data-v-79b09388]{height:%?564?%}.shop .shop-box-title[data-v-79b09388]{line-height:%?110?%;height:%?110?%}.shop .shop-box-item[data-v-79b09388]{width:100%;height:%?388?%}.shop .shop-box-item uni-image[data-v-79b09388]{vertical-align:bottom;width:100%;height:100%}.shop .share-btn[data-v-79b09388]{right:%?30?%;bottom:%?30?%;height:%?42?%}',""]),t.exports=e},1419:function(t,e,n){var i=n("0981");i.__esModule&&(i=i.default),"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var a=n("967d").default;a("0845a0df",i,!0,{sourceMap:!1,shadowMode:!1})},"1f13":function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){}));var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{staticClass:"rel"},[n("v-uni-view",{staticClass:"abs",staticStyle:{"z-index":"99"},style:{top:t.statusBarHeight+"px"}},[t.isShare?n("v-uni-view",{class:[{"back-user-ios":t.configInfo.isIos},{"back-user-android":!t.configInfo.isIos}],on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.$util.goUrl({url:"/pages/service",openType:"reLaunch"})}}},[n("v-uni-view",{staticClass:"iconshouye iconfont"}),n("v-uni-view",{staticClass:"back-user_text"},[t._v("回到首页")])],1):t._e()],1),n("v-uni-view",{staticClass:"banner"},[n("v-uni-swiper",{staticClass:"banner-swiper",style:{background:t.playVideo&&!t.detail.video_vid?"#000":"#f4f6f8"},attrs:{autoplay:!t.playVideo,current:t.current},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.handerSwiperChange.apply(void 0,arguments)},transition:function(e){arguments[0]=e=t.$handleEvent(e),t.swiperTransition.apply(void 0,arguments)}}},t._l(t.detail.images,(function(e,i){return n("v-uni-swiper-item",{key:i,on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.handerBannerClick(i)}}},[0==i&&t.detail.video_url?[t.playVideo?t._e():[n("v-uni-view",{staticClass:"banner-swiper c-base iconfont icontushucxuanzebofangtiaozhuan abs flex-center",staticStyle:{top:"0rpx","font-size":"80rpx","z-index":"9"},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.playCurrent.apply(void 0,arguments)}}}),n("v-uni-image",{staticClass:"banner-img",attrs:{mode:"aspectFill",src:e}})],t.playVideo&&!t.detail.video_vid?n("v-uni-view",{staticClass:"video-box"},[n("v-uni-video",{staticClass:"my-video",attrs:{id:"video_id",loop:!1,"enable-play-gesture":!0,"enable-progress-gesture":!1,src:t.detail.video_url,autoplay:t.playVideo},on:{play:function(e){arguments[0]=e=t.$handleEvent(e),t.onPlay.apply(void 0,arguments)},pause:function(e){arguments[0]=e=t.$handleEvent(e),t.onPause.apply(void 0,arguments)},ended:function(e){arguments[0]=e=t.$handleEvent(e),t.onEnded.apply(void 0,arguments)},timeupdate:function(e){arguments[0]=e=t.$handleEvent(e),t.onTimeUpdate.apply(void 0,arguments)},waiting:function(e){arguments[0]=e=t.$handleEvent(e),t.onWaiting.apply(void 0,arguments)},progress:function(e){arguments[0]=e=t.$handleEvent(e),t.onProgress.apply(void 0,arguments)},loadedmetadata:function(e){arguments[0]=e=t.$handleEvent(e),t.onLoadedMetaData.apply(void 0,arguments)}}})],1):t._e()]:n("v-uni-image",{staticClass:"banner-img",attrs:{mode:"aspectFill",src:e}})],2)})),1),!t.playVideo&&t.detail.images.length?n("v-uni-view",{staticClass:"banner-tagitem banner-tagitem_count"},[t._v(t._s(t.current+1)+"/"+t._s(t.detail.images.length))]):t._e()],1)],1)},a=[]},"4c64":function(t,e,n){var i=n("c86c");e=i(!1),e.push([t.i,".home-return-btn[data-v-7b3cce58]{margin-top:%?10?%;margin-left:%?24?%;width:%?60?%;height:%?60?%;border:none;background-color:rgba(0,0,0,.3)}.video-box[data-v-7b3cce58]{position:relative;width:100%;height:%?500?%}.my-video[data-v-7b3cce58]{position:absolute;left:0;top:0;width:100%;height:80%;align-items:center;margin-top:%?120?%}.banner[data-v-7b3cce58]{position:relative}.banner-swiper[data-v-7b3cce58]{width:%?750?%;height:%?564?%}.banner-img[data-v-7b3cce58]{width:100%;height:100%}.banner-taglist[data-v-7b3cce58]{display:flex;align-items:center;justify-content:center;position:absolute;bottom:%?32?%;width:100%}.banner-tagitem[data-v-7b3cce58]{display:flex;align-items:center;justify-content:center;width:%?90?%;height:%?42?%;border-radius:%?21?%;background:hsla(0,0%,100%,.8);color:#2b2b2b;font-size:%?22?%;margin-left:%?32?%}.banner-tagitem[data-v-7b3cce58]:nth-child(1){margin-left:0}.banner-tagitem_count[data-v-7b3cce58]{background:rgba(0,0,0,.5);color:#fff;position:absolute;bottom:%?32?%;right:%?32?%;z-index:10}.banner-tagitem_active[data-v-7b3cce58]{background:#19c865;color:#fff}",""]),t.exports=e},6217:function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){}));var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return t.isLoad?n("v-uni-view",{staticClass:"shop",style:{background:t.pageColor}},[n("v-uni-view",{staticClass:"shop-swiper"},[n("shopBanner",{attrs:{detail:t.shopInfo,isShare:t.isShare}})],1),n("v-uni-view",[n("v-uni-view",{staticClass:"pd-lg fill-base flex-between rel",staticStyle:{"align-items":"flex-end"}},[n("v-uni-view",[n("v-uni-view",{staticClass:"f-sm-title text-bold c-black"},[t._v(t._s(t.shopInfo.name))]),n("v-uni-view",{staticClass:"pt-md c-warning",staticStyle:{"line-height":"1"}},[n("v-uni-text",{staticClass:"f-icontext"},[t._v("￥")]),n("v-uni-text",{staticClass:"f-sm-title text-bold"},[t._v(t._s(t.shopInfo.price))])],1)],1)],1),n("v-uni-view",{staticClass:"mt-md fill-base pl-lg pr-lg"},[n("v-uni-view",{staticClass:"f-mini-title c-black shop-box-title"},[t._v("商品详情")]),n("v-uni-view",{staticClass:"fill-base pt-lg pb-lg"},[n("parser",{attrs:{html:t.shopInfo.desc,"show-with-animation":!0,"lazy-load":!0},on:{linkpress:function(e){arguments[0]=e=t.$handleEvent(e),t.linkpress.apply(void 0,arguments)}}},[t._v("加载中...")])],1)],1)],1),n("v-uni-view",{staticClass:"space-max-footer"}),n("fix-bottom-button",{attrs:{text:[{type:"confirm",text:"联系平台"}],bgColor:"#fff"},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.$util.goUrl({url:t.shopInfo.phone,openType:"call"})}}})],1):t._e()},a=[]},"68cd":function(t,e,n){"use strict";n.r(e);var i=n("8a7d"),a=n.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(o);e["default"]=a.a},8872:function(t,e,n){"use strict";var i=n("1419"),a=n.n(i);a.a},8873:function(t,e,n){"use strict";n("6a54"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,n("64aa");var i=n("8f59"),a={props:{detail:{type:Object,default:function(){return{}}},isShare:{type:Boolean,default:function(){return!1}},setCurrent:{type:Number,default:function(){return 0}}},watch:{"detail.images":function(t,e){this.current=0}},data:function(){return{statusBarHeight:uni.getSystemInfoSync().statusBarHeight,videoContexts:{},playVideo:!1,current:0}},computed:(0,i.mapState)({configInfo:function(t){return t.config.configInfo}}),created:function(){this.videoContexts=uni.createVideoContext("video_id",this)},methods:{handerSwiperChange:function(t){var e=t.detail.current;this.current=e,this.videoContexts.pause(),this.playVideo=!1},swiperTransition:function(t){},playCurrent:function(){this.videoContexts.play(),this.playVideo=!0},onPlay:function(t){},onPause:function(t){},onEnded:function(t){},onError:function(t){},onTimeUpdate:function(t){},onWaiting:function(t){},onProgress:function(t){},onLoadedMetaData:function(t){},handerBannerClick:function(t){var e=this.detail,n=e.image_url,i=e.video_url,a=void 0===i?"":i;0==t&&a?this.playVideo=!0:n&&this.$util.goUrl({openType:"web",url:n})},goBack:function(){uni.navigateBack({delta:1})}}};e.default=a},"8a7d":function(t,e,n){"use strict";n("6a54");var i=n("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,n("c223"),n("d4b5");var a=i(n("9b1b")),o=i(n("2634")),s=i(n("2fdc")),r=n("8f59"),c=i(n("0812")),u=i(n("f372")),d=i(n("c57d")),l={components:{parser:c.default,shopBanner:u.default},data:function(){return{isLoad:!1,options:{},shopInfo:{},isShare:!1}},computed:(0,r.mapState)({configInfo:function(t){return t.config.configInfo},isGzhLogin:function(t){return t.user.isGzhLogin}}),onLoad:function(t){var e=this;return(0,s.default)((0,o.default)().mark((function n(){var i,a;return(0,o.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:return i=t.pid,a=void 0===i?0:i,t.pid=1*a,a&&(e.isShare=!0),e.options=t,n.next=6,e.initIndex();case 6:e.scanRecordId&&e.updateScanRecord();case 7:case"end":return n.stop()}}),n)})))()},onPullDownRefresh:function(){uni.showNavigationBarLoading(),this.initRefresh(),uni.stopPullDownRefresh()},onShareAppMessage:function(){var t=this.userInfo.id,e=void 0===t?0:t,n=this.shopInfo,i=n.id,a=n.name,o=n.cover,s="/technician/pages/shop/detail?id=".concat(i,"&pid=").concat(e);return{title:a,imageUrl:o,path:s}},methods:(0,a.default)((0,a.default)({},(0,r.mapActions)(["getConfigInfo","getUserInfo","addScanRecord","updateScanRecord"])),{},{initRefresh:function(){this.initIndex(!0)},initIndex:function(){var t=arguments,e=this;return(0,s.default)((0,o.default)().mark((function n(){var i,a,s,r,c,u;return(0,o.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:if(i=t.length>0&&void 0!==t[0]&&t[0],a=e.options.pid,s=void 0===a?0:a,r=e.scanRecordId,r=e.$util.getQueryString("code"),s&&!r&&e.addScanRecord({type:2,qr_id:s,is_qr:0}),c=e.userInfo.id,u=void 0===c?0:c,!(!e.configInfo.id||i||s&&r&&!u)){n.next=9;break}return n.next=9,e.getConfigInfo();case 9:if(!s||r||u){n.next=12;break}return n.next=12,e.getUserInfo();case 12:if(!s||r||e.userInfo.id){n.next=14;break}return n.abrupt("return");case 14:return n.next=16,e.goodsInfoCall();case 16:e.$jweixin.initJssdk((function(){e.toAppShare()}));case 17:case"end":return n.stop()}}),n)})))()},goodsInfoCall:function(){var t=this;return(0,s.default)((0,o.default)().mark((function e(){var n;return(0,o.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.$util.showLoading(),n=t.options.id,e.next=4,t.$api.technician.goodsInfo({id:n});case 4:t.shopInfo=e.sent,t.isLoad=!0,t.$util.hideAll();case 7:case"end":return e.stop()}}),e)})))()},swiperChange:function(t){},linkpress:function(t){},toAppShare:function(){var t=this.userInfo.id,e=void 0===t?0:t,n=this.shopInfo,i=n.id,a=n.name,o=n.cover,s=d.default.siteroot,r=s.split("/index.php")[0],c="".concat(r,"/h5/#/technician/pages/shop/detail?id=").concat(i,"&pid=").concat(e);this.$jweixin.showOptionMenu(),this.$jweixin.shareAppMessage(a,"",c,o),this.$jweixin.shareTimelineMessage(a,c,o)},toTransImg:function(t){var e=this,n=this.$util.DateToUnix(this.$util.formatTime(new Date,"YY-M-D h:m:s"));uni.downloadFile({url:t.imageUrl,success:function(i){plus.zip.compressImage({src:i.tempFilePath,dst:"_doc/".concat(n,".jpg"),format:"jpg"},(function(i){plus.zip.compressImage({src:i.target,dst:"_doc/".concat(n,"-img.jpg"),quality:50},(function(n){t.imageUrl=n.target||"/static/img/logo.png",e.sharePage(t)}),(function(n){t.imageUrl="/static/img/logo.png",e.sharePage(t)}))}))}})},sharePage:function(t){this.$util.hideAll();var e=t.href,n=t.title,i=t.summary,a=t.imageUrl;uni.share({provider:"weixin",scene:"WXSceneSession",type:0,href:e,title:n,summary:i,imageUrl:a,success:function(t){console.log("success:"+JSON.stringify(t))},fail:function(t){console.log("fail:"+JSON.stringify(t))}})}})};e.default=l},"90c8":function(t,e,n){"use strict";var i=n("de6f"),a=n.n(i);a.a},b72c:function(t,e,n){"use strict";n.r(e);var i=n("6217"),a=n("68cd");for(var o in a)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(o);n("8872");var s=n("828b"),r=Object(s["a"])(a["default"],i["b"],i["c"],!1,null,"79b09388",null,!1,i["a"],void 0);e["default"]=r.exports},bed4:function(t,e,n){"use strict";n.r(e);var i=n("8873"),a=n.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(o);e["default"]=a.a},de6f:function(t,e,n){var i=n("4c64");i.__esModule&&(i=i.default),"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var a=n("967d").default;a("3dad2ec4",i,!0,{sourceMap:!1,shadowMode:!1})},f372:function(t,e,n){"use strict";n.r(e);var i=n("1f13"),a=n("bed4");for(var o in a)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(o);n("90c8");var s=n("828b"),r=Object(s["a"])(a["default"],i["b"],i["c"],!1,null,"7b3cce58",null,!1,i["a"],void 0);e["default"]=r.exports}}]);