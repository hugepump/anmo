webpackJsonp([103],{farE:function(e,t){},yIk5:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var i=a("p00s"),r=a.n(i),n=a("hRKE"),s=a.n(n),o=a("3cXf"),l=a.n(o),c=a("4YfN"),d=a.n(c),m=a("lC5x"),u=a.n(m),h=a("J0Oq"),f=a.n(h),p=a("bSIt"),g=a("PxTW"),_=a.n(g),v={data:function(){return{pickerOptions:{disabledDate:function(e){return e.getTime()>1e3*(_()(_()(Date.now()).format("YYYY-MM-DD")).unix()+86400-1)}},pagePermission:[],searchForm:{article_id:0,page:1,limit:10,start_time:"",end_time:""},downloadLoading:!1,multipleSelection:[],loading:!1,tableHeader:[],tableData:[],total:0}},created:function(){var e=this;return f()(u.a.mark(function t(){var a;return u.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return a=e.$route.query.id,e.searchForm.article_id=a,e.routesItem.routes.map(function(t){"/market"===t.path&&t.children.map(function(t){"MarketArticle"===t.name&&(e.pagePermission=t.meta.pagePermission[0].auth)})}),t.next=5,e.getTableHeader();case 5:e.getTableDataList();case 6:case"end":return t.stop()}},t,e)}))()},computed:d()({},Object(p.e)({routesItem:function(e){return e.routes}})),methods:{getTableHeader:function(){var e=this;return f()(u.a.mark(function t(){var a,i,r,n;return u.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return a=e.searchForm.article_id,t.next=3,e.$api.market.subTitle({article_id:a});case 3:if(i=t.sent,r=i.code,n=i.data,200===r){t.next=8;break}return t.abrupt("return");case 8:n.push({field_id:"create_time",title:"提交时间"}),n.unshift({field_id:"user_id",title:"用户ID"},{field_id:"nickName",title:"微信昵称"},{field_id:"avatarUrl",title:"微信头像"}),e.tableHeader=n,e.loading=!1;case 12:case"end":return t.stop()}},t,e)}))()},toResetForm:function(){this.$refs.multipleTable.clearSelection(),this.getTableDataList(1)},resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},getTableDataList:function(e){var t=this;e&&(this.searchForm.page=e),this.loading=!0;var a=JSON.parse(l()(this.searchForm)),i=a.start_time;i&&i.length>1?(a.start_time=i[0]/1e3,a.end_time=i[1]/1e3):(a.start_time="",a.end_time=""),this.$api.market.subDataList(a).then(function(e){t.loading=!1,200===e.code&&(t.tableData=e.data.data,t.total=e.data.total)})},handleSelectionChange:function(e){this.multipleSelection=e},toExportExcel:function(){var e=this,t=this.total;if(t>1e4)this.$message.error("最多只能导出10000条数据，当前"+t+"条，请筛选数据点击搜索后再操作导出数据！");else{this.downloadLoading=!0;var a=JSON.parse(l()(this.searchForm)),i=a.start_time;i&&i.length>1?(a.start_time=i[0]/1e3,a.end_time=i[1]/1e3):(a.start_time="",a.end_time="");var n=[],o=JSON.parse(l()(this.multipleSelection));"object"===(void 0===o?"undefined":s()(o))&&o&&o.length>0&&o.map(function(e){n.push(e.id)}),a.id=n;var c=this.$util.getProCurrentHref(),d=c.indexOf("?")>0?"":"?",m=c.indexOf("?")>0;r()(a).forEach(function(e,t){d+=m?"&"+e+"="+a[e]:e+"="+a[e],m=!0});var u=window.localStorage.getItem("massage_minitk"),h=c+"/massage/admin/AdminExcel/subDataList"+d+"&token="+u;window.location.href=h,setTimeout(function(){e.downloadLoading=!1},5e3)}}},filters:{handleTime:function(e,t){return 1===t?_()(1e3*e).format("YYYY-MM-DD"):2===t?_()(1e3*e).format("HH:mm:ss"):_()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},b={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-market-article-record"},[a("top-nav",{attrs:{isBack:!0}}),e._v(" "),a("div",{staticClass:"page-main"},[a("div",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"提交时间",prop:"range"}},[a("el-date-picker",{attrs:{type:"datetimerange","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"timestamp","picker-options":e.pickerOptions,"default-time":["00:00:00","23:59:59"]},on:{change:e.toResetForm},model:{value:e.searchForm.start_time,callback:function(t){e.$set(e.searchForm,"start_time",t)},expression:"searchForm.start_time"}})],1)],1)],1),e._v(" "),a("div",{staticClass:"page-search-form",staticStyle:{"padding-bottom":"24px"}},[a("el-row",[a("el-col",{staticClass:"text-bold flex-y-center",attrs:{span:18}},[e._v("数据列表")]),e._v(" "),a("el-col",{staticClass:"text-right",attrs:{span:6}},[a("lb-button",{directives:[{name:"show",rawName:"v-show",value:e.pagePermission.includes("formExport"),expression:"pagePermission.includes('formExport')"}],attrs:{plain:"",type:"primary",icon:"el-icon-download",loading:e.downloadLoading},on:{click:e.toExportExcel}},[e._v(e._s(e.$t("action.export")))])],1)],1),e._v(" "),a("div",{staticClass:"space-lg"}),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],ref:"multipleTable",attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark"},on:{"selection-change":e.handleSelectionChange}},[a("el-table-column",{attrs:{type:"selection",width:"55"}}),e._v(" "),e._l(e.tableHeader,function(t,i){return a("el-table-column",{key:i,attrs:{label:t.title,"min-width":"120"},scopedSlots:e._u([{key:"default",fn:function(i){return["create_time"==t.field_id?a("div",[a("p",[e._v(e._s(e._f("handleTime")(i.row[t.field_id],1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(i.row[t.field_id],2)))])]):"avatarUrl"===t.field_id?a("div",[i.row[t.field_id]?a("img",{staticClass:"avatar",attrs:{src:i.row[t.field_id]}}):e._e()]):a("span",[e._v(e._s(i.row[t.field_id]||"-"))])]}}],null,!0)})})],2),e._v(" "),a("lb-page",{attrs:{isShowBatch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total,selected:e.multipleSelection.length},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}})],1)])],1)},staticRenderFns:[]};var w=a("C7Lr")(v,b,!1,function(e){a("farE")},"data-v-5a70f9ef",null);t.default=w.exports}});