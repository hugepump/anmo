webpackJsonp([157],{n7vn:function(e,t){},r4CE:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=a("lC5x"),r=a.n(n),i=a("J0Oq"),s=a.n(i),o=a("PxTW"),c=a.n(o),l={data:function(){return{loading:!1,pagePermission:[],base_city:[],searchForm:{page:1,limit:10,city_name:""},tableData:[],total:0}},created:function(){var e=this;return s()(r.a.mark(function t(){return r.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:e.pagePermission=e.$route.meta.pagePermission.filter(function(t){return t.title===e.$route.name})[0].auth;case 1:case"end":return t.stop()}},t,e)}))()},activated:function(){var e=this;return s()(r.a.mark(function t(){var a;return r.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:a=1,Number(window.sessionStorage.getItem("currentPage"))&&(a=Number(window.sessionStorage.getItem("currentPage")),window.sessionStorage.removeItem("currentPage")),e.getTableDataList(a);case 3:case"end":return t.stop()}},t,e)}))()},beforeRouteLeave:function(e,t,a){if("SystemCarFeeCityEdit"===e.name){var n=e.query.id;(void 0===n?0:n)&&window.sessionStorage.setItem("currentPage",this.searchForm.page)}a()},methods:{resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},toChange:function(e){var t=this;return s()(r.a.mark(function a(){return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:t.searchForm.status=e,t.getTableDataList(1);case 2:case"end":return a.stop()}},a,t)}))()},getTableDataList:function(e){var t=this;return s()(r.a.mark(function a(){var n,i,s;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(t.searchForm.page=e),t.tableData=[],t.loading=!0,a.next=5,t.$api.system.getCarConfigList(t.searchForm);case 5:if(n=a.sent,i=n.code,s=n.data,t.loading=!1,200===i){a.next=11;break}return a.abrupt("return");case 11:t.tableData=s.data,t.total=s.total;case 13:case"end":return a.stop()}},a,t)}))()},updateItem:function(e,t){var a=this;return s()(r.a.mark(function n(){var i,s;return r.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:i=-1===t?{id:e}:{id:e,status:t},s=-1===t?"getCarConfigDel":"getCarConfigUpdate",a.$api.system[s](i).then(function(e){if(200===e.code){if(a.$message.success(a.$t(-1===t?"tips.successDel":"tips.successOper")),-1!==t)return;a.searchForm.page=a.searchForm.page<Math.ceil((a.total-1)/a.searchForm.limit)?a.searchForm.page:Math.ceil((a.total-1)/a.searchForm.limit),a.getTableDataList()}else{if(-1===t)return;a.getTableDataList()}});case 3:case"end":return n.stop()}},n,a)}))()},confirmDel:function(e){var t=this;this.$confirm("删除之后，该城市的车费默认使用全局车费设置，确认删除吗？",this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){t.updateItem(e,-1)})}},filters:{handleTime:function(e,t){return 1===t?c()(1e3*e).format("YYYY-MM-DD"):2===t?c()(1e3*e).format("HH:mm:ss"):c()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},u={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-system-car-fee-city"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("lb-tips",[e._v("\n      未单独设置车费的区县，将默认使用该区县所属城市设置的车费模式，若该城市也未设置车费模式，将使用默认的全局设置车费模式\n      "),a("div",{staticClass:"mt-sm"},[e._v("\n        如有单独设置城市/区县车费，将使用对应城市/区县的车费模式，全局模式失效\n      ")])]),e._v(" "),a("el-row",{staticClass:"page-top-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-add",expression:"`${$route.name}-add`"}],attrs:{type:"primary",icon:"el-icon-plus"},on:{click:function(t){return e.$router.push("/sys/car-fee-edit")}}},[e._v(e._s(e.$t("menu.SystemCarFeeCityAdd")))])],1),e._v(" "),a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"输入查询",prop:"city_name"}},[a("el-input",{attrs:{placeholder:"请输入城市/区县名称"},model:{value:e.searchForm.city_name,callback:function(t){e.$set(e.searchForm,"city_name",t)},expression:"searchForm.city_name"}})],1),e._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1)}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("searchForm")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"city_name",label:"城市/区县","min-width":"100"}}),e._v(" "),a("el-table-column",{attrs:{label:"起步距离(日间)",prop:"start_distance","min-width":"100"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.start_distance+" km")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"起步价(日间)",prop:"start_price","min-width":"100"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.start_price+" 元")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"起步距离(夜间)",prop:"start_distance_night","min-width":"100"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.start_distance_night+" km")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"起步价(夜间)",prop:"start_price_night","min-width":"100"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.start_price_night+" 元")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"",prop:"invented_distance","min-width":"120"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.invented_distance+" %")+"\n        ")]}}])},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[e._v("\n            虚拟里程\n            "),a("lb-tool-tips",{attrs:{padding:"11"}},[e._v("虚拟里程用于\n              距离计算短、车费计算少的情况可在后台增加一部分虚拟里程，减少"+e._s(e.$t("action.attendantName"))+"损失\n              "),a("div",{staticClass:"mt-sm"},[e._v("\n                用户端显示的距离=实际距离+实际距离*虚拟里程百分比\n              ")])])],1)])],2),e._v(" "),a("el-table-column",{attrs:{prop:"status",label:"是否上架"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-switch",{attrs:{disabled:!e.pagePermission.includes("edit"),"active-value":1,"inactive-value":0},on:{change:function(a){return e.updateItem(t.row.id,t.row.status)}},model:{value:t.row.status,callback:function(a){e.$set(t.row,"status",a)},expression:"scope.row.status"}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"create_time",label:"创建时间","min-width":"110"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,2)))])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"160",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-edit",expression:"`${$route.name}-edit`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(a){return e.$router.push("/sys/car-fee-edit?id="+t.row.id)}}},[e._v(e._s(e.$t("action.edit")))]),e._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(a){return e.confirmDel(t.row.id)}}},[e._v(e._s(e.$t("action.delete")))])],1)]}}])})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}})],1)],1)},staticRenderFns:[]};var m=a("C7Lr")(l,u,!1,function(e){a("n7vn")},"data-v-2e39773a",null);t.default=m.exports}});