(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-protocol"],{"2d0f":function(t,n,e){"use strict";e("6a54");var i=e("f5bd").default;Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var o=i(e("2634")),a=i(e("2fdc")),r=i(e("9b1b")),s=e("8f59"),u=i(e("0812")),f={components:{parser:u.default},data:function(){return{options:{},isLoad:!1,detail:{}}},computed:(0,s.mapState)({configInfo:function(t){return t.config.configInfo},loginPage:function(t){return t.user.loginPage}}),onLoad:function(t){this.options=t,this.$util.showLoading(),this.initIndex()},methods:(0,r.default)((0,r.default)((0,r.default)({},(0,s.mapActions)(["getConfigInfo","getUserInfo"])),(0,s.mapMutations)(["updateUserItem"])),{},{initIndex:function(){var t=arguments,n=this;return(0,a.default)((0,o.default)().mark((function e(){var i;return(0,o.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.length>0&&void 0!==t[0]&&t[0],e.next=3,n.$api.base.getConfig();case 3:i=e.sent,n.detail=i,n.$util.setNavigationBarColor({bg:n.primaryColor}),n.$util.hideAll(),n.isLoad=!0;case 8:case"end":return e.stop()}}),e)})))()},initRefresh:function(){this.initIndex(!0)},linkpress:function(t){console.log("linkpress",t)}})};n.default=f},"6c11":function(t,n,e){"use strict";e.d(n,"b",(function(){return i})),e.d(n,"c",(function(){return o})),e.d(n,"a",(function(){}));var i=function(){var t=this,n=t.$createElement,e=t._self._c||n;return t.isLoad?e("v-uni-view",{staticClass:"user-pages-protocol",style:{background:t.pageColor}},[e("v-uni-view",{staticClass:"pd-lg f-paragraph"},[e("parser",{attrs:{html:t.detail.login_protocol,"show-with-animation":!0,"lazy-load":!0},on:{linkpress:function(n){arguments[0]=n=t.$handleEvent(n),t.linkpress.apply(void 0,arguments)}}},[t._v("加载中...")])],1),e("v-uni-view",{staticClass:"space-footer"})],1):t._e()},o=[]},7449:function(t,n,e){"use strict";e.r(n);var i=e("2d0f"),o=e.n(i);for(var a in i)["default"].indexOf(a)<0&&function(t){e.d(n,t,(function(){return i[t]}))}(a);n["default"]=o.a},a8c9:function(t,n,e){"use strict";e.r(n);var i=e("6c11"),o=e("7449");for(var a in o)["default"].indexOf(a)<0&&function(t){e.d(n,t,(function(){return o[t]}))}(a);var r=e("828b"),s=Object(r["a"])(o["default"],i["b"],i["c"],!1,null,"707fcac1",null,!1,i["a"],void 0);n["default"]=s.exports}}]);