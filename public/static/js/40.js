webpackJsonp([40],{CZ0p:function(e,t){},YDzm:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("3cXf"),n=a.n(r),s=a("lC5x"),l=a.n(s),o=a("J0Oq"),i=a.n(o),c=a("4YfN"),u=a.n(c),m=a("bSIt"),p=a("PxTW"),d=a.n(p),h={data:function(){return{loading:!1,pickerOptions:{disabledDate:function(e){return e.getTime()>1e3*(d()(d()(Date.now()).format("YYYY-MM-DD")).unix()+86400-1)}},statusOptions:[{label:"全部",value:-1},{label:"未读",value:0},{label:"已读",value:1}],cityType:{3:{type:"success",text:"省"},1:{type:"primary",text:"市"},2:{type:"danger",text:"区县"}},statusType:{0:"未读",1:"已读"},searchForm:{page:1,limit:10,start_time:"",end_time:"",name:"",top_name:"",status:-1},tableData:[],total:0}},created:function(){this.getTableDataList(1)},computed:u()({},Object(m.e)({routesItem:function(e){return e.routes}})),methods:{resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},toChange:function(e){var t=this;return i()(l.a.mark(function a(){return l.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:t.searchForm.status=e,t.getTableDataList(1);case 2:case"end":return a.stop()}},a,t)}))()},getTableDataList:function(e){var t=this;return i()(l.a.mark(function a(){var r,s,o,i,c;return l.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(t.searchForm.page=e),t.loading=!0,r=JSON.parse(n()(t.searchForm)),(s=r.start_time)&&s.length>1?(r.start_time=s[0]/1e3,r.end_time=s[1]/1e3):(r.start_time="",r.end_time=""),-1===r.status&&delete r.status,a.next=8,t.$api.agent.agentApplyList(r);case 8:if(o=a.sent,i=o.code,c=o.data,t.loading=!1,200===i){a.next=14;break}return a.abrupt("return");case 14:t.tableData=c.data,t.total=c.total;case 16:case"end":return a.stop()}},a,t)}))()},updateItem:function(e,t){var a=this;return i()(l.a.mark(function r(){return l.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:a.$api.agent.agentApplyCheck({id:e,status:t}).then(function(e){200===e.code&&a.$message.success(a.$t("tips.successOper")),a.getTableDataList()});case 1:case"end":return r.stop()}},r,a)}))()}},filters:{handleTime:function(e,t){return 1===t?d()(1e3*e).format("YYYY-MM-DD"):2===t?d()(1e3*e).format("HH:mm:ss"):d()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},f={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-agent-apply"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"输入查询",prop:"name"}},[a("el-input",{attrs:{placeholder:"请输入姓名/手机号"},model:{value:e.searchForm.name,callback:function(t){e.$set(e.searchForm,"name",t)},expression:"searchForm.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"推荐人",prop:"top_name"}},[a("el-input",{attrs:{placeholder:"请输入推荐人姓名"},model:{value:e.searchForm.top_name,callback:function(t){e.$set(e.searchForm,"top_name",t)},expression:"searchForm.top_name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"阅读状态",prop:"status"}},[a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(t){return e.getTableDataList(1)}},model:{value:e.searchForm.status,callback:function(t){e.$set(e.searchForm,"status",t)},expression:"searchForm.status"}},e._l(e.statusOptions,function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"提交时间",prop:"start_time"}},[a("el-date-picker",{attrs:{type:"datetimerange","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"timestamp","picker-options":e.pickerOptions,"default-time":["00:00:00","23:59:59"]},on:{change:function(t){return e.getTableDataList(1)}},model:{value:e.searchForm.start_time,callback:function(t){e.$set(e.searchForm,"start_time",t)},expression:"searchForm.start_time"}})],1),e._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1)}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("searchForm")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[a("el-table-column",{attrs:{prop:"user_id",label:"用户ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"avatarUrl",label:"微信头像"},scopedSlots:e._u([{key:"default",fn:function(e){return[a("lb-image",{attrs:{src:e.row.avatarUrl}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"nickName",label:"微信昵称"}}),e._v(" "),a("el-table-column",{attrs:{prop:"user_name",label:"姓名"}}),e._v(" "),a("el-table-column",{attrs:{prop:"phone",label:"手机号","min-width":"110"}}),e._v(" "),a("el-table-column",{attrs:{prop:"city_type",label:"申请类型","min-width":"90"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-tag",{attrs:{size:"small",type:e.cityType[t.row.city_type].type}},[e._v(e._s(e.cityType[t.row.city_type].text)+"代理")])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"id_card",label:"身份证号","min-width":"110"}}),e._v(" "),a("el-table-column",{attrs:{prop:"top_name",label:"推荐人"}}),e._v(" "),a("el-table-column",{attrs:{prop:"city",label:"申请加入的区域","min-width":"120"}}),e._v(" "),a("el-table-column",{attrs:{prop:"create_time",label:"提交时间","min-width":"110"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,2)))])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"status",label:"阅读状态"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(e.statusType[t.row.status])+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"90",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"show",rawName:"v-show",value:0===t.row.status,expression:"scope.row.status === 0"},{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-read",expression:"`${$route.name}-read`"}],attrs:{size:"mini",plain:"",type:"success"},on:{click:function(a){return e.updateItem(t.row.id,1)}}},[e._v(e._s(e.$t("action.read")))])],1)]}}])})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}})],1)],1)},staticRenderFns:[]};var _=a("C7Lr")(h,f,!1,function(e){a("CZ0p")},"data-v-d8f47894",null);t.default=_.exports}});