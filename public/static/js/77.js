webpackJsonp([77],{"2gj8":function(t,e){},"m6e+":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=a("p00s"),n=a.n(i),s=a("3cXf"),r=a.n(s),l=a("lC5x"),o=a.n(l),c=a("J0Oq"),d=a.n(c),m=a("4YfN"),p=a.n(m),u=a("PxTW"),_=a.n(u),h=a("bSIt"),v={components:{sexEcharts:a("SOPO").default},data:function(){return{isLoad:!1,pickerOptions:{disabledDate:function(t){return t.getTime()>1e3*(_()(_()(Date.now()).format("YYYY-MM-DD")).unix()+86400-1)}},countDataList:[{title:"全部"+this.$t("action.attendantName"),key:"total_coach",icon:"icon-account-line",tips:"平台入驻的全部"+this.$t("action.attendantName")+"，手机端的全部"+this.$t("action.attendantName")+"+未认证"+this.$t("action.attendantName"),color:"#608dff"},{title:"当前在线"+this.$t("action.attendantName")+"人数",key:"work_coach",icon:"icon-zaixian",tips:"当前在接单的"+this.$t("action.attendantName")+"是指当前时间为时间范围内的"+this.$t("action.attendantName")+"，包含在线的虚拟"+this.$t("action.attendantName"),color:"#2bcd8e"},{title:"休息"+this.$t("action.attendantName")+"人数",key:"rest_coach",icon:"icon-xiuxi",tips:'<div class="mt-sm">1、关闭了接单按钮的'+this.$t("action.attendantName")+'</div><div class="mt-sm">2、开启了接单按钮但当前时间不在'+this.$t("action.attendantName")+"工作时间范围内的"+this.$t("action.attendantName")+"</div>",color:"#ff6b73"},{title:"接单"+this.$t("action.attendantName")+"人数",key:"app_coach",icon:"icon-jiedan",tips:"当前时间有在进行的订单的"+this.$t("action.attendantName")+"人数",color:"#9370db"},{title:"已绑定"+this.$t("action.attendantName"),key:"bind_coach",icon:"iconshenqingjishi3",tips:"绑定了真实用户ID的"+this.$t("action.attendantName"),color:"#daa520"},{title:"未绑定"+this.$t("action.attendantName"),key:"nobind_coach",icon:"iconpingbiyonghu",tips:"后台添加的"+this.$t("action.attendantName")+"，但未绑定真实用户",color:"#ff6347"},{title:"解约"+this.$t("action.attendantName"),key:"cancel_coach",icon:"iconwoyaojieyue",tips:"取消授权的"+this.$t("action.attendantName"),color:"#5f9ea0"}],base_count:{},rankInd:2,rankList:[{id:1,title:"今日"},{id:2,title:"本周"},{id:3,title:"本月"},{id:4,title:"全年"},{id:5,title:"自定义"}],technicialTypeList:[{id:6,title:"评分"},{id:2,title:"服务时长"},{id:3,title:"业绩"},{id:4,title:"加钟率"}],colorType:{0:"#608dff",1:"#2bcd8e",2:"#ff6b73"},loading:!1,searchForm:{page:1,limit:10,coach_name:"",top_type:6,time_type:3,start_time:"",end_time:""},tableData:[],total:0,downloadLoading:!1}},created:function(){this.initIndex()},computed:p()({},Object(h.e)({routesItem:function(t){return t.routes}})),methods:{initIndex:function(){var t=this;return d()(o.a.mark(function e(){var a,i,n,s,r;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.$api.survey.coachAndUserData();case 2:return a=e.sent,i=a.data,n=i.sex,s=n.man,r=n.woman,s.title="男",r.title="女",i.sex=[s,r],t.base_count=i,t.isLoad=!0,e.next=12,t.getTableDataList(1);case 12:case"end":return e.stop()}},e,t)}))()},resetForm:function(t){this.rankInd=2,this.searchForm.time_type=3,this.$refs[t].resetFields(),this.getTableDataList(1)},handleSizeChange:function(t){this.searchForm.limit=t,this.handleCurrentChange(1)},handleCurrentChange:function(t){this.searchForm.page=t,this.getTableDataList()},getTableDataList:function(t){var e=this;return d()(o.a.mark(function a(){var i,n,s,l,c,d;return o.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:if(t&&(e.searchForm.page=t),e.loading=!0,i=JSON.parse(r()(e.searchForm)),(n=i.start_time)&&n.length>1?(s="00:00:00"===_()(n[1]).format("HH:mm:ss")?86399:0,i.start_time=n[0]/1e3,i.end_time=n[1]/1e3+s):(i.start_time="",i.end_time=""),5!==i.time_type||i.start_time){a.next=9;break}return e.$message.error("请选择自定义时间段"),e.loading=!1,a.abrupt("return");case 9:return a.next=11,e.$api.technician.coachDataList(i);case 11:if(l=a.sent,c=l.code,d=l.data,e.loading=!1,200===c){a.next=17;break}return a.abrupt("return");case 17:e.tableData=d.data,e.total=d.total;case 19:case"end":return a.stop()}},a,e)}))()},toChangeItem:function(t,e){var a=this;return d()(o.a.mark(function i(){var n;return o.a.wrap(function(i){for(;;)switch(i.prev=i.next){case 0:if("rankInd"!==t){i.next=8;break}if(n=a.rankList[e].id,a.searchForm.time_type=n,a.rankInd=e,!(4===e&&a.searchForm.start_time.length<2)){i.next=6;break}return i.abrupt("return");case 6:i.next=10;break;case 8:if(null!==e){i.next=10;break}return i.abrupt("return");case 10:a.getTableDataList(1);case 11:case"end":return i.stop()}},i,a)}))()},toExportExcel:function(){var t=this,e=this.total;if(e>1e4)this.$message.error("最多只能导出10000条数据，当前"+e+"条，请筛选数据点击搜索后再操作导出数据！");else{this.downloadLoading=!0;var a=JSON.parse(r()(this.searchForm)),i=a.start_time;if(i=null===i?[]:i,5===a.time_type&&i.length<2)return this.downloadLoading=!1,void this.$message.error("请选择自定义时间段");if(i&&i.length>1){var s="00:00:00"===_()(i[1]).format("HH:mm:ss")?86399:0;a.start_time=i[0]/1e3,a.end_time=i[1]/1e3+s}else a.start_time="",a.end_time="";var l=this.$util.getProCurrentHref(),o=l.indexOf("?")>0?"":"?",c=l.indexOf("?")>0;n()(a).forEach(function(t,e){o+=c?"&"+t+"="+a[t]:t+"="+a[t],c=!0});var d=window.localStorage.getItem("massage_minitk"),m=l+"/massage/admin/AdminExcel/coachDataList"+o+"&token="+d;window.location.href=m,setTimeout(function(){t.downloadLoading=!1},5e3)}}}},b={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("el-row",{directives:[{name:"loading",rawName:"v-loading",value:!t.isLoad,expression:"!isLoad"}],staticClass:"lb-data-index"},[a("div",{staticClass:"page-main"},[a("div",{staticClass:"flex-between pb-lg f-title text-bold c-title b-1px-b"},[a("div",{staticClass:"flex-y-baseline"},[t._v(t._s(t.$t("action.attendantName"))+"概况")])]),t._v(" "),a("div",{staticClass:"class-menu-list flex-warp mt-lg pt-md"},t._l(t.countDataList,function(e,i){return a("div",{key:i,staticClass:"item-child flex-center pb-lg",class:[{"mt-lg":i>3}]},[a("div",{staticClass:"item-icon flex-center radius mb-sm",style:{background:e.color}},[a("i",{staticClass:"iconfont c-base",class:e.icon})]),t._v(" "),a("div",{staticClass:"flex-1 ml-lg c-title"},[a("div",{staticClass:"f-sm-title text-bold"},[t._v("\n            "+t._s(t.base_count[e.key]||0)+"\n          ")]),t._v(" "),a("div",{staticClass:"flex-y-baseline"},[a("div",{staticClass:"f-caption"},[t._v(t._s(e.title))]),t._v(" "),a("lb-tool-tips",{attrs:{padding:0}},[a("div",{domProps:{innerHTML:t._s(e.tips)}})])],1)])])}),0)]),t._v(" "),a("div",{staticClass:"fill-body space-lg"}),t._v(" "),a("div",{staticClass:"page-main",staticStyle:{height:"552px"}},[a("div",{staticClass:"flex-between pb-lg f-title text-bold c-title b-1px-b"},[a("div",{staticClass:"flex-y-baseline"},[t._v("\n        "+t._s(t.$t("action.attendantName"))+"数据统计\n      ")])]),t._v(" "),a("div",{staticClass:"space-lg"}),t._v(" "),a("div",{staticClass:"data-count-list flex-warp"},[a("div",{staticClass:"item-child pt-md"},[a("div",{staticClass:"flex-y-center pb-lg",staticStyle:{"font-size":"15px"}},[a("div",{staticClass:"c-title text-bold"},[t._v("区域分布")]),t._v(" "),a("a",{staticClass:"c-link ml-md",attrs:{href:"/#/map",target:"_blank"}},[t._v("点击查看地图数据")])]),t._v(" "),a("div",{staticStyle:{width:"90%"}},[a("el-table",{staticStyle:{width:"100%"},attrs:{data:t.base_count.province_coach,height:"365",border:""}},[a("el-table-column",{attrs:{property:"title",label:"省份"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v("\n                "+t._s(1*e.$index+1+"、"+e.row.title)+"\n              ")]}}])}),t._v(" "),a("el-table-column",{attrs:{property:"coach_count",label:"累计"+t.$t("action.attendantName")+"数量"}})],1)],1)]),t._v(" "),a("div",{staticClass:"item-child f-paragraph pt-md"},[a("div",{staticClass:"pb-lg c-title text-bold",staticStyle:{"font-size":"15px"}},[t._v("\n          性别占比\n        ")]),t._v(" "),t.isLoad?a("div",{staticStyle:{width:"100%",height:"400px",background:"#fff"}},[a("sex-echarts",{attrs:{datas:t.base_count.sex}})],1):t._e()])])]),t._v(" "),a("div",{staticClass:"fill-body space-lg"}),t._v(" "),a("div",{staticClass:"page-main"},[a("div",{staticClass:"pb-lg c-title b-1px-b"},[a("div",{staticClass:"flex-y-baseline f-title text-bold"},[t._v("\n        "+t._s(t.$t("action.attendantName"))+"数据\n      ")])]),t._v(" "),a("div",{staticClass:"space-lg"}),t._v(" "),a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:t.searchForm},nativeOn:{submit:function(t){t.preventDefault()}}},[a("el-form-item",{attrs:{label:"输入查询",prop:"coach_name"}},[a("el-input",{attrs:{placeholder:"请输入"+t.$t("action.attendantName")+"姓名全称"},model:{value:t.searchForm.coach_name,callback:function(e){t.$set(t.searchForm,"coach_name",e)},expression:"searchForm.coach_name"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"统计类型",prop:"top_type"}},[a("el-select",{attrs:{placeholder:"请选择",filterable:""},on:{change:function(e){return t.getTableDataList(1)}},model:{value:t.searchForm.top_type,callback:function(e){t.$set(t.searchForm,"top_type",e)},expression:"searchForm.top_type"}},t._l(t.technicialTypeList,function(t){return a("el-option",{key:t.id,attrs:{label:t.title,value:t.id}})}),1)],1),t._v(" "),a("el-form-item",{attrs:{label:"时间",prop:"start_time"}},[a("el-button-group",t._l(t.rankList,function(e,i){return a("el-button",{key:i,attrs:{plain:t.rankInd!==i,type:"primary"},on:{click:function(e){return t.toChangeItem("rankInd",i)}}},[t._v(t._s(e.title))])}),1),t._v(" "),5===t.searchForm.time_type?a("el-date-picker",{attrs:{type:"daterange","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"timestamp","picker-options":t.pickerOptions,"default-time":["00:00:00","23:59:59"]},on:{change:function(e){return t.toChangeItem("start_time",e)}},model:{value:t.searchForm.start_time,callback:function(e){t.$set(t.searchForm,"start_time",e)},expression:"searchForm.start_time"}}):t._e()],1),t._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(e){return t.getTableDataList(1)}}},[t._v(t._s(t.$t("action.search")))]),t._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(e){return t.resetForm("searchForm")}}},[t._v(t._s(t.$t("action.reset")))])],1)],1)],1),t._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:t.$route.name+"-export",expression:"`${$route.name}-export`"}],staticStyle:{"margin-bottom":"20px"},attrs:{size:"mini",plain:"",type:"primary",icon:"el-icon-download",loading:t.downloadLoading},on:{click:t.toExportExcel}},[t._v("\n      "+t._s(t.$t("action.export"))+"\n    ")]),t._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:t.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[a("el-table-column",{attrs:{prop:"coach_id",label:"ID"}}),t._v(" "),a("el-table-column",{attrs:{prop:"work_img",label:"头像","min-width":"120"},scopedSlots:t._u([{key:"default",fn:function(t){return[a("lb-image",{attrs:{src:t.row.work_img}})]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"coach_name",label:"姓名","min-width":"120"}}),t._v(" "),t.routesItem.auth.coachcredit?a("el-table-column",{attrs:{prop:"credit_value",label:"信用分","min-width":"100"}}):t._e(),t._v(" "),a("el-table-column",{attrs:{prop:"coach_star",label:"评分"}}),t._v(" "),a("el-table-column",{attrs:{prop:"coach_level.title",label:"等级","min-width":"120"}},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[t._v("\n            等级"),a("lb-tool-tips",{attrs:{padding:"10"}},[t._v(t._s(t.$t("action.attendantName"))+"当日等级")])],1)])],2),t._v(" "),a("el-table-column",{attrs:{prop:"service_timelong",label:"服务时长","min-width":"120"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v("\n          "+t._s(e.row.service_timelong)+"分钟\n        ")]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"coach_onlinetime",label:"在线时长","min-width":"120"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v("\n          "+t._s(e.row.coach_onlinetime)+"分钟\n        ")]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"order_service_price",label:"业绩","min-width":"120"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v("\n          ¥"+t._s(e.row.order_service_price)+"\n        ")]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"add_balance",label:"","min-width":"120"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v(" "+t._s(e.row.add_balance)+"% ")]}}])},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[t._v("\n            加钟率"),a("lb-tool-tips",{attrs:{padding:"10"}},[t._v("完成加钟业绩总和/业绩总和的百分比")])],1)])],2),t._v(" "),a("el-table-column",{attrs:{prop:"coach_integral",label:"积分","min-width":"120"}},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[t._v("\n            积分"),a("lb-tool-tips",{attrs:{padding:"10"}},[t._v("积分包含邀请储值和时长兑换的积分")])],1)])],2),t._v(" "),a("el-table-column",{attrs:{prop:"total_order_count",label:"总订单量","min-width":"125"}},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[t._v("\n            总订单量"),a("lb-tool-tips",{attrs:{padding:"10"}},[t._v("时间筛选范围内，产生的所有订单量，包含退款订单和已服务订单以及拒单的订单")])],1)])],2),t._v(" "),a("el-table-column",{attrs:{prop:"service_order_count",label:"已完成单量","min-width":"125"}},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[t._v("\n            已完成单量"),a("lb-tool-tips",{attrs:{padding:"10"}},[t._v("时间筛选范围内，统计这个"+t._s(t.$t("action.attendantName"))+"已完成服务的订单量，完成支付的且除去"+t._s(t.$t("action.attendantName"))+"拒单的")])],1)])],2),t._v(" "),a("el-table-column",{attrs:{prop:"refund_balance",label:"","min-width":"125"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v("\n          "+t._s(e.row.refund_balance)+"%\n        ")]}}])},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[t._v("\n            退单率"),a("lb-tool-tips",{attrs:{padding:"10"}},[t._v("退单率=用户退单订单数/总订单数")])],1)])],2),t._v(" "),a("el-table-column",{attrs:{prop:"cancel_order_count",label:"总拒单数","min-width":"120"}})],1),t._v(" "),a("lb-page",{attrs:{batch:!1,page:t.searchForm.page,pageSize:t.searchForm.limit,total:t.total},on:{handleSizeChange:t.handleSizeChange,handleCurrentChange:t.handleCurrentChange}}),t._v(" "),a("div",{staticClass:"space-md"})],1)])},staticRenderFns:[]};var f=a("C7Lr")(v,b,!1,function(t){a("2gj8")},"data-v-7ea44b2b",null);e.default=f.exports}});