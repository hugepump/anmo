(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["dynamic-pages-technician-detail~technician-pages-fxq-index~technician-pages-fxq-new_file~technician-~165d86f0"],{1372:function(t,e,r){"use strict";r.r(e);var a=r("2c2d"),n=r.n(a);for(var s in a)["default"].indexOf(s)<0&&function(t){r.d(e,t,(function(){return a[t]}))}(s);e["default"]=n.a},"2c2d":function(t,e,r){"use strict";r("6a54");var a=r("f5bd").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(r("3471")),s=a(r("fcf3"));r("4626"),r("5ac7"),r("5c47"),r("a1c1"),r("0506"),r("e838"),r("aa9c"),r("5ef2");var i=r("92d9"),o={name:"parser",data:function(){return{uid:this._uid,showAnimation:"",nodes:[]}},props:{html:{type:null,default:null},autocopy:{type:Boolean,default:!0},autopause:{type:Boolean,default:!0},autopreview:{type:Boolean,default:!0},autosetTitle:{type:Boolean,default:!0},domain:{type:String,default:null},imgMode:{type:String,default:"default"},lazyLoad:{type:Boolean,default:!1},selectable:{type:Boolean,default:!1},tagStyle:{type:Object,default:function(){return{}}},showWithAnimation:{type:Boolean,default:!1},useAnchor:{type:Boolean,default:!1},useCache:{type:Boolean,default:!1}},watch:{html:function(t){this.setContent(t,void 0,!0)}},mounted:function(){this.imgList=[],this.imgList.each=function(t){for(var e=0;e<this.length;e++){var r=t(this[e],e,this);r&&(this.includes(r)?this[e]=Deduplication(r):this[e]=r)}},this.setContent(this.html,void 0,!0)},beforeDestroy:function(){this._observer&&this._observer.disconnect()},methods:{setContent:function(t,e,r){var a=this;if("object"==(0,s.default)(e))for(var o in e)o=o.replace(/-(\w)/g,(function(){return arguments[1].toUpperCase()})),this[o]=e[o];if(t=t||"",t){if("string"!=typeof t&&(t=this.Dom2Str(t.nodes||t)),/[0-9.]*?rpx/.test(t)){var l=uni.getSystemInfoSync().screenWidth/750;t=t.replace(/([0-9.]*?)rpx/g,(function(){return parseFloat(arguments[1])*l+"px"}))}var c="<style scoped>";for(var u in i.userAgentStyles)c+=u+"{"+i.userAgentStyles[u]+"}";for(var u in this.tagStyle)c+=u+"{"+this.tagStyle[u]+"}";c+="</style>",t=c+t,this.rtf&&this.rtf.parentNode.removeChild(this.rtf),this.rtf=document.createElement("div"),this.rtf.innerHTML=t;var d,f=(0,n.default)(this.rtf.getElementsByTagName("style"));try{for(f.s();!(d=f.n()).done;){c=d.value;c.innerHTML=c.innerHTML.replace(/\s*body/g,"#rtf"+this._uid),c.setAttribute("scoped","true")}}catch(O){f.e(O)}finally{f.f()}this.lazyLoad&&IntersectionObserver&&(this._observer&&this._observer.disconnect(),this._observer=new IntersectionObserver((function(t){var e,r=(0,n.default)(t);try{for(r.s();!(e=r.n()).done;){var s=e.value;s.isIntersecting&&(s.target.src=s.target.getAttribute("data-src"),s.target.removeAttribute("data-src"),a._observer.unobserve(s.target))}}catch(O){r.e(O)}finally{r.f()}}),{rootMargin:"1000px 0px 1000px 0px"}));var h=this,p=this.rtf.getElementsByTagName("title");p.length&&this.autosetTitle&&uni.setNavigationBarTitle({title:p[0].innerText}),this.imgList.length=0;for(var m=this.rtf.getElementsByTagName("img"),y=0;y<m.length;y++){var g=m[y];g.style.maxWidth="100%",g.i=y,this.domain&&"/"==g.getAttribute("src")[0]&&("/"==g.getAttribute("src")[1]?g.src=(this.domain.includes("://")?this.domain.split("://")[0]:"http")+":"+g.getAttribute("src"):g.src=this.domain+g.getAttribute("src")),h.imgList.push(g.src),"A"!=g.parentElement.nodeName&&(g.onclick=function(){if(!this.hasAttribute("ignore")){var t=!0;this.ignore=function(){return t=!1},h.$emit("imgtap",this),t&&h.autopreview&&uni.previewImage({current:this.i,urls:h.imgList})}}),g.onerror=function(){h.$emit("error",{source:"img",target:this})},h.lazyLoad&&this._observer&&(g.setAttribute("data-src",g.src),g.removeAttribute("src"),this._observer.observe(g))}var v,b=this.rtf.getElementsByTagName("a"),x=(0,n.default)(b);try{for(x.s();!(v=x.n()).done;){var w=v.value;w.onclick=function(t){var e=!0,r=this.getAttribute("href");if(h.$emit("linkpress",{href:r,ignore:function(){return e=!1}}),e&&r)if("#"==r[0])h.useAnchor&&h.navigateTo({id:r.substring(1)});else{if(0==r.indexOf("http")||0==r.indexOf("//"))return!0;uni.navigateTo({url:r})}return!1}}}catch(O){x.e(O)}finally{x.f()}var _=this.rtf.getElementsByTagName("video");h.videoContexts=_;var A,T=(0,n.default)(_);try{for(T.s();!(A=T.n()).done;){var k=A.value;k.style.maxWidth="100%",k.onerror=function(){h.$emit("error",{source:"video",target:this})},k.onplay=function(){if(h.autopause){var t,e=(0,n.default)(h.videoContexts);try{for(e.s();!(t=e.n()).done;){var r=t.value;r!=this&&r.pause()}}catch(O){e.e(O)}finally{e.f()}}}}}catch(O){T.e(O)}finally{T.f()}var S,C=this.rtf.getElementsByTagName("audios"),B=(0,n.default)(C);try{for(B.s();!(S=B.n()).done;){var M=S.value;M.onerror=function(t){h.$emit("error",{source:"audio",target:this})}}}catch(O){B.e(O)}finally{B.f()}document.getElementById("rtf"+this._uid).appendChild(this.rtf),this.showWithAnimation&&(this.showAnimation="transition:400ms ease 0ms;transition-property:transform,opacity;transform-origin:50% 50% 0;-webkit-transition:400ms ease 0ms;-webkit-transform:;-webkit-transition-property:transform,opacity;-webkit-transform-origin:50% 50% 0;opacity: 1"),r||(this.nodes=[0]),this.$nextTick((function(){a.$emit("ready",a.rtf.getBoundingClientRect())}))}else this.rtf&&this.rtf.parentNode.removeChild(this.rtf)},Dom2Str:function(t){var e,r="",a=(0,n.default)(t);try{for(a.s();!(e=a.n()).done;){var s=e.value;if("text"==s.type)r+=s.text;else{for(var i in r+="<"+s.name,s.attrs||{})r+=" "+i+'="'+s.attrs[i]+'"';s.children&&s.children.length?r+=">"+this.Dom2Str(s.children)+"</"+s.name+">":r+="/>"}}}catch(o){a.e(o)}finally{a.f()}return r},getText:function(){var t=!(arguments.length>0&&void 0!==arguments[0])||arguments[0];return t?this.rtf.innerText:this.rtf.innerText.replace(/\s/g,"")},navigateTo:function(t){if(!t.id)return window.scrollTo(0,this.rtf.offsetTop),t.success?t.success({errMsg:"pageScrollTo:ok"}):null;var e=document.getElementById(t.id);if(!e)return t.fail?t.fail({errMsg:"Label Not Found"}):null;uni.pageScrollTo({scrollTop:this.rtf.offsetTop+e.offsetTop,success:t.success,fail:t.fail})},getVideoContext:function(t){if(!t)return this.videoContexts;var e,r=(0,n.default)(this.videoContexts);try{for(r.s();!(e=r.n()).done;){var a=e.value;if(a.id==t)return a}}catch(s){r.e(s)}finally{r.f()}return null}}};e.default=o},3471:function(t,e,r){"use strict";r("6a54"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=function(t,e){var r="undefined"!==typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!r){if(Array.isArray(t)||(r=(0,a.default)(t))||e&&t&&"number"===typeof t.length){r&&(t=r);var n=0,s=function(){};return{s:s,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:s}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var i,o=!0,l=!1;return{s:function(){r=r.call(t)},n:function(){var t=r.next();return o=t.done,t},e:function(t){l=!0,i=t},f:function(){try{o||null==r["return"]||r["return"]()}finally{if(l)throw i}}}},r("01a2"),r("e39c"),r("bf0f"),r("844d"),r("18f7"),r("de6c"),r("7a76"),r("c9b5");var a=function(t){return t&&t.__esModule?t:{default:t}}(r("5d6b"))},"4cf1":function(t,e,r){r("01a2"),r("e39c"),r("bf0f"),r("844d"),r("18f7"),r("de6c"),r("7a76"),r("c9b5");var a=r("79b7");t.exports=function(t,e){var r="undefined"!==typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!r){if(Array.isArray(t)||(r=a(t))||e&&t&&"number"===typeof t.length){r&&(t=r);var n=0,s=function(){};return{s:s,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:s}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var i,o=!0,l=!1;return{s:function(){r=r.call(t)},n:function(){var t=r.next();return o=t.done,t},e:function(t){l=!0,i=t},f:function(){try{o||null==r["return"]||r["return"]()}finally{if(l)throw i}}}},t.exports.__esModule=!0,t.exports["default"]=t.exports},7622:function(t,e,r){"use strict";r.r(e);var a=r("bbbe"),n=r("1372");for(var s in n)["default"].indexOf(s)<0&&function(t){r.d(e,t,(function(){return n[t]}))}(s);r("a1b1");var i=r("828b"),o=Object(i["a"])(n["default"],a["b"],a["c"],!1,null,"4ec12de4",null,!1,a["a"],void 0);e["default"]=o.exports},"79b7":function(t,e,r){r("f7a5"),r("bf0f"),r("08eb"),r("18f7"),r("5c47"),r("0506");var a=r("e476");t.exports=function(t,e){if(t){if("string"===typeof t)return a(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?a(t,e):void 0}},t.exports.__esModule=!0,t.exports["default"]=t.exports},"92d9":function(t,e,r){var a=r("4cf1").default;function n(t){var e,r=Object.create(null),n=t.split(","),s=a(n);try{for(s.s();!(e=s.n()).done;){var i=e.value;r[i]=!0}}catch(o){s.e(o)}finally{s.f()}return r}r("5c47"),r("2c10"),r("4626"),r("5ac7"),r("c9b5"),r("bf0f"),r("ab80"),r("e966"),r("e838"),r("aa9c"),r("a1c1");var s=n("align,alt,app-id,appId,author,autoplay,border,cellpadding,cellspacing,class,color,colspan,controls,data-src,dir,face,height,href,id,ignore,loop,muted,name,path,poster,rowspan,size,span,src,start,style,type,lbType,lbtype,width,xmlns"),i=n("a,abbr,ad,audio,b,blockquote,br,code,col,colgroup,dd,del,dl,dt,div,em,fieldset,h1,h2,h3,h4,h5,h6,hr,i,img,ins,label,legend,li,ol,p,q,source,span,strong,sub,sup,table,tbody,td,tfoot,th,thead,tr,title,u,ul,video,iframe"),o=n("address,article,aside,body,center,cite,footer,header,html,nav,pre,section"),l=n("area,base,basefont,canvas,circle,command,ellipse,embed,frame,head,input,isindex,keygen,line,link,map,meta,param,path,polygon,rect,script,source,svg,textarea,track,use,wbr,"),c=n("a,ad,audio,colgroup,fieldset,legend,li,ol,sub,sup,table,tbody,td,tfoot,th,thead,tr,ul,video,iframe,"),u=n("area,base,basefont,br,col,circle,ellipse,embed,frame,hr,img,input,isindex,keygen,line,link,meta,param,path,polygon,rect,source,track,use,wbr,");function d(t){for(var e=t._STACK.length-1;e>=0;e--){if(c[t._STACK[e].name])return!1;t._STACK[e].c=1}return!0}t.exports={highlight:null,LabelAttrsHandler:function(t,e){var r="max-width: 100% !important;display:block;";switch(t.attrs.style=e.CssHandler.match(t.name,t.attrs,t)+(t.attrs.style||""),t.name){case"ul":case"ol":case"li":case"dd":case"dl":case"dt":case"div":case"span":case"em":case"p":"span"===t.name&&(r="white-space:normal;"),"p"!==t.name||t.attrs.style&&t.attrs.style.includes("margin-top:")||(r+="margin-top:10px;"),t.attrs.style&&(t.attrs.style=t.attrs.style.includes("width:")?r:t.attrs.style+";".concat(r)),t.attrs.align&&(t.attrs.style="text-align:"+t.attrs.align+";"+t.attrs.style,t.attrs.align=void 0);break;case"img":t.attrs.height&&(t.attrs.height="auto"),t.attrs.style&&(t.attrs.style=t.attrs.style.includes("height:")?r:t.attrs.style+";".concat(r)),t.attrs["data-src"]&&(t.attrs.src=t.attrs.src||t.attrs["data-src"],t.attrs["data-src"]=void 0),t.attrs.src&&(t.attrs.ignore||(d(e)?t.attrs.i=(e._imgNum++).toString():t.attrs.ignore="true"),e._domain&&"/"==t.attrs.src[0]&&("/"==t.attrs.src[1]?t.attrs.src=e._protocol+":"+t.attrs.src:t.attrs.src=e._domain+t.attrs.src));break;case"a":case"ad":d(e);break;case"font":if(t.attrs.color&&(t.attrs.style="color:"+t.attrs.color+";"+t.attrs.style,t.attrs.color=void 0),t.attrs.face&&(t.attrs.style="font-family:"+t.attrs.face+";"+t.attrs.style,t.attrs.face=void 0),t.attrs.size){var a=parseInt(t.attrs.size);a<1?a=1:a>7&&(a=7);t.attrs.style="font-size:"+["xx-small","x-small","small","medium","large","x-large","xx-large"][a-1]+";"+t.attrs.style,t.attrs.size=void 0}break;case"iframe":case"video":case"audio":t.attrs.loop=t.attrs.hasOwnProperty("loop")||!1,t.attrs.controls=t.attrs.hasOwnProperty("controls")||!0,t.attrs.autoplay=t.attrs.hasOwnProperty("autoplay")||!1,t.attrs.id?e["_"+t.name+"Num"]++:t.attrs.id=t.name+ ++e["_"+t.name+"Num"],"video"==t.name&&(t.attrs.style=t.attrs.style||"",t.attrs.width&&(t.attrs.style="width:"+parseFloat(t.attrs.width)+(t.attrs.height.includes("%")?"%":"px")+";"+t.attrs.style,t.attrs.width=void 0),t.attrs.height&&(t.attrs.style="height:"+parseFloat(t.attrs.height)+(t.attrs.height.includes("%")?"%":"px")+";"+t.attrs.style,t.attrs.height=void 0),e._videoNum>3&&(t.lazyLoad=!0)),t.name,t.attrs.source=[],t.attrs.src&&t.attrs.source.push(t.attrs.src),t.attrs.controls||t.attrs.autoplay||console.warn("存在没有controls属性的 "+t.name+" 标签，可能导致无法播放",t),d(e);break;case"source":var n=e._STACK[e._STACK.length-1];!n||"video"!=n.name&&"audio"!=n.name||(n.attrs.source.push(t.attrs.src),n.attrs.src||(n.attrs.src=t.attrs.src));break}e._domain&&t.attrs.style.includes("url")&&(t.attrs.style=t.attrs.style.replace(/url\s*\(['"\s]*(\S*?)['"\s]*\)/,(function(){var t=arguments[1];return t&&"/"==t[0]?"/"==t[1]?"url("+e._protocol+":"+t+")":"url("+e._domain+t+")":arguments[0]}))),t.attrs.style||(t.attrs.style=void 0),e._useAnchor&&t.attrs.id&&d(e)},trustAttrs:s,trustTags:i,blockTags:o,ignoreTags:l,selfClosingTags:u,userAgentStyles:{a:"color:#366092;word-break:break-all;padding:1.5px 0 1.5px 0",address:"font-style:italic",blockquote:"background-color:#f6f6f6;border-left:3px solid #dbdbdb;color:#6c6c6c;padding:5px 0 5px 10px",center:"text-align:center",cite:"font-style:italic",code:"padding:0 1px 0 1px;margin-left:2px;margin-right:2px;background-color:#f8f8f8;border-radius:3px",dd:"margin-left:40px",img:"max-width:100%",mark:"background-color:yellow",pre:"font-family:monospace;white-space:pre;overflow:scroll",s:"text-decoration:line-through",u:"text-decoration:underline"},makeMap:n}},a1b1:function(t,e,r){"use strict";var a=r("c0a1"),n=r.n(a);n.a},bbbe:function(t,e,r){"use strict";r.d(e,"b",(function(){return a})),r.d(e,"c",(function(){return n})),r.d(e,"a",(function(){}));var a=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("v-uni-view",[t.html||t.nodes.length?t._e():t._t("default"),r("div",{style:(t.selectable?"user-select:text;-webkit-user-select:text;":"")+(t.showWithAnimation?"opacity:0;"+t.showAnimation:""),attrs:{id:"rtf"+t.uid}})],2)},n=[]},c0a1:function(t,e,r){var a=r("d973");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=r("967d").default;n("e1713f7a",a,!0,{sourceMap:!1,shadowMode:!1})},d973:function(t,e,r){var a=r("c86c");e=a(!1),e.push([t.i,"\n[data-v-4ec12de4]:host{display:block;overflow:scroll;-webkit-overflow-scrolling:touch}\n",""]),t.exports=e},e476:function(t,e){t.exports=function(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,a=new Array(e);r<e;r++)a[r]=t[r];return a},t.exports.__esModule=!0,t.exports["default"]=t.exports}}]);