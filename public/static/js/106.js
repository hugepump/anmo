webpackJsonp([106],{"3Y1J":function(t,e){},OT9E:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var o=i("3cXf"),r=i.n(o),s=i("lC5x"),n=i.n(s),c=i("rVsN"),a=i.n(c),l=i("KH7x"),f=i.n(l),u=i("J0Oq"),_=i.n(u),m=i("4YfN"),d=i.n(m),v=i("bSIt"),g={data:function(){var t=this;return{typeArr:{1:["可服务","#282B34","#EBDDB1"],2:["服务中","#2A2D35","#FFFFFF"],3:["可预约","#FF971E","#FFFFFF"],4:["不可预约","#E82F21","#FFFFFF"]},user_image:"https://lbqny.migugu.com/admin/anmo/mine/bg.png",coach_image:"https://lbqny.migugu.com/admin/anmo/coachport/mine-bg.png",colorList:["#739bc6","#60a06a","#d4b64c","#c09e51","#d5964b","#c26a51","#ffb6b1","#b0b4c7","#616570"],ind:{user_font_color:"",coach_font_color:""},color:{user_font_color:"",coach_font_color:""},subForm:{img_watermark:"",attendant_name:"",user_font_color:"",user_image:[],coach_font_color:"",coach_image:[],service_list:[]},subFormRules:{img_watermark:{required:!0,validator:this.$reg.isNotNull,text:"打上水印后需要显示的文字",reg_type:2,trigger:"blur"},attendant_name:{required:!0,validator:this.$reg.isNotNull,text:"行业服务人员名称",reg_type:2,trigger:"blur"},user_font_color:{required:!0,type:"string",message:"请选择文字颜色",trigger:"blur"},user_image:{required:!0,type:"array",message:"请选择背景图",trigger:"blur"},coach_font_color:{required:!0,type:"string",message:"请选择文字颜色",trigger:"blur"},coach_image:{required:!0,type:"array",message:"请选择背景图",trigger:"blur"},service_list:{required:!0,validator:function(e,i,o){var r=t.typeArr;for(var s in i){var n=i[s],c=n.type,a=n.text,l=n.bcolor,f=n.btn_color_ind,u=n.fcolor,_=n.font_color_ind;if(!(a=a.replace(/(^\s*)|(\s*$)/g,""))||9===f&&!l||9===_&&!u){var m=a?9!==f||l?"请选择文字颜色":"请选择按钮颜色":"请输入状态名称";return void o(new Error("服务状态【"+r[c][0]+"】："+m))}}i.filter(function(t){return t.text&&(9!==t.cind||9===t.cind&&t.bcolor)&&(9!==t.find||9===t.find&&t.fcolor)}).length===i.length&&o()},trigger:"blur"}}}},created:function(){this.getFormInfo()},computed:d()({},Object(v.e)({routesItem:function(t){return t.routes}})),methods:d()({},Object(v.d)(["changeRoutesItem"]),{getFormInfo:function(){var t=this;return _()(n.a.mark(function e(){var i,o,r,s,c,l,u,_,m,d,v;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,a.a.all([t.$api.system.configInfo(),t.$api.system.btnConfigInfo()]);case 2:for(v in i=e.sent,o=f()(i,2),r=o[0],s=o[1],c=r.data,l=c.user_font_color,u=c.coach_font_color,_=c.user_image,m=c.coach_image,c.user_font_color=l||"#ffffff",c.coach_font_color=u||"#ffffff",c.user_image=[{url:_||t.user_image}],c.coach_image=[{url:m||t.coach_image}],s.data.map(function(e){var i=t.colorList.findIndex(function(t){return e.btn_color===t}),o=t.colorList.findIndex(function(t){return e.font_color===t});e.btn_color_ind=-1===i?9:i,e.font_color_ind=-1===o?9:o,e.bcolor=-1===i?e.btn_color:"",e.fcolor=-1===o?e.font_color:""}),c.service_list=s.data,d=function(e){var i=t.colorList.findIndex(function(t){return c[e]===t});t.ind[e]=-1===i?9:i,t.color[e]=-1===i?c[e]:""},t.ind)d(v);for(v in t.subForm)t.subForm[v]=c[v];case 17:case"end":return e.stop()}},e,t)}))()},getCover:function(t,e){this.subForm[e]=t},changeIndex:function(t,e,i,o){var r=this;return _()(n.a.mark(function s(){var c;return n.a.wrap(function(s){for(;;)switch(s.prev=s.next){case 0:if("service_list"!==o){s.next=6;break}return(c=r.subForm[o])[t][i+"_ind"]=e,c[t][i]=r.colorList[e],r.subForm[o]=c,s.abrupt("return");case 6:r.subForm[o]=r.colorList[t],r.ind[o]=t;case 8:case"end":return s.stop()}},s,r)}))()},changeColor:function(t,e,i,o){var r=null===t?"":t;if("bcolor"===i||"fcolor"===i)return this.subForm.service_list[e][i]=r,void(this.subForm.service_list[e][o]=r);this.subForm[i]=r,this.color[i]=r},toReset:function(t,e){if("service_list"===e){var i=this.typeArr,o=this.subForm[e],r=o[t].type;return o[t].btn_color_ind=9,o[t].font_color_ind=9,o[t].text=i[r][0],o[t].btn_color=i[r][1],o[t].font_color=i[r][2],o[t].bcolor=i[r][1],o[t].fcolor=i[r][2],this.subForm[e]=o,void this.submitFormInfo()}this.ind[e+"_font_color"]=9,this.color[e+"_font_color"]="#ffffff",this.subForm[e+"_font_color"]="#ffffff",this.subForm[e+"_image"]=[{url:this[e+"_image"]}],this.submitFormInfo()},submitFormInfo:function(){var t=this;return _()(n.a.mark(function e(){var i,o,s,c,l;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:if(i=!0,t.$refs.subForm.validate(function(t){t||(i=!1)}),i){e.next=4;break}return e.abrupt("return");case 4:return(o=JSON.parse(r()(t.subForm))).user_image=o.user_image[0].url,o.coach_image=o.coach_image[0].url,s=o.service_list.map(function(e){return t.$util.pick(e,["type","text","btn_color","font_color"])}),delete o.service_list,e.next=11,a.a.all([t.$api.system.configUpdate(o),t.$api.system.btnConfigUpdate({data:s})]);case 11:c=e.sent,l=f()(c,1),200===l[0].code&&(t.changeRoutesItem({key:"attendant_name",val:o.attendant_name}),t.$message.success(t.$t("tips.successSub")),t.getFormInfo());case 15:case"end":return e.stop()}},e,t)}))()}})},p={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"lb-diy-set"},[i("top-nav"),t._v(" "),i("div",{staticClass:"page-main"},[i("el-form",{ref:"subForm",staticClass:"config-form",attrs:{model:t.subForm,rules:t.subFormRules,"label-width":"140px"},nativeOn:{submit:function(t){t.preventDefault()}}},[i("el-form-item",{attrs:{label:"水印文案",prop:"img_watermark"}},[i("el-input",{attrs:{maxlength:"10","show-word-limit":"",placeholder:"请输入打上水印后需要显示的文字"},model:{value:t.subForm.img_watermark,callback:function(e){t.$set(t.subForm,"img_watermark",e)},expression:"subForm.img_watermark"}}),t._v(" "),i("lb-tool-tips",[t._v("该字段用于后台编辑图片时添加水印")])],1),t._v(" "),i("el-form-item",{attrs:{label:"行业服务人员名称",prop:"attendant_name"}},[i("el-input",{attrs:{maxlength:"5","show-word-limit":"",placeholder:"请输入行业服务人员名称"},model:{value:t.subForm.attendant_name,callback:function(e){t.$set(t.subForm,"attendant_name",e)},expression:"subForm.attendant_name"}}),t._v(" "),i("lb-tool-tips",[t._v("该字段适用于修改不同行业服务人员的专职称呼，例如美甲师、维修人员、瑜伽老师、营养师等\n          "),i("div",{staticClass:"mt-sm"},[t._v("\n            修改之后，后台和手机端所有关于服务人员的称呼将和输入的字段一致\n          ")])])],1),t._v(" "),i("lb-classify-title",{attrs:{title:"个人中心 - 背景图及文字颜色配置"}}),t._v(" "),i("div",{staticClass:"c-title text-bold"},[t._v("用户端")]),t._v(" "),i("el-form-item",{staticClass:"margin",attrs:{label:"背景图",prop:"user_image"}},[i("div",{staticClass:"flex-y-center"},[i("div",[i("lb-cover",{attrs:{fileList:t.subForm.user_image},on:{selectedFiles:function(e){return t.getCover(e,"user_image")}}}),t._v(" "),i("lb-tool-tips",[t._v("图片建议尺寸：750 * 368")])],1),t._v(" "),i("div",{staticClass:"ml-lg",staticStyle:{height:"115px"}},[i("lb-button",{attrs:{type:"danger",plain:"",size:"small"},on:{click:function(e){return t.toReset(0,"user")}}},[t._v(t._s(t.$t("action.defaultSet")))])],1)])]),t._v(" "),i("el-form-item",{attrs:{label:"文字颜色",prop:"user_font_color"}},[i("div",{staticClass:"flex-warp mb-sm"},[i("div",{staticClass:"flex-warp mt-sm"},t._l(t.colorList,function(e,o){return i("div",{key:o},[i("div",{staticClass:"color-item",class:[{active:o===t.ind.user_font_color}],on:{click:function(e){return t.changeIndex(o,0,0,"user_font_color")}}},[i("div",{staticClass:"flex-center"},[i("div",{staticClass:"primaryColor flex-center"},[i("div",{staticClass:"color-bg",style:{background:e}})])]),t._v(" "),o===t.ind.user_font_color?i("i",{staticClass:"iconfont icon-xuanze-fill flex-center"}):t._e()])])}),0),t._v(" "),i("div",{staticClass:"color-item mt-sm",class:[{active:9===t.ind.user_font_color}],staticStyle:{width:"auto",padding:"0 2px"},on:{click:function(e){return t.changeIndex(9,0,0,"user_font_color")}}},[i("div",{staticClass:"flex-center",staticStyle:{"margin-top":"4px"}},[i("el-color-picker",{staticStyle:{"margin-right":"4px"},attrs:{size:"mini"},on:{change:function(e){return t.changeColor(e,0,"user_font_color")}},model:{value:t.color.user_font_color,callback:function(e){t.$set(t.color,"user_font_color",e)},expression:"color.user_font_color"}})],1),t._v(" "),i("div",{staticClass:"flex-y-center",staticStyle:{height:"18px","margin-top":"4px"}},[i("div",{staticStyle:{"line-height":"18px","font-size":"10px"}},[t._v("自定义配色")]),t._v(" "),i("i",{staticClass:"iconfont icon-xuanze flex-center",class:[{"icon-xuanze-fill":9===t.ind.user_font_color}],staticStyle:{margin:"0"}})])])])]),t._v(" "),i("div",{staticClass:"c-title text-bold"},[t._v(t._s(t.$t("action.attendantName"))+"端")]),t._v(" "),i("el-form-item",{staticClass:"margin",attrs:{label:"背景图",prop:"coach_image"}},[i("div",{staticClass:"flex-y-center"},[i("div",[i("lb-cover",{attrs:{fileList:t.subForm.coach_image},on:{selectedFiles:function(e){return t.getCover(e,"coach_image")}}}),t._v(" "),i("lb-tool-tips",[t._v("图片建议尺寸：750 * 500")])],1),t._v(" "),i("div",{staticClass:"ml-lg",staticStyle:{height:"115px"}},[i("lb-button",{attrs:{type:"danger",plain:"",size:"small"},on:{click:function(e){return t.toReset(0,"coach")}}},[t._v(t._s(t.$t("action.defaultSet")))])],1)])]),t._v(" "),i("el-form-item",{attrs:{label:"文字颜色",prop:"coach_font_color"}},[i("div",{staticClass:"flex-warp mb-sm"},[i("div",{staticClass:"flex-warp mt-sm"},t._l(t.colorList,function(e,o){return i("div",{key:o},[i("div",{staticClass:"color-item",class:[{active:o===t.ind.coach_font_color}],on:{click:function(e){return t.changeIndex(o,0,0,"coach_font_color")}}},[i("div",{staticClass:"flex-center"},[i("div",{staticClass:"primaryColor flex-center"},[i("div",{staticClass:"color-bg",style:{background:e}})])]),t._v(" "),o===t.ind.coach_font_color?i("i",{staticClass:"iconfont icon-xuanze-fill flex-center"}):t._e()])])}),0),t._v(" "),i("div",{staticClass:"color-item mt-sm",class:[{active:9===t.ind.coach_font_color}],staticStyle:{width:"auto",padding:"0 2px"},on:{click:function(e){return t.changeIndex(9,0,0,"coach_font_color")}}},[i("div",{staticClass:"flex-center",staticStyle:{"margin-top":"4px"}},[i("el-color-picker",{staticStyle:{"margin-right":"4px"},attrs:{size:"mini"},on:{change:function(e){return t.changeColor(e,0,"coach_font_color")}},model:{value:t.color.coach_font_color,callback:function(e){t.$set(t.color,"coach_font_color",e)},expression:"color.coach_font_color"}})],1),t._v(" "),i("div",{staticClass:"flex-y-center",staticStyle:{height:"18px","margin-top":"4px"}},[i("div",{staticStyle:{"line-height":"18px","font-size":"10px"}},[t._v("自定义配色")]),t._v(" "),i("i",{staticClass:"iconfont icon-xuanze flex-center",class:[{"icon-xuanze-fill":9===t.ind.coach_font_color}],staticStyle:{margin:"0"}})])])])]),t._v(" "),i("lb-classify-title",{attrs:{title:t.$t("action.attendantName")+"页面 - 服务状态颜色及文案配置"}}),t._v(" "),i("el-form-item",{staticClass:"service_list rel",attrs:{prop:"service_list"}},t._l(t.subForm.service_list,function(e,o){return i("div",{key:o,staticClass:"service-item rel"},[i("div",{staticClass:"c-title text-bold"},[t._v("\n            "+t._s(["","可服务","服务中","可预约","不可预约"][e.type])+"\n          ")]),t._v(" "),i("div",{staticClass:"flex-y-center"},[i("div",{staticClass:"flex-warp title"},[i("div",{staticClass:"flex-1"}),t._v(" "),i("div",[i("i",{staticClass:"iconfont icon-required c-warning"}),t._v("状态名称\n              ")])]),t._v(" "),i("div",{staticClass:"flex-y-center"},[i("el-input",{attrs:{maxlength:"4","show-word-limit":"",placeholder:"请输入状态名称"},model:{value:e.text,callback:function(i){t.$set(e,"text",i)},expression:"item.text"}}),t._v(" "),i("div",{staticClass:"ml-lg"},[i("lb-button",{attrs:{type:"danger",plain:"",size:"small"},on:{click:function(e){return t.toReset(o,"service_list")}}},[t._v(t._s(t.$t("action.defaultSet")))])],1)],1)]),t._v(" "),i("div",{staticClass:"flex-warp mt-sm mb-sm"},[i("div",{staticClass:"flex-warp title"},[i("div",{staticClass:"flex-1"}),t._v(" "),i("div",[i("i",{staticClass:"iconfont icon-required c-warning"}),t._v("按钮颜色\n              ")])]),t._v(" "),i("div",{staticClass:"flex-warp"},[i("div",{staticClass:"flex-warp mt-sm"},t._l(t.colorList,function(r,s){return i("div",{key:s},[i("div",{staticClass:"color-item",class:[{active:e.btn_color_ind===s}],on:{click:function(e){return t.changeIndex(o,s,"btn_color","service_list")}}},[i("div",{staticClass:"flex-center"},[i("div",{staticClass:"primaryColor flex-center"},[i("div",{staticClass:"color-bg",style:{background:r}})])]),t._v(" "),e.btn_color_ind===s?i("i",{staticClass:"iconfont icon-xuanze-fill flex-center"}):t._e()])])}),0),t._v(" "),i("div",{staticClass:"color-item mt-sm",class:[{active:9===e.btn_color_ind}],staticStyle:{width:"auto",padding:"0 2px"},on:{click:function(e){return t.changeIndex(o,9,"btn_color","service_list")}}},[i("div",{staticClass:"flex-center",staticStyle:{"margin-top":"4px"}},[i("el-color-picker",{staticStyle:{"margin-right":"4px"},attrs:{size:"mini"},on:{change:function(e){return t.changeColor(e,o,"bcolor","btn_color")}},model:{value:e.bcolor,callback:function(i){t.$set(e,"bcolor",i)},expression:"item.bcolor"}})],1),t._v(" "),i("div",{staticClass:"flex-y-center",staticStyle:{height:"18px","margin-top":"4px"}},[i("div",{staticStyle:{"line-height":"18px","font-size":"10px"}},[t._v("\n                    自定义配色\n                  ")]),t._v(" "),i("i",{staticClass:"iconfont icon-xuanze flex-center",class:[{"icon-xuanze-fill":9===e.btn_color_ind}],staticStyle:{margin:"0"}})])])])]),t._v(" "),i("div",{staticClass:"flex-warp"},[i("div",{staticClass:"flex-warp title"},[i("div",{staticClass:"flex-1"}),t._v(" "),i("div",[i("i",{staticClass:"iconfont icon-required c-warning"}),t._v("文字颜色\n              ")])]),t._v(" "),i("div",[i("div",{staticClass:"flex-warp mb-sm"},[i("div",{staticClass:"flex-warp mt-sm"},t._l(t.colorList,function(r,s){return i("div",{key:s},[i("div",{staticClass:"color-item",class:[{active:e.font_color_ind===s}],on:{click:function(e){return t.changeIndex(o,s,"font_color","service_list")}}},[i("div",{staticClass:"flex-center"},[i("div",{staticClass:"primaryColor flex-center"},[i("div",{staticClass:"color-bg",style:{background:r}})])]),t._v(" "),e.font_color_ind===s?i("i",{staticClass:"iconfont icon-xuanze-fill flex-center"}):t._e()])])}),0),t._v(" "),i("div",{staticClass:"color-item mt-sm",class:[{active:9===e.font_color_ind}],staticStyle:{width:"auto",padding:"0 2px"},on:{click:function(e){return t.changeIndex(o,9,"font_color","service_list")}}},[i("div",{staticClass:"flex-center",staticStyle:{"margin-top":"4px"}},[i("el-color-picker",{staticStyle:{"margin-right":"4px"},attrs:{size:"mini"},on:{change:function(e){return t.changeColor(e,o,"fcolor","font_color")}},model:{value:e.fcolor,callback:function(i){t.$set(e,"fcolor",i)},expression:"item.fcolor"}})],1),t._v(" "),i("div",{staticClass:"flex-y-center",staticStyle:{height:"18px","margin-top":"4px"}},[i("div",{staticStyle:{"line-height":"18px","font-size":"10px"}},[t._v("\n                      自定义配色\n                    ")]),t._v(" "),i("i",{staticClass:"iconfont icon-xuanze flex-center",class:[{"icon-xuanze-fill":9===e.font_color_ind}],staticStyle:{margin:"0"}})])])])])])])}),0),t._v(" "),i("el-form-item",[i("lb-button",{directives:[{name:"preventReClick",rawName:"v-preventReClick"}],attrs:{type:"primary"},on:{click:t.submitFormInfo}},[t._v(t._s(t.$t("action.submit")))])],1)],1)],1)],1)},staticRenderFns:[]};var b=i("C7Lr")(g,p,!1,function(t){i("3Y1J")},"data-v-5942dc26",null);e.default=b.exports}});