webpackJsonp([56],{W4Kb:function(e,t){},trgX:function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r("3cXf"),s=r.n(a),o=r("4YfN"),i=r.n(o),n=r("lC5x"),l=r.n(n),u=r("J0Oq"),c=r.n(u),m=r("PxTW"),d=r.n(m),h=r("bSIt"),p={data:function(){return{base_store:[],checkList:[],authList:[{title:"订单管理",key:"shopOrder"},{title:"加钟管理",key:"shopBellOrder"},{title:"拒单管理",key:"shopRefuseOrder"},{title:"服务退款",key:"shopRefund"},{title:"加钟退款",key:"shopBellRefund"},{title:"卡券核销",key:"MarketCoupHxrecord"}],loading:{list:!1,user:!1},searchForm:{list:{page:1,limit:10},user:{page:1,limit:10,phone:""}},tableData:{list:[],user:[]},total:{list:0,user:0},showDialog:{sub:!1,user:!1},currentRow:{},subForm:{id:0,user_id:"",nickName:"",mobile:"",node:[],store:[]},subFormRules:{user_id:{required:!0,type:"number",message:"请选择关联用户",trigger:["blur","change"]},mobile:{required:!0,validator:this.$reg.isTel,text:"手机号",reg_type:2,trigger:"blur"},store:{required:!0,type:"array",message:"请选择管理门店",trigger:"change"},node:{required:!0,type:"array",message:"请选择权限内容",trigger:"change"}}}},created:function(){var e=this;return c()(l.a.mark(function t(){return l.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.getBaseInfo();case 2:e.getTableDataList(1,"list");case 3:case"end":return t.stop()}},t,e)}))()},computed:i()({},Object(h.e)({routesItem:function(e){return e.routes}})),methods:i()({},Object(h.d)(["changeRoutesItem"]),{getBaseInfo:function(){var e=this;return c()(l.a.mark(function t(){var r,a,s,o,i;return l.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(r=e.routesItem,a=r.userInfo,s=r.auth,a.is_admin||!s.store||!s.store_auth){t.next=7;break}return t.next=4,e.$api.store.storeSelect();case 4:o=t.sent,i=o.data,e.base_store=i;case 7:case"end":return t.stop()}},t,e)}))()},resetForm:function(e){var t=e+"Form";this.$refs[t].resetFields(),this.getTableDataList(1,e)},handleSizeChange:function(e,t){this.searchForm[t].limit=e,this.handleCurrentChange(1,t)},handleCurrentChange:function(e,t){this.searchForm[t].page=e,this.getTableDataList("",t)},getTableDataList:function(e,t){var r=this;return c()(l.a.mark(function a(){var o,i,n,u,c,m,d,h,p,f,b,v,g,_,k,w;return l.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return e&&(r.searchForm[t].page=e),r.loading[t]=!0,o=JSON.parse(s()(r.searchForm[t])),i={list:{methodKey:"account",methodModel:"mobileList"},user:{methodKey:"custom",methodModel:"userSelectByPhone"}}[t],n=i.methodKey,u=i.methodModel,a.next=7,r.$api[n][u](o);case 7:if(c=a.sent,m=c.code,d=c.data,r.loading[t]=!1,200===m){a.next=13;break}return a.abrupt("return");case 13:"list"===t&&(h=r.routesItem.userInfo.is_admin,p=r.routesItem.auth,f=p.agent_coach_auth,b=void 0===f?0:f,v=p.store,g=p.store_auth,_=void 0===g?0:g,k=p.group_write_off_auth,w=void 0===k?0:k,d.data.map(function(e){e.node=e.node.filter(function(e){return"MarketCoupHxrecord"!==e&&(h||!h&&b)||"MarketCoupHxrecord"===e&&!h&&v&&_&&w})})),r.tableData[t]=d.data,r.total[t]=d.total;case 16:case"end":return a.stop()}},a,r)}))()},confirmDel:function(e){var t=this;this.$confirm(this.$t("tips.confirmDelete"),this.$t("tips.reminder"),{confirmButtonText:this.$t("action.comfirm"),cancelButtonText:this.$t("action.cancel"),type:"warning"}).then(function(){t.updateItem(e,-1,"list")}).catch(function(){})},updateItem:function(e,t,r){var a=this;return c()(l.a.mark(function s(){return l.a.wrap(function(s){for(;;)switch(s.prev=s.next){case 0:a.$api.account.mobileStatusUpdate({id:e,status:t}).then(function(e){if(200===e.code)a.$message.success(a.$t(-1===t?"tips.successDel":"tips.successOper")),-1===t&&(a.searchForm[r].page=a.searchForm[r].page<Math.ceil((a.total[r]-1)/a.searchForm[r].limit)?a.searchForm[r].page:Math.ceil((a.total[r]-1)/a.searchForm[r].limit),a.getTableDataList("",r));else{if(-1===t)return;a.getTableDataList("",r)}});case 1:case"end":return s.stop()}},s,a)}))()},toShowDialog:function(e){var t=this,r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{id:0,user_id:"",nickName:"",node:[],store:[]};return c()(l.a.mark(function a(){var o,i;return l.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:if("sub"!==e){a.next=7;break}for(i in(r=JSON.parse(s()(r))).store=r.store&&r.store.length>0?r.store.map(function(e){return e.store_id}):[],r.id?(o=[],t.authList.map(function(e){r.node.includes(e.key)&&o.push(e.title)}),t.checkList=o):t.checkList=[],t.subForm)t.subForm[i]=r[i];a.next=10;break;case 7:return t.searchForm.user.phone="",a.next=10,t.getTableDataList(1,e);case 10:t.showDialog[e]=!t.showDialog[e],["sub"].includes(e)&&t.$refs[e+"Form"].validate&&t.$refs[e+"Form"].clearValidate();case 12:case"end":return a.stop()}},a,t)}))()},changeCheckBox:function(e){var t=[];this.authList.map(function(r){e.includes(r.title)&&t.push(r.key)}),this.subForm.node=t},handleSelectionChange:function(e){var t=e=JSON.parse(s()(e)),r=t.id,a=t.nickName;e.nickName=a||"用户ID "+r,this.currentRow=e},handleDialogConfirm:function(){if(null!==this.currentRow&&this.currentRow.id){var e=this.currentRow,t=e.id,r=void 0===t?0:t,a=e.nickName,s=void 0===a?"":a,o=e.phone,i=void 0===o?"":o;this.subForm.user_id=r,this.subForm.nickName=s,!this.subForm.mobile&&i&&(this.subForm.mobile=i),this.showDialog.user=!1}else this.$message.error("请选择用户")},submitFormInfo:function(){var e=this;return c()(l.a.mark(function t(){var r,a,o,i;return l.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(r=!0,e.$refs.subForm.validate(function(e){e||(r=!1)}),r){t.next=4;break}return t.abrupt("return");case 4:return a=e.subForm.id?"mobileUpdate":"mobileAdd",delete(o=JSON.parse(s()(e.subForm))).nickName,t.next=9,e.$api.account[a](o);case 9:if(i=t.sent,200===i.code){t.next=13;break}return t.abrupt("return");case 13:e.$message.success(e.$t(o.id?"tips.successRev":"tips.successSub")),e.showDialog.sub=!1,e.getTableDataList("","list");case 16:case"end":return t.stop()}},t,e)}))()}}),filters:{handleTime:function(e,t){return 1===t?d()(1e3*e).format("YYYY-MM-DD"):2===t?d()(1e3*e).format("HH:mm:ss"):d()(1e3*e).format("YYYY-MM-DD HH:mm:ss")},handleTitle:function(e,t){var r=t.filter(function(t){return t.key===e});return r&&r.length>0?r[0].title:""}}},f={render:function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"lb-account-phone"},[r("top-nav"),e._v(" "),r("div",{staticClass:"page-main"},[r("el-row",{staticClass:"page-top-operate"},[r("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-add",expression:"`${$route.name}-add`"}],attrs:{type:"primary",icon:"el-icon-plus"},on:{click:function(t){return e.toShowDialog("sub")}}},[e._v(e._s(e.$t("menu.AccountPhoneAdd")))])],1),e._v(" "),r("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading.list,expression:"loading.list"}],staticStyle:{width:"100%"},attrs:{data:e.tableData.list,"header-cell-style":{background:"#f5f7fa",color:"#606266"}}},[r("el-table-column",{attrs:{prop:"nickName",label:"关联人员"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.nickName||"-")+"\n        ")]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"mobile",label:"手机号"}}),e._v(" "),!e.routesItem.userInfo.is_admin&&e.routesItem.auth.store&&e.routesItem.auth.store_auth?r("el-table-column",{attrs:{prop:"store_name",label:"管理门店"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("el-popover",{attrs:{placement:"top-start",width:"350",trigger:"hover"}},[r("div",{staticClass:"f-caption c-title",attrs:{slot:""},slot:"default"},[r("div",{staticClass:"c-caption pb-sm"},[e._v("管理门店：")]),e._v(" "),r("div",{staticStyle:{"max-height":"80vh",overflow:"auto"},domProps:{innerHTML:e._s(t.row.store_name)}})]),e._v(" "),r("div",{staticClass:"ellipsis-3",attrs:{slot:"reference"},domProps:{innerHTML:e._s(t.row.store_name)},slot:"reference"})])]}}],null,!1,4003430519)}):e._e(),e._v(" "),r("el-table-column",{attrs:{prop:"role",label:"权限内容","min-width":"250"},scopedSlots:e._u([{key:"default",fn:function(t){return e._l(t.row.node,function(t,a){return r("el-tag",{key:a,staticClass:"mt-sm mb-sm mr-md",attrs:{size:"small",type:"primary"}},[e._v(e._s(e._f("handleTitle")(t,e.authList)))])})}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"create_time",label:"创建时间"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,1)))]),e._v(" "),r("p",[e._v(e._s(e._f("handleTime")(t.row.create_time,2)))])]}}])}),e._v(" "),r("el-table-column",{attrs:{label:"操作",width:"160",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("div",{staticClass:"table-operate"},[r("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-edit",expression:"`${$route.name}-edit`"}],attrs:{size:"mini",plain:"",type:"primary"},on:{click:function(r){return e.toShowDialog("sub",t.row)}}},[e._v(e._s(e.$t("action.edit")))]),e._v(" "),r("lb-button",{directives:[{name:"hasPermi",rawName:"v-hasPermi",value:e.$route.name+"-delete",expression:"`${$route.name}-delete`"}],attrs:{size:"mini",plain:"",type:"danger"},on:{click:function(r){return e.confirmDel(t.row.id)}}},[e._v(e._s(e.$t("action.delete")))])],1)]}}])})],1),e._v(" "),r("lb-page",{attrs:{batch:!1,page:e.searchForm.list.page,pageSize:e.searchForm.list.limit,total:e.total.list},on:{handleSizeChange:function(t){return e.handleSizeChange(t,"list")},handleCurrentChange:function(t){return e.handleCurrentChange(t,"list")}}}),e._v(" "),r("el-dialog",{staticClass:"dialog-form",attrs:{title:e.$t(e.subForm.id?"menu.AccountPhoneEdit":"menu.AccountPhoneAdd"),visible:e.showDialog.sub,width:"715px",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"sub",t)}}},[r("el-form",{ref:"subForm",attrs:{model:e.subForm,rules:e.subFormRules,"label-width":"100px"}},[r("el-form-item",{attrs:{label:"关联用户",prop:"user_id"}},[r("el-tag",{staticClass:"cursor-pointer",attrs:{type:e.subForm.user_id?"primary":"danger"},on:{click:function(t){return e.toShowDialog("user")}}},[e._v(e._s(e.subForm.user_id?e.subForm.nickName:"选择关联用户"))])],1),e._v(" "),r("el-form-item",{attrs:{label:"手机号",prop:"mobile"}},[r("el-input",{attrs:{maxlength:"11","show-word-limit":"",placeholder:"请输入手机号"},model:{value:e.subForm.mobile,callback:function(t){e.$set(e.subForm,"mobile",t)},expression:"subForm.mobile"}})],1),e._v(" "),!e.routesItem.userInfo.is_admin&&e.routesItem.auth.store&&e.routesItem.auth.store_auth?r("el-form-item",{attrs:{label:"管理门店",prop:"store"}},[r("el-select",{attrs:{multiple:"","collapse-tags":"",filterable:"",clearable:"",placeholder:"请选择"},model:{value:e.subForm.store,callback:function(t){e.$set(e.subForm,"store",t)},expression:"subForm.store"}},e._l(e.base_store,function(e){return r("el-option",{key:e.id,attrs:{label:e.title,value:e.id}})}),1)],1):e._e(),e._v(" "),r("el-form-item",{attrs:{label:"权限内容",prop:"node"}},[r("el-checkbox-group",{on:{change:e.changeCheckBox},model:{value:e.checkList,callback:function(t){e.checkList=t},expression:"checkList"}},e._l(e.authList,function(t,a){return r("div",{key:a,style:{display:"inline-block",marginLeft:0===a?0:"15px"}},["MarketCoupHxrecord"!==t.key&&(e.routesItem.userInfo.is_admin||!e.routesItem.userInfo.is_admin&&e.routesItem.auth.agent_coach_auth)||"MarketCoupHxrecord"===t.key&&!e.routesItem.userInfo.is_admin&&e.routesItem.auth.store&&e.routesItem.auth.store_auth&&e.routesItem.auth.group_write_off_auth?r("block",[r("el-checkbox",{attrs:{label:t.title}}),e._v(" "),t.tips?r("lb-tool-tips",[e._v(e._s(t.tips))]):e._e()],1):e._e()],1)}),0)],1)],1),e._v(" "),r("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[r("el-button",{on:{click:function(t){e.showDialog.sub=!1}}},[e._v("取 消")]),e._v(" "),r("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:e.submitFormInfo}},[e._v("确 定")])],1)],1),e._v(" "),r("el-dialog",{attrs:{title:"关联用户",visible:e.showDialog.user,width:"800px",top:"5vh",center:""},on:{"update:visible":function(t){return e.$set(e.showDialog,"user",t)}}},[e.routesItem.userInfo.is_admin?e._e():r("lb-tips",[e._v("请输入用户昵称/手机号后点击查询数据")]),e._v(" "),r("el-form",{ref:"userForm",staticClass:"dialog-form",attrs:{inline:!0,model:e.searchForm.user,"label-width":"70px"}},[r("el-form-item",{attrs:{label:"输入查询",prop:"phone"}},[r("el-input",{attrs:{placeholder:"请输入用户昵称/手机号"},model:{value:e.searchForm.user.phone,callback:function(t){e.$set(e.searchForm.user,"phone",t)},expression:"searchForm.user.phone"}})],1),e._v(" "),r("el-form-item",[r("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",type:"primary",icon:"el-icon-search"},on:{click:function(t){return e.getTableDataList(1,"user")}}},[e._v(e._s(e.$t("action.search")))]),e._v(" "),r("lb-button",{staticStyle:{"margin-right":"5px"},attrs:{size:"medium",icon:"el-icon-refresh-left"},on:{click:function(t){return e.resetForm("user")}}},[e._v(e._s(e.$t("action.reset")))])],1)],1),e._v(" "),r("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading.user,expression:"loading.user"}],ref:"singleTable",staticStyle:{width:"100%"},attrs:{data:e.tableData.user,"header-cell-style":{background:"#f5f7fa",color:"#606266"},"tooltip-effect":"dark","highlight-current-row":""},on:{"current-change":e.handleSelectionChange}},[r("el-table-column",{attrs:{prop:"id",label:"用户ID"}}),e._v(" "),r("el-table-column",{attrs:{prop:"avatarUrl",label:"头像"},scopedSlots:e._u([{key:"default",fn:function(e){return[r("lb-image",{attrs:{src:e.row.avatarUrl}})]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"nickName",label:"昵称"}}),e._v(" "),r("el-table-column",{attrs:{prop:"phone",label:"手机号"}})],1),e._v(" "),r("lb-page",{attrs:{batch:!1,page:e.searchForm.user.page,pageSize:e.searchForm.user.limit,total:e.total.user},on:{handleSizeChange:function(t){return e.handleSizeChange(t,"user")},handleCurrentChange:function(t){return e.handleCurrentChange(t,"user")}}}),e._v(" "),r("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[r("el-button",{on:{click:function(t){e.showDialog.user=!1}}},[e._v("取 消")]),e._v(" "),r("el-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:e.handleDialogConfirm}},[e._v("确 定")])],1)],1)],1)],1)},staticRenderFns:[]};var b=r("C7Lr")(p,f,!1,function(e){r("W4Kb")},"data-v-af53a2c6",null);t.default=b.exports}});