webpackJsonp([174],{"0hxo":function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("lC5x"),n=a.n(r),s=a("J0Oq"),i=a.n(s),o=a("PxTW"),l=a.n(o),c={data:function(){return{statusOptions:[],loading:!1,searchForm:{page:1,limit:10,name:"",carte:0},tableData:[],total:0}},activated:function(){var e=this;return i()(n.a.mark(function t(){var a;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:a=1,Number(window.sessionStorage.getItem("currentPage"))&&(a=Number(window.sessionStorage.getItem("currentPage")),window.sessionStorage.removeItem("currentPage")),e.getBaseInfo(),e.getTableDataList(a);case 4:case"end":return t.stop()}},t,e)}))()},beforeRouteLeave:function(e,t,a){if("MallGoodEdit"===e.name){var r=e.query.id;(void 0===r?0:r)&&window.sessionStorage.setItem("currentPage",this.searchForm.page)}a()},methods:{getBaseInfo:function(){var e=this;return i()(n.a.mark(function t(){var a,r,s;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.$api.mall.goodsCarteList();case 2:if(a=t.sent,r=a.code,s=a.data,200===r){t.next=7;break}return t.abrupt("return");case 7:s.unshift({id:0,name:"全部"}),e.statusOptions=s;case 9:case"end":return t.stop()}},t,e)}))()},resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},getTableDataList:function(e){var t=this;return i()(n.a.mark(function a(){var r,s,i,o;return n.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(t.searchForm.page=e),t.loading=!0,r=t.searchForm,a.next=5,t.$api.mall.goodsList(r);case 5:if(s=a.sent,i=s.code,o=s.data,t.loading=!1,200===i){a.next=11;break}return a.abrupt("return");case 11:t.tableData=o.data,t.total=o.total;case 13:case"end":return a.stop()}},a,t)}))()},confirmDel:function(e,t){var a=this;this.$confirm(this.$t("tips.confirmDelete"),this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){a.updateItem(e,t)}).catch(function(){})},updateItem:function(e,t){var a=this;return i()(n.a.mark(function r(){return n.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:a.$api.mall.goodsStatus({id:e,status:t}).then(function(e){if(200===e.code)a.$message.success(a.$t(-1===t?"tips.successDel":"tips.successOper")),-1===t&&(a.searchForm.page=a.searchForm.page<Math.ceil((a.total-1)/a.searchForm.limit)?a.searchForm.page:Math.ceil((a.total-1)/a.searchForm.limit),a.getTableDataList());else{if(-1===t)return;a.getTableDataList()}});case 1:case"end":return r.stop()}},r,a)}))()}},filters:{handleTime:function(e,t){return 1===t?l()(1e3*e).format("YYYY-MM-DD"):2===t?l()(1e3*e).format("HH:mm:ss"):l()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},u={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-mall-list"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("el-row",{staticClass:"page-top-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-add",expression:"`${$route.name}-add`"}],attrs:{size:"medium",type:"primary",icon:"el-icon-plus"},on:{click:function(t){return e.$router.push("/market/mall/edit")}}},[e._v(e._s(e.$t("menu.MallGoodAdd")))])],1),e._v(" "),a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"分类",prop:"carte"}},[a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(t){return e.getTableDataList(1)}},model:{value:e.searchForm.carte,callback:function(t){e.$set(e.searchForm,"carte",t)},expression:"searchForm.carte"}},e._l(e.statusOptions,function(e){return a("el-option",{key:e.id,attrs:{label:e.name,value:e.id}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"商品名称",prop:"name"}},[a("el-input",{attrs:{placeholder:"请输入商品名称"},model:{value:e.searchForm.name,callback:function(t){e.$set(e.searchForm,"name",t)},expression:"searchForm.name"}})],1),e._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1)}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("searchForm")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"cover",label:"封面图"},scopedSlots:e._u([{key:"default",fn:function(e){return[a("lb-image",{attrs:{src:e.row.cover}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"商品名称"}}),e._v(" "),a("el-table-column",{attrs:{prop:"carte",label:"所属分类"}}),e._v(" "),a("el-table-column",{attrs:{prop:"price",label:"商品价格"}}),e._v(" "),a("el-table-column",{attrs:{prop:"status",label:"是否上架"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-switch",{attrs:{disabled:!e.$route.meta.pagePermission[0].auth.includes("edit"),"active-value":1,"inactive-value":0},on:{change:function(a){return e.updateItem(t.row.id,t.row.status)}},model:{value:t.row.status,callback:function(a){e.$set(t.row,"status",a)},expression:"scope.row.status"}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"sort",label:"排序值"}}),e._v(" "),a("el-table-column",{attrs:{prop:"create_time",label:"创建时间","min-width":"180"}}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"160",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-edit",expression:"`${$route.name}-edit`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(a){return e.$router.push("/market/mall/edit?id="+t.row.id)}}},[e._v(e._s(e.$t("action.edit")))]),e._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(a){return e.confirmDel(t.row.id,-1)}}},[e._v(e._s(e.$t("action.delete")))])],1)]}}])})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}})],1)],1)},staticRenderFns:[]};var m=a("C7Lr")(c,u,!1,function(e){a("WvoR")},"data-v-1d24ad60",null);t.default=m.exports},WvoR:function(e,t){}});