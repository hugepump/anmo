webpackJsonp([141],{"4pja":function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=a("3cXf"),i=a.n(n),r=a("lC5x"),l=a.n(r),o=a("J0Oq"),c=a.n(o),s=a("PxTW"),m=a.n(s),h={data:function(){return{pickerOptions:{disabledDate:function(e){return e.getTime()>1e3*(m()(m()(Date.now()).format("YYYY-MM-DD")).unix()+86400-1)}},loading:{list:!1,technician:!1},searchForm:{list:{page:1,limit:10,start_time:"",end_time:"",name:""},technician:{page:1,limit:10,name:""}},tableData:{list:[],technician:[]},total:{list:0,technician:0},showDialog:{technician:!1}}},created:function(){this.getTableDataList(1,"list")},methods:{resetForm:function(e){var t=e+"Form";this.$refs[t].resetFields(),this.getTableDataList(1,e)},handleSizeChange:function(e,t){this.searchForm[t].limit=e,this.handleCurrentChange(1,t)},handleCurrentChange:function(e,t){this.searchForm[t].page=e,this.getTableDataList("",t)},toChange:function(e){var t=this;return c()(l.a.mark(function a(){return l.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:t.searchForm.list.status=e,t.getTableDataList(1,"list");case 2:case"end":return a.stop()}},a,t)}))()},getTableDataList:function(e,t){var a=this;return c()(l.a.mark(function n(){var r,o,c,s,m,h,u,d;return l.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return e&&(a.searchForm[t].page=e),a.loading[t]=!0,r=JSON.parse(i()(a.searchForm[t])),"list"===t&&((o=r.start_time)&&o.length>1?(r.start_time=o[0]/1e3,r.end_time=o[1]/1e3):(r.start_time="",r.end_time="")),c={list:{methodKey:"coachbroker",methodModel:"brokerDataList"},technician:{methodKey:"coachbroker",methodModel:"brokerCoachList"}}[t],s=c.methodKey,m=c.methodModel,n.next=8,a.$api[s][m](r);case 8:if(h=n.sent,u=h.code,d=h.data,a.loading[t]=!1,200===u){n.next=14;break}return n.abrupt("return");case 14:a.tableData[t]=d.data,a.total[t]=d.total;case 16:case"end":return n.stop()}},n,a)}))()},toShowDialog:function(e,t){var a=this;return c()(l.a.mark(function n(){return l.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:if("technician"!==e){n.next=4;break}return a.searchForm[e].id=t,n.next=4,a.getTableDataList(1,e);case 4:a.showDialog[e]=!a.showDialog[e];case 5:case"end":return n.stop()}},n,a)}))()}},filters:{handleTime:function(e,t){return 1===t?m()(1e3*e).format("YYYY-MM-DD"):2===t?m()(1e3*e).format("HH:mm:ss"):m()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},u={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-coachbroker-count"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"listForm",attrs:{inline:!0,model:e.searchForm.list},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"输入查询",prop:"name"}},[a("el-input",{staticStyle:{width:"250px"},attrs:{placeholder:"请输入"+e.$t("action.brokerName")+"姓名/昵称/手机号"},model:{value:e.searchForm.list.name,callback:function(t){e.$set(e.searchForm.list,"name",t)},expression:"searchForm.list.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"入驻时间",prop:"start_time"}},[a("el-date-picker",{attrs:{type:"datetimerange","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"timestamp","picker-options":e.pickerOptions,"default-time":["00:00:00","23:59:59"]},on:{change:function(t){return e.getTableDataList(1,"list")}},model:{value:e.searchForm.list.start_time,callback:function(t){e.$set(e.searchForm.list,"start_time",t)},expression:"searchForm.list.start_time"}})],1),e._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1,"list")}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("list")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading.list,expression:"loading.list"}],staticStyle:{width:"100%"},attrs:{data:e.tableData.list,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"user_id",label:"用户ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"avatarUrl",label:"微信头像"},scopedSlots:e._u([{key:"default",fn:function(e){return[a("lb-image",{attrs:{src:e.row.avatarUrl}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"nickName",label:"微信昵称"}}),e._v(" "),a("el-table-column",{attrs:{prop:"user_name",label:e.$t("action.brokerName")+"姓名"}}),e._v(" "),a("el-table-column",{attrs:{prop:"mobile",label:"手机号","min-width":"110"}}),e._v(" "),a("el-table-column",{attrs:{prop:"sh_time",label:"入驻时间","min-width":"100"},scopedSlots:e._u([{key:"default",fn:function(t){return[0!==t.row.sh_time?a("block",[a("p",[e._v(e._s(e._f("handleTime")(t.row.sh_time,1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(t.row.sh_time,2)))])]):a("block",[e._v("-")])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"coach_count",label:"累计邀请"+e.$t("action.attendantName")}}),e._v(" "),a("el-table-column",{attrs:{prop:"order_count",label:"累计成交订单数量"}}),e._v(" "),a("el-table-column",{attrs:{prop:"order_price",label:"累计获得佣金"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s("¥"+t.row.order_price)+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"120",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-inviteTechnician",expression:"`${$route.name}-inviteTechnician`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(a){return e.toShowDialog("technician",t.row.id)}}},[e._v(e._s(e.$t("action.inviteTechnician")))])],1)]}}])})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.list.page,pageSize:e.searchForm.list.limit,total:e.total.list},on:{handleSizeChange:function(t){return e.handleSizeChange(t,"list")},handleCurrentChange:function(t){return e.handleCurrentChange(t,"list")}}}),e._v(" "),a("el-dialog",{attrs:{title:e.$t("action.inviteTechnician"),visible:e.showDialog.technician,width:"900px",top:"5vh",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"technician",t)}}},[a("el-form",{ref:"technicianForm",attrs:{inline:!0,model:e.searchForm.technician,"label-width":"70px"}},[a("el-form-item",{attrs:{label:"输入查询",prop:"coach_name"}},[a("el-input",{attrs:{placeholder:"请输入"+e.$t("action.attendantName")+"姓名"},model:{value:e.searchForm.technician.coach_name,callback:function(t){e.$set(e.searchForm.technician,"coach_name",t)},expression:"searchForm.technician.coach_name"}})],1),e._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1,"technician")}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("technician")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading.technician,expression:"loading.technician"}],ref:"singleTable",staticStyle:{width:"100%"},attrs:{data:e.tableData.technician,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark"}},[a("el-table-column",{attrs:{prop:"nickName",label:e.$t("action.attendantName")+"头像"},scopedSlots:e._u([{key:"default",fn:function(e){return[a("lb-image",{attrs:{src:e.row.work_img}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"coach_name",label:e.$t("action.attendantName")+"姓名"}}),e._v(" "),a("el-table-column",{attrs:{prop:"order_price",label:"周期业绩"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",[e._v("¥"+e._s(t.row.order_price||0))])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"username",label:"入驻时间"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",[e._v(e._s(e._f("handleTime")(t.row.sh_time,1)))]),e._v(" "),a("div",[e._v(e._s(e._f("handleTime")(t.row.sh_time,2)))])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"city",label:"意向工作城市"}}),e._v(" "),a("el-table-column",{attrs:{prop:"admin_name",label:"所属"+e.$t("action.agentName")}})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.technician.page,pageSize:e.searchForm.technician.limit,total:e.total.technician},on:{handleSizeChange:function(t){return e.handleSizeChange(t,"technician")},handleCurrentChange:function(t){return e.handleCurrentChange(t,"technician")}}})],1)],1)],1)},staticRenderFns:[]};var d=a("C7Lr")(h,u,!1,function(e){a("RYmj")},"data-v-3dd8f383",null);t.default=d.exports},RYmj:function(e,t){}});