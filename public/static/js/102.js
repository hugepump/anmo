webpackJsonp([102],{"1pKu":function(e,t){},zgk7:function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r("lC5x"),i=r.n(a),s=r("J0Oq"),n=r.n(s),l=r("PxTW"),o=r.n(l),u={data:function(){return{loading:!1,searchForm:{page:1,limit:10,name:""},tableData:[],total:0,showDialog:!1,subForm:{id:0,name:"",type:1,is_required:1,top:0},subFormRules:{name:{required:!0,validator:this.$reg.isNotNull,text:"字段名称",reg_type:2,trigger:"blur"},type:{required:!0,type:"number",message:"请选择",trigger:["blur","change"]},is_required:{required:!0,type:"number",message:"请选择",trigger:["blur","change"]},top:{required:!0,type:"number",message:"请输入排序值",trigger:"blur"}},fieldType:{1:"文本",2:"单选",3:"图片"},fieldTypeList:[1,2,3],selectArr:[]}},created:function(){this.getTableDataList()},methods:{resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},addSelectItem:function(){console.log(this.subForm.select),this.selectArr.push({val:""})},delSelectItem:function(e){this.selectArr.splice(e,1)},getFieldList:function(e){console.log(e),1===e&&(this.selectArr=[{val:""}])},toShowDialog:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{id:0,type:1,name:"",top:0,is_required:1};return n()(i.a.mark(function r(){var a;return i.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:for(a in 1===t.type?e.selectArr=[{val:""}]:e.selectArr=t.select?t.select.map(function(e){return{val:e}}):"",e.subForm)e.subForm[a]=t[a];e.showDialog=!0,e.$refs.subForm.validate&&e.$refs.subForm.clearValidate();case 4:case"end":return r.stop()}},r,e)}))()},getTableDataList:function(e){var t=this;return n()(i.a.mark(function r(){var a,s,n,l;return i.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return e&&(t.searchForm.page=e),t.loading=!0,a=t.searchForm,r.next=5,t.$api.market.partnerFieldList(a);case 5:if(s=r.sent,n=s.code,l=s.data,t.loading=!1,200===n){r.next=11;break}return r.abrupt("return");case 11:t.tableData=l.data,t.total=l.total;case 13:case"end":return r.stop()}},r,t)}))()},confirmDel:function(e,t){var r=this;this.$confirm(this.$t("tips.confirmDelete"),this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){r.updateItem(e,t)}).catch(function(){})},updateItem:function(e,t){var r=this;return n()(i.a.mark(function a(){return i.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:r.$api.market.partnerFiledEdit({id:e,status:t}).then(function(e){if(200===e.code)r.$message.success(r.$t(-1===t?"tips.successDel":"tips.successOper")),-1===t&&(r.searchForm.page=r.searchForm.page<Math.ceil((r.total-1)/r.searchForm.limit)?r.searchForm.page:Math.ceil((r.total-1)/r.searchForm.limit),r.getTableDataList());else{if(-1===t)return;r.getTableDataList()}});case 1:case"end":return a.stop()}},a,r)}))()},submitFormInfo:function(){var e=this;return n()(i.a.mark(function t(){var r,a,s;return i.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(r=!0,e.$refs.subForm.validate(function(e){e||(r=!1)}),r){t.next=4;break}return t.abrupt("return");case 4:if(2!==e.subForm.type){t.next=10;break}if(a=[],e.selectArr.map(function(e){e.val&&a.push(e.val)}),0!==a.length){t.next=9;break}return t.abrupt("return",e.$message.error("请输入选项值"));case 9:e.subForm.select=a;case 10:return t.next=12,e.$api.market[e.subForm.id?"partnerFiledEdit":"partnerFiledAdd"](e.subForm);case 12:if(s=t.sent,200===s.code){t.next=16;break}return t.abrupt("return");case 16:e.$message.success(e.$t("tips.successSub")),e.showDialog=!1,e.getTableDataList();case 19:case"end":return t.stop()}},t,e)}))()}},filters:{handleTime:function(e,t){return 1===t?o()(1e3*e).format("YYYY-MM-DD"):2===t?o()(1e3*e).format("HH:mm:ss"):o()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},c={render:function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"lb-mall-sort"},[r("top-nav"),e._v(" "),r("div",{staticClass:"page-main"},[r("el-row",{staticClass:"page-top-operate"},[r("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-add",expression:"`${$route.name}-add`"}],attrs:{size:"medium",type:"primary",icon:"el-icon-plus"},on:{click:e.toShowDialog}},[e._v(e._s(e.$t("menu.PartnerFieldAdd")))])],1),e._v(" "),r("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[r("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),r("el-table-column",{attrs:{prop:"name",label:"字段名称"}}),e._v(" "),r("el-table-column",{attrs:{prop:"type",label:"字段类型"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(e.fieldType[t.row.type])+"\n        ")]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"is_required",label:"是否必填"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("el-tag",{attrs:{type:1===t.row.is_required?"":"info"}},[e._v(e._s(1===t.row.is_required?"是":"否"))])]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"top",label:"排序值"}}),e._v(" "),r("el-table-column",{attrs:{label:"操作","min-width":"100"},scopedSlots:e._u([{key:"default",fn:function(t){return[t.row.is_def?e._e():r("div",{staticClass:"table-operate"},[r("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-edit",expression:"`${$route.name}-edit`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(r){return e.toShowDialog(t.row)}}},[e._v(e._s(e.$t("action.edit")))]),e._v(" "),r("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(r){return e.confirmDel(t.row.id,-1)}}},[e._v(e._s(e.$t("action.delete")))])],1)]}}])})],1),e._v(" "),r("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}})],1),e._v(" "),r("el-dialog",{attrs:{title:e.$t(e.subForm.id?"menu.PartnerFieldEdit":"menu.PartnerFieldAdd"),visible:e.showDialog,width:"500px",center:""},on:{"update:visible":function(t){e.showDialog=t}}},[r("el-form",{ref:"subForm",staticClass:"dialog-form",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"100px"}},[r("el-form-item",{attrs:{label:"字段名称",prop:"name"}},[r("el-input",{staticStyle:{width:"200px"},attrs:{maxlength:"8","show-word-limit":"",placeholder:"请输入字段名称"},model:{value:e.subForm.name,callback:function(t){e.$set(e.subForm,"name",t)},expression:"subForm.name"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"字段格式",prop:"type"}},[r("el-radio-group",{attrs:{disabled:e.subForm.id},on:{change:function(t){return e.getFieldList(t)}},model:{value:e.subForm.type,callback:function(t){e.$set(e.subForm,"type",t)},expression:"subForm.type"}},e._l(e.fieldTypeList,function(t,a){return r("el-radio",{key:a,attrs:{label:t}},[e._v(e._s(e.fieldType[t]))])}),1)],1),e._v(" "),r("el-form-item",{attrs:{label:"是否必填",prop:"is_required"}},[r("el-radio-group",{attrs:{disabled:e.subForm.id},on:{change:function(t){return e.getFieldList(t)}},model:{value:e.subForm.is_required,callback:function(t){e.$set(e.subForm,"is_required",t)},expression:"subForm.is_required"}},[r("el-radio",{attrs:{label:1}},[e._v("必填")]),e._v(" "),r("el-radio",{attrs:{label:0}},[e._v("非必填")])],1)],1),e._v(" "),2==e.subForm.type?r("el-form-item",{attrs:{label:"选项值",prop:"select"}},e._l(e.selectArr,function(t,a){return r("div",{key:a,staticClass:"flex-y-center",class:a>0?"mt-md":""},[r("el-input",{staticClass:"mr-md",staticStyle:{width:"200px"},attrs:{maxlength:"10","show-word-limit":"",placeholder:"请输入选项值"},model:{value:t.val,callback:function(r){e.$set(t,"val",r)},expression:"item.val"}}),e._v(" "),1!==e.selectArr.length?r("el-button",{attrs:{type:"danger",icon:"el-icon-delete"},on:{click:function(t){return e.delSelectItem(a)}}}):e._e(),e._v(" "),a==e.selectArr.length-1?r("el-button",{attrs:{type:"primary",icon:"el-icon-plus"},on:{click:e.addSelectItem}}):e._e()],1)}),0):e._e(),e._v(" "),r("el-form-item",{attrs:{label:"排序值",prop:"top"}},[r("el-input-number",{staticClass:"lb-input-number",staticStyle:{width:"200px"},attrs:{min:0,controls:!1,placeholder:"请输入排序值"},model:{value:e.subForm.top,callback:function(t){e.$set(e.subForm,"top",t)},expression:"subForm.top"}}),e._v(" "),r("lb-tool-tips",[e._v("值越大, 排序越靠前")])],1)],1),e._v(" "),r("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[r("el-button",{on:{click:function(t){e.showDialog=!1}}},[e._v("取 消")]),e._v(" "),r("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:e.submitFormInfo}},[e._v("确 定")])],1)],1)],1)},staticRenderFns:[]};var m=r("C7Lr")(u,c,!1,function(e){r("1pKu")},"data-v-5a937358",null);t.default=m.exports}});