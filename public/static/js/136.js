webpackJsonp([136],{"5Dfg":function(t,e,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a=s("83k7"),r=(s("sCIe"),s("w/pT"),s("x/Rj"),s("yMsf"),s("e+8j"),{props:{datas:{type:Array,default:function(){return[]}}},data:function(){return{isEmpty:!1,echartsOptions:{color:["#608dff","#2bcd8e","#FFA500","#ff6b73"],tooltip:{trigger:"item",formatter:"{a} <br/>{b}: {c}人"},legend:{orient:"vertical",left:"left",data:[]},series:[{name:"用户比例",type:"pie",radius:"50%",data:[],label:{show:!1},emphasis:{itemStyle:{shadowBlur:10,shadowOffsetX:0,shadowColor:"rgba(0, 0, 0, 0.5)"}}}]}}},components:{ECharts:a.a},created:function(){this.datas.length&&this.handleDatas(this.datas)},mounted:function(){window.addEventListener("resize",this.loadEcharts)},methods:{handleDatas:function(t){this.echartsOptions.legend.data=t.map(function(t){return t.true_title}),this.echartsOptions.series[0].data=t.map(function(t){return{value:t.count||0,name:t.true_title}}),this.echartsOptions.formatter=function(e){for(var s="",a=0,r=t.length;a<r;a++)t[a].true_title===e&&(s=e+"  "+t[a].count+"人  "+t[a].balance+"%");return s}},loadEcharts:function(){var t=this;this.$refs.myecharts&&setTimeout(function(){t.$refs.myecharts.resize()},10)}},watch:{datas:function(t){t.length?(this.isEmpty=!1,this.handleDatas(t)):this.isEmpty=!0}},destroyed:function(){window.removeEventListener("resize",this.loadEcharts)}}),n={render:function(){var t=this.$createElement,e=this._self._c||t;return this.isEmpty?e("div",{staticClass:"empty"},[this._v("暂无数据")]):e("e-charts",{ref:"myecharts",attrs:{id:"sale-echarts",theme:"ovilia-green",options:this.echartsOptions}})},staticRenderFns:[]};var i=s("C7Lr")(r,n,!1,function(t){s("SX5u")},"data-v-4167b16f",null);e.default=i.exports},SX5u:function(t,e){}});