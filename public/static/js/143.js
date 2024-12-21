webpackJsonp([143],{"4kFC":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=a("3cXf"),s=a.n(r),i=a("lC5x"),o=a.n(i),n=a("J0Oq"),l=a.n(n),u=a("PxTW"),c=a.n(u),d={components:{},data:function(){return{loading:{list:!1},searchForm:{list:{page:1,limit:10}},tableData:{list:[]},total:{list:0},showDialog:{sub:!1},subForm:{id:"",title:"",price:"",discount:"",month:"",top:0},subFormRules:{title:{required:!0,validator:this.$reg.isNotNull,text:"折扣卡名称",reg_type:2,trigger:"blur"},price:{required:!0,validator:this.$reg.isFloatNum,text:"充值金额",trigger:"blur"},discount:{required:!0,validator:function(t,e,a){""===e||!/^(?:[1-9]?|([0-9]*\.\d{1}))$/.test(e)||e&&1*e>10?a(new Error(""===e?"请输入"+t.text:"请输入正确"+t.text+"，0.1至9.9，最多保留1位小数")):a()},text:"折扣比例",trigger:"blur"},month:{required:!0,validator:this.$reg.isNum,reg_type:2,text:"有效期",trigger:"blur"},top:{required:!0,type:"number",message:"请输入排序值",trigger:"blur"}}}},created:function(){var t=this;return l()(o.a.mark(function e(){return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:t.getTableDataList(1,"list");case 1:case"end":return e.stop()}},e,t)}))()},methods:{resetForm:function(t){var e=t+"Form";this.$refs[e].resetFields(),this.getTableDataList(1,t)},handleSizeChange:function(t,e){this.searchForm[e].limit=t,this.handleCurrentChange(1,e)},handleCurrentChange:function(t,e){this.searchForm[e].page=t,this.getTableDataList("",e)},getTableDataList:function(t,e){var a=this;return l()(o.a.mark(function r(){var i,n,l,u,c,d,m;return o.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return t&&(a.searchForm[e].page=t),a.loading[e]=!0,i=JSON.parse(s()(a.searchForm[e])),n={list:{methodKey:"balancediscount",methodModel:"cardList"}}[e],l=n.methodKey,u=n.methodModel,r.next=7,a.$api[l][u](i);case 7:if(c=r.sent,d=c.code,m=c.data,a.loading[e]=!1,200===d){r.next=13;break}return r.abrupt("return");case 13:a.tableData[e]=m.data,a.total[e]=m.total;case 15:case"end":return r.stop()}},r,a)}))()},confirmDel:function(t){var e=this;this.$confirm("删除折扣卡之后，已购买该储值折扣卡的用户继续享有该权益，但不能续费或者再次购买。确认是否删除？",this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){e.updateItem(t,-1)})},updateItem:function(t,e){var a=this;return l()(o.a.mark(function r(){return o.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:a.$api.balancediscount.cardStatusUpdate({id:t,status:e}).then(function(t){if(200===t.code){if(a.$message.success(a.$t(-1===e?"tips.successDel":"tips.successOper")),-1!==e)return;a.searchForm.list.page=a.searchForm.list.page<Math.ceil((a.total.list-1)/a.searchForm.list.limit)?a.searchForm.list.page:Math.ceil((a.total.list-1)/a.searchForm.list.limit),a.getTableDataList("","list")}else{if(-1===e)return;a.getTableDataList("","list")}});case 1:case"end":return r.stop()}},r,a)}))()},toShowDialog:function(t){var e=this,a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{id:"",title:"",price:"",discount:"",month:"",top:0};return l()(o.a.mark(function r(){var i;return o.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:for(i in a=JSON.parse(s()(a)),e[t+"Form"])e[t+"Form"][i]=a[i];e.showDialog[t]=!e.showDialog[t],["sub"].includes(t)&&e.$refs[t+"Form"].validate&&e.$refs[t+"Form"].clearValidate();case 4:case"end":return r.stop()}},r,e)}))()},submitFormInfo:function(t){var e=this;return l()(o.a.mark(function a(){var r,i,n,l;return o.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:if(r=!0,e.$refs[t+"Form"].validate(function(t){t||(r=!1)}),r){a.next=4;break}return a.abrupt("return");case 4:return i=JSON.parse(s()(e[t+"Form"])),n=i.id?"cardUpdate":"cardAdd",a.next=8,e.$api.balancediscount[n](i);case 8:if(l=a.sent,200===l.code){a.next=12;break}return a.abrupt("return");case 12:e.$message.success(e.$t("sub"===t&&i.id?"tips.successRev":"tips.successSub")),e.showDialog[t]=!1,e.getTableDataList("","list");case 15:case"end":return a.stop()}},a,e)}))()}},filters:{handleTime:function(t,e){return 1===e?c()(1e3*t).format("YYYY-MM-DD"):2===e?c()(1e3*t).format("HH:mm:ss"):c()(1e3*t).format("YYYY-MM-DD HH:mm:ss")}}},m={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"lb-finance-stored-list"},[a("top-nav"),t._v(" "),a("div",{staticClass:"page-main"},[a("el-row",{staticClass:"page-top-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:t.$route.name+"-add",expression:"`${$route.name}-add`"}],attrs:{size:"medium",type:"primary",icon:"el-icon-plus"},on:{click:function(e){return t.toShowDialog("sub")}}},[t._v(t._s(t.$t("menu.FinanceBalancediscountAdd")))])],1),t._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.loading.list,expression:"loading.list"}],staticStyle:{width:"100%"},attrs:{data:t.tableData.list,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark"}},[a("el-table-column",{attrs:{prop:"id",label:"ID"}}),t._v(" "),a("el-table-column",{attrs:{prop:"title",label:"折扣卡名称"}}),t._v(" "),a("el-table-column",{attrs:{prop:"price",label:"充值金额"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v(" ¥"+t._s(e.row.price)+" ")]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"discount",label:"折扣比例"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v(" "+t._s(e.row.discount)+"折 ")]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"month",label:"有效期"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v(" "+t._s(e.row.month)+"月 ")]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"top",label:"排序值"}}),t._v(" "),a("el-table-column",{attrs:{prop:"create_time",label:"创建时间","min-width":"110"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("p",[t._v(t._s(t._f("handleTime")(e.row.create_time,1)))]),t._v(" "),a("p",[t._v(t._s(t._f("handleTime")(e.row.create_time,2)))])]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"address",label:"是否上架"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-switch",{attrs:{disabled:!t.$route.meta.pagePermission[0].auth.includes("edit"),"active-value":1,"inactive-value":0},on:{change:function(a){return t.updateItem(e.row.id,e.row.status)}},model:{value:e.row.status,callback:function(a){t.$set(e.row,"status",a)},expression:"scope.row.status"}})]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"操作",width:"160",fixed:"right"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:t.$route.name+"-edit",expression:"`${$route.name}-edit`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(a){return t.toShowDialog("sub",e.row)}}},[t._v(t._s(t.$t("action.edit")))]),t._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:t.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(a){return t.confirmDel(e.row.id)}}},[t._v(t._s(t.$t("action.delete")))])],1)]}}])})],1),t._v(" "),a("lb-page",{attrs:{batch:!1,page:t.searchForm.list.page,pageSize:t.searchForm.list.limit,total:t.total.list},on:{handleSizeChange:function(e){return t.handleSizeChange(e,"list")},handleCurrentChange:function(e){return t.handleCurrentChange(e,"list")}}}),t._v(" "),a("el-dialog",{attrs:{title:t.$t(t.subForm.id?"menu.FinanceBalancediscountEdit":"menu.FinanceBalancediscountAdd"),visible:t.showDialog.sub,width:"550px",center:""},on:{"update:visible":function(e){return t.$set(t.showDialog,"sub",e)}}},[a("el-form",{ref:"subForm",staticClass:"dialog-form",attrs:{model:t.subForm,rules:t.subFormRules,"label-width":"140px"}},[a("el-form-item",{attrs:{label:"折扣卡名称",prop:"title"}},[a("el-input",{attrs:{maxlength:"10","show-word-limit":"",placeholder:"请输入折扣卡名称"},model:{value:t.subForm.title,callback:function(e){t.$set(t.subForm,"title",e)},expression:"subForm.title"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"充值金额",prop:"price"}},[a("el-input",{attrs:{disabled:1*t.subForm.id>0,placeholder:"请输入充值金额"},model:{value:t.subForm.price,callback:function(e){t.$set(t.subForm,"price",e)},expression:"subForm.price"}},[a("template",{slot:"append"},[t._v("元")])],2),t._v(" "),a("lb-tool-tips",[t._v("用户所要支付的价格")])],1),t._v(" "),a("el-form-item",{attrs:{label:"折扣比例",prop:"discount"}},[a("el-input",{attrs:{disabled:1*t.subForm.id>0,placeholder:"请输入折扣比例"},model:{value:t.subForm.discount,callback:function(e){t.$set(t.subForm,"discount",e)},expression:"subForm.discount"}},[a("template",{slot:"append"},[t._v("折")])],2),t._v(" "),a("lb-tool-tips",[t._v("取值0.1-9.9的数值，支持输入小数，保留小数点后1位")])],1),t._v(" "),a("el-form-item",{attrs:{label:"有效期",prop:"month"}},[a("el-input",{attrs:{disabled:1*t.subForm.id>0,placeholder:"请输入有效期"},model:{value:t.subForm.month,callback:function(e){t.$set(t.subForm,"month",e)},expression:"subForm.month"}},[a("template",{slot:"append"},[t._v("月")])],2)],1),t._v(" "),a("el-form-item",{attrs:{label:"排序值",prop:"top"}},[a("el-input-number",{staticClass:"lb-input-number",attrs:{min:0,controls:!1,placeholder:"请输入排序值"},model:{value:t.subForm.top,callback:function(e){t.$set(t.subForm,"top",e)},expression:"subForm.top"}}),t._v(" "),a("lb-tool-tips",[t._v("值越大, 排序越靠前")])],1)],1),t._v(" "),a("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(e){t.showDialog.sub=!1}}},[t._v("取 消")]),t._v(" "),a("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(e){return t.submitFormInfo("sub")}}},[t._v("确 定")])],1)],1)],1)],1)},staticRenderFns:[]};var p=a("C7Lr")(d,m,!1,function(t){a("fmBg")},"data-v-3cdab117",null);e.default=p.exports},fmBg:function(t,e){}});