(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-feedback-success"],{"12dc":function(t,e,n){var i=n("c86c");e=i(!1),e.push([t.i,".header uni-image[data-v-6e322ed4]{width:%?260?%;height:%?260?%}.as-btn[data-v-6e322ed4]{margin-top:%?98?%;height:%?84?%;line-height:%?84?%;border-radius:%?84?%}.as-box-cont[data-v-6e322ed4]{padding-top:%?40?%;font-size:%?46?%}",""]),t.exports=e},"6a6b":function(t,e,n){"use strict";n("6a54");var i=n("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=i(n("2634")),r=i(n("2fdc")),o=n("8f59"),c={data:function(){return{}},computed:(0,o.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(){this.$util.setNavigationBarColor({bg:this.primaryColor}),this.initIndex()},methods:{initIndex:function(){var t=arguments,e=this;return(0,r.default)((0,a.default)().mark((function n(){var i;return(0,a.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:i=t.length>0&&void 0!==t[0]&&t[0],i||e.$jweixin.hideOptionMenu();case 2:case"end":return n.stop()}}),n)})))()}}};e.default=c},"6c9e":function(t,e,n){"use strict";var i=n("6d85"),a=n.n(i);a.a},"6d85":function(t,e,n){var i=n("12dc");i.__esModule&&(i=i.default),"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var a=n("967d").default;a("5e80a695",i,!0,{sourceMap:!1,shadowMode:!1})},"7af2":function(t,e,n){"use strict";n.r(e);var i=n("6a6b"),a=n.n(i);for(var r in i)["default"].indexOf(r)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(r);e["default"]=a.a},a338:function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){}));var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{staticClass:"pt-lg",style:{background:t.pageColor}},[n("v-uni-view",{staticClass:"header flex-center pt-lg"},[n("v-uni-image",{attrs:{src:"https://lbqny.migugu.com/admin/shop/succ.png",mode:"aspectFill"}})],1),n("v-uni-view",{staticClass:"as-box-cont text-bold text-center"},[t._v("提交成功")]),n("v-uni-view",{staticClass:"flex-center flex-column f-mini-title c-caption mt-md"},[n("v-uni-view",[t._v("您已经成功提交申请")]),n("v-uni-view",{staticClass:"mt-sm"},[t._v("审核进度和结果可在反馈记录里查看")])],1),n("v-uni-view",{staticClass:"as-btn c-base f-mini-title text-center ml-lg mr-lg",style:{backgroundColor:t.primaryColor},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.$util.goUrl({url:"/user/pages/feedback/list",openType:"redirectTo"})}}},[t._v("查看反馈记录")])],1)},a=[]},f065:function(t,e,n){"use strict";n.r(e);var i=n("a338"),a=n("7af2");for(var r in a)["default"].indexOf(r)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(r);n("6c9e");var o=n("828b"),c=Object(o["a"])(a["default"],i["b"],i["c"],!1,null,"6e322ed4",null,!1,i["a"],void 0);e["default"]=c.exports}}]);