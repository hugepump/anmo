(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["technician-pages-fxq-real-name"],{"05f1":function(e,t,n){var a=n("c86c");t=a(!1),t.push([e.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.fxq-pages .popup-phone[data-v-1fc71fb2]{width:%?630?%;height:%?500?%}.fxq-pages .popup-phone .input-info[data-v-1fc71fb2]{width:%?570?%;height:%?90?%;background:#f7f7f7}.fxq-pages .popup-phone .input-info .item-input[data-v-1fc71fb2]{height:%?90?%;font-size:%?32?%;text-align:left}.fxq-pages .popup-phone .input-info.sm[data-v-1fc71fb2]{width:%?400?%}.fxq-pages .popup-phone .send-btn[data-v-1fc71fb2]{width:%?150?%;height:%?90?%}',""]),e.exports=t},"183a":function(e,t,n){var a=n("05f1");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var i=n("967d").default;i("2235190c",a,!0,{sourceMap:!1,shadowMode:!1})},"23a6":function(e,t,n){"use strict";n.r(t);var a=n("e972"),i=n("c3a3");for(var o in i)["default"].indexOf(o)<0&&function(e){n.d(t,e,(function(){return i[e]}))}(o);n("2700");var r=n("828b"),u=Object(r["a"])(i["default"],a["b"],a["c"],!1,null,"1fc71fb2",null,!1,a["a"],void 0);t["default"]=u.exports},2700:function(e,t,n){"use strict";var a=n("183a"),i=n.n(a);i.a},b330:function(e,t,n){"use strict";n("6a54");var a=n("f5bd").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,n("fd3c");var i=a(n("2634")),o=a(n("2fdc")),r=a(n("9b1b")),u=n("8f59"),c={data:function(){return{subForm:{name:"",id_code:""},subRule:[{name:"name",checkType:"isNotNull",errorMsg:"请输入姓名",regText:"姓名"},{name:"id_code",checkType:"isIdCard",errorMsg:"请输入身份证号"}]}},computed:(0,u.mapState)({coachInfo:function(e){return e.user.coachInfo},configInfo:function(e){return e.config.configInfo}}),onLoad:function(e){console.log(e,"========> options"),this.initIndex()},methods:(0,r.default)((0,r.default)({},(0,u.mapActions)(["getConfigInfo","getCoachInfo"])),{},{initIndex:function(){var e=arguments,t=this;return(0,o.default)((0,i.default)().mark((function n(){var a;return(0,i.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:if(a=e.length>0&&void 0!==e[0]&&e[0],t.configInfo.id&&!a){n.next=4;break}return n.next=4,t.getConfigInfo();case 4:return t.$util.setNavigationBarColor({bg:t.primaryColor}),n.next=7,t.getCoachInfo();case 7:a||t.$jweixin.hideOptionMenu();case 8:case"end":return n.stop()}}),n)})))()},validate:function(e){var t=new this.$util.Validate;this.subRule.map((function(n){var a=n.name;t.add(e[a],n)}));var n=t.start();return n},submitRealName:function(){var e=this;return(0,o.default)((0,i.default)().mark((function t(){var n,a,o;return(0,i.default)().wrap((function(t){while(1)switch(t.prev=t.next){case 0:if(n=e.$util.deepCopy(e.subForm),a=e.validate(n),!a){t.next=5;break}return e.$util.showToast({title:a}),t.abrupt("return");case 5:return e.$util.showLoading(),t.next=8,e.$api.technician.fxqCheck(n);case 8:o=t.sent,console.log(o,"======> fxqCheck"),e.$util.hideAll(),1==o.check_type?e.$util.goUrl({url:"/technician/pages/fxq/index"}):window.location.href=o.url;case 12:case"end":return t.stop()}}),t)})))()},toFxqSign:function(){var e=this;return(0,o.default)((0,i.default)().mark((function t(){var n,a,o;return(0,i.default)().wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,e.getCoachInfo();case 2:if(n=e.coachInfo,n.id_code,a=n.true_user_name,void 0===a?"":a,o=n.fxq_status,o){t.next=6;break}return e.$util.showToast({title:"请联系平台发起合同签署"}),t.abrupt("return");case 6:if(1!=o){t.next=9;break}return e.$util.showToast({title:"请联系平台签署合同"}),t.abrupt("return");case 9:2!=o&&3!=o||e.$util.goUrl({url:"/technician/pages/fxq/index"});case 10:case"end":return t.stop()}}),t)})))()}})};t.default=c},c3a3:function(e,t,n){"use strict";n.r(t);var a=n("b330"),i=n.n(a);for(var o in a)["default"].indexOf(o)<0&&function(e){n.d(t,e,(function(){return a[e]}))}(o);t["default"]=i.a},e972:function(e,t,n){"use strict";n.d(t,"b",(function(){return a})),n.d(t,"c",(function(){return i})),n.d(t,"a",(function(){}));var a=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("v-uni-view",{staticClass:"fxq-pages flex-x-center fill-base"},[n("v-uni-view",{staticClass:"common-popup-content popup-phone pd-lg flex-center flex-column radius-16"},[n("v-uni-view",{staticClass:"space-lg pb-lg"}),n("v-uni-view",{staticClass:"space-lg pb-lg"}),n("v-uni-view",{staticClass:"flex-center mb-lg"},[n("v-uni-view",{staticClass:"input-info radius-16"},[n("v-uni-input",{staticClass:"item-input flex-y-center pl-lg pr-lg f-sm-title c-title",attrs:{type:"text","placeholder-class":"c-placeholder",placeholder:e.subRule[0].errorMsg},model:{value:e.subForm.name,callback:function(t){e.$set(e.subForm,"name",t)},expression:"subForm.name"}})],1)],1),n("v-uni-view",{staticClass:"input-info radius-16"},[n("v-uni-input",{staticClass:"item-input flex-y-center pl-lg pr-lg f-sm-title c-title",attrs:{type:"text",maxlength:"18","placeholder-class":"c-placeholder",placeholder:e.subRule[1].errorMsg},model:{value:e.subForm.id_code,callback:function(t){e.$set(e.subForm,"id_code",t)},expression:"subForm.id_code"}})],1),n("v-uni-view",{staticClass:"button"},[n("v-uni-view",{staticClass:"item-child",on:{click:function(t){t.stopPropagation(),arguments[0]=t=e.$handleEvent(t),e.$util.goUrl({url:1,openType:"navigateBack"})}}},[e._v("取消")]),n("v-uni-view",{staticClass:"item-child",style:{background:e.primaryColor,color:"#fff"},on:{click:function(t){t.stopPropagation(),arguments[0]=t=e.$handleEvent(t),e.submitRealName.apply(void 0,arguments)}}},[e._v("确定")])],1)],1)],1)},i=[]}}]);