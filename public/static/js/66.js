webpackJsonp([66],{ISMb:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("3cXf"),n=a.n(r),o=a("lC5x"),i=a.n(o),l=a("J0Oq"),s=a.n(l),c=a("PxTW"),d=a.n(c),m={components:{},data:function(){return{loading:!1,pickerOptions:{disabledDate:function(e){return e.getTime()>1e3*(d()(d()(Date.now()).format("YYYY-MM-DD")).unix()+86400-1)}},storeList:[],searchForm:{page:1,limit:10,start_time:"",end_time:""},tableData:[],total:0,showDialog:!1,fileForm:{record_url:""}}},created:function(){var e=this;return s()(i.a.mark(function t(){return i.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:e.getTableDataList();case 1:case"end":return t.stop()}},t,e)}))()},methods:{handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},getTableDataList:function(e){var t=this;e&&(this.searchForm.page=e),this.loading=!0;var a=JSON.parse(n()(this.searchForm)),r=a.start_time;r&&r.length>1?(a.start_time=r[0]/1e3,a.end_time=r[1]/1e3):(a.start_time="",a.end_time=""),this.$api.system.phoneRecordList(a).then(function(e){t.loading=!1,200===e.code&&(t.tableData=e.data.data,t.total=e.data.total)})},toShowDialog:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return s()(i.a.mark(function a(){var r;return i.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:for(r in e.fileForm)e.fileForm[r]=t[r];e.showDialog=!e.showDialog;case 2:case"end":return a.stop()}},a,e)}))()},toDownLoad:function(e){var t=e.record_url,a=t.split("?Expires=")[0],r=a.substring(a.lastIndexOf("/")+1),n=document.createElement("a"),o=new MouseEvent("click");n.download=r,n.href=t,n.dispatchEvent(o)}},watch:{showDialog:function(e,t){!1===e&&this.$refs.audio_item.pause()}},filters:{handleTime:function(e,t){return 1===t?d()(1e3*e).format("YYYY-MM-DD"):2===t?d()(1e3*e).format("HH:mm:ss"):d()(1e3*e).format("YYYY-MM-DD HH:mm:ss")},handleFileName:function(e){var t="-";if(e){var a=e.split("?Expires=")[0];t=a.substring(a.lastIndexOf("/")+1)}return t}}},u={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-system-virtual-record"},[a("top-nav"),e._v(" "),a("div",{staticClass:"page-main"},[a("el-row",{staticClass:"page-search-form"},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-form-item",{attrs:{label:"拨打时间",prop:"start_time"}},[a("el-date-picker",{attrs:{type:"datetimerange","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"timestamp","picker-options":e.pickerOptions,"default-time":["00:00:00","23:59:59"]},on:{change:function(t){return e.getTableDataList(1)}},model:{value:e.searchForm.start_time,callback:function(t){e.$set(e.searchForm,"start_time",t)},expression:"searchForm.start_time"}})],1)],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark"}},[a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"record_url",label:"文件名称","min-width":"150"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(e._f("handleFileName")(t.row.record_url))+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"call_time",label:"拨打时间","min-width":"110"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("p",[e._v(e._s(e._f("handleTime")(t.row.call_time,1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(t.row.call_time,2)))])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"start_time",label:"接通时间","min-width":"110"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("p",[e._v(e._s(e._f("handleTime")(t.row.start_time,1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(t.row.start_time,2)))])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"end_time",label:"挂断时间","min-width":"110"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("p",[e._v(e._s(e._f("handleTime")(t.row.end_time,1)))]),e._v(" "),a("p",[e._v(e._s(e._f("handleTime")(t.row.end_time,2)))])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"phone_a",label:e.$t("action.attendantName")+"号码","min-width":"110"}}),e._v(" "),a("el-table-column",{attrs:{prop:"phone_b",label:"客户号码","min-width":"110"}}),e._v(" "),a("el-table-column",{attrs:{prop:"phone_x",label:"虚拟号码","min-width":"110"}}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"160",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[t.row.record_url?a("div",{staticClass:"table-operate"},[a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-play",expression:"`${$route.name}-play`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(a){return e.toShowDialog(t.row)}}},[e._v(e._s(e.$t("action.play")))]),e._v(" "),a("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-download",expression:"`${$route.name}-download`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(a){return e.toDownLoad(t.row)}}},[e._v(e._s(e.$t("action.download")))])],1):e._e()]}}])})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}}),e._v(" "),a("el-dialog",{attrs:{title:"音频内容",visible:e.showDialog,"close-on-click-modal":!1,"show-close":!1,width:"600px",center:""},on:{"update:visible":function(t){e.showDialog=t}}},[a("div",{staticClass:"flex-center flex-column",staticStyle:{padding:"50px"}},[a("audio",{ref:"audio_item",attrs:{controls:"",src:e.fileForm.record_url}}),e._v(" "),a("div",{staticClass:"f-title c-title text-bold pt-lg"},[e._v("\n          "+e._s(e._f("handleFileName")(e.fileForm.record_url))+"\n        ")])]),e._v(" "),a("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.showDialog=!1}}},[e._v("取 消")])],1)])],1)],1)},staticRenderFns:[]};var p=a("C7Lr")(m,u,!1,function(e){a("uu23")},"data-v-94f086a4",null);t.default=p.exports},uu23:function(e,t){}});