(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["agent-pages-poster-channel"],{"1f46":function(t,e,n){"use strict";n("6a54");var r=n("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=r(n("5de6")),a=r(n("9b1b")),o=r(n("2634")),s=r(n("2fdc")),c=n("8f59"),u={components:{},props:{},data:function(){return{options:{},confirmText:"长按上图保存图片",src:""}},computed:(0,c.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(t){var e=this;return(0,s.default)((0,o.default)().mark((function n(){var r;return(0,o.default)().wrap((function(n){while(1)switch(n.prev=n.next){case 0:return e.options=t,e.$util.showLoading(),n.next=4,e.getConfigInfo();case 4:e.$util.setNavigationBarColor({bg:e.primaryColor}),uni.setNavigationBarTitle({title:e.$t("action.channelName")}),r=e,setTimeout((function(){r.renderToCanvas()}),1e3),e.$jweixin.hideOptionMenu();case 9:case"end":return n.stop()}}),n)})))()},methods:(0,a.default)((0,a.default)({},(0,c.mapActions)(["getConfigInfo"])),{},{renderToCanvas:function(){var t=this;return(0,s.default)((0,o.default)().mark((function e(){var n,r,i,a,s,c,u,f;return(0,o.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return n=t,e.next=3,t.$api.agent.agentInviteQr({type:1});case 3:r=e.sent,i=t.configInfo.channel_poster,a=void 0===i?"":i,s=a||"https://lbqny.migugu.com/admin/anmo/mine/channel-share.png",c=t.$util.getPage(-1).detail.agent_name,u=t.userInfo.avatarUrl,c=c.length>15?c.substring(0,15)+"...":c,"0rpx",f={css:{width:"750rpx",height:"1280rpx"},views:[{type:"image",src:s,css:{width:"750rpx",height:"1140rpx",objectFit:"cover",top:"0rpx",left:"0rpx",position:"absolute"}},{type:"view",css:{background:"#fff",width:"750rpx",height:"140rpx",bottom:"0rpx",left:"0rpx",position:"absolute"},views:[{type:"image",src:u,css:{position:"absolute",width:"94rpx",height:"94rpx",objectFit:"cover",borderRadius:"50rpx",bottom:"23rpx",left:"20rpx"}},{type:"text",text:c,css:{position:"absolute",bottom:"70rpx",left:"130rpx",width:"600rpx",fontSize:"32rpx",fontWeight:"400",color:"#000"}},{type:"text",text:"邀请您成为TA的".concat(t.$t("action.channelName"),"，扫描二维码立即加入吧!"),css:{position:"absolute",bottom:"25rpx",left:"130rpx",width:"600rpx",fontSize:"26rpx",color:"#999999"}}]},{type:"image",src:r,css:{position:"absolute",width:"200rpx",height:"200rpx",bottom:"337rpx",left:"53rpx",background:"#ffffff",borderRadius:"0rpx"}},{type:"text",text:"扫一扫",css:{position:"absolute",bottom:"290rpx",left:"53rpx",width:"200rpx",fontSize:"26rpx",color:"#999999",textAlign:"center"}}]},t.$refs.painter.render(f),t.$refs.painter.canvasToTempFilePathSync({fileType:"jpg",quality:1,success:function(e){n.$util.hideAll(),t.src=e.tempFilePath}});case 13:case"end":return e.stop()}}),e)})))()},previewImage:function(){var t=this.src;this.$util.previewImage({current:t,urls:[t]})},saveImage:function(){var t=this;return(0,s.default)((0,o.default)().mark((function e(){var n,r,a,s;return(0,o.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return e.next=2,t.$util.checkAuth({type:"writePhotosAlbum"});case 2:return n=t.src,e.next=5,uni.saveImageToPhotosAlbum({filePath:n});case 5:if(r=e.sent,a=(0,i.default)(r,2),s=a[0],a[1],!s){e.next=11;break}return e.abrupt("return");case 11:uni.showToast({icon:"none",title:"保存成功"});case 12:case"end":return e.stop()}}),e)})))()},toPreviewSave:function(){this.toConfirmPreviewSave()},toConfirmPreviewSave:function(){this.previewImage()}})};e.default=u},"2aa4":function(t,e,n){var r=n("b054");r.__esModule&&(r=r.default),"string"===typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);var i=n("967d").default;i("75a7d39f",r,!0,{sourceMap:!1,shadowMode:!1})},7339:function(t,e,n){"use strict";n.r(e);var r=n("1f46"),i=n.n(r);for(var a in r)["default"].indexOf(a)<0&&function(t){n.d(e,t,(function(){return r[t]}))}(a);e["default"]=i.a},"9f17":function(t,e,n){"use strict";n.r(e);var r=n("c769"),i=n("7339");for(var a in i)["default"].indexOf(a)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(a);n("dcf0");var o=n("828b"),s=Object(o["a"])(i["default"],r["b"],r["c"],!1,null,"4cf11bdd",null,!1,r["a"],void 0);e["default"]=s.exports},b054:function(t,e,n){var r=n("c86c");e=r(!1),e.push([t.i,".code-img[data-v-4cf11bdd]{width:%?750?%;height:%?1280?%}",""]),t.exports=e},c769:function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){return r}));var r={lPainter:n("9ca4").default},i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{style:{background:t.pageColor}},[n("v-uni-view",{staticClass:"hideCanvasView"},[n("l-painter",{ref:"painter",staticClass:"hideCanvas",attrs:{useCORS:!0}})],1),t.src?[n("v-uni-image",{staticClass:"code-img",attrs:{src:t.src},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.previewImage.apply(void 0,arguments)}}}),n("v-uni-view",{staticClass:"space-max-footer"}),n("fix-bottom-button",{attrs:{text:[{text:t.confirmText,type:"confirm"}],bgColor:"#fff",classType:2},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.toPreviewSave.apply(void 0,arguments)}}})]:t._e()],2)},a=[]},dcf0:function(t,e,n){"use strict";var r=n("2aa4"),i=n.n(r);i.a}}]);