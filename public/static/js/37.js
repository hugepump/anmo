webpackJsonp([37],{lqSQ:function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=i("4YfN"),o=i.n(r),n=i("lC5x"),a=i.n(n),s=i("3cXf"),c=i.n(s),l=i("J0Oq"),d=i.n(l),u=i("PxTW"),p=i.n(u),m=i("bSIt"),h=i("Mpzp"),v={data:function(){var e=this;return{total:0,cityType:{1:"城市",2:"区县",3:"省"},CodeToText:h.CodeToText,areaOptions:[],regionDataCopy:[],provinceAndCityDataCopy:[],provinceArr:[11e4,12e4,31e4,5e5,81e4,82e4],cityArr:[81e4,82e4],cityCodeArr:[41e4,42e4,46e4,65e4],cityAddArr:[],loading:!1,showDialog:{sub:!1,code:!1,phone:!1},isTreeOpen:!0,showTable:!0,showMap:!1,searchForm:{page:1,limit:10},tableData:[],subForm:{id:0,city_type:1,pid:0,selectedOptions:[],title:"",lng:"",lat:"",province:"",city:"",area:"",province_code:"",city_code:"",area_code:"",is_city:0},subFormRules:{selectedOptions:{required:!0,validator:function(t,i,r){var o=e.subForm.city_type,n=e.provinceArr,a=e.cityArr;1===o&&(0===i.length||i.length<2&&!n.includes(1*i[0]))?r(new Error("请选择城市")):2===o&&(1===i.length||i.length<3&&!a.includes(1*i[0]))?r(new Error("请选择具体区县")):r()},trigger:"blur"},lng:{required:!0,validator:this.$reg.isLng,trigger:"blur"},lat:{required:!0,validator:this.$reg.isLat,trigger:"blur"}},codeForm:{id:0,old_appid:"",winner_appid:"",winner_token:""},codeFormRules:{winner_appid:{required:!0,validator:this.$reg.isNotNull,reg_type:2,text:"APPID",trigger:"blur"},winner_token:{required:!0,validator:this.$reg.isNotNull,reg_type:2,text:"TOKEN",trigger:"blur"}},phoneForm:{id:0,old_phone:"",merchant_phone:""},phoneFormRules:{merchant_phone:{validator:function(e,t,i){t&&!/((^400)-([0-9]{7})$)|(^1[3-9]\d{9}$)|((^0\d{2,3})-(\d{7,8})$)/.test(t)?i(new Error("请输入有效的商家电话，支持400格式")):i()},trigger:"blur"}}}},created:function(){var e=this;return d()(a.a.mark(function t(){var i,r,o;return a.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return i=JSON.parse(c()(h.regionData)),(r=JSON.parse(c()(h.provinceAndCityData))).map(function(e){(1*e.value==5e5||[81e4,82e4].includes(1*e.value)||e.children&&1===e.children.length&&"市辖区"===e.children[0].label)&&delete e.children}),e.regionDataCopy=i,e.provinceAndCityDataCopy=r,o=[],i.map(function(t,i){if(e.cityCodeArr.includes(1*t.value)){var n=t.children.filter(function(e){return!e.label.includes("直辖县级行政区划")}),a=r[i].children.filter(function(e){return!e.label.includes("直辖县级行政区划")}),s=t.children.filter(function(e){return e.label.includes("直辖县级行政区划")})[0].children,c=s.map(function(e){return 1*e.value});o=o.concat(c),e.$nextTick(function(){e.regionDataCopy[i].children=n.concat(s),e.provinceAndCityDataCopy[i].children=a.concat(s)})}}),e.cityAddArr=o,t.next=10,e.getTableDataList(1);case 10:case"end":return t.stop()}},t,e)}))()},computed:o()({},Object(m.e)({routesItem:function(e){return e.routes}})),methods:{handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},getTableDataList:function(e){var t=this;return d()(a.a.mark(function i(){var r,o,n;return a.a.wrap(function(i){for(;;)switch(i.prev=i.next){case 0:return e&&(t.searchForm.page=e),t.tableData=[],t.loading=!0,i.next=5,t.$api.system.cityList(t.searchForm);case 5:if(r=i.sent,o=r.code,n=r.data,t.loading=!1,200===o){i.next=11;break}return i.abrupt("return");case 11:t.tableData=n.data,t.total=n.total;case 13:case"end":return i.stop()}},i,t)}))()},switchTreeOpen:function(){this.isTreeOpen=!this.isTreeOpen},toShowDialog:function(e){var t=this,i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=JSON.parse(c()(i));if("sub"===e){var o=r.pid,n=void 0===o?0:o,a=r.city_type,s=r.province_code,l=r.city_code,d=r.children,u=void 0===d?[]:d,p=JSON.parse(c()(this.regionDataCopy)),m=JSON.parse(c()(this.provinceAndCityDataCopy)),h=p.filter(function(e){return"500000"===e.value});if(n)if("500000"===s){var v=h[0].children[0].children,b=h[0].children[1].children;h[0].children[0].children=v.concat(b),h[0].children=[h[0].children[0]]}else(h=p.filter(function(e){return e.value===s})).map(function(e){e.children&&e.children.map(function(e){e.value===l&&(1===a&&0===u.length||(h[0].children=[e]))})});h.map(function(e){e.children.map(function(e){e.children=e.children&&e.children.length>0?e.children.filter(function(e){return"市辖区"!==e.label}):[]})});var y=1===a&&n||2===a?h:m,_=["province_code"];1===a&&_.push("city_code"),1===a&&n&&y.map(function(e){e.disabled=!0,e.children&&e.children.map(function(e){delete e.children})}),2===a&&(_=_.concat(["city_code","area_code"]),y.map(function(e){e.disabled=!0,!t.cityArr.includes(1*s)&&e.children&&e.children&&e.children.map(function(e){e.disabled=!0})})),this.areaOptions=y,r.selectedOptions=[],(1===a&&r.id||1!==a)&&_.map(function(e){r[e]&&r.selectedOptions.push(r[e])}),r.is_city=r.id?r.is_city:0}for(var f in"code"===e&&(r.old_appid=r.winner_appid),"phone"===e&&(r.old_phone=r.merchant_phone),this[e+"Form"])this[e+"Form"][f]=r[f];this.showDialog[e]=!0,["sub","code","phone"].includes(e)&&this.$refs[e+"Form"].validate&&this.$refs[e+"Form"].clearValidate()},handleChange:function(e){var t=this.subForm.pid,i=void 0===t?0:t,r=e[0]||"",o=[11e4,12e4,31e4,5e5].includes(r)?{110000:110100,120000:120100,310000:310100,500000:500100}[r]:e&&e.length>1?e[1]:"",n=e&&e.length>2?e[2]:"";this.subForm.province_code=r,this.subForm.city_code=o,i&&(this.subForm.area_code=n),this.subForm.province=r?this.CodeToText[r]:"",this.subForm.city=o?this.CodeToText[o]:"",this.subForm.area=n?this.CodeToText[n]:"",this.subForm.title="",this.subForm.lat="",this.subForm.lng=""},clickCity:function(e){this.showMap=!0},getLatLng:function(e){var t=e.lat,i=e.lng;this.subForm.lat=t,this.subForm.lng=i},confirmDel:function(e,t){var i=this;this.$confirm(this.$t("tips.confirmDelete"),this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){i.updateItem(e,-1,"status",t)})},updateItem:function(e,t,i,r){var o=this;return d()(a.a.mark(function n(){return a.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:o.$api.system.cityUpdate("status"===i?{id:e,status:t}:{id:e,is_hot:t}).then(function(e){if(200===e.code)o.$message.success(o.$t(-1===t?"tips.successDel":"tips.successOper")),-1!==t||r||(o.searchForm.page=o.searchForm.page<Math.ceil((o.total-1)/o.searchForm.limit)?o.searchForm.page:Math.ceil((o.total-1)/o.searchForm.limit)),o.getTableDataList();else{if(-1===t)return;o.getTableDataList()}});case 1:case"end":return n.stop()}},n,o)}))()},submitFormInfo:function(e){var t=this,i=!0;if(this.$refs[e+"Form"].validate(function(e){e||(i=!1)}),i){var r=JSON.parse(c()(this[e+"Form"]));if("sub"===e){var o=r.selectedOptions,n=this.provinceArr,a=this.cityArr;r.city_type=r.id?r.city_type:r.area||r.pid&&a.includes(1*o[0])?2:r.city||n.includes(1*o[0])?1:0,delete r.selectedOptions,r.city&&!["市辖区","县"].includes(r.city)||(r.city=r.province),r.title=r.area||r.city||r.province}"code"===e&&delete r.old_appid,"phone"===e&&delete r.old_phone;var s=r.id?"cityUpdate":"cityAdd";this.$api.system[s](r).then(function(i){200===i.code&&(t.$message.success(t.$t("tips.successSub")),t.showDialog[e]=!1,t.getTableDataList())})}}},watch:{isTreeOpen:function(){var e=this;this.showTable=!1,this.$nextTick(function(){e.showTable=!0})}},filters:{handleTime:function(e,t){return 1===t?p()(1e3*e).format("YYYY-MM-DD"):2===t?p()(1e3*e).format("HH:mm:ss"):p()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},b={render:function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{staticClass:"lb-system-city"},[i("top-nav"),e._v(" "),i("div",{staticClass:"page-main"},[i("el-row",{staticClass:"page-top-operate"},[i("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-add",expression:"`${$route.name}-add`"}],staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-plus"},on:{click:function(t){return e.toShowDialog("sub",{city_type:1})}}},[e._v(e._s(e.$t("menu.SystemCityAdd")))]),e._v(" "),i("lb-button",{attrs:{type:"danger",icon:e.isTreeOpen?"el-icon-arrow-up":"el-icon-arrow-down"},on:{click:e.switchTreeOpen}},[e._v("全部"+e._s(e.isTreeOpen?"关闭":"展开"))])],1),e._v(" "),e.showTable?i("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"row-key":"id","default-expand-all":e.isTreeOpen,"tree-props":{children:"children",hasChildren:"hasChildren"}}},[i("el-table-column",{attrs:{prop:"title",label:"城市"}}),e._v(" "),i("el-table-column",{attrs:{prop:"lng",label:"经度"}}),e._v(" "),i("el-table-column",{attrs:{prop:"lat",label:"纬度"}}),e._v(" "),i("el-table-column",{attrs:{prop:"create_time",label:"创建时间"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,1)))]),e._v(" "),i("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,2)))])]}}],null,!1,2718285459)}),e._v(" "),i("el-table-column",{attrs:{prop:"index_show",label:"设为热门"},scopedSlots:e._u([{key:"default",fn:function(t){return 1===t.row.city_type||2===t.row.city_type&&1===t.row.is_city?[i("el-switch",{attrs:{disabled:!e.$route.meta.pagePermission[0].auth.includes("edit"),"active-value":1,"inactive-value":0},on:{change:function(i){return e.updateItem(t.row.id,t.row.is_hot,"is_hot")}},model:{value:t.row.is_hot,callback:function(i){e.$set(t.row,"is_hot",i)},expression:"scope.row.is_hot"}})]:void 0}}],null,!0)}),e._v(" "),i("el-table-column",{attrs:{label:"操作",width:"220",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("div",{staticClass:"table-operate"},[i("lb-button",{directives:[{name:"show",rawName:"v-show",value:3!==t.row.city_type,expression:"scope.row.city_type !== 3"},{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-edit",expression:"`${$route.name}-edit`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(i){return e.toShowDialog("sub",t.row)}}},[e._v(e._s(e.$t("action.edit")))]),e._v(" "),i("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(i){return e.confirmDel(t.row.id,t.row.pid)}}},[e._v(e._s(e.$t("action.delete")))]),e._v(" "),1===t.row.city_type&&!e.cityAddArr.includes(1*t.row.city_code)||3===t.row.city_type&&!e.provinceArr.includes(1*t.row.province_code)?i("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-add",expression:"`${$route.name}-add`"}],attrs:{size:"mini",plain:"",type:1===t.row.city_type?"success":"warning"},on:{click:function(i){return e.toShowDialog("sub",{pid:t.row.id,city_type:1===t.row.city_type?2:1,province_code:t.row.province_code,city_code:t.row.city_code,area_code:t.row.area_code,province:t.row.province,city:t.row.city,area:t.row.area})}}},[e._v(e._s(e.$t(1===t.row.city_type?"menu.SystemCityAreaAdd":"menu.SystemCityAdd")))]):e._e(),e._v(" "),i("lb-button",{directives:[{name:"show",rawName:"v-show",value:3!==t.row.city_type,expression:"scope.row.city_type !== 3"},{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-mobilecode",expression:"`${$route.name}-mobilecode`"}],attrs:{size:"mini",plain:"",type:"purple"},on:{click:function(i){return e.toShowDialog("code",t.row)}}},[e._v(e._s(e.$t(t.row.winner_appid?"menu.SystemCityMobileCodeEdit":"menu.SystemCityMobileCodeAdd")))]),e._v(" "),i("lb-button",{directives:[{name:"show",rawName:"v-show",value:3!==t.row.city_type,expression:"scope.row.city_type !== 3"},{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-merchantphone",expression:"`${$route.name}-merchantphone`"}],attrs:{size:"mini",plain:"",type:"orchid"},on:{click:function(i){return e.toShowDialog("phone",t.row)}}},[e._v(e._s(e.$t(t.row.merchant_phone?"menu.SystemCityMerchantPhoneEdit":"menu.SystemCityMerchantPhoneAdd")))])],1)]}}],null,!1,1976674843)})],1):e._e(),e._v(" "),i("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}})],1),e._v(" "),i("el-dialog",{attrs:{title:e.$t(2===e.subForm.city_type?e.subForm.id?"menu.SystemCityAreaEdit":"menu.SystemCityAreaAdd":e.subForm.id?"menu.SystemCityEdit":"menu.SystemCityAdd"),visible:e.showDialog.sub,width:"500px",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"sub",t)}}},[i("el-form",{ref:"subForm",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"100px"},nativeOn:{submit:function(e){e.preventDefault()}}},[i("el-form-item",{attrs:{label:e.cityType[e.subForm.city_type],prop:"selectedOptions"}},[i("el-cascader",{attrs:{disabled:!(!e.subForm.id||2===e.subForm.city_type),size:"large",options:e.areaOptions,placeholder:"请选择"+e.cityType[e.subForm.city_type],props:{checkStrictly:!0}},on:{change:e.handleChange},model:{value:e.subForm.selectedOptions,callback:function(t){e.$set(e.subForm,"selectedOptions",t)},expression:"subForm.selectedOptions"}})],1),e._v(" "),i("el-form-item",{attrs:{label:"经度",prop:"lng"}},[i("el-input",{attrs:{placeholder:"请输入经度"},model:{value:e.subForm.lng,callback:function(t){e.$set(e.subForm,"lng",t)},expression:"subForm.lng"}})],1),e._v(" "),i("el-form-item",{attrs:{label:"纬度",prop:"lat"}},[i("el-input",{attrs:{placeholder:"请输入纬度"},model:{value:e.subForm.lat,callback:function(t){e.$set(e.subForm,"lat",t)},expression:"subForm.lat"}})],1),e._v(" "),i("el-form-item",{attrs:{label:"",prop:""}},[i("lb-button",{staticClass:"mr-md",attrs:{plain:"",type:"primary"},on:{click:function(t){e.showMap=!0}}},[e._v(e._s(e.$t("action.getLatLng")))])],1),e._v(" "),2===e.subForm.city_type?i("el-form-item",{attrs:{label:"是否被筛选",prop:"is_city"}},[i("el-radio-group",{model:{value:e.subForm.is_city,callback:function(t){e.$set(e.subForm,"is_city",t)},expression:"subForm.is_city"}},[i("el-radio",{attrs:{label:1}},[e._v(e._s(e.$t("action.ON")))]),e._v(" "),i("el-radio",{attrs:{label:0}},[e._v(e._s(e.$t("action.OFF")))])],1),e._v(" "),i("lb-tool-tips",[e._v("选择开启，可在手机端选择该区县")])],1):e._e()],1),e._v(" "),i("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{on:{click:function(t){e.showDialog.sub=!1}}},[e._v(e._s(e.$t("action.cancel")))]),e._v(" "),i("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.submitFormInfo("sub")}}},[e._v(e._s(e.$t("action.comfirm")))])],1)],1),e._v(" "),i("el-dialog",{attrs:{title:e.$t(e.codeForm.old_appid?"menu.SystemCityMobileCodeEdit":"menu.SystemCityMobileCodeAdd"),visible:e.showDialog.code,width:"500px",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"code",t)}}},[i("el-form",{ref:"codeForm",attrs:{model:e.codeForm,rules:e.codeFormRules,"label-width":"100px"},nativeOn:{submit:function(e){e.preventDefault()}}},[i("el-form-item",{attrs:{label:"APPID",prop:"winner_appid"}},[i("el-input",{attrs:{placeholder:"请输入APPID"},model:{value:e.codeForm.winner_appid,callback:function(t){e.$set(e.codeForm,"winner_appid",t)},expression:"codeForm.winner_appid"}})],1),e._v(" "),i("el-form-item",{attrs:{label:"TOKEN",prop:"winner_token"}},[i("el-input",{attrs:{placeholder:"请输入TOKEN"},model:{value:e.codeForm.winner_token,callback:function(t){e.$set(e.codeForm,"winner_token",t)},expression:"codeForm.winner_token"}})],1)],1),e._v(" "),i("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{on:{click:function(t){e.showDialog.code=!1}}},[e._v(e._s(e.$t("action.cancel")))]),e._v(" "),i("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.submitFormInfo("code")}}},[e._v(e._s(e.$t("action.comfirm")))])],1)],1),e._v(" "),i("el-dialog",{attrs:{title:e.$t(e.phoneForm.old_phone?"menu.SystemCityMerchantPhoneEdit":"menu.SystemCityMerchantPhoneAdd"),visible:e.showDialog.phone,width:"500px",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"phone",t)}}},[i("el-form",{ref:"phoneForm",attrs:{model:e.phoneForm,rules:e.phoneFormRules,"label-width":"100px"},nativeOn:{submit:function(e){e.preventDefault()}}},[i("el-form-item",{attrs:{label:"商家电话",prop:"merchant_phone"}},[i("el-input",{attrs:{placeholder:"请输入商家电话"},model:{value:e.phoneForm.merchant_phone,callback:function(t){e.$set(e.phoneForm,"merchant_phone",t)},expression:"phoneForm.merchant_phone"}}),e._v(" "),i("lb-tool-tips",[e._v("支持400电话\n          "),i("div",{staticClass:"mt-sm"},[e._v("400电话格式：400-xxxxxxx")])])],1)],1),e._v(" "),i("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{on:{click:function(t){e.showDialog.phone=!1}}},[e._v(e._s(e.$t("action.cancel")))]),e._v(" "),i("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.submitFormInfo("phone")}}},[e._v(e._s(e.$t("action.comfirm")))])],1)],1),e._v(" "),i("lb-map",{attrs:{addr:e.subForm.area?e.subForm.province===e.subForm.city?""+e.subForm.city+e.subForm.area:""+e.subForm.province+e.subForm.city+e.subForm.area:e.subForm.city?e.subForm.province===e.subForm.city?e.subForm.city:""+e.subForm.province+e.subForm.city:e.subForm.province,dialogVisible:e.showMap},on:{"update:dialogVisible":function(t){e.showMap=t},"update:dialog-visible":function(t){e.showMap=t},selectedLatLng:e.getLatLng}})],1)},staticRenderFns:[]};var y=i("C7Lr")(v,b,!1,function(e){i("tF+k")},"data-v-ece4012a",null);t.default=y.exports},"tF+k":function(e,t){}});