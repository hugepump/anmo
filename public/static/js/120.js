webpackJsonp([120],{"+/SY":function(e,t){},ehDU:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=a("lC5x"),s=a.n(n),r=a("J0Oq"),i=a.n(r),o=a("4YfN"),c=a.n(o),l=a("bSIt"),m=a("PxTW"),u=a.n(m),d={data:function(){return{loading:!1,userTypeText:{1:{type:"primary",text:"用户"},2:{type:"success",text:this.$t("action.attendantName")}},pagePermission:[],subForm:{reply_content:""},dataInfo:{}}},created:function(){var e=this;this.routesItem.routes.map(function(t){"/feedback"===t.path&&t.children.map(function(t){"FeedbackList"===t.name&&(e.pagePermission=t.meta.pagePermission[0].auth)})});var t=this.$route.query.id;this.getDetail(t)},computed:c()({},Object(l.e)({routesItem:function(e){return e.routes}})),methods:{getDetail:function(e){var t=this;return i()(s.a.mark(function a(){var n,r,i,o;return s.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return a.next=2,t.$api.system.feedbackInfo({id:e});case 2:if(n=a.sent,r=n.code,i=n.data,200===r){a.next=7;break}return a.abrupt("return");case 7:i.is_balance=1*i.balance>0?1:0,i.content=i.content?i.content.replace(/\n/g,"<br>"):"暂无内容",t.subForm.reply_content=i.reply_content,t.subForm.id=i.id,o=[],i.images&&i.images.length&&i.images.forEach(function(e){o.push({url:e})}),t.dataInfo=i,t.dataInfo.images=o;case 15:case"end":return a.stop()}},a,t)}))()},submitFormInfo:function(){var e=this,t=this.subForm;this.$api.system.feedbackHandle(t).then(function(t){200===t.code&&(e.$message.success(e.$t("tips.successSub")),e.goBack())})},goBack:function(){this.$route.meta.refresh=!0,this.$router.back(-1)}},filters:{handleTime:function(e,t){return 1===t?u()(1e3*e).format("YYYY-MM-DD"):2===t?u()(1e3*e).format("HH:mm:ss"):3===t?u()(1e3*e).format("YYYY-MM-DD HH:mm"):4===t?u()(1e3*e).format("HH:mm"):u()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},f={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-feedback-detail"},[a("top-nav",{attrs:{isBack:!0}}),e._v(" "),a("div",{staticClass:"page-main"},[a("el-form",{attrs:{model:e.subForm,"label-width":"130px",size:"mini"},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"ID："}},[a("div",[e._v(e._s(e.dataInfo.id))])]),e._v(" "),a("el-form-item",{attrs:{label:"反馈类型："}},[a("div",[e._v(e._s(e.dataInfo.type_name))])]),e._v(" "),a("el-form-item",{attrs:{label:"反馈人："}},[a("div",[e._v(e._s(e.dataInfo.coach_name))])]),e._v(" "),a("el-form-item",{attrs:{label:"反馈人身份："}},[a("div",[a("el-tag",{attrs:{size:"small",type:e.userTypeText[e.dataInfo.type].type}},[e._v(e._s(e.userTypeText[e.dataInfo.type].text))])],1)]),e._v(" "),a("el-form-item",{attrs:{label:"反馈人手机号："}},[a("div",[e._v(e._s(e.dataInfo.mobile||"无"))])]),e._v(" "),a("el-form-item",{attrs:{label:"订单编号："}},[a("div",[e._v(e._s(e.dataInfo.order_code||"无"))])]),e._v(" "),a("el-form-item",{attrs:{label:"反馈内容："}},[a("div",{domProps:{innerHTML:e._s(e.dataInfo.content)}}),e._v(" "),e.dataInfo.images&&e.dataInfo.images.length?a("div",{staticClass:"flex pt-md"},[a("lb-cover",{attrs:{fileList:e.dataInfo.images,isToDel:!1,fileType:"image",type:"more",size:"big",fileSize:1}})],1):e._e(),e._v(" "),e.dataInfo.video_url?a("div",{staticClass:"pt-md"},[a("video",{attrs:{controls:"",width:"500",height:"300",src:e.dataInfo.video_url}})]):e._e()]),e._v(" "),1!=e.dataInfo.status||1==e.dataInfo.status&&e.pagePermission.includes("handle")?a("el-form-item",{attrs:{label:"处理结果："}},[1!==e.dataInfo.status?a("div",[e._v("\n          "+e._s(e.subForm.reply_content||"无")+"\n        ")]):a("div",[a("el-input",{attrs:{type:"textarea",rows:6,placeholder:"请输入内容"},model:{value:e.subForm.reply_content,callback:function(t){e.$set(e.subForm,"reply_content",t)},expression:"subForm.reply_content"}})],1)]):e._e(),e._v(" "),a("el-form-item",[1==e.dataInfo.status&&e.pagePermission.includes("handle")?a("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:e.submitFormInfo}},[e._v(e._s(e.$t("action.submit")))]):e._e(),e._v(" "),a("lb-button",{on:{click:function(t){return e.$router.back(-1)}}},[e._v(e._s(e.$t("action.back")))])],1)],1)],1)],1)},staticRenderFns:[]};var p=a("C7Lr")(d,f,!1,function(e){a("+/SY")},"data-v-4cbc6e2b",null);t.default=p.exports}});