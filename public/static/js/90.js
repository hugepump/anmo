webpackJsonp([90],{Hgpp:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("aA9S"),l=a.n(r),i=a("3cXf"),s=a.n(i),o=a("rVsN"),n=a.n(o),c=a("KH7x"),u=a.n(c),m=a("4YfN"),p=a.n(m),b=a("lC5x"),d=a.n(b),_=a("J0Oq"),v=a.n(_),h=a("bSIt"),f={components:{},data:function(){var e=this,t=function(t,a,r){var l=e.max_balance,i=e.routesItem.userInfo.is_admin,s=e.subForm.admin_id,o=i&&!(void 0===s?0:s)?100:1*l;""!==a&&(!/^(?:0|[1-9][0-9]?|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2}))|100)$/.test(a)||1*a>o)?r(new Error("请输入正确的"+t.text+"，0-"+o+"，支持2位小数")):r()};return{navTitle:"",base_cate:[],base_industry:[],base_service_level:[],reseller_cash_type:1,max_balance:"",base_sendtype:[{title:"上门服务",key:"is_door"},{title:"到店服务",key:"is_store",auth:"store"}],subForm:{is_add:1,id:"",title:"",sub_title:"",industry_type:"",industry_title:"",cate_id:[],cate_name:[],send_type:[],is_door:0,is_store:0,cover:[],price:"",show_unit:"",material_price:"",sale:"",true_sale:"",show_salenum:1,time_long:"",com_balance:"",level_balance:"",admin_id:"",reseller_auth:0,member_service:0,service_level:[],member_service_level:[],coach:[],status:1,top:0},subFormRules:{title:{required:!0,validator:this.$reg.isNotNull,text:"服务名称",reg_type:2,trigger:"blur"},sub_title:{required:!0,validator:this.$reg.isNotNull,text:"副标题",reg_type:2,trigger:"blur"},industry_type:{required:!0,type:"number",message:"请选择关联行业",trigger:"blur"},send_type:{required:!0,type:"array",message:"请选择服务方式",trigger:"blur"},cover:{required:!0,type:"array",message:"请上传图片",trigger:["blur","change"]},price:{required:!0,validator:this.$reg.isMoney,text:"服务价格",trigger:"blur"},material_price:{required:!0,validator:this.$reg.isMoney,text:this.$t("action.materialText"),trigger:"blur"},sale:{required:!0,validator:this.$reg.isNum,trigger:"blur"},time_long:{required:!0,validator:this.$reg.isNum,reg_type:2,trigger:"blur"},com_balance:{required:!1,validator:t,text:"一级"+this.$t("action.resellerName")+"提成",trigger:"blur"},level_balance:{required:!1,validator:t,text:"二级"+this.$t("action.resellerName")+"提成",trigger:"blur"},member_service:{required:!0,type:"number",message:"请选择",trigger:"blur"},service_level:{required:!0,validator:function(t,a,r){var l=e.subForm.service_level;void 0!==l&&l&&0===a.length?r(new Error("请选择指定会员等级专享")):r()},trigger:"blur"},top:{required:!0,type:"number",message:"请输入排序值",trigger:"blur"},coach:{required:!0,type:"array",message:"请选择关联"+this.$t("action.attendantName"),trigger:"blur"}},priceForm:{id:[],price:""},priceFormRules:{price:{required:!0,validator:this.$reg.isMoney,text:"服务价格",trigger:"blur"}},balanceForm:{id:[],balance:""},balanceFormRules:{balance:{required:!0,validator:this.$reg.isPercentDot,text:"服务提成",trigger:"blur"}},searchForm:{page:1,limit:10,status:2,industry_type:"",name:""},total:0,loading:!1,tableData:[],multipleSelection:[],showDialog:{coach:!1,price:!1,balance:!1}}},created:function(){var e=this;return v()(d.a.mark(function t(){var a,r,l,i,s,o,n,c,u,m;return d.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return a=e.$route.query,r=a.id,l=a.view,i=void 0===l?0:l,e.edit_page_info=1*i==1?0:1,s=e.routesItem.userInfo,o=s.is_admin,n=s.id,c=s.admin_id,n=(void 0===c?0:c)||n,e.searchForm.admin_id=o?0:n,t.next=7,e.getBaseInfo();case 7:r?(e.subForm.id=r,e.getDetail(r)):3!==(u=e.routesItem.auth.industrytype)&&(m=e.base_industry.filter(function(e){return e.type===u})[0].id,e.subForm.industry_type=m),e.navTitle=e.edit_page_info?e.$t(r?"menu.ServiceBellEdit":"menu.ServiceBellAdd"):"查看服务";case 9:case"end":return t.stop()}},t,e)}))()},watch:{"subForm.industry_type":function(e){this.searchForm.industry_type=e,this.$refs.subForm.validate&&this.$refs.subForm.clearValidate()}},computed:p()({},Object(h.e)({routesItem:function(e){return e.routes}})),methods:{getBaseInfo:function(){var e=this;return v()(d.a.mark(function t(){var a,r,l,i,s,o,c,m;return d.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return a=e.routesItem.auth.member,t.next=3,n.a.all([e.$api.service.cateSelect(),e.$api.system.configInfo(),e.$api.technician.typeSelect()]);case 3:if(r=t.sent,l=u()(r,3),i=l[0],s=l[1],o=l[2],e.base_cate=i.data,e.reseller_cash_type=s.data.reseller_cash_type,e.max_balance=s.data.agent_reseller_max_balance,e.base_industry=o.data,!a){t.next=18;break}return t.next=15,e.$api.member.levelSelect({is_service:1});case 15:c=t.sent,m=c.data,e.base_service_level=m;case 18:case"end":return t.stop()}},t,e)}))()},getCover:function(e){this.subForm.cover=e},getDetail:function(e){var t=this;return v()(d.a.mark(function a(){var r,l,i,s,o;return d.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return a.next=2,t.$api.service.serviceInfo({id:e});case 2:if(r=a.sent,l=r.code,i=r.data,200===l){a.next=7;break}return a.abrupt("return");case 7:for(s in i.cover=[{url:i.cover}],i.member_service_level=i.service_level,i.service_level=i.service_level.length>0?i.service_level.map(function(e){return e.id}):[],i.com_balance=1*i.com_balance==-1?"":i.com_balance,i.level_balance=1*i.level_balance==-1?"":i.level_balance,!t.routesItem.auth.store&&i.is_store&&(i.is_store=0),t.subForm)t.subForm[s]=i[s];o=[],t.base_sendtype.map(function(e){1===i[e.key]&&o.push(e.title)}),t.subForm.send_type=o,t.searchForm.admin_id=i.admin_id;case 19:case"end":return a.stop()}},a,t)}))()},changeCheckBox:function(e){var t=this;this.base_sendtype.map(function(a){t.subForm[a.key]=e.includes(a.title)?1:0})},toShowDialog:function(e){var t=this,a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return v()(d.a.mark(function r(){var l;return d.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:if("coach"!==e){r.next=6;break}return t.searchForm.name="",r.next=4,t.getTableDataList();case 4:r.next=7;break;case 6:for(l in t[e+"Form"])t[e+"Form"][l]=a[l];case 7:t.showDialog[e]=!t.showDialog[e];case 8:case"end":return r.stop()}},r,t)}))()},resetForm:function(e){this.$refs[e].resetFields(),this.getTableDataList(1)},handleSizeChange:function(e){this.searchForm.limit=e,this.handleCurrentChange(1)},handleCurrentChange:function(e){this.searchForm.page=e,this.getTableDataList()},getTableDataList:function(e){var t=this;return v()(d.a.mark(function a(){var r,l,i;return d.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(t.searchForm.page=e),t.loading=!0,t.multipleSelection.length>0&&t.$refs.multipleTable.clearSelection(),a.next=5,t.$api.technician.coachList(t.searchForm);case 5:if(r=a.sent,l=r.code,i=r.data,t.loading=!1,200===l){a.next=11;break}return a.abrupt("return");case 11:t.tableData=i.data,t.total=i.total;case 13:case"end":return a.stop()}},a,t)}))()},handleSelectionChange:function(e){this.multipleSelection=e},handleDialogConfirm:function(){var e=this;if(0!==this.multipleSelection.length){var t=JSON.parse(s()(this.subForm.coach)),a=t.length>0?t.map(function(e){return e.id}):[],r=this.subForm.price,l=/^(([0-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;this.multipleSelection.map(function(i){i.price=r&&l.test(r)?r:0,a.includes(i.id)||t.push(e.$util.pick(i,["id","admin_id","admin_name","coach_name","work_img","price"]))}),this.subForm.coach=t,this.showDialog.coach=!1}else this.$message.error("请选择"+this.$t("action.attendantName"))},toResetMultipleSelection:function(){this.multipleSelection=[]},toDelItem:function(e){this.subForm.coach.splice(e,1)},toBatch:function(e){var t=this;return v()(d.a.mark(function a(){var r,i,s;return d.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:if(0!==t.multipleSelection.length){a.next=3;break}return t.$message.error("请选择要操作的数据"),a.abrupt("return");case 3:if(r=t.multipleSelection.map(function(e){return e.id}),"delete"!==e){a.next=8;break}return i=t.subForm.coach.filter(function(e){return!r.includes(e.id)}),t.subForm.coach=i,a.abrupt("return");case 8:s="price"===e?{price:""}:{balance:""},s=l()({},s,{id:r}),t.toShowDialog(e,s);case 11:case"end":return a.stop()}},a,t)}))()},submitFormInfo:function(e){var t=this,a=!0;if(this.$refs[e+"Form"].validate(function(e){e||(a=!1)}),a){var r=JSON.parse(s()(this[e+"Form"]));if("sub"!==e){var l=this.subForm.coach;return l.map(function(t){r.id.includes(t.id)&&(t[e]=1*r[e])}),this.subForm.coach=l,void(this.showDialog[e]=!1)}r.cover=r.cover[0].url;var i=r.coach.map(function(e){return{coach_id:e.id,price:e.price||0}});r.coach=i,r.com_balance=""===r.com_balance?"-1":r.com_balance,r.level_balance=""===r.level_balance?"-1":r.level_balance,delete r.industry_title,delete r.send_type,delete r.cate_name,delete r.admin_id,delete r.reseller_auth,delete r.member_service_level;var o=r.id?"serviceUpdate":"serviceAdd";this.$api.service[o](r).then(function(e){200===e.code&&(t.$message.success(t.$t(r.id?"tips.successRev":"tips.successSub")),t.$router.back(-1))})}}},filters:{handleIndustry:function(e,t){var a=t.filter(function(t){return t.id===e});return a&&a.length>0?a[0].title:""}}},g={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"lb-service-bell-edit"},[a("top-nav",{attrs:{title:e.navTitle,isBack:!0}}),e._v(" "),a("div",{staticClass:"page-main"},[e.edit_page_info?e._e():a("block",[a("lb-service-info",{attrs:{info:e.subForm,reseller_cash_type:e.reseller_cash_type}})],1),e._v(" "),e.edit_page_info?a("el-form",{ref:"subForm",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"140px"},nativeOn:{submit:function(e){e.preventDefault()}}},[a("lb-classify-title",{attrs:{title:"基础信息"}}),e._v(" "),a("el-form-item",{attrs:{label:"服务名称",prop:"title"}},[a("el-input",{attrs:{maxlength:"15","show-word-limit":"",placeholder:"请输入服务名称"},model:{value:e.subForm.title,callback:function(t){e.$set(e.subForm,"title",t)},expression:"subForm.title"}})],1),e._v(" "),3===e.routesItem.auth.industrytype?a("block",[a("el-form-item",{attrs:{label:"关联行业",prop:"industry_type"}},[a("div",{staticClass:"flex-y-center"},[a("el-select",{attrs:{placeholder:"请选择",filterable:"",clearable:""},on:{change:function(t){e.subForm.coach=[]}},model:{value:e.subForm.industry_type,callback:function(t){e.$set(e.subForm,"industry_type",t)},expression:"subForm.industry_type"}},e._l(e.base_industry,function(e){return a("el-option",{key:e.id,attrs:{label:e.title,value:e.id}})}),1),e._v(" "),a("div",{staticClass:"ml-md f-caption c-tips"},[e._v("\n              切换关联行业，将清空关联"+e._s(e.$t("action.attendantName"))+"数据\n            ")])],1)])],1):e._e(),e._v(" "),a("el-form-item",{attrs:{label:"所属分类",prop:"cate_id"}},[a("el-select",{attrs:{multiple:"","collapse-tags":"",filterable:"",clearable:"",placeholder:"请选择"},model:{value:e.subForm.cate_id,callback:function(t){e.$set(e.subForm,"cate_id",t)},expression:"subForm.cate_id"}},e._l(e.base_cate,function(e){return a("el-option",{key:e.id,attrs:{label:e.title,value:e.id}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"服务类型",prop:"send_type"}},[a("el-checkbox-group",{on:{change:e.changeCheckBox},model:{value:e.subForm.send_type,callback:function(t){e.$set(e.subForm,"send_type",t)},expression:"subForm.send_type"}},e._l(e.base_sendtype,function(t,r){return a("div",{directives:[{name:"show",rawName:"v-show",value:"is_store"===t.key&&e.routesItem.auth.store||"is_store"!==t.key,expression:"\n              (item.key === 'is_store' && routesItem.auth.store) ||\n              item.key !== 'is_store'\n            "}],key:r,style:{display:"inline-block",marginLeft:0===r?0:"15px"}},[a("el-checkbox",{attrs:{label:t.title}}),e._v(" "),t.tips?a("lb-tool-tips",[e._v(e._s(t.tips))]):e._e()],1)}),0)],1),e._v(" "),a("el-form-item",{attrs:{label:"封面图",prop:"cover"}},[a("lb-cover",{attrs:{fileList:e.subForm.cover},on:{selectedFiles:e.getCover}}),e._v(" "),a("lb-tool-tips",[e._v("图片建议尺寸: 400 * 400")])],1),e._v(" "),a("el-form-item",{attrs:{label:"服务价格",prop:"price"}},[a("el-input",{attrs:{placeholder:"请输入服务价格"},model:{value:e.subForm.price,callback:function(t){e.$set(e.subForm,"price",t)},expression:"subForm.price"}},[a("template",{slot:"append"},[e._v("元")])],2)],1),e._v(" "),a("el-form-item",{attrs:{label:"显示单位",prop:"show_unit"}},[a("el-input",{attrs:{placeholder:"请输入显示单位，例如：小时/次/平方"},model:{value:e.subForm.show_unit,callback:function(t){e.$set(e.subForm,"show_unit",t)},expression:"subForm.show_unit"}}),e._v(" "),a("lb-tool-tips",[e._v("例如：小时/次/平方")])],1),e._v(" "),a("el-form-item",{attrs:{label:e.$t("action.materialText"),prop:"material_price"}},[a("el-input",{attrs:{placeholder:"请输入"+e.$t("action.materialText")},model:{value:e.subForm.material_price,callback:function(t){e.$set(e.subForm,"material_price",t)},expression:"subForm.material_price"}},[a("template",{slot:"append"},[e._v("元")])],2),e._v(" "),a("lb-tool-tips",[e._v("服务过程中消耗的"+e._s(e.$t("action.materialText"))+"，例如：一次性衣物，"+e._s(e.$t("action.materialText"))+"不计入"+e._s(e.$t("action.attendantName"))+"提成")])],1),e._v(" "),a("el-form-item",{attrs:{label:"服务时长",prop:"time_long"}},[a("el-input",{attrs:{placeholder:"请输入服务时长"},model:{value:e.subForm.time_long,callback:function(t){e.$set(e.subForm,"time_long",t)},expression:"subForm.time_long"}},[a("template",{slot:"append"},[e._v("分钟")])],2),e._v(" "),a("lb-tool-tips",[e._v("一次服务的时间段，一般为60分钟")])],1),e._v(" "),a("el-form-item",{attrs:{label:"虚拟销售量",prop:"sale"}},[a("el-input",{attrs:{placeholder:"请输入虚拟销售量"},model:{value:e.subForm.sale,callback:function(t){e.$set(e.subForm,"sale",t)},expression:"subForm.sale"}},[a("template",{slot:"prepend"},[e._v("已售")])],2),e._v(" "),a("lb-tool-tips",[e._v("该虚拟销售量=虚拟+实际销售量")])],1),e._v(" "),e.routesItem.auth.reseller&&1===e.reseller_cash_type&&(e.routesItem.userInfo.is_admin&&(!e.subForm.admin_id||e.subForm.admin_id&&e.subForm.reseller_auth)||!e.routesItem.userInfo.is_admin&&e.routesItem.auth.reseller_auth)?a("block",[a("el-form-item",{attrs:{label:"一级"+e.$t("action.resellerName")+"提成",prop:"com_balance"}},[a("el-input",{attrs:{placeholder:"请输入一级"+e.$t("action.resellerName")+"提成"},model:{value:e.subForm.com_balance,callback:function(t){e.$set(e.subForm,"com_balance",t)},expression:"subForm.com_balance"}},[a("template",{slot:"append"},[e._v("%")])],2),e._v(" "),a("lb-tool-tips",[e._v("实际支付金额的百分比，取值0-100\n            "),a("div",{staticClass:"mt-sm"},[e._v("\n              不输入则表示不单独设置，使用全局设置比例\n            ")])])],1),e._v(" "),a("el-form-item",{attrs:{label:"二级"+e.$t("action.resellerName")+"提成",prop:"level_balance"}},[a("el-input",{attrs:{placeholder:"请输入二级"+e.$t("action.resellerName")+"提成"},model:{value:e.subForm.level_balance,callback:function(t){e.$set(e.subForm,"level_balance",t)},expression:"subForm.level_balance"}},[a("template",{slot:"append"},[e._v("%")])],2),e._v(" "),a("lb-tool-tips",[e._v("实际支付金额的百分比，取值0-100\n            "),a("div",{staticClass:"mt-sm"},[e._v("\n              不输入则表示不单独设置，使用全局设置比例\n            ")])])],1)],1):e._e(),e._v(" "),a("el-form-item",{attrs:{label:"排序值",prop:"top"}},[a("el-input-number",{staticClass:"lb-input-number",attrs:{min:0,controls:!1,placeholder:"请输入排序值"},model:{value:e.subForm.top,callback:function(t){e.$set(e.subForm,"top",t)},expression:"subForm.top"}}),e._v(" "),a("lb-tool-tips",[e._v("值越大, 排序越靠前")])],1),e._v(" "),e.routesItem.auth.member?a("block",[a("lb-classify-title",{attrs:{title:"会员信息"}}),e._v(" "),a("el-form-item",{attrs:{label:"会员专享",prop:"member_service"}},[a("el-radio-group",{model:{value:e.subForm.member_service,callback:function(t){e.$set(e.subForm,"member_service",t)},expression:"subForm.member_service"}},[a("el-radio",{attrs:{label:1}},[e._v(e._s(e.$t("action.ON")))]),e._v(" "),a("el-radio",{attrs:{label:0}},[e._v(e._s(e.$t("action.OFF")))])],1),e._v(" "),a("lb-tool-tips",[e._v("选择开启，该服务只能是指定等级会员可下单")])],1),e._v(" "),e.subForm.member_service?a("el-form-item",{attrs:{label:"指定会员等级专享",prop:"service_level"}},[a("el-select",{attrs:{multiple:"","collapse-tags":"",filterable:"",clearable:"",placeholder:"请选择"},model:{value:e.subForm.service_level,callback:function(t){e.$set(e.subForm,"service_level",t)},expression:"subForm.service_level"}},e._l(e.base_service_level,function(e){return a("el-option",{key:e.id,attrs:{label:e.title,value:e.id}})}),1)],1):e._e()],1):e._e(),e._v(" "),e.subForm.industry_type?a("block",[a("lb-classify-title",{attrs:{title:"其他信息"}}),e._v(" "),a("el-form-item",{attrs:{label:"关联"+e.$t("action.attendantName"),prop:"coach"}},[a("lb-button",{attrs:{type:"primary",icon:"el-icon-plus"},on:{click:function(t){return e.toShowDialog("coach")}}},[e._v("选择"+e._s(e.$t("action.attendantName")))]),e._v(" "),a("el-table",{ref:"multipleTable",staticClass:"mt-lg",staticStyle:{width:"100%"},attrs:{data:e.subForm.coach,"header-cell-style":{background:"#f5f7fa",color:"#606266"},height:e.subForm.coach.length>5?550:""},on:{"selection-change":e.handleSelectionChange}},[a("el-table-column",{attrs:{type:"selection",width:"55"}}),e._v(" "),a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"work_img",label:"头像"},scopedSlots:e._u([{key:"default",fn:function(e){return[a("lb-image",{attrs:{src:e.row.work_img}})]}}],null,!1,1359628658)}),e._v(" "),a("el-table-column",{attrs:{prop:"coach_name",label:"姓名"}}),e._v(" "),a("el-table-column",{attrs:{prop:"admin_name",label:"所属"+e.$t("action.agentName")}}),e._v(" "),a("el-table-column",{attrs:{prop:"price",label:"","min-width":"140"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-input-number",{staticStyle:{width:"120px"},attrs:{size:"small",precision:2,controls:!1,min:0},model:{value:t.row.price,callback:function(a){e.$set(t.row,"price",a)},expression:"scope.row.price"}})]}}],null,!1,3333415811)},[a("template",{slot:"header"},[a("div",{staticStyle:{"margin-top":"7px",padding:"0"}},[e._v("\n                  "+e._s(e.$t("action.attendantName"))+"服务价格"),a("lb-tool-tips",{attrs:{padding:"10"}},[e._v("选择"+e._s(e.$t("action.attendantName"))+"点击确认后，该价格默认为服务价格\n                    "),a("div",{staticClass:"mt-sm"},[e._v("\n                      若没有填写服务价格或填写错误则默认为0\n                    ")])])],1)])],2),e._v(" "),a("el-table-column",{attrs:{label:"操作"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"table-operate"},[a("lb-button",{attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(a){return e.toDelItem(t.$index)}}},[e._v(e._s(e.$t("action.delete")))])],1)]}}],null,!1,2461405206)})],1),e._v(" "),e.subForm.coach.length>0?a("lb-page",{attrs:{selected:e.showDialog.coach?0:e.multipleSelection.length,onlyBatch:!0}},[a("div",{staticClass:"flex-warp batch-btn-list"},[a("lb-button",{attrs:{size:"mini",type:"success"},on:{click:function(t){return e.toBatch("price")}}},[e._v("设置服务价格")]),e._v(" "),a("lb-button",{attrs:{size:"mini",type:"danger"},on:{click:function(t){return e.toBatch("delete")}}},[e._v("删除")])],1)]):e._e()],1)],1):e._e(),e._v(" "),a("el-form-item",[a("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.submitFormInfo("sub")}}},[e._v(e._s(e.$t("action.submit")))]),e._v(" "),a("lb-button",{on:{click:function(t){return e.$router.back(-1)}}},[e._v(e._s(e.$t("action.back")))])],1)],1):e._e(),e._v(" "),a("el-dialog",{attrs:{title:"关联"+e.$t("action.attendantName"),visible:e.showDialog.coach,width:"1000px",top:"5vh",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"coach",t)},close:e.toResetMultipleSelection}},[a("el-form",{ref:"searchForm",attrs:{inline:!0,model:e.searchForm,"label-width":"70px"}},[a("el-form-item",{attrs:{label:"输入查询",prop:"name"}},[a("el-input",{attrs:{placeholder:"请输入"+e.$t("action.attendantName")+"姓名/手机号"},model:{value:e.searchForm.name,callback:function(t){e.$set(e.searchForm,"name",t)},expression:"searchForm.name"}})],1),e._v(" "),a("el-form-item",[a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1)}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),a("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("searchForm")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1),e._v(" "),a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark"},on:{"selection-change":e.handleSelectionChange}},[a("el-table-column",{attrs:{type:"selection",width:"55"}}),e._v(" "),a("el-table-column",{attrs:{prop:"id",label:"ID"}}),e._v(" "),a("el-table-column",{attrs:{prop:"work_img",label:"头像"},scopedSlots:e._u([{key:"default",fn:function(e){return[a("lb-image",{attrs:{src:e.row.work_img}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"coach_name",label:"姓名"}}),e._v(" "),a("el-table-column",{attrs:{prop:"admin_name",label:"所属"+e.$t("action.agentName")}}),e._v(" "),a("el-table-column",{attrs:{prop:"mobile",label:"手机号"}})],1),e._v(" "),a("lb-page",{attrs:{batch:!1,page:e.searchForm.page,pageSize:e.searchForm.limit,total:e.total},on:{handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}}),e._v(" "),a("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.showDialog.coach=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:e.handleDialogConfirm}},[e._v("确 定")])],1)],1),e._v(" "),a("el-dialog",{attrs:{title:"批量设置服务价格",visible:e.showDialog.price,width:"600px",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"price",t)}}},[a("el-form",{ref:"priceForm",attrs:{inline:!0,model:e.priceForm,rules:e.priceFormRules,"label-width":"100px"}},[a("el-form-item",{attrs:{label:"服务价格",prop:"price"}},[a("el-input",{attrs:{placeholder:"请输入服务价格"},model:{value:e.priceForm.price,callback:function(t){e.$set(e.priceForm,"price",t)},expression:"priceForm.price"}})],1)],1),e._v(" "),a("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.showDialog.price=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.submitFormInfo("price")}}},[e._v("确 定")])],1)],1),e._v(" "),a("el-dialog",{attrs:{title:"批量设置服务提成",visible:e.showDialog.balance,width:"500px",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"balance",t)}}},[a("el-form",{ref:"balanceForm",attrs:{inline:!0,model:e.balanceForm,rules:e.balanceFormRules,"label-width":"100px"}},[a("lb-tips",[e._v(e._s(e.$t("action.attendantName"))+"提成优先级：服务提成＞阶段性提成＞等级提成\n          "),a("div",{staticClass:"mt-sm"},[e._v("\n            输入为0，则按照"+e._s(e.$t("action.attendantName"))+"对应的阶段性提成或等级提成来核算\n          ")])]),e._v(" "),a("el-form-item",{attrs:{label:"服务提成",prop:"balance"}},[a("el-input",{attrs:{placeholder:"请输入服务提成"},model:{value:e.balanceForm.balance,callback:function(t){e.$set(e.balanceForm,"balance",t)},expression:"balanceForm.balance"}}),e._v(" "),a("lb-tool-tips",[e._v("\n            取值0-100的数值，支持输入小数，保留小数点后2位\n          ")])],1)],1),e._v(" "),a("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.showDialog.balance=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.submitFormInfo("balance")}}},[e._v("确 定")])],1)],1)],1)],1)},staticRenderFns:[]};var F=a("C7Lr")(f,g,!1,function(e){a("zdH0")},"data-v-68244af8",null);t.default=F.exports},zdH0:function(e,t){}});