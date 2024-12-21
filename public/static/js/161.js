webpackJsonp([161],{"45f/":function(t,e,r){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a=r("3cXf"),s=r.n(a),o=r("lC5x"),i=r.n(o),n=r("J0Oq"),l=r.n(n),c=r("4YfN"),u=r.n(c),m=r("bSIt"),p={data:function(){return{loading:!1,navTitle:"",subForm:{status:0,fxq_api_key:"",fxq_secret_key:"",company_name:"",company_title:"",contract_years:"",company_seq_no:"",company_id_no:"",contract:"",commitment:"",corporation:"",is_check:0},subFormRules:{status:{required:!0,type:"number",message:"请选择",trigger:"blur"}},bFormRules:{fxq_api_key:{required:!0,validator:this.$reg.isNotNull,text:"AppId",reg_type:2,trigger:"blur"},fxq_secret_key:{required:!0,validator:this.$reg.isNotNull,text:"AppSecret",reg_type:2,trigger:"blur"},company_name:{required:!0,validator:this.$reg.isNotNull,text:"公司名称",reg_type:2,trigger:"blur"},company_title:{required:!0,validator:this.$reg.isNotNull,text:"公章副标题",reg_type:2,trigger:"blur"},contract_years:{required:!0,validator:this.$reg.isNum,text:"合同有效期",reg_type:2,trigger:"blur"},contract:{required:!0,validator:this.$reg.isNotNull,text:"合同模版",reg_type:2,trigger:"blur"},company_id_no:{required:!0,validator:this.$reg.isNotNull,text:"社会统一信用代码",reg_type:2,trigger:"blur"},corporation:{required:!0,validator:this.$reg.isNotNull,text:"公司法人",reg_type:2,trigger:"blur"},company_seq_no:{required:!0,validator:this.$reg.isNotNull,text:"公章下弦文",reg_type:2,trigger:"blur"}},regArr:{reg:/\.(doc|docx)$/,text:"doc/docx"},showDialog:!1}},computed:u()({},Object(m.e)({routesItem:function(t){return t.routes}})),created:function(){this.getDetail()},methods:{getDetail:function(){var t=this;return l()(i.a.mark(function e(){var r,a,s,o;return i.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.$api.system.fxqConfigInfo();case 2:if(r=e.sent,a=r.code,s=r.data,200===a){e.next=7;break}return e.abrupt("return");case 7:for(o in t.subForm)t.subForm[o]=s[o];case 8:case"end":return e.stop()}},e,t)}))()},getFile:function(t,e){var r=t.length-1;this.subForm[e]=t[r].url},toResetFile:function(t){this.subForm[t]=""},submitFormInfo:function(){var t=this;return l()(i.a.mark(function e(){var r,a,o,n,l,c,u,m,p;return i.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:for(r=JSON.parse(s()(t.subForm)),a=["aForm"],1===r.status&&a.push("bForm"),o=!0,n=0,l=a.length;n<l;n++)t.$refs[a[n]].validate(function(t){if(!t)return o=!1,!1});if(o){e.next=7;break}return e.abrupt("return");case 7:if(c=t.regArr,u=c.reg,m=c.text,1!==r.status||u.test(r.contract)){e.next=11;break}return t.$message.error("合同模版仅支持"+m+"格式!"),e.abrupt("return");case 11:if(!r.commitment||1!==r.status||u.test(r.commitment)){e.next=14;break}return t.$message.error("承诺书仅支持"+m+"格式!"),e.abrupt("return");case 14:return delete r.attestation_status,t.loading=!0,e.next=18,t.$api.system.fxqConfigUpdate(r);case 18:if(p=e.sent,200===p.code){e.next=22;break}return e.abrupt("return");case 22:t.loading=!1,t.$message.success(t.$t("tips.successSub"));case 24:case"end":return e.stop()}},e,t)}))()}},filters:{handleFileName:function(t,e){var r=1===e?"请上传合同模版":"请上传承诺书";return t&&(r=t.substring(t.lastIndexOf("/")+1)),r}}},_={render:function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"lb-system-fdd-set"},[r("top-nav"),t._v(" "),r("div",{staticClass:"page-main"},[r("lb-tips",[t._v("系统目前对接的是放心签第三方电子签约平台，用于和平台入驻"+t._s(t.$t("action.attendantName"))+"签订线上合作协议，如需使用需在放心签平台购买套餐后，电子合同方可生效\n    ")]),t._v(" "),r("el-form",{ref:"aForm",staticClass:"basic-form",attrs:{model:t.subForm,rules:t.subFormRules,"label-width":"160px"},nativeOn:{submit:function(t){t.preventDefault()}}},[r("el-form-item",{attrs:{label:"是否启用",prop:"status"}},[r("el-radio-group",{model:{value:t.subForm.status,callback:function(e){t.$set(t.subForm,"status",e)},expression:"subForm.status"}},[r("el-radio",{attrs:{label:1}},[t._v(t._s(t.$t("action.ON")))]),t._v(" "),r("el-radio",{attrs:{label:0}},[t._v(t._s(t.$t("action.OFF")))])],1)],1),t._v(" "),1===t.subForm.status?r("el-form",{ref:"bForm",staticClass:"basic-form",attrs:{model:t.subForm,rules:t.bFormRules,"label-width":"160px"},nativeOn:{submit:function(t){t.preventDefault()}}},[r("el-form-item",{attrs:{label:"AppId",prop:"fxq_api_key"}},[r("el-input",{attrs:{placeholder:"请输入AppId"},model:{value:t.subForm.fxq_api_key,callback:function(e){t.$set(t.subForm,"fxq_api_key",e)},expression:"subForm.fxq_api_key"}})],1),t._v(" "),r("el-form-item",{attrs:{label:"AppSecret",prop:"fxq_secret_key"}},[r("el-input",{attrs:{placeholder:"请输入AppSecret"},model:{value:t.subForm.fxq_secret_key,callback:function(e){t.$set(t.subForm,"fxq_secret_key",e)},expression:"subForm.fxq_secret_key"}})],1),t._v(" "),r("el-form-item",{attrs:{label:"公司名称",prop:"company_name"}},[r("el-input",{attrs:{placeholder:"请输入公司名称"},model:{value:t.subForm.company_name,callback:function(e){t.$set(t.subForm,"company_name",e)},expression:"subForm.company_name"}})],1),t._v(" "),r("el-form-item",{attrs:{label:"公司法人",prop:"corporation"}},[r("el-input",{attrs:{placeholder:"请输入公司法人"},model:{value:t.subForm.corporation,callback:function(e){t.$set(t.subForm,"corporation",e)},expression:"subForm.corporation"}})],1),t._v(" "),r("el-form-item",{attrs:{label:"公章副标题",prop:"company_title"}},[r("el-input",{attrs:{placeholder:"请输入公章副标题"},model:{value:t.subForm.company_title,callback:function(e){t.$set(t.subForm,"company_title",e)},expression:"subForm.company_title"}})],1),t._v(" "),r("el-form-item",{attrs:{label:"公章下弦文",prop:"company_seq_no"}},[r("el-input",{attrs:{placeholder:"请输入公章下弦文"},model:{value:t.subForm.company_seq_no,callback:function(e){t.$set(t.subForm,"company_seq_no",e)},expression:"subForm.company_seq_no"}})],1),t._v(" "),r("el-form-item",{attrs:{label:"合同有效期",prop:"contract_years"}},[r("el-input",{attrs:{placeholder:"请输入合同有效期"},model:{value:t.subForm.contract_years,callback:function(e){t.$set(t.subForm,"contract_years",e)},expression:"subForm.contract_years"}},[r("template",{slot:"append"},[t._v("年")])],2),t._v(" "),r("lb-tool-tips",[t._v("\n            自签约合同当日起算时间，合同期设置多少年，就在签约日期上增加年限，"),r("br"),t._v("到期后，"+t._s(t.$t("action.attendantName"))+"可续费，未到合同期限日，"+t._s(t.$t("action.attendantName"))+"不可操作续签\n          ")])],1),t._v(" "),r("el-form-item",{attrs:{label:"社会统一信用代码",prop:"company_id_no"}},[r("el-input",{attrs:{placeholder:"请输入社会统一信用代码"},model:{value:t.subForm.company_id_no,callback:function(e){t.$set(t.subForm,"company_id_no",e)},expression:"subForm.company_id_no"}})],1),t._v(" "),r("el-form-item",{attrs:{label:"合同模版",prop:"contract"}},[r("div",{staticClass:"flex-warp"},[r("div",{staticClass:"lb-file-input flex-between"},[r("div",{class:[{"c-title":t.subForm.contract}]},[t._v("\n                "+t._s(t._f("handleFileName")(t.subForm.contract,1))+"\n              ")]),t._v(" "),r("div",{staticClass:"flex-center"},[t.subForm.contract?r("i",{staticClass:"c-warning iconfont icon-guanbi-fill pl-sm pr-sm",on:{click:function(e){return t.toResetFile("contract")}}}):t._e(),t._v(" "),r("lb-cover",{attrs:{type:"button",fileType:"file",fileSize:1,regType:3},on:{selectedFiles:function(e){return t.getFile(e,"contract")}}})],1)]),t._v(" "),r("lb-tool-tips",[t._v("\n              合同模版上传要求：\n              "),r("p",{staticClass:"pt-sm"},[t._v("1、文件格式只支持doc\\docx格式。")]),t._v(" "),r("p",{staticClass:"pt-sm"},[t._v("\n                2、文件头部甲方信息需填写完整。头部乙方（技师）信息必须包含“乙方签字”四个字。\n              ")]),t._v(" "),r("p",{staticClass:"pt-sm"},[t._v("\n                3、文件底部甲乙双方，必须包含“甲方（盖章）”和“乙方（签字）”，冒号和括号必须用中文符号。\n              ")]),t._v(" "),r("div",{staticClass:"cursor-pointer c-link mt-md",on:{click:function(e){t.showDialog=!0}}},[t._v("\n                查看示例\n              ")])])],1)]),t._v(" "),r("el-form-item",{attrs:{label:"承诺书",prop:"commitment"}},[r("div",{staticClass:"flex-warp"},[r("div",{staticClass:"lb-file-input flex-between"},[r("div",{class:[{"c-title":t.subForm.commitment}]},[t._v("\n                "+t._s(t._f("handleFileName")(t.subForm.commitment,2))+"\n              ")]),t._v(" "),r("div",{staticClass:"flex-center"},[t.subForm.commitment?r("i",{staticClass:"c-warning iconfont icon-guanbi-fill pl-sm pr-sm",on:{click:function(e){return t.toResetFile("commitment")}}}):t._e(),t._v(" "),r("lb-cover",{attrs:{type:"button",fileType:"file",fileSize:1,regType:3},on:{selectedFiles:function(e){return t.getFile(e,"commitment")}}})],1)]),t._v(" "),r("lb-tool-tips",[t._v(" 文件支持doc、docx格式 ")])],1)]),t._v(" "),r("el-form-item",{attrs:{label:"实名认证",prop:"is_check"}},[r("div",{staticClass:"flex-y-center"},[r("el-tag",{staticClass:"mt-md",attrs:{size:"small",type:1===t.subForm.is_check?"primary":"danger"}},[t._v(t._s(1===t.subForm.is_check?"已认证":"未认证"))])],1)])],1):t._e(),t._v(" "),r("el-form-item",[r("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:t.submitFormInfo}},[t._v(t._s(t.$t("action.submit")))])],1)],1)],1),t._v(" "),r("el-dialog",{staticClass:"lbmiddle-dialog",attrs:{title:"查看设置区别",visible:t.showDialog,width:"800px",top:"5vh",center:""},on:{"update:visible":function(e){t.showDialog=e}}},[r("div",{staticClass:"gzh-img-list flex-center"},[r("img",{attrs:{src:"https://lbqny.migugu.com/admin/anmo/pc/fxq.png"}})])])],1)},staticRenderFns:[]};var b=r("C7Lr")(p,_,!1,function(t){r("BRPv")},"data-v-2ae86695",null);e.default=b.exports},BRPv:function(t,e){}});