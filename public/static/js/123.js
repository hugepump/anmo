webpackJsonp([123],{lNsi:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("3cXf"),s=a.n(r),i=a("lC5x"),o=a.n(i),n=a("J0Oq"),l=a.n(n),c=a("PxTW"),u=a.n(c),m={components:{},data:function(){return{loading:!1,storeList:[],searchForm:{page:1,limit:10,name:""},tableData:[],total:0,showDialog:!1,subForm:{id:"",name:"",top:0,img:""},subFormRules:{name:{required:!0,type:"string",message:"请输入分类名称",trigger:"blur"},img:{required:!0,type:"array",message:"请上传图标",trigger:"blur"},top:{required:!0,type:"number",message:"请输入排序值",trigger:"blur"}}}},activated:function(){var e=this;return l()(o.a.mark(function t(){var a;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:a=1,Number(window.sessionStorage.getItem("currentPage"))&&(a=Number(window.sessionStorage.getItem("currentPage")),window.sessionStorage.removeItem("currentPage")),e.getTableDataList(a);case 3:case"end":return t.stop()}},t,e)}))()},beforeRouteLeave:function(e,t,a){"StorePackageEdit"===e.name&&window.sessionStorage.setItem("currentPage",this.searchForm.page),a()},methods:{resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},getTableDataList:function(e){var t=this;e&&(this.searchForm.page=1),this.loading=!0,this.$api.store.packageList(this.searchForm).then(function(e){t.loading=!1,200===e.code&&(t.tableData=e.data.data,t.total=e.data.total)})},confirmDel:function(e){var t=this;this.$confirm(this.$t("tips.confirmDelete"),this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){t.updateItem(e,-1)})},updateItem:function(e,t){var a=this;return l()(o.a.mark(function r(){return o.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:a.$api.store.packageUpdateStatus({id:e,status:t}).then(function(e){if(200===e.code){if(a.$message.success(a.$t(-1===t?"tips.successDel":"tips.successOper")),-1!==t)return;a.searchForm.page=a.searchForm.page<Math.ceil((a.total-1)/a.searchForm.limit)?a.searchForm.page:Math.ceil((a.total-1)/a.searchForm.limit),a.getTableDataList()}else{if(-1===t)return;a.getTableDataList()}});case 1:case"end":return r.stop()}},r,a)}))()},toShowDialog:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{top:0};return l()(o.a.mark(function a(){var r;return o.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:for(r in t.img=t.img?[{url:t.img}]:[],e.subForm)e.subForm[r]=t[r];e.showDialog=!e.showDialog;case 3:case"end":return a.stop()}},a,e)}))()},getCover:function(e,t){this.subForm[t]=e},submitFormInfo:function(){var e=this;return l()(o.a.mark(function t(){var a,r,i,n;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(a=!0,e.$refs.subForm.validate(function(e){e||(a=!1)}),(r=JSON.parse(s()(e.subForm))).img=r.img[0].url,!a){t.next=15;break}return i=r.id?"typeUpdate":"typeAdd",t.next=8,e.$api.store[i](r);case 8:if(n=t.sent,200===n.code){t.next=12;break}return t.abrupt("return");case 12:e.$message.success(e.$t(r.id?"tips.successRev":"tips.successSub")),e.showDialog=!1,e.getTableDataList();case 15:case"end":return t.stop()}},t,e)}))()}},filters:{handleTime:function(e,t){return 1===t?u()(1e3*e).format("YYYY-MM-DD"):2===t?u()(1e3*e).format("HH:mm:ss"):u()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},p={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-appclass-classroom-list"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-add",expression:"`${$route.name}-add`"}],attrs:{size:"medium",type:"primary",icon:"el-icon-plus"},on:{click:function(t){return e.$router.push("/join/store/package/edit")}}},[e._v("新增套餐")]),e._v(" "),a("div",{staticClass:"space-lg"}),e._v(" "),a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"输入查询",prop:"name"}},[a("el-input",{attrs:{placeholder:"输入套餐名称"},model:{value:e.searchForm.name,callback:function(t){e.$set(e.searchForm,"name",t)},expression:"searchForm.name"}})],1),e._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1)}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("searchForm")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark"}},[a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"cover",label:"封面图"},scopedSlots:e._u([{key:"default",fn:function(e){return[a("lb-image",{attrs:{src:e.row.cover}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"套餐名称"}}),e._v(" "),a("el-table-column",{attrs:{prop:"price",label:"现价"}}),e._v(" "),a("el-table-column",{attrs:{prop:"init_price",label:"原价"}}),e._v(" "),a("el-table-column",{attrs:{prop:"total_sale",label:"年售"}}),e._v(" "),a("el-table-column",{attrs:{prop:"true_sale",label:"真实销量"}}),e._v(" "),a("el-table-column",{attrs:{prop:"store_name",label:"所属门店"}}),e._v(" "),a("el-table-column",{attrs:{prop:"status",label:"是否上架"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-switch",{attrs:{disabled:!e.$route.meta.pagePermission[0].auth.includes("edit"),"active-value":1,"inactive-value":0},on:{change:function(a){return e.updateItem(t.row.id,t.row.status)}},model:{value:t.row.status,callback:function(a){e.$set(t.row,"status",a)},expression:"scope.row.status"}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"create_time",label:"创建时间","min-width":"110"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,2)))])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作","min-width":"160",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-edit",expression:"`${$route.name}-edit`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(a){return e.$router.push("/join/store/package/edit?id="+t.row.id)}}},[e._v(e._s(e.$t("action.edit")))]),e._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(a){return e.confirmDel(t.row.id)}}},[e._v(e._s(e.$t("action.delete")))]),e._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-copy",expression:"`${$route.name}-copy`"}],attrs:{size:"mini",plain:"",type:"success"},on:{click:function(a){return e.$router.push("/join/store/package/edit?id="+t.row.id+"&type=copy")}}},[e._v(e._s(e.$t("action.copy")))]),e._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-setSeckill",expression:"`${$route.name}-setSeckill`"},{name:"show",rawName:"v-show",value:!t.row.seckill_id,expression:"!scope.row.seckill_id"}],attrs:{size:"mini",plain:"",type:"warning"},on:{click:function(a){return e.$router.push("/join/store/package/edit?package_id="+t.row.id)}}},[e._v(e._s(e.$t("action.setSeckill")))])],1)]}}])})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}}),e._v(" "),a("el-dialog",{attrs:{title:e.subForm.id?"编辑分类":"添加分类",visible:e.showDialog,width:"500px",center:""},on:{"update:visible":function(t){e.showDialog=t}}},[a("el-form",{ref:"subForm",staticClass:"dialog-form",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"100px"}},[a("el-form-item",{attrs:{label:"分类名称",prop:"name"}},[a("el-input",{attrs:{maxlength:"5","show-word-limit":"",placeholder:"请输入分类名称"},model:{value:e.subForm.name,callback:function(t){e.$set(e.subForm,"name",t)},expression:"subForm.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"图标",prop:"img"}},[a("lb-cover",{attrs:{fileList:e.subForm.img},on:{selectedFiles:function(t){return e.getCover(t,"img")}}}),e._v(" "),a("lb-tool-tips",[e._v("图标建议尺寸: 50 * 50")])],1),e._v(" "),a("el-form-item",{attrs:{label:"排序值",prop:"top"}},[a("el-input-number",{staticClass:"lb-input-number",attrs:{min:0,controls:!1,placeholder:"请输入排序值"},model:{value:e.subForm.top,callback:function(t){e.$set(e.subForm,"top",t)},expression:"subForm.top"}}),e._v(" "),a("lb-tool-tips",[e._v("值越大, 排序越靠前")])],1)],1),e._v(" "),a("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.showDialog=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:e.submitFormInfo}},[e._v("确 定")])],1)],1)],1)],1)},staticRenderFns:[]};var d=a("C7Lr")(m,p,!1,function(e){a("lVv9")},"data-v-4ab666fe",null);t.default=d.exports},lVv9:function(e,t){}});