(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-gzh"],{"2d80":function(e,n,t){"use strict";t.r(n);var i=t("92ab"),a=t("991b");for(var o in a)["default"].indexOf(o)<0&&function(e){t.d(n,e,(function(){return a[e]}))}(o);t("ec6e");var r=t("828b"),c=Object(r["a"])(a["default"],i["b"],i["c"],!1,null,"75ce7de2",null,!1,i["a"],void 0);n["default"]=c.exports},"2f31":function(e,n,t){"use strict";t("6a54");var i=t("f5bd").default;Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var a=i(t("9b1b")),o=i(t("2634")),r=i(t("2fdc")),c=t("8f59"),d={data:function(){return{isLoad:!1,options:{},gzh_img:"https://lbqny.migugu.com/admin/anmo/mine/web-code-img.png"}},computed:(0,c.mapState)({configInfo:function(e){return e.config.configInfo},isGzhLogin:function(e){return e.user.isGzhLogin}}),onLoad:function(e){var n=this;return(0,r.default)((0,o.default)().mark((function t(){var i,a,r,c;return(0,o.default)().wrap((function(t){while(1)switch(t.prev=t.next){case 0:return i=e.pid,a=void 0===i?0:i,r=e.channel_id,c=void 0===r?0:r,e.pid=1*a,e.channel_id=1*c,n.options=e,t.next=6,n.initIndex();case 6:n.scanRecordId&&n.updateScanRecord();case 7:case"end":return t.stop()}}),t)})))()},methods:(0,a.default)((0,a.default)((0,a.default)({},(0,c.mapActions)(["getConfigInfo","getUserInfo","addScanRecord","updateScanRecord"])),(0,c.mapMutations)(["updateConfigItem"])),{},{initIndex:function(){var e=this;return(0,r.default)((0,o.default)().mark((function n(){var t,i,a,r,c,d;return(0,o.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:return t=e.options,i=t.pid,a=void 0===i?0:i,r=t.channel_id,c=void 0===r?0:r,e.scanRecordId,d=e.$util.getQueryString("code"),a&&!d&&e.addScanRecord({type:2,qr_id:a}),c&&!d&&e.addScanRecord({type:9,qr_id:c}),n.next=7,e.getConfigInfo();case 7:return n.next=9,e.getUserInfo();case 9:if(e.userInfo.id){n.next=11;break}return n.abrupt("return");case 11:if(!c){n.next=14;break}return n.next=14,e.$api.user.bindChannel({channel_id:c});case 14:e.$util.setNavigationBarColor({bg:e.primaryColor}),e.isLoad=!0,e.$jweixin.hideOptionMenu();case 17:case"end":return n.stop()}}),n)})))()}})};n.default=d},"32f3":function(e,n,t){var i=t("c86c");n=i(!1),n.push([e.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.mine-pages-gzh .web-code-img[data-v-75ce7de2]{width:%?750?%}.mine-pages-gzh .gzh-img-info[data-v-75ce7de2]{width:%?536?%;height:%?552?%;margin-top:%?114?%}.mine-pages-gzh .gzh-img-info .gzh-img[data-v-75ce7de2]{width:%?536?%;height:%?552?%;border-radius:%?30?%}.mine-pages-gzh .gzh-img-info .none-text[data-v-75ce7de2]{width:%?536?%;color:#2e2e31;bottom:%?-20?%}.mine-pages-gzh .home-btn[data-v-75ce7de2]{width:%?690?%;height:%?90?%;margin-top:%?126?%}',""]),e.exports=n},"571d":function(e,n,t){var i=t("32f3");i.__esModule&&(i=i.default),"string"===typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);var a=t("967d").default;a("138f09ca",i,!0,{sourceMap:!1,shadowMode:!1})},"92ab":function(e,n,t){"use strict";t.d(n,"b",(function(){return i})),t.d(n,"c",(function(){return a})),t.d(n,"a",(function(){}));var i=function(){var e=this,n=e.$createElement,t=e._self._c||n;return e.isLoad?t("v-uni-view",{staticClass:"mine-pages-gzh",style:{background:e.pageColor}},[t("v-uni-view",{staticClass:"flex-center flex-column"},[e.configInfo.web_code_img?t("v-uni-image",{staticClass:"web-code-img",attrs:{mode:"widthFix",src:e.configInfo.web_code_img}}):t("v-uni-view",{staticClass:"gzh-img-info rel"},[t("v-uni-image",{staticClass:"gzh-img",attrs:{mode:"aspectFill",src:e.gzh_img}}),e.configInfo.web_code_img?e._e():t("v-uni-view",{staticClass:"none-text f-title flex-center abs"},[e._v("商家还没有放公众号二维码哟~")])],1),e.configInfo.web_code_img?e._e():t("v-uni-view",{staticClass:"home-btn flex-center f-sm-title c-base radius",style:{background:e.primaryColor},on:{click:function(n){n.stopPropagation(),arguments[0]=n=e.$handleEvent(n),e.$util.goUrl({url:"/pages/service",openType:"redirectTo"})}}},[e._v("直接进入首页")])],1)],1):e._e()},a=[]},"991b":function(e,n,t){"use strict";t.r(n);var i=t("2f31"),a=t.n(i);for(var o in i)["default"].indexOf(o)<0&&function(e){t.d(n,e,(function(){return i[e]}))}(o);n["default"]=a.a},ec6e:function(e,n,t){"use strict";var i=t("571d"),a=t.n(i);a.a}}]);