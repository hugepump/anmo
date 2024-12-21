webpackJsonp([176],{"3nXN":function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("3cXf"),s=a.n(r),i=a("lC5x"),o=a.n(i),l=a("J0Oq"),n=a.n(l),u={data:function(){return{subForm:{status:1,appid:"",api_key_live:"",api_key_test:"",rsa_private_key:"",pay_mode:1,commission:0,draw_cash_type:"T1"},subFormRules:{status:{required:!0,type:"number",message:"请选择是否开启分账功能",trigger:"blur"},appid:{required:!0,validator:this.$reg.isNotNull,text:"appid",reg_type:2,trigger:"blur"},api_key_live:{required:!0,validator:this.$reg.isNotNull,text:"api_key_live",reg_type:2,trigger:"blur"},api_key_test:{required:!0,validator:this.$reg.isNotNull,text:"api_key_test",reg_type:2,trigger:"blur"},rsa_private_key:{required:!0,validator:this.$reg.isNotNull,text:"rsa_private_key",reg_type:2,trigger:"blur"},pay_mode:{required:!0,type:"number",message:"请选择分账方式",trigger:"blur"},commission:{required:!0,type:"number",message:"请选择手续费承担人",trigger:"blur"},draw_cash_type:{required:!0,type:"string",message:"请选择转账到账时间",trigger:"blur"}}}},created:function(){this.getDetail()},methods:{getDetail:function(){var e=this;return n()(o.a.mark(function t(){var a,r,s,i;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.$api.adapay.configInfo();case 2:if(a=t.sent,r=a.code,s=a.data,200===r){t.next=7;break}return t.abrupt("return");case 7:for(i in e.subForm)e.subForm[i]=s[i];case 8:case"end":return t.stop()}},t,e)}))()},submitFormInfo:function(){var e=this;return n()(o.a.mark(function t(){var a,r,i;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(a=e.subForm.status,r=!0,1===a&&e.$refs.subForm.validate(function(e){e||(r=!1)}),i=JSON.parse(s()(e.subForm)),r){t.next=6;break}return t.abrupt("return");case 6:return t.next=8,e.$api.adapay.configUpdate(i);case 8:e.$message.success(e.$t("tips.successSub"));case 9:case"end":return t.stop()}},t,e)}))()}}},p={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-adapay-set"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("el-form",{ref:"subForm",staticClass:"basic-form",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"120px"},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"分账功能",prop:"status"}},[a("el-radio-group",{model:{value:e.subForm.status,callback:function(t){e.$set(e.subForm,"status",t)},expression:"subForm.status"}},[a("el-radio",{attrs:{label:1}},[e._v(e._s(e.$t("action.ON")))]),e._v(" "),a("el-radio",{attrs:{label:0}},[e._v(e._s(e.$t("action.OFF")))])],1)],1),e._v(" "),1===e.subForm.status?a("block",[a("el-form-item",{attrs:{label:"appid",prop:"appid"}},[a("el-input",{attrs:{placeholder:"请输入appid"},model:{value:e.subForm.appid,callback:function(t){e.$set(e.subForm,"appid",t)},expression:"subForm.appid"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"api_key_live",prop:"api_key_live"}},[a("el-input",{attrs:{placeholder:"请输入api_key_live"},model:{value:e.subForm.api_key_live,callback:function(t){e.$set(e.subForm,"api_key_live",t)},expression:"subForm.api_key_live"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"api_key_test",prop:"api_key_test"}},[a("el-input",{attrs:{placeholder:"请输入api_key_test"},model:{value:e.subForm.api_key_test,callback:function(t){e.$set(e.subForm,"api_key_test",t)},expression:"subForm.api_key_test"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"rsa_private_key",prop:"rsa_private_key"}},[a("el-input",{attrs:{type:"textarea",resize:"none",rows:10,placeholder:"请输入rsa_private_key"},model:{value:e.subForm.rsa_private_key,callback:function(t){e.$set(e.subForm,"rsa_private_key",t)},expression:"subForm.rsa_private_key"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"分账方式",prop:"pay_mode"}},[a("el-radio-group",{model:{value:e.subForm.pay_mode,callback:function(t){e.$set(e.subForm,"pay_mode",t)},expression:"subForm.pay_mode"}},[a("el-radio",{attrs:{label:1}},[e._v("自动分账")]),e._v(" "),a("el-radio",{attrs:{label:2}},[e._v("余额结算")])],1),e._v(" "),a("lb-tool-tips",[e._v("\n            自动分账即在订单完成后立即发生分账动作，每个角色所分得的金额不能修改\n            "),a("div",{staticClass:"mt-sm"},[e._v("\n              同时必须有对应角色去提现，平台不能把其他角色应得金额提走，扣除"+e._s(e.$t("action.attendantName"))+"余额功能会失效\n            ")]),e._v(" "),a("div",{staticClass:"mt-md"},[e._v("\n              选择余额结算，资金会直接到平台空中账户的余额账户，平台可以将所有人的钱提走，也可以灵活设置打款金额\n            ")])]),e._v(" "),a("div",{staticClass:"f-caption c-warning"},[e._v("\n            使用余额结算需要去找汇付天下商务开通余额结算权限，才可使用\n          ")])],1),e._v(" "),1===e.subForm.pay_mode?a("el-form-item",{attrs:{label:"手续费承担人",prop:"commission"}},[a("el-radio-group",{model:{value:e.subForm.commission,callback:function(t){e.$set(e.subForm,"commission",t)},expression:"subForm.commission"}},[a("el-radio",{attrs:{label:0}},[e._v("平台")]),e._v(" "),a("el-radio",{attrs:{label:1}},[e._v("提现人")])],1),e._v(" "),a("lb-tool-tips",[e._v("\n            对接第三方支付，会产生技术服务费，即和第三方谈的支付手续费\n            "),a("div",{staticClass:"mt-sm"},[e._v("\n              可选择该手续费是由平台自行承担还是提现的人来承担，如果是提现的人承担，则参与了分账的角色都会扣除该笔手续费\n            ")])])],1):e._e(),e._v(" "),a("el-form-item",{attrs:{label:"转账到账时间",prop:"draw_cash_type"}},[a("el-radio-group",{model:{value:e.subForm.draw_cash_type,callback:function(t){e.$set(e.subForm,"draw_cash_type",t)},expression:"subForm.draw_cash_type"}},[a("el-radio",{attrs:{label:"T1"}},[e._v("T1")]),e._v(" "),a("el-radio",{attrs:{label:"D1"}},[e._v("D1")])],1),e._v(" "),a("div",{staticClass:"f-caption c-warning"},[e._v("\n            默认T1，选择D1需联系汇付天下商务开通\n          ")])],1)],1):e._e(),e._v(" "),a("el-form-item",[a("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:e.submitFormInfo}},[e._v(e._s(e.$t("action.submit")))])],1)],1)],1)],1)},staticRenderFns:[]};var _=a("C7Lr")(u,p,!1,function(e){a("o0My")},"data-v-1a429da2",null);t.default=_.exports},o0My:function(e,t){}});