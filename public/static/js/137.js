webpackJsonp([137],{DacI:function(e,r){},yED0:function(e,r,t){"use strict";Object.defineProperty(r,"__esModule",{value:!0});var o=t("3cXf"),s=t.n(o),l=t("lC5x"),_=t.n(l),n=t("J0Oq"),a=t.n(n),i={data:function(){var e=this,r=function(r,t,o){var s=e.subForm[r.key],l=e.subForm.type;s=s?s.replace(/(^\s*)|(\s*$)/g,""):"",2===l&&(2!==l||"short_sign"===r.key)||s?o():o(new Error("请输入"+r.text))},t=function(r,t,o){var s=e.subForm,l=s.type,_=s.short_code_status,n=void 0===_?0:_;t=t?t.replace(/(^\s*)|(\s*$)/g,""):"",1!==n||t?o():o(new Error(3===l?"请输入验证码通知内容":"请输入短信验证码模板CODE"))};return{subForm:{type:1,short_sign:"",order_short_code:"",refund_short_code:"",help_short_code:"",short_code_status:0,bind_phone_type:0,short_code:"",moor_order_short_code:"",moor_refund_short_code:"",moor_help_short_code:"",moor_short_code:"",winner_user:"",winner_pass:"",winner_order_text:"",winner_refund_text:"",winner_police_text:"",winner_code_text:""},subFormRules:{type:{required:!0,type:"number",message:"请选择短信通知运营商",trigger:"blur"},short_sign:{required:!0,validator:r,key:"short_sign",text:"签名",trigger:"blur"},order_short_code:{required:!0,validator:r,key:"order_short_code",text:"下单模板CODE",trigger:"blur"},refund_short_code:{required:!0,validator:r,key:"refund_short_code",text:"退单模板CODE",trigger:"blur"},help_short_code:{required:!0,validator:r,key:"help_short_code",text:"求救通知模板CODE",trigger:"blur"},moor_order_short_code:{required:!0,validator:r,key:"moor_order_short_code",text:"下单模板CODE",trigger:"blur"},moor_refund_short_code:{required:!0,validator:r,key:"moor_refund_short_code",text:"退单模板CODE",trigger:"blur"},moor_help_short_code:{required:!0,validator:r,key:"moor_help_short_code",text:"求救通知模板CODE",trigger:"blur"},winner_user:{required:!0,validator:r,key:"winner_user",text:"短信登录账号",trigger:"blur"},winner_pass:{required:!0,validator:r,key:"winner_pass",text:"短信登录密码",trigger:"blur"},winner_order_text:{required:!0,validator:r,key:"winner_order_text",text:"下单通知内容",trigger:"blur"},winner_refund_text:{required:!0,validator:r,key:"winner_order_text",text:"退款通知内容",trigger:"blur"},winner_police_text:{required:!0,validator:r,key:"winner_police_text",text:"求救通知内容",trigger:"blur"},short_code_status:{required:!0,type:"number",message:"请选择是否启用短信模板",trigger:"blur"},bind_phone_type:{required:!0,type:"number",message:"请选择绑定手机号方式",trigger:"blur"},short_code:{required:!0,validator:t,trigger:"blur"},moor_short_code:{required:!0,validator:t,trigger:"blur"},winner_code_text:{required:!0,validator:t,trigger:"blur"}}}},created:function(){var e=this;return a()(_.a.mark(function r(){return _.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return r.next=2,e.getDetail();case 2:case"end":return r.stop()}},r,e)}))()},methods:{getDetail:function(){var e=this;return a()(_.a.mark(function r(){var t,o,s,l;return _.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return r.next=2,e.$api.system.shortCodeConfigInfo();case 2:if(t=r.sent,o=t.code,s=t.data,200===o){r.next=7;break}return r.abrupt("return");case 7:for(l in e.subForm)e.subForm[l]=s[l];case 8:case"end":return r.stop()}},r,e)}))()},submitFormInfo:function(e){var r=this,t=!0;if(this.$refs[e].validate(function(e){e||(t=!1)}),t){var o=JSON.parse(s()(this.subForm));this.$api.system.shortCodeConfigUpdate(o).then(function(e){200===e.code&&r.$message.success(r.$t("tips.successSub"))})}}}},u={render:function(){var e=this,r=e.$createElement,t=e._self._c||r;return t("div",{staticClass:"lb-system-message"},[t("top-nav"),e._v(" "),t("div",{staticClass:"page-main"},[t("el-form",{ref:"subForm",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"160px"},nativeOn:{submit:function(e){e.preventDefault()}}},[t("el-form-item",{attrs:{label:"短信通知运营商",prop:"type"}},[t("el-radio-group",{model:{value:e.subForm.type,callback:function(r){e.$set(e.subForm,"type",r)},expression:"subForm.type"}},[t("el-radio",{attrs:{label:1}},[e._v("阿里云")]),e._v(" "),t("el-radio",{attrs:{label:2}},[e._v("容联七陌")]),e._v(" "),t("el-radio",{attrs:{label:3}},[e._v("云信")])],1)],1),e._v(" "),2!==e.subForm.type?t("el-form-item",{attrs:{label:"签名",prop:"short_sign"}},[t("el-input",{attrs:{placeholder:"请输入签名"},model:{value:e.subForm.short_sign,callback:function(r){e.$set(e.subForm,"short_sign",r)},expression:"subForm.short_sign"}}),e._v(" "),t("lb-tool-tips",[e._v("签名在"+e._s(1===e.subForm.type?"阿里云":"云信")+"短信签名管理处查看")])],1):e._e(),e._v(" "),1===e.subForm.type?t("block",[t("el-form-item",{attrs:{label:"下单模板CODE",prop:"order_short_code"}},[t("el-input",{attrs:{placeholder:"请输入下单模板CODE"},model:{value:e.subForm.order_short_code,callback:function(r){e.$set(e.subForm,"order_short_code",r)},expression:"subForm.order_short_code"}}),e._v(" "),t("lb-tool-tips",[e._v("短信模板CODE, 如: SMS_129755997")])],1),e._v(" "),t("el-form-item",{attrs:{label:"退单模板CODE",prop:"refund_short_code"}},[t("el-input",{attrs:{placeholder:"请输入下单模板CODE"},model:{value:e.subForm.refund_short_code,callback:function(r){e.$set(e.subForm,"refund_short_code",r)},expression:"subForm.refund_short_code"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"求救通知模板CODE",prop:"help_short_code"}},[t("el-input",{attrs:{placeholder:"请输入求救通知模板CODE"},model:{value:e.subForm.help_short_code,callback:function(r){e.$set(e.subForm,"help_short_code",r)},expression:"subForm.help_short_code"}})],1)],1):e._e(),e._v(" "),2===e.subForm.type?t("block",[t("el-form-item",{attrs:{label:"下单模板CODE",prop:"moor_order_short_code"}},[t("el-input",{attrs:{placeholder:"请输入下单模板CODE"},model:{value:e.subForm.moor_order_short_code,callback:function(r){e.$set(e.subForm,"moor_order_short_code",r)},expression:"subForm.moor_order_short_code"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"退单模板CODE",prop:"moor_refund_short_code"}},[t("el-input",{attrs:{placeholder:"请输入下单模板CODE"},model:{value:e.subForm.moor_refund_short_code,callback:function(r){e.$set(e.subForm,"moor_refund_short_code",r)},expression:"subForm.moor_refund_short_code"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"求救通知模板CODE",prop:"moor_help_short_code"}},[t("el-input",{attrs:{placeholder:"请输入求救通知模板CODE"},model:{value:e.subForm.moor_help_short_code,callback:function(r){e.$set(e.subForm,"moor_help_short_code",r)},expression:"subForm.moor_help_short_code"}})],1)],1):e._e(),e._v(" "),3===e.subForm.type?t("block",[t("el-form-item",{attrs:{label:"短信登录账号",prop:"winner_user"}},[t("el-input",{attrs:{placeholder:"请输入短信登录账号"},model:{value:e.subForm.winner_user,callback:function(r){e.$set(e.subForm,"winner_user",r)},expression:"subForm.winner_user"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"短信登录密码",prop:"winner_pass"}},[t("el-input",{attrs:{placeholder:"请输入短信登录密码"},model:{value:e.subForm.winner_pass,callback:function(r){e.$set(e.subForm,"winner_pass",r)},expression:"subForm.winner_pass"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"下单通知内容",prop:"winner_order_text"}},[t("el-input",{attrs:{placeholder:"请输入下单通知内容"},model:{value:e.subForm.winner_order_text,callback:function(r){e.$set(e.subForm,"winner_order_text",r)},expression:"subForm.winner_order_text"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"退款通知内容",prop:"winner_refund_text"}},[t("el-input",{attrs:{placeholder:"请输入退款通知内容"},model:{value:e.subForm.winner_refund_text,callback:function(r){e.$set(e.subForm,"winner_refund_text",r)},expression:"subForm.winner_refund_text"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"求救通知内容",prop:"winner_police_text"}},[t("el-input",{attrs:{placeholder:"请输入求救通知内容"},model:{value:e.subForm.winner_police_text,callback:function(r){e.$set(e.subForm,"winner_police_text",r)},expression:"subForm.winner_police_text"}})],1)],1):e._e(),e._v(" "),t("el-form-item",{attrs:{label:"是否启用短信验证码模板",prop:"short_code_status"}},[t("el-radio-group",{model:{value:e.subForm.short_code_status,callback:function(r){e.$set(e.subForm,"short_code_status",r)},expression:"subForm.short_code_status"}},[t("el-radio",{attrs:{label:1}},[e._v(e._s(e.$t("action.ON")))]),e._v(" "),t("el-radio",{attrs:{label:0}},[e._v(e._s(e.$t("action.OFF")))])],1),e._v(" "),t("lb-tool-tips",[e._v("开启后，必须配置短信验证码模板CODE，用于手机端通过短信验证码收集用户手机号码\n        ")])],1),e._v(" "),1===e.subForm.short_code_status?t("block",[t("el-form-item",{attrs:{label:"绑定手机号方式",prop:"bind_phone_type"}},[t("el-radio-group",{model:{value:e.subForm.bind_phone_type,callback:function(r){e.$set(e.subForm,"bind_phone_type",r)},expression:"subForm.bind_phone_type"}},[t("el-radio",{attrs:{label:0}},[e._v("弹窗显示绑定手机号")]),e._v(" "),t("el-radio",{attrs:{label:1}},[e._v("跳转到绑定手机号页面")])],1),e._v(" "),t("lb-tool-tips",[e._v("当用户在手机端点击登录且未绑定手机号时，若再次访问用户端其他页面，绑定手机号方式应以当前设置为准\n            "),t("div",{staticClass:"mt-md"},[e._v("手机端触发绑定手机号位置：")]),e._v(" "),t("div",{staticClass:"mt-sm"},[e._v("\n              1、手机端点击登录按钮后，若未绑定手机号，将自动跳转至绑定手机号页面（这里默认跳转,与绑定手机号方式设置无关）\n            ")]),e._v(" "),t("div",{staticClass:"mt-sm"},[e._v("2、关联表单数据的文章详情，【提交】按钮")]),e._v(" "),t("div",{staticClass:"mt-sm"},[e._v("\n              3、服务列表/详情页面，【选择"+e._s(e.$t("action.attendantName"))+"】按钮\n            ")]),e._v(" "),t("div",{staticClass:"mt-sm"},[e._v("\n              4、"+e._s(e.$t("action.attendantName"))+"列表页面，【立即预约】按钮\n            ")]),e._v(" "),t("div",{staticClass:"mt-sm"},[e._v("\n              5、我的页面，用户端【设置】按钮除外的其他功能模块\n            ")])])],1),e._v(" "),1===e.subForm.type?t("el-form-item",{attrs:{label:"短信验证码模板CODE",prop:"short_code"}},[t("el-input",{attrs:{placeholder:"请输入短信验证码模板CODE"},model:{value:e.subForm.short_code,callback:function(r){e.$set(e.subForm,"short_code",r)},expression:"subForm.short_code"}}),e._v(" "),t("lb-tool-tips",[e._v("用于发送短信验证码")])],1):e._e(),e._v(" "),2===e.subForm.type?t("el-form-item",{attrs:{label:"短信验证码模板CODE",prop:"moor_short_code"}},[t("el-input",{attrs:{placeholder:"请输入短信验证码模板CODE"},model:{value:e.subForm.moor_short_code,callback:function(r){e.$set(e.subForm,"moor_short_code",r)},expression:"subForm.moor_short_code"}}),e._v(" "),t("lb-tool-tips",[e._v("用于发送短信验证码")])],1):e._e(),e._v(" "),3===e.subForm.type?t("el-form-item",{attrs:{label:"验证码通知内容",prop:"winner_code_text"}},[t("el-input",{attrs:{placeholder:"请输入验证码通知内容"},model:{value:e.subForm.winner_code_text,callback:function(r){e.$set(e.subForm,"winner_code_text",r)},expression:"subForm.winner_code_text"}}),e._v(" "),t("lb-tool-tips",[e._v("用于发送短信验证码")])],1):e._e()],1):e._e(),e._v(" "),t("el-form-item",[t("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(r){return e.submitFormInfo("subForm")}}},[e._v(e._s(e.$t("action.submit")))])],1)],1)],1)],1)},staticRenderFns:[]};var d=t("C7Lr")(i,u,!1,function(e){t("DacI")},"data-v-4091d770",null);r.default=d.exports}});