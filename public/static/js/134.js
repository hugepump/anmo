webpackJsonp([134],{jFHz:function(e,t){},piSy:function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r("aA9S"),i=r.n(a),s=r("IHPB"),n=r.n(s),l=r("3cXf"),o=r.n(l),c=r("rVsN"),u=r.n(c),d=r("KH7x"),m=r.n(d),p=r("4YfN"),v=r.n(p),h=r("lC5x"),b=r.n(h),f=r("J0Oq"),g=r.n(f),_=r("PxTW"),F=r.n(_),y=r("bSIt"),x={components:{},data:function(){var e=this;return{navTitle:"",showMap:!1,base_city:[],base_star:[{id:1,title:"一星"},{id:2,title:"二星"},{id:3,title:"三星"},{id:4,title:"四星"},{id:5,title:"五星"}],oldData:{},searchForm:{service:{page:1,limit:10,status:1,name:""}},total:{service:0},loading:{service:!1},tableData:{service:[]},showDialog:{service:!1},multipleSelection:[],subForm:{id:0,title:"",city_data:[],province:"",city:"",area:"",address:"",lat:"",lng:"",star:"",phone1:"",phone2:"",min_price:"",cover:[],imgs:[],service:[],status:"",sh_text:""},subFormRules:{title:{required:!0,validator:this.$reg.isNotNull,text:"酒店名称",reg_type:2,trigger:"blur"},city_data:{required:!0,validator:function(e,t,r){t&&t.length<2?r(new Error("请选择具体的市/区县")):r()},trigger:["blur","change"]},star:{required:!0,validator:function(e,t,r){""===t||!/^(([0-5]*)|(([0]\.\d{1}|[1-5]*\.\d{1})))$/.test(t)||t&&1*t>5?r(new Error(""===t?"请输入评分":"请输入正确的评分，取值0至5，最多保留1位小数")):r()},trigger:["blur","change"]},phone:{required:!0,validator:function(t,r,a){var i=/((^400)-([0-9]{7})$)|(^1[3-9]\d{9}$)|((^0\d{2,3})-(\d{7,8})$)/,s=e.subForm,n=s.phone1,l=s.phone2;""!==n&&i.test(n)?l&&!i.test(l)?a(new Error("号码二：请输入有效的座机或手机号")):a():a(new Error(""===r?"号码一：请输入座机或手机号":"号码一：请输入有效的座机或手机号"))},trigger:"blur"},min_price:{required:!0,validator:this.$reg.isFloatNum,text:"房间价格",trigger:"blur"},address:{required:!0,validator:function(t,r,a){var i=e.subForm,s=i.address,n=i.lat,l=i.lng;(s=s?s.replace(/(^\s*)|(\s*$)/g,""):"")?l&&/^[\-\+]?(0(\.\d{1,15})?|([1-9](\d)?)(\.\d{1,15})?|1[0-7]\d{1}(\.\d{1,15})?|180\.0{1,15})$/.test(l)?n&&/^[\-\+]?((0|([1-8]\d?))(\.\d{1,15})?|90(\.0{1,15})?)$/.test(n)?a():a(new Error(n?"请输入正确的纬度":"请输入酒店纬度")):a(new Error(l?"请输入正确的经度":"请输入酒店经度")):a(new Error("请输入酒店详细地址/门牌号"))},trigger:["blur","change"]},cover:{required:!0,type:"array",message:"请上传酒店封面图",trigger:["blur","change"]},imgs:{required:!0,type:"array",message:"请上传酒店详情图",trigger:["blur","change"]},service:{required:!0,type:"array",message:"请选择关联项目",trigger:["blur","change"]}}}},created:function(){var e=this;return g()(b.a.mark(function t(){var r;return b.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return r=e.$route.query.id,t.next=3,e.getBaseInfo();case 3:r&&(e.subForm.id=r,e.getDetail(r)),e.navTitle=e.$t(r?"menu.HotelEdit":"menu.HotelAdd");case 5:case"end":return t.stop()}},t,e)}))()},computed:v()({},Object(y.e)({routesItem:function(e){return e.routes}})),methods:{getBaseInfo:function(){var e=this;return g()(b.a.mark(function t(){var r,a,i;return b.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,u.a.all([e.$api.system.citySelect({city_type:3})]);case 2:r=t.sent,a=m()(r,1),(i=a[0]).data.map(function(e){e.children.map(function(e){0===e.children.length&&delete e.children})}),e.base_city=i.data;case 7:case"end":return t.stop()}},t,e)}))()},getDetail:function(e){var t=this;return g()(b.a.mark(function r(){var a,i,s,n,l,c,u;return b.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return r.next=2,t.$api.hotel.hotelInfo({id:e});case 2:if(a=r.sent,i=a.code,s=a.data,200===i){r.next=7;break}return r.abrupt("return");case 7:for(c in(n=JSON.parse(o()(s))).service=n.service.map(function(e){return e.service_id}),t.oldData=n,s.cover=[{url:s.cover}],s.imgs=s.imgs.map(function(e){return{url:e}}),s.service.map(function(e){e.id=e.service_id}),s.sh_text=4===s.status?s.sh_text?s.sh_text.replace(/\n/g,"<br>"):"没有填写原因哦":"",l=t.handleCityData(s),s.city_data=l,t.subForm)t.subForm[c]=s[c];u=t.routesItem.userInfo.is_admin,(void 0===u?0:u)&&s.admin_id&&(t.searchForm.service.coupon_admin_id=s.admin_id);case 19:case"end":return r.stop()}},r,t)}))()},handleCityData:function(e){var t=[],r=e.province,a=e.city,i=e.area;return this.base_city.map(function(e){e.title===r&&(t.push(e.id),e.children.map(function(e){e.title===a&&(t.push(e.id),e.children&&e.children.length>0&&e.children.map(function(e){e.title===i&&t.push(e.id)}))}))}),t},getCover:function(e,t){this.subForm[t]=e},selectedFiles:function(e,t){var r;(r=this.subForm[t]).push.apply(r,n()(e))},moveFiles:function(e,t){this.subForm[t]=e},getLatLng:function(e){var t=e.lat,r=e.lng,a=e.address;this.subForm.lat=t,this.subForm.lng=r,this.subForm.address=a.split(" ")[1]},changeCityData:function(e){var t=e?e.length:0,r="",a="",s="";if(t>0){var n=this.base_city.filter(function(t){return t.id===e[0]})[0];if(r=n.title,t>1){var l=n.children.filter(function(t){return t.id===e[1]})[0];if(a=l.title,t>2)s=l.children.filter(function(t){return t.id===e[2]})[0].title}}this.subForm=i()({},this.subForm,{province:r,city:a,area:s,lat:"",lng:"",address:""})},changeAddr:function(e){e||(this.subForm=i()({},this.subForm,{lat:"",lng:""}))},toShowDialog:function(e){var t=this;return g()(b.a.mark(function r(){var a;return b.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return a="service"===e?"name":"title",t.searchForm[e][a]="",r.next=4,t.getTableDataList(1,e);case 4:t.showDialog[e]=!t.showDialog[e];case 5:case"end":return r.stop()}},r,t)}))()},resetForm:function(e){var t=e+"Form";this.$refs[t].resetFields(),this.getTableDataList(1,e)},handleSizeChange:function(e,t){this.searchForm[t].limit=e,this.handleCurrentChange(1,t)},handleCurrentChange:function(e,t){this.searchForm[t].page=e,this.getTableDataList("",t)},getTableDataList:function(e,t){var r=this;return g()(b.a.mark(function a(){var i,s,n,l,c,u,d,m;return b.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(r.searchForm[t].page=e),r.tableData[t]=[],r.loading[t]=!0,r.multipleSelection.length>0&&(i=t+"MultipleTable",r.$refs[i].clearSelection()),s=JSON.parse(o()(r.searchForm[t])),n={service:{methodKey:"service",methodModel:"serviceList"}}[t],l=n.methodKey,c=n.methodModel,a.next=9,r.$api[l][c](s);case 9:if(u=a.sent,d=u.code,m=u.data,r.loading[t]=!1,200===d){a.next=15;break}return a.abrupt("return");case 15:r.tableData[t]=m.data,r.total[t]=m.total;case 17:case"end":return a.stop()}},a,r)}))()},handleSelectionChange:function(e){this.multipleSelection=e},handleDialogConfirm:function(e){if(0!==this.multipleSelection.length){var t=JSON.parse(o()(this.subForm[e])),r=t.length>0?t.map(function(e){return e.id}):[];this.multipleSelection.map(function(e){r.includes(e.id)||t.push(e)}),this.subForm[e]=t,this.showDialog[e]=!1}else this.$message.error("请选择服务")},toResetMultipleSelection:function(){this.multipleSelection=[]},toDelItem:function(e,t){this.subForm[t].splice(e,1)},toBatch:function(e){var t=this;return g()(b.a.mark(function r(){var a,i;return b.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:if(0!==t.multipleSelection.length){r.next=3;break}return t.$message.error("请选择要操作的数据"),r.abrupt("return");case 3:a=t.multipleSelection.map(function(e){return e.id}),i=t.subForm[e].filter(function(e){return!a.includes(e.id)}),t.subForm[e]=i;case 6:case"end":return r.stop()}},r,t)}))()},submitFormInfo:function(){var e=this,t=!0;if(this.$refs.subForm.validate(function(e){e||(t=!1)}),t){var r=JSON.parse(o()(this.subForm));r.cover=r.cover[0].url,r.imgs=r.imgs.map(function(e){return e.url}),4===r.status?r.status=1:delete r.status,delete r.sh_text,delete r.city_data,r.service=r.service.map(function(e){return e.id});var a=this.routesItem.userInfo.is_admin,i=void 0===a?0:a,s={},n=0;if(r.id&&!r.status&&!i){var l=["province","city","area","address","lat","lng"],c=o()(this.$util.pick(this.oldData,l)),u=o()(this.$util.pick(r,l));for(var d in r){var m=l.includes(d)?c!==u:o()(r[d])!==o()(this.oldData[d]);m&&n++,s[d]="id"===d||m?r[d]:"-1734593"}n&&(r=s)}if(!r.id||i||n||r.status){var p=r.id?i||r.status?"hotelUpdate":"adminHotelUpdate":"hotelAdd";this.$api.hotel[p](r).then(function(t){200===t.code&&(e.$message.success(e.$t(r.id?"tips.successRev":"tips.successSub")),e.$router.back(-1))})}else this.$message.error("您暂未修改任何内容哦，请修改后再保存！")}}},filters:{handleStartEndTime:function(e){var t="",r=e.start_time,a=e.end_time,i=F()(Date.now()).format("YYYY-MM-DD");return r&&a&&F()(i+" "+a).unix()<=F()(i+" "+r).unix()&&(t="次日"),t}}},w={render:function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"lb-hotel-edit"},[r("top-nav",{attrs:{title:e.navTitle,isBack:!0}}),e._v(" "),r("div",{staticClass:"page-main"},[4===e.subForm.status?r("lb-tips",[r("div",{staticClass:"flex-warp"},[e._v("\n        驳回原因：\n        "),r("div",{staticClass:"flex-1",domProps:{innerHTML:e._s(e.subForm.sh_text)}})])]):e._e(),e._v(" "),r("el-form",{ref:"subForm",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"130px"},nativeOn:{submit:function(e){e.preventDefault()}}},[r("el-form-item",{attrs:{label:"酒店名称",prop:"title"}},[r("el-input",{attrs:{maxlength:"15","show-word-limit":"",placeholder:"请输入酒店名称"},model:{value:e.subForm.title,callback:function(t){e.$set(e.subForm,"title",t)},expression:"subForm.title"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"酒店地址",prop:"city_data"}},[r("el-cascader",{attrs:{size:"large",options:e.base_city,placeholder:"请选择市/区县",props:{checkStrictly:!0,label:"title",value:"id"}},on:{change:e.changeCityData},model:{value:e.subForm.city_data,callback:function(t){e.$set(e.subForm,"city_data",t)},expression:"subForm.city_data"}})],1),e._v(" "),e.subForm.city_data&&e.subForm.city_data.length>1?r("el-form-item",{attrs:{label:"详细地址",prop:"address"}},[r("el-input",{attrs:{placeholder:"请输入详细地址/门牌号"},on:{input:e.changeAddr},model:{value:e.subForm.address,callback:function(t){e.$set(e.subForm,"address",t)},expression:"subForm.address"}}),e._v(" "),e.subForm.address?r("block",[r("div",{staticClass:"mt-md mb-md"},[r("el-input",{attrs:{placeholder:"请输入酒店经度"},model:{value:e.subForm.lng,callback:function(t){e.$set(e.subForm,"lng",t)},expression:"subForm.lng"}})],1),e._v(" "),r("div",[r("el-input",{attrs:{placeholder:"请输入酒店纬度"},model:{value:e.subForm.lat,callback:function(t){e.$set(e.subForm,"lat",t)},expression:"subForm.lat"}}),e._v(" "),r("lb-button",{staticClass:"getLocation",staticStyle:{"margin-left":"10px"},attrs:{type:"primary",plain:""},on:{click:function(t){e.showMap=!0}}},[e._v("获取经纬度")])],1)]):e._e()],1):e._e(),e._v(" "),r("el-form-item",{attrs:{label:"酒店星级",prop:"star"}},[r("el-select",{attrs:{placeholder:"请选择"},model:{value:e.subForm.star,callback:function(t){e.$set(e.subForm,"star",t)},expression:"subForm.star"}},e._l(e.base_star,function(e){return r("el-option",{key:e.id,attrs:{label:e.title,value:e.id}})}),1)],1),e._v(" "),r("el-form-item",{attrs:{label:"酒店电话",prop:"phone"}},[r("div",{staticClass:"flex-y-center"},[r("el-input",{attrs:{placeholder:"请输入座机或手机号"},model:{value:e.subForm.phone1,callback:function(t){e.$set(e.subForm,"phone1",t)},expression:"subForm.phone1"}}),e._v(" "),r("div",{staticClass:"ml-md"},[e._v("号码一（必填）")]),e._v(" "),r("lb-tool-tips",[e._v("支持400电话\n            "),r("div",{staticClass:"mt-sm"},[e._v("400电话格式：400-xxxxxxx")])])],1),e._v(" "),r("div",{staticClass:"flex-y-center mt-md"},[r("el-input",{attrs:{placeholder:"请输入座机或手机号"},model:{value:e.subForm.phone2,callback:function(t){e.$set(e.subForm,"phone2",t)},expression:"subForm.phone2"}}),e._v(" "),r("div",{staticClass:"ml-md"},[e._v("号码二（非必填）")]),e._v(" "),r("lb-tool-tips",[e._v("支持400电话\n            "),r("div",{staticClass:"mt-sm"},[e._v("400电话格式：400-xxxxxxx")])])],1)]),e._v(" "),r("el-form-item",{attrs:{label:"房间价格",prop:"min_price"}},[r("div",{staticClass:"flex-y-center"},[r("el-input",{attrs:{placeholder:"请输入房间价格"},model:{value:e.subForm.min_price,callback:function(t){e.$set(e.subForm,"min_price",t)},expression:"subForm.min_price"}}),e._v(" "),r("div",{staticClass:"ml-md"},[e._v("元起")]),e._v(" "),r("lb-tool-tips",[e._v("酒店房间最低价格")])],1)]),e._v(" "),r("el-form-item",{attrs:{label:"酒店封面图",prop:"cover"}},[r("lb-cover",{attrs:{fileList:e.subForm.cover},on:{selectedFiles:function(t){return e.getCover(t,"cover")}}}),e._v(" "),r("lb-tool-tips",[e._v("图片建议尺寸:160 * 185")])],1),e._v(" "),r("el-form-item",{attrs:{label:"酒店详情图",prop:"imgs"}},[r("lb-cover",{attrs:{fileList:e.subForm.imgs,fileType:"image",type:"more",tips:"750 * 653",fileSize:16},on:{selectedFiles:function(t){return e.selectedFiles(t,"imgs")},moveFiles:function(t){return e.moveFiles(t,"imgs")}}})],1),e._v(" "),r("el-form-item",{attrs:{label:"关联服务",prop:"service"}},[r("lb-button",{attrs:{type:"primary",icon:"el-icon-plus"},on:{click:function(t){return e.toShowDialog("service")}}},[e._v("选择服务")]),e._v(" "),r("el-table",{directives:[{name:"show",rawName:"v-show",value:!e.subForm.use_scene,expression:"!subForm.use_scene"}],ref:"serviceMultipleTable",staticClass:"mt-lg",staticStyle:{width:"100%"},attrs:{data:e.subForm.service,"header-cell-style":{background:"#f5f7fa",color:"#606266"},height:e.subForm.service.length>5?550:""},on:{"selection-change":e.handleSelectionChange}},[r("el-table-column",{attrs:{type:"selection",width:"55"}}),e._v(" "),r("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),r("el-table-column",{attrs:{prop:"cover",label:"封面图"},scopedSlots:e._u([{key:"default",fn:function(e){return[r("lb-image",{attrs:{src:e.row.cover}})]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"title",label:"服务名称"}}),e._v(" "),r("el-table-column",{attrs:{prop:"admin_id",label:"创建人"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.admin_id?""+t.row.admin_city+e.$t("action.agentName")+"-"+t.row.admin_name:t.row.admin_name)+"\n            ")]}}])}),e._v(" "),r("el-table-column",{attrs:{width:"180",label:"操作"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("div",{staticClass:"table-operate"},[r("lb-button",{attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(r){return e.toDelItem(t.$index,"service")}}},[e._v(e._s(e.$t("action.delete")))])],1)]}}])})],1),e._v(" "),e.subForm.service.length>0?r("lb-page",{attrs:{selected:e.showDialog.service?0:e.multipleSelection.length,onlyBatch:!0}},[r("div",{staticClass:"flex-warp batch-btn-list",staticStyle:{width:"90%"}},[r("lb-button",{attrs:{size:"mini",type:"danger"},on:{click:function(t){return e.toBatch("service")}}},[e._v(e._s(e.$t("action.delete")))])],1)]):e._e()],1),e._v(" "),r("el-form-item",[r("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:e.submitFormInfo}},[e._v(e._s(e.$t("action.submit")))]),e._v(" "),r("lb-button",{on:{click:function(t){return e.$router.back(-1)}}},[e._v(e._s(e.$t("action.back")))])],1)],1),e._v(" "),r("el-dialog",{attrs:{title:"关联服务",visible:e.showDialog.service,width:"800px",top:"5vh",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"service",t)},close:e.toResetMultipleSelection}},[r("el-form",{ref:"serviceForm",attrs:{inline:!0,model:e.searchForm.service,"label-width":"70px"}},[r("el-form-item",{attrs:{label:"输入查询",prop:"name"}},[r("el-input",{attrs:{placeholder:"请输入服务名称"},model:{value:e.searchForm.service.name,callback:function(t){e.$set(e.searchForm.service,"name",t)},expression:"searchForm.service.name"}})],1),e._v(" "),r("el-form-item",[r("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1,"service")}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),r("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("service")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1),e._v(" "),r("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData.service,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark"},on:{"selection-change":e.handleSelectionChange}},[r("el-table-column",{attrs:{type:"selection",width:"55"}}),e._v(" "),r("el-table-column",{attrs:{prop:"cover",label:"封面图"},scopedSlots:e._u([{key:"default",fn:function(e){return[r("lb-image",{attrs:{src:e.row.cover}})]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"title",label:"服务名称"}}),e._v(" "),r("el-table-column",{attrs:{prop:"price",label:"服务价格"}}),e._v(" "),r("el-table-column",{attrs:{prop:"admin_name",label:"创建人"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n            "+e._s(t.row.admin_id?""+t.row.admin_city+e.$t("action.agentName")+"-"+t.row.admin_name:t.row.admin_name)+"\n          ")]}}])})],1),e._v(" "),r("lb-page",{attrs:{batch:!1,page:e.searchForm.service.page,pageSize:e.searchForm.service.limit,total:e.total.service},on:{handleSizeChange:function(t){return e.handleSizeChange(t,"service")},handleCurrentChange:function(t){return e.handleCurrentChange(t,"service")}}}),e._v(" "),r("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[r("el-button",{on:{click:function(t){e.showDialog.service=!1}}},[e._v("取 消")]),e._v(" "),r("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.handleDialogConfirm("service")}}},[e._v("确 定")])],1)],1),e._v(" "),r("lb-map",{attrs:{addr:""+e.subForm.province+e.subForm.city+(e.subForm.area||"")+e.subForm.address,dialogVisible:e.showMap},on:{"update:dialogVisible":function(t){e.showMap=t},"update:dialog-visible":function(t){e.showMap=t},selectedLatLng:e.getLatLng}})],1)],1)},staticRenderFns:[]};var k=r("C7Lr")(x,w,!1,function(e){r("jFHz")},"data-v-42b67702",null);t.default=k.exports}});