(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["member-pages-rule"],{1148:function(t,e,n){"use strict";n.r(e);var i=n("4136"),a=n.n(i);for(var r in i)["default"].indexOf(r)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(r);e["default"]=a.a},4136:function(t,e,n){"use strict";n("6a54");var i=n("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=i(n("2634")),r=i(n("2fdc")),o=i(n("9b1b")),u=n("8f59"),s=i(n("0812")),d={components:{parser:s.default},data:function(){return{options:{},isLoad:!1,detail:{}}},computed:(0,u.mapState)({}),onLoad:function(t){this.options=t,this.$util.showLoading(),this.initIndex()},methods:(0,o.default)((0,o.default)((0,o.default)({},(0,u.mapActions)(["getConfigInfo","getUserInfo"])),(0,u.mapMutations)(["updateUserItem"])),{},{initIndex:function(){var t=arguments,e=this;return(0,r.default)((0,a.default)().mark((function n(){var i,r;return(0,a.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:return i=t.length>0&&void 0!==t[0]&&t[0],n.next=3,e.$api.member.configInfo();case 3:r=n.sent,e.detail=r,e.$util.setNavigationBarColor({bg:e.primaryColor}),e.$util.hideAll(),e.isLoad=!0,i||e.$jweixin.hideOptionMenu();case 9:case"end":return n.stop()}}),n)})))()},initRefresh:function(){this.initIndex(!0)},linkpress:function(t){}})};e.default=d},"7a75":function(t,e,n){"use strict";n.r(e);var i=n("e2c3"),a=n("1148");for(var r in a)["default"].indexOf(r)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(r);var o=n("828b"),u=Object(o["a"])(a["default"],i["b"],i["c"],!1,null,"c2985442",null,!1,i["a"],void 0);e["default"]=u.exports},e2c3:function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){}));var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return t.isLoad?n("v-uni-view",{staticClass:"member-rule",style:{background:t.pageColor}},[n("v-uni-view",{staticClass:"pd-lg f-paragraph"},[n("parser",{attrs:{html:t.detail.member_text,"show-with-animation":!0,"lazy-load":!0},on:{linkpress:function(e){arguments[0]=e=t.$handleEvent(e),t.linkpress.apply(void 0,arguments)}}},[t._v("加载中...")])],1),n("v-uni-view",{staticClass:"space-footer"})],1):t._e()},a=[]}}]);