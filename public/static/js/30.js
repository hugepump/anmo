webpackJsonp([30],{HYNU:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("lC5x"),n=a.n(r),o=a("3cXf"),i=a.n(o),s=a("J0Oq"),l=a.n(s),c=a("4YfN"),u=a.n(c),d=a("bSIt"),p=a("PxTW"),m=a.n(p),h={data:function(){return{pickerOptions:{disabledDate:function(e){return e.getTime()>1e3*(m()(m()(Date.now()).format("YYYY-MM-DD")).unix()+86400-1)}},typeList:[],statusList:[{id:-1,title:"全部"},{id:0,title:"未读"},{id:1,title:"已读"}],noticeTypeText:{1:"你有一笔新的订单，请及时联系"+this.$t("action.attendantName")+"处理",2:"你有一笔新的退款订单，请及时处理",3:"您有一笔新的订单，已被"+this.$t("action.attendantName")+"拒绝，请在后台及时处理转单，避免平台订单流失",4:"你有一笔新的订单，"+this.$t("action.attendantName")+"长时间未接单，请联系"+this.$t("action.attendantName")+"及时接单",5:"你有一笔新的订单有迟到风险，请跟进"+this.$t("action.attendantName")+"是否达到目的地",6:"监测到有"+this.$t("action.attendantName")+"完成服务后，未离开目的地，请联系"+this.$t("action.attendantName")+"询问具体情况，如遇安全问题，请及时报警，如是跳单情况，根据平台规则自行处理"},loading:!1,searchForm:{page:1,limit:10,start_time:"",end_time:"",type:0,have_look:-1},tableData:[],total:0}},created:function(){var e=this.routesItem,t=e.ShopOrderPage,a=e.ShopBellOrderPage,r=e.ShopRefundPage,n=e.ShopBellRefundPage,o=e.ShopRefuseOrderPage,i=[{id:0,title:"全部"},{id:1,title:"来单通知"},{id:2,title:"退款通知"},{id:3,title:"拒单通知"},{id:4,title:"未接单通知"},{id:5,title:"服务迟到通知"},{id:6,title:"跳单预警通知"}];i.map(function(e){(!t&&[5,6].includes(e.id)||!t&&!a&&[1,4].includes(e.id)||!r&&!n&&2===e.id||!o&&3===e.id)&&(e.is_hide=!0)});var s=i.filter(function(e){return!e.id||!e.is_hide});this.typeList=s},activated:function(){this.getTableDataList()},computed:u()({},Object(d.e)({routesItem:function(e){return e.routes}})),methods:u()({},Object(d.d)(["changeRoutesItem"]),{resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},getTableDataList:function(e){var t=this;return l()(n.a.mark(function a(){var r,o,s,l,c;return n.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(t.searchForm.page=e),t.tableData=[],t.loading=!0,r=JSON.parse(i()(t.searchForm)),(o=r.start_time)&&o.length>1?(r.start_time=o[0]/1e3,r.end_time=o[1]/1e3):(r.start_time="",r.end_time=""),-1===r.have_look&&delete r.have_look,a.next=9,t.$api.shop.noticeList(r);case 9:if(s=a.sent,l=s.code,c=s.data,t.loading=!1,200===l){a.next=15;break}return a.abrupt("return");case 15:t.tableData=c.data,t.total=c.total;case 17:case"end":return a.stop()}},a,t)}))()},confirmDel:function(e){var t=this;this.$confirm(this.$t("tips.confirmDelete"),this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){t.updateItem(e,-1)}).catch(function(){})},updateItem:function(e,t){var a=this,r=!(arguments.length>2&&void 0!==arguments[2])||arguments[2];return l()(n.a.mark(function o(){var i;return n.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:i={id:e,have_look:t},1===t&&(i.is_pop=1),a.$api.shop.noticeUpdate(i).then(function(e){if(200===e.code)1===t&&a.changeRoutesItem({key:"notice_num",val:a.routesItem.notice_num-1}),r&&a.$message.success(a.$t(-1===t?"tips.successDel":"tips.successOper")),-1===t&&(a.searchForm.page=a.searchForm.page<Math.ceil((a.total-1)/a.searchForm.limit)?a.searchForm.page:Math.ceil((a.total-1)/a.searchForm.limit)),a.getTableDataList();else{if(-1===t)return;a.getTableDataList()}});case 3:case"end":return n.stop()}},o,a)}))()},goNoticeItem:function(e){var t=this;return l()(n.a.mark(function a(){var r,o,i,s,l,c;return n.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:r=e.id,o=e.type,i=e.order_id,s=void 0===i?0:i,l=e.have_look,(void 0===l?0:l)||t.updateItem(r,1,!1),c=2===o?"/shop/refund/detail?id="+s:3===o?"/shop/order/refuse":"/shop/order/detail?id="+s,t.$router.push(c);case 4:case"end":return a.stop()}},a,t)}))()}}),filters:{handleTime:function(e,t){return 1===t?m()(1e3*e).format("YYYY-MM-DD"):2===t?m()(1e3*e).format("HH:mm:ss"):m()(1e3*e).format("YYYY-MM-DD HH:mm:ss")},handleNoticType:function(e,t){var a=t.filter(function(t){return t.id===e});return a.length>0?a[0].title:""}}},f={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-shop-notice"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"通知类型",prop:"type"}},[a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(t){return e.getTableDataList(1)}},model:{value:e.searchForm.type,callback:function(t){e.$set(e.searchForm,"type",t)},expression:"searchForm.type"}},e._l(e.typeList,function(e){return a("el-option",{key:e.id,attrs:{label:e.title,value:e.id}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"阅读状态",prop:"have_look"}},[a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(t){return e.getTableDataList(1)}},model:{value:e.searchForm.have_look,callback:function(t){e.$set(e.searchForm,"have_look",t)},expression:"searchForm.have_look"}},e._l(e.statusList,function(e){return a("el-option",{key:e.id,attrs:{label:e.title,value:e.id}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"推送时间",prop:"start_time"}},[a("el-date-picker",{attrs:{type:"datetimerange","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"timestamp","picker-options":e.pickerOptions,"default-time":["00:00:00","23:59:59"]},on:{change:function(t){return e.getTableDataList(1)}},model:{value:e.searchForm.start_time,callback:function(t){e.$set(e.searchForm,"start_time",t)},expression:"searchForm.start_time"}})],1)],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"order_id",label:"订单ID",width:"120"}}),e._v(" "),a("el-table-column",{attrs:{prop:"type",label:"通知类型"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(e._f("handleNoticType")(t.row.type,e.typeList))+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"type",label:"消息内容","min-width":"200"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"flex-y-center"},[e._v("\n            "+e._s(e.noticeTypeText[t.row.type])+"\n          ")])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"create_time",label:"推送时间","min-width":"110"}}),e._v(" "),a("el-table-column",{attrs:{prop:"have_look",label:"阅读状态"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(e._f("handleNoticType")(t.row.have_look,e.statusList))+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"160",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"show",rawName:"v-show",value:e.$route.meta.pagePermission[0].auth.includes("read")&&(3===t.row.type?e.routesItem.ShopRefuseOrder:2===t.row.type?t.row.is_add?e.routesItem.ShopBellRefund:e.routesItem.ShopRefund:t.row.is_add?e.routesItem.ShopBellOrder:e.routesItem.ShopOrder),expression:"\n                $route.meta.pagePermission[0].auth.includes('read') &&\n                (scope.row.type === 3\n                  ? routesItem.ShopRefuseOrder\n                  : scope.row.type === 2\n                  ? scope.row.is_add\n                    ? routesItem.ShopBellRefund\n                    : routesItem.ShopRefund\n                  : scope.row.is_add\n                  ? routesItem.ShopBellOrder\n                  : routesItem.ShopOrder)\n              "}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(a){return e.goNoticeItem(t.row)}}},[e._v(e._s(e.$t("action.view")))]),e._v(" "),a("lb-button",{directives:[{name:"show",rawName:"v-show",value:1===t.row.have_look,expression:"scope.row.have_look === 1"},{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(a){return e.confirmDel(t.row.id)}}},[e._v(e._s(e.$t("action.delete")))]),e._v(" "),a("lb-button",{directives:[{name:"show",rawName:"v-show",value:0===t.row.have_look,expression:"scope.row.have_look === 0"},{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-read",expression:"`${$route.name}-read`"}],attrs:{size:"mini",plain:"",type:"success"},on:{click:function(a){return e.updateItem(t.row.id,1)}}},[e._v(e._s(e.$t("action.read")))])],1)]}}])})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}})],1)],1)},staticRenderFns:[]};var v=a("C7Lr")(h,f,!1,function(e){a("YMe2"),a("qfo3")},"data-v-09726271",null);t.default=v.exports},YMe2:function(e,t){},qfo3:function(e,t){}});