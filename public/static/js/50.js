webpackJsonp([50],{Jys6:function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r("3cXf"),n=r.n(a),s=r("rVsN"),i=r.n(s),o=r("KH7x"),u=r.n(o),l=r("4YfN"),c=r.n(l),m=r("lC5x"),d=r.n(m),h=r("J0Oq"),p=r.n(h),b=r("bSIt"),f=r("PxTW"),_=r.n(f),v={data:function(){return{pickerOptions:{disabledDate:function(e){return e.getTime()>1e3*(_()(_()(Date.now()).format("YYYY-MM-DD")).unix()+86400-1)}},navTitle:"",cityType:{3:{type:"success",text:"省"},1:{type:"primary",text:"市"},2:{type:"danger",text:"区县"}},base_agent:[],have_user_id:!1,searchForm:{user:{page:1,limit:10,nickName:""}},loading:{user:!1},tableData:{user:[]},total:{user:0},showDialog:{user:!1},multipleSelection:[],currentRow:{},subForm:{id:"",user_id:"",nickName:"",user_name:"",true_user_name:"",phone:"",admin_id:"",admin_name:""},subFormRules:{user_id:{required:!0,type:"number",message:"请选择关联用户",trigger:"blur"},user_name:{required:!0,validator:this.$reg.isNotNull,text:"业务员姓名",reg_type:2,trigger:"blur"},true_user_name:{required:!0,validator:this.$reg.isNotNull,text:"真实姓名",reg_type:2,trigger:"blur"},phone:{required:!0,validator:this.$reg.isTel,text:"手机号",reg_type:2,trigger:"blur"}}}},created:function(){var e=this;return p()(d.a.mark(function t(){var r,a,n,s,i,o,u;return d.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return r=e.$route.query.id,a=e.routesItem.userInfo,n=a.is_admin,s=a.id,i=a.admin_id,o=void 0===i?0:i,u=a.agent_name,s=o||s,e.subForm.admin_id=n?"":s,e.subForm.admin_name=n?"":u,t.next=7,e.getBaseInfo();case 7:if(!r){t.next=11;break}return e.subForm.id=r,t.next=11,e.getDetail(r);case 11:e.navTitle=e.$t(r?"menu.SalesmanEdit":"menu.SalesmanAdd");case 12:case"end":return t.stop()}},t,e)}))()},computed:c()({},Object(b.e)({routesItem:function(e){return e.routes}})),methods:{getBaseInfo:function(){var e=this;return p()(d.a.mark(function t(){var r,a,n;return d.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,i.a.all([e.$api.agent.adminSelect({salesman_auth:1})]);case 2:r=t.sent,a=u()(r,1),n=a[0],e.base_agent=n.data;case 6:case"end":return t.stop()}},t,e)}))()},getDetail:function(e){var t=this;return p()(d.a.mark(function r(){var a,n,s;return d.a.wrap(function(r){for(;;)switch(r.prev=r.next){case 0:return r.next=2,t.$api.salesman.salesmanInfo({id:e});case 2:for(s in a=r.sent,(n=a.data).admin_id=n.admin_id||"",t.subForm)t.subForm[s]=n[s];t.have_user_id=n.id&&n.user_id;case 7:case"end":return r.stop()}},r,t)}))()},toShowDialog:function(e){var t=this,r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return p()(d.a.mark(function a(){return d.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:if(r=JSON.parse(n()(r)),"user"!==e||!t.have_user_id){a.next=3;break}return a.abrupt("return");case 3:return t.currentRow={},t.searchForm[e].nickName="",a.next=7,t.getTableDataList(1,e);case 7:t.showDialog[e]=!t.showDialog[e];case 8:case"end":return a.stop()}},a,t)}))()},resetForm:function(e){var t=e+"Form";this.$refs[t].resetFields(),this.getTableDataList(1,e)},handleSizeChange:function(e,t){this.searchForm[t].limit=e,this.handleCurrentChange(1,t)},handleCurrentChange:function(e,t){this.searchForm[t].page=e,this.getTableDataList("",t)},getTableDataList:function(e,t){var r=this;return p()(d.a.mark(function a(){var s,i,o,u,l,c,m;return d.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(r.searchForm[t].page=e),r.tableData[t]=[],r.loading[t]=!0,s=JSON.parse(n()(r.searchForm[t])),i={user:{methodKey:"salesman",methodModel:"noSalesmanUserList"}}[t],o=i.methodKey,u=i.methodModel,a.next=8,r.$api[o][u](s);case 8:if(l=a.sent,c=l.code,m=l.data,r.loading[t]=!1,200===c){a.next=14;break}return a.abrupt("return");case 14:r.tableData[t]=m.data,r.total[t]=m.total;case 16:case"end":return a.stop()}},a,r)}))()},handleSelectionChange:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};arguments[1];e=JSON.parse(n()(e)),this.currentRow=e},handleDialogConfirm:function(e){if(null!==this.currentRow&&this.currentRow.id){var t=this.currentRow,r=t.id,a=void 0===r?0:r,n=t.nickName,s=void 0===n?"":n,i=t.phone,o=void 0===i?"":i;"user"===e&&(this.subForm.user_id=a,this.subForm.nickName=s,!this.subForm.phone&&o&&(this.subForm.phone=o)),this.showDialog[e]=!1}else this.$message.error("请选择用户")},submitFormInfo:function(e){var t=this,r=!0;if(this.$refs[e+"Form"].validate(function(e){e||(r=!1)}),r){var a=JSON.parse(n()(this[e+"Form"]));delete a.nickName,delete a.admin_name;var s=a.id?"checkSalesman":"addSalesman";this.$api.salesman[s](a).then(function(e){200===e.code&&(t.$message.success(t.$t(a.id?"tips.successRev":"tips.successSub")),t.$router.back(-1))})}}},filters:{handleTime:function(e,t){return 1===t?_()(1e3*e).format("YYYY-MM-DD"):2===t?_()(1e3*e).format("HH:mm:ss"):_()(1e3*e).format("YYYY-MM-DD HH:mm:ss")}}},g={render:function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"lb-coachbroker-edit"},[r("top-nav",{attrs:{title:e.navTitle,isBack:!0}}),e._v(" "),r("div",{staticClass:"page-main"},[r("el-form",{ref:"subForm",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"120px"},nativeOn:{submit:function(e){e.preventDefault()}}},[r("el-form-item",{attrs:{label:"关联用户",prop:"user_id"}},[r("el-tag",{staticClass:"cursor-pointer",attrs:{type:e.have_user_id?"info":"primary"},on:{click:function(t){return e.toShowDialog("user")}}},[e._v(e._s(e.subForm.user_id?e.subForm.nickName:"选择关联用户"))])],1),e._v(" "),r("el-form-item",{attrs:{label:"业务员姓名",prop:"user_name"}},[r("el-input",{attrs:{maxlength:"20","show-word-limit":"",placeholder:"请输入业务员姓名"},model:{value:e.subForm.user_name,callback:function(t){e.$set(e.subForm,"user_name",t)},expression:"subForm.user_name"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"真实姓名",prop:"true_user_name"}},[r("el-input",{attrs:{maxlength:"10","show-word-limit":"",placeholder:"请输入真实姓名"},model:{value:e.subForm.true_user_name,callback:function(t){e.$set(e.subForm,"true_user_name",t)},expression:"subForm.true_user_name"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"手机号",prop:"phone"}},[r("el-input",{attrs:{maxlength:"11","show-word-limit":"",placeholder:"请输入手机号"},model:{value:e.subForm.phone,callback:function(t){e.$set(e.subForm,"phone",t)},expression:"subForm.phone"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"所属"+e.$t("action.agentName"),prop:"admin_id"}},[e.routesItem.userInfo.is_admin?r("el-select",{attrs:{placeholder:"请选择"+e.$t("action.agentName"),filterable:"",clearable:""},model:{value:e.subForm.admin_id,callback:function(t){e.$set(e.subForm,"admin_id",t)},expression:"subForm.admin_id"}},e._l(e.base_agent,function(e){return r("el-option",{key:e.id,attrs:{label:e.agent_name,value:e.id}})}),1):r("block",[e._v(e._s(e.subForm.admin_name||"-"))])],1),e._v(" "),r("el-form-item",[r("lb-button",{attrs:{type:"primary"},on:{click:function(t){return e.submitFormInfo("sub")}}},[e._v(e._s(e.$t("action.submit")))]),e._v(" "),r("lb-button",{on:{click:function(t){return e.$router.back(-1)}}},[e._v(e._s(e.$t("action.back")))])],1)],1),e._v(" "),r("el-dialog",{attrs:{title:"关联用户",visible:e.showDialog.user,width:"800px",top:"5vh",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"user",t)}}},[e.routesItem.userInfo.is_admin?e._e():r("lb-tips",[e._v("请输入用户昵称/手机号后点击查询数据")]),e._v(" "),r("el-form",{ref:"userForm",attrs:{inline:!0,model:e.searchForm.user,"label-width":"70px"}},[r("el-form-item",{attrs:{label:"输入查询",prop:"nickName"}},[r("el-input",{attrs:{placeholder:"请输入用户昵称/手机号"},model:{value:e.searchForm.user.nickName,callback:function(t){e.$set(e.searchForm.user,"nickName",t)},expression:"searchForm.user.nickName"}})],1),e._v(" "),r("el-form-item",[r("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1,"user")}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),r("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("user")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1),e._v(" "),r("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading.user,expression:"loading.user"}],ref:"singleTable",staticStyle:{width:"100%"},attrs:{data:e.tableData.user,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark","highlight-current-row":""},on:{"current-change":function(t){return e.handleSelectionChange(t,"user")}}},[r("el-table-column",{attrs:{prop:"id",label:"用户ID"}}),e._v(" "),r("el-table-column",{attrs:{prop:"avatarUrl",label:"头像"},scopedSlots:e._u([{key:"default",fn:function(e){return[r("lb-image",{attrs:{src:e.row.avatarUrl}})]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"nickName",label:"昵称"}}),e._v(" "),r("el-table-column",{attrs:{prop:"phone",label:"手机号"}})],1),e._v(" "),r("lb-page",{attrs:{batch:!1,page:e.searchForm.user.page,pageSize:e.searchForm.user.limit,total:e.total.user},on:{handleSizeChange:function(t){return e.handleSizeChange(t,"user")},handleCurrentChange:function(t){return e.handleCurrentChange(t,"user")}}}),e._v(" "),r("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[r("el-button",{on:{click:function(t){e.showDialog.user=!1}}},[e._v("取 消")]),e._v(" "),r("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:function(t){return e.handleDialogConfirm("user")}}},[e._v("确 定")])],1)],1)],1)],1)},staticRenderFns:[]};var F=r("C7Lr")(v,g,!1,function(e){r("jNO2")},"data-v-b857a280",null);t.default=F.exports},jNO2:function(e,t){}});