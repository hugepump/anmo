webpackJsonp([43],{J2F0:function(t,e){},iwfl:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=a("3cXf"),n=a.n(r),s=a("lC5x"),i=a.n(s),o=a("J0Oq"),u=a.n(o),c={data:function(){return{navTitle:"",subForm:{status:0,discount:"",balance:"",text:""},subFormRules:{status:{required:!0,type:"number",message:"请选择",trigger:"blur"},discount:{required:!0,validator:function(t,e,a){""===e||!/^(?:[1-9]?|([0-9]*\.\d{1}))$/.test(e)||e&&1*e>10?a(new Error(""===e?"请输入"+t.text:"请输入正确"+t.text+"，0.1至9.9，最多保留1位小数")):a()},text:"会员卡折扣",trigger:"blur"},discount_type:{required:!0,type:"number",message:"请选择",trigger:"blur"},discount_coach_balance:{required:!0,validator:this.$reg.isDotPercent,text:this.$t("action.attendantName")+"承担比例",trigger:"blur"},discount_admin_balance:{required:!0,validator:this.$reg.isDotPercent,text:this.$t("action.attendantName")+"所属"+this.$t("action.agentName")+"承担比例",trigger:"blur"},balance:{required:!0,validator:this.$reg.isDotPercent,text:this.$t("action.attendantName")+"返佣",trigger:"blur"},admin_balance:{required:!0,validator:this.$reg.isDotPercent,text:this.$t("action.agentName")+"返佣",trigger:"blur"},text:{required:!0,validator:this.$reg.isNotNull,text:"会员协议",reg_type:2,trigger:"blur"}}}},created:function(){var t=this;return u()(i.a.mark(function e(){return i.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:t.getDetail();case 1:case"end":return e.stop()}},e,t)}))()},methods:{getDetail:function(){var t=this;return u()(i.a.mark(function e(){var a,r,n;return i.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.$api.memberdiscount.configInfo();case 2:for(n in a=e.sent,(r=a.data).text=null===r.text?"":r.text,t.subForm)t.subForm[n]=r[n];case 6:case"end":return e.stop()}},e,t)}))()},submitFormInfo:function(){var t=this;return u()(i.a.mark(function e(){var a,r,s;return i.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:if(a=t.subForm.status,r=!0,1===a&&t.$refs.subForm.validate(function(t){t||(r=!1)}),!(1*(s=JSON.parse(n()(t.subForm))).discount_coach_balance+1*s.discount_admin_balance>100)){e.next=7;break}return t.$message.error(t.$t("action.attendantName")+"和"+t.$t("action.attendantName")+"所属"+t.$t("action.agentName")+"承担比例总计不能大于100%"),e.abrupt("return");case 7:if(!(1*s.balance+1*s.admin_balance>100)){e.next=10;break}return t.$message.error(t.$t("action.attendantName")+"和"+t.$t("action.agentName")+"返佣总计不能大于100%"),e.abrupt("return");case 10:if(r){e.next=12;break}return e.abrupt("return");case 12:return e.next=14,t.$api.memberdiscount.configUpdate(s);case 14:t.$message.success(t.$t("tips.successSub"));case 15:case"end":return e.stop()}},e,t)}))()}}},l={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"lb-custom-member-set"},[a("top-nav"),t._v(" "),a("div",{staticClass:"page-main"},[a("el-form",{ref:"subForm",attrs:{model:t.subForm,rules:t.subFormRules,"label-width":"140px"},nativeOn:{submit:function(t){t.preventDefault()}}},[a("el-form-item",{attrs:{label:"会员设置",prop:"status"}},[a("el-radio-group",{model:{value:t.subForm.status,callback:function(e){t.$set(t.subForm,"status",e)},expression:"subForm.status"}},[a("el-radio",{attrs:{label:1}},[t._v(t._s(t.$t("action.ON")))]),t._v(" "),a("el-radio",{attrs:{label:0}},[t._v(t._s(t.$t("action.OFF")))])],1),t._v(" "),a("lb-tool-tips",[t._v("开启后，会员折扣和会员套餐才能生效")])],1),t._v(" "),1===t.subForm.status?a("block",[a("el-form-item",{attrs:{label:"会员卡折扣",prop:"discount"}},[a("el-input",{attrs:{placeholder:""},model:{value:t.subForm.discount,callback:function(e){t.$set(t.subForm,"discount",e)},expression:"subForm.discount"}},[a("template",{slot:"append"},[t._v("折")])],2),t._v(" "),a("lb-tool-tips",[t._v("取值0.1-9.9的数值，支持输入小数，保留小数点后1位")])],1),t._v(" "),a("el-form-item",{attrs:{label:t.$t("action.attendantName")+"返佣",prop:"balance"}},[a("el-input",{attrs:{placeholder:""},model:{value:t.subForm.balance,callback:function(e){t.$set(t.subForm,"balance",e)},expression:"subForm.balance"}},[a("template",{slot:"append"},[t._v("%")])],2),t._v(" "),a("lb-tool-tips",[t._v(t._s(t.$t("action.attendantName"))+"邀请用户购买会员卡，"+t._s(t.$t("action.attendantName"))+"获得用户购买会员卡金额的百分比\n            "),a("div",{staticClass:"mt-sm"},[t._v("\n              取值0-100的数值，支持输入小数，保留小数点后2位\n            ")])])],1),t._v(" "),a("el-form-item",{attrs:{label:"会员协议",prop:"text"}},[a("lb-ueditor",{attrs:{destroy:!0,ueditorType:2},model:{value:t.subForm.text,callback:function(e){t.$set(t.subForm,"text",e)},expression:"subForm.text"}})],1)],1):t._e(),t._v(" "),a("el-form-item",[a("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:t.submitFormInfo}},[t._v(t._s(t.$t("action.submit")))])],1)],1)],1)],1)},staticRenderFns:[]};var m=a("C7Lr")(c,l,!1,function(t){a("J2F0")},"data-v-d1c2ece8",null);e.default=m.exports}});