(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["agent-pages-poster-salesman"],{"15a2":function(t,e,n){"use strict";n("6a54");var r=n("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=r(n("5de6")),i=r(n("9b1b")),o=r(n("2634")),s=r(n("2fdc")),u=n("8f59"),c={components:{},props:{},data:function(){return{confirmText:"长按上图保存图片",src:""}},computed:(0,u.mapState)({configInfo:function(t){return t.config.configInfo}}),onLoad:function(t){var e=this;return(0,s.default)((0,o.default)().mark((function t(){var n;return(0,o.default)().wrap((function(t){while(1)switch(t.prev=t.next){case 0:return e.$util.showLoading(),t.next=3,e.getConfigInfo();case 3:e.$util.setNavigationBarColor({bg:e.primaryColor}),n=e,setTimeout((function(){n.renderToCanvas()}),1e3),e.$jweixin.hideOptionMenu();case 7:case"end":return t.stop()}}),t)})))()},methods:(0,i.default)((0,i.default)({},(0,u.mapActions)(["getConfigInfo"])),{},{renderToCanvas:function(){var t=this;return(0,s.default)((0,o.default)().mark((function e(){var n,r,a,i,s,u,c,f;return(0,o.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return n=t,e.next=3,t.$api.agent.agentInviteQr({type:2});case 3:r=e.sent,a=t.configInfo.salesman_poster,i=void 0===a?"":a,s=i||"https://lbqny.migugu.com/admin/anmo/mine/salesman-share.png",u=t.$util.getPage(-1).detail.agent_name,c=t.userInfo.avatarUrl,u=u.length>15?u.substring(0,15)+"...":u,"0rpx",f={css:{width:"750rpx",height:"1280rpx"},views:[{type:"image",src:s,css:{width:"750rpx",height:"1140rpx",objectFit:"cover",top:"0rpx",left:"0rpx",position:"absolute"}},{type:"view",css:{background:"#fff",width:"750rpx",height:"140rpx",bottom:"0rpx",left:"0rpx",position:"absolute"},views:[{type:"image",src:c,css:{position:"absolute",width:"94rpx",height:"94rpx",objectFit:"cover",borderRadius:"50rpx",bottom:"23rpx",left:"20rpx"}},{type:"text",text:u,css:{position:"absolute",bottom:"70rpx",left:"130rpx",width:"600rpx",fontSize:"32rpx",fontWeight:"400",color:"#000"}},{type:"text",text:"邀请您成为TA的业务员，扫描二维码立即加入吧!",css:{position:"absolute",bottom:"25rpx",left:"130rpx",width:"600rpx",fontSize:"26rpx",color:"#999999"}}]},{type:"image",src:r,css:{position:"absolute",width:"290rpx",height:"290rpx",bottom:"366rpx",left:"228rpx",background:"#ffffff",borderRadius:"0rpx"}}]},t.$refs.painter.render(f),t.$refs.painter.canvasToTempFilePathSync({fileType:"jpg",quality:1,success:function(e){n.$util.hideAll(),t.src=e.tempFilePath}});case 13:case"end":return e.stop()}}),e)})))()},previewImage:function(){var t=this.src;this.$util.previewImage({current:t,urls:[t]})},saveImage:function(){var t=this;return(0,s.default)((0,o.default)().mark((function e(){var n,r,i,s;return(0,o.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return e.next=2,t.$util.checkAuth({type:"writePhotosAlbum"});case 2:return n=t.src,e.next=5,uni.saveImageToPhotosAlbum({filePath:n});case 5:if(r=e.sent,i=(0,a.default)(r,2),s=i[0],i[1],!s){e.next=11;break}return e.abrupt("return");case 11:uni.showToast({icon:"none",title:"保存成功"});case 12:case"end":return e.stop()}}),e)})))()},toPreviewSave:function(){this.toConfirmPreviewSave()},toConfirmPreviewSave:function(){this.previewImage()}})};e.default=c},"2d15":function(t,e,n){"use strict";n.d(e,"b",(function(){return a})),n.d(e,"c",(function(){return i})),n.d(e,"a",(function(){return r}));var r={lPainter:n("9ca4").default},a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{style:{background:t.pageColor}},[n("v-uni-view",{staticClass:"hideCanvasView"},[n("l-painter",{ref:"painter",staticClass:"hideCanvas",attrs:{useCORS:!0}})],1),t.src?[n("v-uni-image",{staticClass:"code-img",attrs:{src:t.src},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.previewImage.apply(void 0,arguments)}}}),n("v-uni-view",{staticClass:"space-max-footer"}),n("fix-bottom-button",{attrs:{text:[{text:t.confirmText,type:"confirm"}],bgColor:"#fff",classType:2},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.toPreviewSave.apply(void 0,arguments)}}})]:t._e()],2)},i=[]},"7bfa":function(t,e,n){"use strict";var r=n("f863"),a=n.n(r);a.a},a349:function(t,e,n){"use strict";n.r(e);var r=n("15a2"),a=n.n(r);for(var i in r)["default"].indexOf(i)<0&&function(t){n.d(e,t,(function(){return r[t]}))}(i);e["default"]=a.a},b969:function(t,e,n){var r=n("c86c");e=r(!1),e.push([t.i,".code-img[data-v-769a8e0c]{width:%?750?%;height:%?1280?%}",""]),t.exports=e},bc44:function(t,e,n){"use strict";n.r(e);var r=n("2d15"),a=n("a349");for(var i in a)["default"].indexOf(i)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(i);n("7bfa");var o=n("828b"),s=Object(o["a"])(a["default"],r["b"],r["c"],!1,null,"769a8e0c",null,!1,r["a"],void 0);e["default"]=s.exports},f863:function(t,e,n){var r=n("b969");r.__esModule&&(r=r.default),"string"===typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);var a=n("967d").default;a("21e3eaba",r,!0,{sourceMap:!1,shadowMode:!1})}}]);