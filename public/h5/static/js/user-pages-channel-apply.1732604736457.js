(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["user-pages-channel-apply"],{"3c08":function(t,e,n){"use strict";n.d(e,"b",(function(){return a})),n.d(e,"c",(function(){return i})),n.d(e,"a",(function(){}));var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return t.isLoad?n("v-uni-view",{staticClass:"apply-pages",style:{background:(t.options.channel_qr_id||t.options.admin_id||t.options.salesman_id)&&-1!=t.channel_status||!t.options.channel_qr_id&&!t.options.admin_id&&!t.options.salesman_id&&[1,2,3].includes(t.channel_status)?"":t.pageColor}},[(t.options.channel_qr_id||t.options.admin_id||t.options.salesman_id)&&-1!=t.channel_status||!t.options.channel_qr_id&&!t.options.admin_id&&!t.options.salesman_id&&[1,2,3].includes(t.channel_status)?n("v-uni-view",{staticClass:"page-height"},[n("abnor",{attrs:{percent:"150%",title:t.title[t.channel_status],tip:t.tipArr[t.channel_status],button:t.buttonArr[t.channel_status],image:t.image[t.channel_status],tipMax:4==t.channel_status?"690rpx":""},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.confirm.apply(void 0,arguments)},cancel:function(e){arguments[0]=e=t.$handleEvent(e),t.$util.goUrl({url:"/pages/mine",openType:"reLaunch"})}}})],1):[n("v-uni-view",{staticClass:"apply-form"},[n("v-uni-view",{staticClass:"space-md"}),n("v-uni-view",{staticClass:"fill-base radius-16"},[n("v-uni-view",{staticClass:"flex-between pl-lg pr-lg b-1px-b"},[n("v-uni-view",{staticClass:"item-text"},[t._v("您的姓名")]),n("v-uni-input",{staticClass:"item-input flex-1",attrs:{type:"text",maxlength:"20",placeholder:t.rule[0].errorMsg},model:{value:t.form.user_name,callback:function(e){t.$set(t.form,"user_name",e)},expression:"form.user_name"}})],1),n("v-uni-view",{staticClass:"flex-between pl-lg pr-lg b-1px-b"},[n("v-uni-view",{staticClass:"item-text"},[t._v("真实姓名")]),n("v-uni-input",{staticClass:"item-input flex-1",attrs:{type:"text",maxlength:"10",placeholder:t.rule[1].errorMsg},model:{value:t.form.true_user_name,callback:function(e){t.$set(t.form,"true_user_name",e)},expression:"form.true_user_name"}})],1),n("v-uni-view",{staticClass:"flex-between pl-lg pr-lg b-1px-b"},[n("v-uni-view",{staticClass:"item-text"},[t._v("手机号码")]),n("v-uni-input",{staticClass:"item-input flex-1",attrs:{type:"text",maxlength:"11",placeholder:t.rule[2].errorMsg},model:{value:t.form.mobile,callback:function(e){t.$set(t.form,"mobile",e)},expression:"form.mobile"}})],1),t.configInfo.plugAuth.channelcate?n("v-uni-view",{staticClass:"flex-between pl-lg pr-lg b-1px-b"},[n("v-uni-view",{staticClass:"item-text"},[t._v("申请渠道")]),n("v-uni-view",{staticClass:"item-input flex-1 text"},[n("v-uni-picker",{attrs:{value:t.channelInd,range:t.channelList,"range-key":"title"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.pickerChange(e)}}},[n("v-uni-view",{staticClass:"flex-between",staticStyle:{color:"#A9A9A9"}},[t._v(t._s(-1!=t.channelInd&&t.channelList.length>0?t.channelList[t.channelInd].title:"请选择您能接入的渠道")),n("i",{staticClass:"iconfont icon-right ml-sm",staticStyle:{"font-size":"28rpx"}})])],1)],1)],1):t._e()],1),n("v-uni-view",{staticClass:"fill-base mt-md radius-16"},[n("v-uni-view",{staticClass:"flex-between pl-lg pr-lg"},[n("v-uni-view",{staticClass:"item-text"},[t._v("备注信息")]),n("v-uni-input",{staticClass:"item-input flex-1",attrs:{disabled:!0,type:"text"}})],1),n("v-uni-textarea",{staticClass:"item-textarea pd-lg",attrs:{maxlength:"300",placeholder:"请输入备注信息"},model:{value:t.form.text,callback:function(e){t.$set(t.form,"text",e)},expression:"form.text"}}),n("v-uni-view",{staticClass:"text-right pb-lg pr-lg"},[t._v(t._s(t.form.text.length>300?300:t.form.text.length)+"/300")])],1)],1),n("v-uni-view",{staticClass:"space-max-footer"}),n("fix-bottom-button",{attrs:{text:[{text:"确定申请",type:"confirm",isAuth:!0}],bgColor:"#fff"},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.submit.apply(void 0,arguments)}}})]],2):t._e()},i=[]},"5bc7":function(t,e,n){"use strict";n.r(e);var a=n("3c08"),i=n("7b93");for(var l in i)["default"].indexOf(l)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(l);var s=n("828b"),r=Object(s["a"])(i["default"],a["b"],a["c"],!1,null,"28b027c6",null,!1,a["a"],void 0);e["default"]=r.exports},"77da":function(t,e,n){"use strict";n("6a54");var a=n("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,n("bf0f"),n("18f7"),n("de6c"),n("3efd"),n("bd06"),n("fd3c");var i=a(n("5de6")),l=a(n("9b1b")),s=a(n("2634")),r=a(n("2fdc")),c=n("8f59"),o={components:{},data:function(){return{tipArr:{1:[{text:"您已经成功提交申请",color:0},{text:"审核将在3个工作日内出结果，请耐心等待",color:0}],2:[{text:"恭喜您，审核通过！",color:0},{text:"您已具备".concat(this.$t("action.channelName"),"资格，快去分享体验吧~"),color:0}],3:[{text:"请联系平台管理人员询问原因",color:0}],4:[{text:"请联系平台管理人员询问失败原因",color:0}]},buttonArr:{1:[{text:"个人中心",type:"cancel"}],2:[{text:"去分享",type:"confirm"}],3:[{text:"个人中心",type:"cancel"}],4:[{text:"再次申请",type:"confirm"},{text:"个人中心",type:"cancel"}]},title:{"-1":"申请".concat(this.$t("action.channelName")),1:"等待审核",2:"",3:"取消授权",4:"申请失败"},image:{1:"https://lbqny.migugu.com/admin/public/apply_wait.png",2:"https://lbqny.migugu.com/admin/public/apply_suc.png",3:"https://lbqny.migugu.com/admin/public/apply_fail.png",4:"https://lbqny.migugu.com/admin/public/apply_fail.png"},channel_status:-1,channelInd:-1,channelList:[],isLoad:!1,options:{},form:{id:0,user_name:"",true_user_name:"",mobile:"",cate_id:"",text:"",channel_qr_id:0,admin_id:0,salesman_id:0},rule:[{name:"user_name",checkType:"isNotNull",errorMsg:"请输入您的姓名",regType:2},{name:"true_user_name",checkType:"isNotNull",errorMsg:"请输入您的真实姓名",regType:2},{name:"mobile",checkType:"isMobile",errorMsg:"请输入常用手机号码"}],lockTap:!1}},computed:(0,c.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(t){var e=this;return(0,r.default)((0,s.default)().mark((function n(){var a,i,l,r,c,o,u,d;return(0,s.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:return a=t.channel_qr_id,i=void 0===a?0:a,l=t.admin_id,r=void 0===l?0:l,c=t.salesman_id,o=void 0===c?0:c,t.admin_id=1*r,t.salesman_id=1*o,e.options=t,e.form.channel_qr_id=i,e.form.admin_id=r,e.form.salesman_id=o,e.$util.showLoading(),n.next=10,e.initIndex();case 10:u=e.title,d=e.channel_status,uni.setNavigationBarTitle({title:u[d]}),e.scanRecordId&&e.updateScanRecord(),e.isLoad=!0,e.$jweixin.hideOptionMenu();case 15:case"end":return n.stop()}}),n)})))()},methods:(0,l.default)((0,l.default)((0,l.default)({},(0,c.mapActions)(["getConfigInfo","getUserInfo","addScanRecord","updateScanRecord"])),(0,c.mapMutations)(["updateUserItem"])),{},{initIndex:function(){var t=arguments,e=this;return(0,r.default)((0,s.default)().mark((function n(){var a,l,r,c,o,u,d,p,f,m,h,v,_,g,x,b,w,y;return(0,s.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:return t.length>0&&void 0!==t[0]&&t[0],a=e.options,l=a.channel_qr_id,r=void 0===l?0:l,c=a.admin_id,o=void 0===c?0:c,u=a.salesman_id,d=void 0===u?0:u,e.scanRecordId,p=e.$util.getQueryString("code"),o&&!p&&e.addScanRecord({type:6,qr_id:o}),d&&!p&&e.addScanRecord({type:8,qr_id:d}),n.next=8,e.getConfigInfo();case 8:return f=e.configInfo.plugAuth.channelcate,n.next=11,Promise.all(f?[e.$api.channel.channelInfo(),e.$api.channel.channelCateSelect()]:[e.$api.channel.channelInfo()]);case 11:if(m=n.sent,h=(0,i.default)(m,2),v=h[0],_=h[1],g=void 0===_?[]:_,e.$util.setNavigationBarColor({bg:e.primaryColor}),g.unshift({id:0,title:"暂无接入渠道"}),e.channelList=g,v&&(!v||v.id)){n.next=22;break}return e.$util.hideAll(),n.abrupt("return");case 22:for(x in r&&(v.channel_qr_id=r),o&&(v.admin_id=o),d&&(v.salesman_id=d),e.form)e.form[x]=v[x];e.channelInd=g.findIndex((function(t){return t.id===v.cate_id})),b=v.status,w=v.sh_text,y=r||o||d||4!=b?b:-1,e.channel_status=y,4==y&&w&&(e.tipArr[4][0].text=w),e.$util.hideAll();case 32:case"end":return n.stop()}}),n)})))()},initRefresh:function(){this.initIndex(!0)},pickerChange:function(t,e){this.channelInd=t.target.value,this.form.cate_id=this.channelList[this.channelInd].id},confirm:function(){var t=this;return(0,r.default)((0,s.default)().mark((function e(){var n;return(0,s.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(n=t.channel_status,4!=n){e.next=5;break}return t.channel_status=-1,uni.setNavigationBarTitle({title:"申请".concat(t.$t("action.channelName"))}),e.abrupt("return");case 5:t.$util.goUrl({url:"/user/pages/channel/income",openType:"reLaunch"});case 6:case"end":return e.stop()}}),e)})))()},validate:function(t){this.configInfo.plugAuth.channelcate;var e=new this.$util.Validate;this.rule.map((function(n){var a=n.name;e.add(t[a],n)}));var n=e.start();return n},submit:function(){var t=this;return(0,r.default)((0,s.default)().mark((function e(){var n,a,i,l,r,c,o;return(0,s.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(n=t.$util.deepCopy(t.form),a=t.validate(n),!a){e.next=5;break}return t.$util.showToast({title:a}),e.abrupt("return");case 5:if(i=t.options,l=i.admin_id,r=void 0===l?0:l,c=i.salesman_id,o=void 0===c?0:c,n.admin_id=r,n.salesman_id=o,!t.lockTap){e.next=10;break}return e.abrupt("return");case 10:return t.lockTap=!0,n.text=n.text.length>300?n.text.substring(0,300):n.text,t.$util.showLoading(),e.prev=13,e.next=16,t.$api.channel.applyChannel(n);case 16:t.$util.hideAll(),t.$util.showToast({title:"提交成功"}),setTimeout((function(){t.$util.back(),t.$util.goUrl({url:"/user/pages/apply-result?type=3",openType:"redirectTo"})}),1e3),e.next=24;break;case 21:e.prev=21,e.t0=e["catch"](13),setTimeout((function(){t.lockTap=!1,t.$util.hideAll()}),2e3);case 24:case"end":return e.stop()}}),e,null,[[13,21]])})))()}})};e.default=o},"7b93":function(t,e,n){"use strict";n.r(e);var a=n("77da"),i=n.n(a);for(var l in a)["default"].indexOf(l)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(l);e["default"]=i.a}}]);