(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-feedback-box"],{"55ca":function(t,e,i){var n=i("c86c");e=n(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.box-item .box-item-title[data-v-7e4c9777]{padding-top:%?40?%}.box-item .box-item-cont[data-v-7e4c9777]{height:%?110?%}.box-item .box-item-cont uni-input[data-v-7e4c9777]{width:100%;height:100%}.box-item .c-5A677E[data-v-7e4c9777]{color:#5a677e}.box-item .nav-item[data-v-7e4c9777]{width:calc((100% - %?40?%) / 3);height:%?68?%;line-height:%?68?%;border-radius:%?68?%}',""]),t.exports=e},7030:function(t,e,i){"use strict";i.r(e);var n=i("7adb"),a=i("c801");for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);i("e05a");var s=i("828b"),l=Object(s["a"])(a["default"],n["b"],n["c"],!1,null,"7e4c9777",null,!1,n["a"],void 0);e["default"]=l.exports},7517:function(t,e,i){var n=i("55ca");n.__esModule&&(n=n.default),"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var a=i("967d").default;a("5443769e",n,!0,{sourceMap:!1,shadowMode:!1})},"7adb":function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return a})),i.d(e,"a",(function(){}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"box",style:{background:t.pageColor}},[i("v-uni-view",{staticClass:"pl-lg pr-lg pb-lg fill-base"},[i("v-uni-view",{staticClass:"box-item"},[i("v-uni-view",{staticClass:"box-item-title f-paragraph pb-md text-bold flex"},[i("v-uni-text",{staticClass:"c-warning f-sm-title"},[t._v("*")]),i("v-uni-text",[t._v("反馈类型")])],1),i("v-uni-view",{staticClass:"radius-16 flex-warp"},[t._l(t.navList,(function(e,n){return[i("v-uni-view",{key:n+"_0",staticClass:"nav-item text-center f-desc c-paragraph mb-md fill-body",class:n%3>0&&"ml-md",style:{backgroundColor:n==t.navIndex?t.primaryColor:"",color:n==t.navIndex?"#fff":""},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.getNavIndex(n)}}},[t._v(t._s(e.title))])]}))],2)],1),i("v-uni-view",{staticClass:"box-item"},[i("v-uni-view",{staticClass:"box-item-title"},[i("v-uni-view",{staticClass:"text-bold f-paragraph"},[t._v("订单编号")]),i("v-uni-view",{staticClass:"c-caption f-caption pt-md pb-lg"},[t._v("若涉及订单, 填入订单号有助解决问题(订单详情页可复制)")])],1),i("v-uni-view",{staticClass:"pr-lg pl-lg fill-body radius-16 f-paragraph flex-between box-item-cont"},[i("v-uni-input",{attrs:{type:"number",placeholder:"请输入订单号"},on:{input:function(e){arguments[0]=e=t.$handleEvent(e),t.getInput.apply(void 0,arguments)}},model:{value:t.param.order_code,callback:function(e){t.$set(t.param,"order_code",e)},expression:"param.order_code"}})],1)],1),i("v-uni-view",{staticClass:"box-item pt-md"},[i("v-uni-view",{staticClass:"box-item-title f-paragraph pb-sm text-bold flex"},[i("v-uni-text",{staticClass:"c-warning f-sm-title"},[t._v("*")]),i("v-uni-text",[t._v("反馈内容")])],1),i("v-uni-view",{staticClass:"pd-lg radius-16 fill-body",staticStyle:{overflow:"hidden"}},[i("v-uni-textarea",{staticClass:"c-paragraph",staticStyle:{width:"100%"},attrs:{cols:"30",rows:"10",placeholder:"您宝贵的建议，是我们不断进步的动力！请详细描述遇到的问题",value:t.param.content,maxlength:"1000"},on:{input:function(e){arguments[0]=e=t.$handleEvent(e),t.bindInput.apply(void 0,arguments)}}}),i("v-uni-view",{staticClass:"pt-md text-right c-5A677E f-paragraph"},[t._v(t._s(t.param.content.length>1e3?1e3:t.param.content.length)+" / 1000")])],1)],1),i("v-uni-view",{staticClass:"box-item mt-md"},[i("upload",{attrs:{imagelist:t.param.images,imgtype:"images",text:"上传图片",imgsize:3},on:{upload:function(e){arguments[0]=e=t.$handleEvent(e),t.imgUpload.apply(void 0,arguments)},del:function(e){arguments[0]=e=t.$handleEvent(e),t.imgUpload.apply(void 0,arguments)}}})],1),i("v-uni-view",{staticClass:"box-item mt-md"},[i("upload",{attrs:{imagelist:t.param.video_url,filetype:"video",imgtype:"video_url",text:"上传视频",imgsize:1,videoSize:30},on:{upload:function(e){arguments[0]=e=t.$handleEvent(e),t.imgUpload.apply(void 0,arguments)},del:function(e){arguments[0]=e=t.$handleEvent(e),t.imgUpload.apply(void 0,arguments)}}})],1),i("v-uni-view",{staticClass:"f-desc c-caption pb-sm"},[t._v("*最多只能上传3张图片、1个视频（视频只支持30兆以内）")])],1),i("v-uni-view",{staticClass:"f-caption c-warning pd-lg"},[t._v("注: 您反馈的意见问题，平台不会透露给他人，保护您的隐私")]),i("v-uni-view",{staticClass:"space-max-footer"}),i("fix-bottom-button",{attrs:{text:[{text:"反馈记录",type:"cancel"},{text:"提交反馈",type:"confirm"}],bgColor:"#fff",classType:2},on:{cancel:function(e){arguments[0]=e=t.$handleEvent(e),t.$util.goUrl({url:"/user/pages/feedback/list"})},confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.confirm.apply(void 0,arguments)}}})],1)},a=[]},"9e02":function(t,e,i){"use strict";i("6a54");var n=i("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("fd3c"),i("0c26"),i("5c47"),i("a1c1");var a=n(i("2634")),o=n(i("2fdc")),s=i("8f59"),l={data:function(){return{param:{content:"",type_name:"",order_code:"",images:[],video_url:[]},navList:[{title:"订单问题"},{title:"功能问题"},{title:"账号问题"},{title:"操作问题"},{title:"BUG反馈"},{title:"其他"}],navIndex:-1}},computed:(0,s.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(t){this.options=t;var e=t.blockuser,i=void 0===e?0:e,n=t.index,a=void 0===n?0:n;if(i){var o=this.$util.getPage(-1).list.data[a].order_code;this.navIndex=5,this.param.order_code=o}this.$util.setNavigationBarColor({bg:this.primaryColor}),this.initIndex()},methods:{initIndex:function(){var t=arguments,e=this;return(0,o.default)((0,a.default)().mark((function i(){var n;return(0,a.default)().wrap((function(i){while(1)switch(i.prev=i.next){case 0:n=t.length>0&&void 0!==t[0]&&t[0],n||e.$jweixin.hideOptionMenu();case 2:case"end":return i.stop()}}),i)})))()},initRefresh:function(){this.initIndex(!0)},bindInput:function(t){this.$nextTick((function(){this.param.content=t.detail.value}))},imgUpload:function(t){var e=t.imagelist,i=t.imgtype;this.param[i]=e},confirm:function(){var t=this,e=this.param,i=e.content,n=e.images,a=e.video_url,o=this.$util.deepCopy(this.param);return o.type_name=this.navIndex>=0&&this.navList[this.navIndex].title,n.length&&(o.images=n.map((function(t){return t.path}))),a.length&&(o.video_url=a[0].path),o.type_name?i.trim()?(this.$util.showLoading(),void this.$api.mine.addFeedback(o).then((function(e){t.$util.hideAll(),t.$util.goUrl({url:"/user/pages/feedback/success",openType:"redirectTo"})}))):this.$util.showToast({title:"请输入反馈内容"}):this.$util.showToast({title:"请选择反馈类型"})},getNavIndex:function(t){this.navIndex=t},getInput:function(t){this.$nextTick((function(){this.param.order_code=t.detail.value.replace(/\D/g,"")}))}}};e.default=l},c801:function(t,e,i){"use strict";i.r(e);var n=i("9e02"),a=i.n(n);for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);e["default"]=a.a},e05a:function(t,e,i){"use strict";var n=i("7517"),a=i.n(n);a.a}}]);