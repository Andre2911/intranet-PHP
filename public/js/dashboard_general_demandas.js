"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[192],{7786:(a,t,e)=>{e.r(t),e.d(t,{default:()=>n});const s={components:{highcharts:e(5130).Chart},props:["date_final","date_inicio"],data:function(){return{demandas:[],totalDemandas:0}},computed:{seriesDemandasPeriodo:function(){var a=this,t={data:[{name:"ATENDIDO",data:[],color:"rgba(12, 237, 12, 0.4)"},{name:"PENDIENTE",data:[],color:"rgba(237, 12, 12, 0.5)"},{name:"ANULADO",data:[],color:"rgba(38, 25, 12, 0.3)"}],periodos:[]},e=[];return null!=this.demandas[0]&&(e=this.demandas.reduce((function(a,t){var e=a.findIndex((function(a){return a.name===t.PERIODO}));return e>-1?"ATENDIDO"==t.DEN_ESTADO_DEMANDA?a[e].data[0]+=1*t.CONTADOR:"PENDIENTE"==t.DEN_ESTADO_DEMANDA?a[e].data[1]+=1*t.CONTADOR:a[e].data[2]+=1*t.CONTADOR:"ATENDIDO"==t.DEN_ESTADO_DEMANDA?a.push({name:t.PERIODO,data:[1*t.CONTADOR,0,0]}):"PENDIENTE"==t.DEN_ESTADO_DEMANDA?a.push({name:t.PERIODO,data:[0,1*t.CONTADOR,0]}):a.push({name:t.PERIODO,data:[0,0,1*t.CONTADOR]}),a}),[])),this.totalDemandas=0,t.periodos=[],e.forEach((function(e){t.periodos.push(e.name),t.data[0].data.push(e.data[0]),t.data[1].data.push(e.data[1]),t.data[2].data.push(e.data[2]),a.totalDemandas+=e.data[0],a.totalDemandas+=e.data[1],a.totalDemandas+=e.data[2]})),t},seriesDemandasInstancia:function(){var a={data:[{name:"ATENDIDO",data:[],color:"rgba(12, 237, 12, 0.4)"},{name:"PENDIENTE",data:[],color:"rgba(237, 12, 12, 0.5)"},{name:"ANULADO",data:[],color:"rgba(38, 25, 12, 0.3)"}],instancias:[]},t=[];return null!=this.demandas[0]&&(t=this.demandas.reduce((function(a,t){var e=a.findIndex((function(a){return a.name===t.DEN_INSTANCIA_SIJ}));return e>-1?"ATENDIDO"==t.DEN_ESTADO_DEMANDA?a[e].data[0]+=1*t.CONTADOR:"PENDIENTE"==t.DEN_ESTADO_DEMANDA?a[e].data[1]+=1*t.CONTADOR:a[e].data[2]+=1*t.CONTADOR:"ATENDIDO"==t.DEN_ESTADO_DEMANDA?a.push({name:t.DEN_INSTANCIA_SIJ,data:[1*t.CONTADOR,0,0]}):"PENDIENTE"==t.DEN_ESTADO_DEMANDA?a.push({name:t.DEN_INSTANCIA_SIJ,data:[0,1*t.CONTADOR,0]}):a.push({name:t.DEN_INSTANCIA_SIJ,data:[0,0,1*t.CONTADOR]}),a}),[])),t.sort((function(a,t){return t.data[0]-a.data[0]})),a.instancias=[],t.forEach((function(t){a.instancias.push(t.name),a.data[0].data.push(t.data[0]),a.data[1].data.push(t.data[1]),a.data[2].data.push(t.data[2])})),a},chartDataTime:function(){return{chart:{type:"column"},title:{text:"Demandas"},xAxis:{type:"datetime",dateTimeLabelFormats:{month:"%e. %b",year:"%b"},title:{text:"Estado"},categories:this.seriesDemandasPeriodo.periodos},yAxis:{min:0,title:{text:"Cantidad de Demandas"},labels:{overflow:"justify"},stackLabels:{enabled:!0,style:{fontWeight:"bold",color:"gray",textOutline:"none"}}},legend:{align:"right",x:-30,verticalAlign:"top",y:25,floating:!0,backgroundColor:"white",borderColor:"#CCC",borderWidth:1,shadow:!1},tooltip:{headerFormat:"<b>{point.x}</b> <br/>",pointFormat:'<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.y:.0f} {point.stackTotal}%)<br/>',shared:!0},series:this.seriesDemandasPeriodo.data,plotOptions:{column:{dataLabels:{enabled:!0}},series:{cursor:"pointer",point:{events:{click:function(){alert("Category: "+this.category+", value: "+this.y)}}}}},colors:["#C62828","#6CF","#39F","#06C","#036","#000","#B38","#4DD0E","#00838F"]}},chartTopEscIns:function(){return{chart:{type:"bar",height:700},title:{text:"TOP 15 DEMANDAS POR INSTANCIA"},xAxis:{categories:this.seriesDemandasInstancia.instancias,max:this.seriesDemandasInstancia.instancias.length<15?this.seriesDemandasInstancia.instancias-1:15},yAxis:{min:0,title:{text:"Cantidad"},labels:{overflow:"justify"}},series:this.seriesDemandasInstancia.data,plotOptions:{series:{borderWidth:0,dataLabels:{enabled:!0},dataSorting:{enabled:!0}}},credits:{enabled:!1}}},chartTopEscInsPend:function(){return{chart:{type:"bar",height:700},title:{text:"PRODUCCIÓN POR INSTANCIA"},xAxis:{categories:this.seriesDemandasInstancia.instancias,max:this.seriesDemandasInstancia.instancias.length<15?this.seriesDemandasInstancia.instancias-1:15},yAxis:{min:0,title:{text:"Cantidad"},labels:{overflow:"justify"}},series:this.seriesDemandasInstancia.data,plotOptions:{series:{borderWidth:0,dataLabels:{enabled:!0},dataSorting:{enabled:!0}}},credits:{enabled:!1}}}},mounted:function(){this.getDemandas()},methods:{getDemandas:function(){var a=this,t=this.date_inicio,e=this.date_final,s="../consultasSIJ/reporteDemandas?f_ini="+t+"&f_fin="+e;this.showLoad("grey darken-2","Realizando consulta"),this.demandas=[],axios.get(s).then((function(s){0!=s.data.status?(a.demandas=s.data.data,a.nombre_archivo="Demandas__"+t+" - "+e+".xls",a.json_data=a.demandas,a.json_fields={Periodo:"PERIODO",Instancia:"DEN_INSTANCIA_SIJ","Estado demanda":"DEN_ESTADO_DEMANDA","Días Respuesta":"NUM_DIAS_RESPUESTA"},a.showSnack("success","Consulta de demandas obtenida")):(a.demandas=[],a.showSnack("warning",s.data.message)),a.showLoad("","",!1)})).catch((function(t){"Network Error"===t.message&&a.showSnack("warning","No se pudo conectar con el servicio de Base de Datos del SIJ"),500===t.response.status&&a.showSnack("warning","No se pudo conectar con el servicio de Base de Datos del SIJ"),401===t.response.status&&"Unauthenticated"==t.response.data.error?window.location="../login":403===t.response.status||419===t.response.status?window.location="../inicio":a.getResoluciones(),a.showLoad("","",!1)}))},showSnack:function(a,t){var e=!(arguments.length>2&&void 0!==arguments[2])||arguments[2];this.$emit("showSnack",a,t,e)},showLoad:function(a,t){var e=!(arguments.length>2&&void 0!==arguments[2])||arguments[2];this.$emit("showLoad",a,t,e)}}};const n=(0,e(1900).Z)(s,(function(){var a=this,t=a.$createElement,e=a._self._c||t;return e("v-row",{staticClass:"mt-2"},[e("v-col",{attrs:{cols:"12",md:"12"}},[null!=a.demandas[0]?[a.totalDemandas>0?e("v-alert",{staticClass:"pb-2",attrs:{value:!0,color:"success",outlined:""}},[a._v("\n                "+a._s(a.totalDemandas.toLocaleString("es-PE"))+" demandas obtenidos\n            ")]):a._e(),a._v(" "),null!=a.demandas[0]&&a.seriesDemandasPeriodo.periodos.length>0?[e("highcharts",{ref:"spline",attrs:{options:a.chartDataTime}})]:e("v-skeleton-loader",{staticClass:"mx-auto",attrs:{type:"card"}})]:[e("v-alert",{staticClass:"pb-2",attrs:{value:!0,color:"success",outlined:""}},[e("v-progress-linear",{attrs:{color:"light-blue",height:"10",indeterminate:"",striped:""}})],1)]],2),a._v(" "),e("v-col",[null!=a.demandas[0]?[e("highcharts",{ref:"spline",attrs:{options:a.chartTopEscIns}})]:a._e()],2)],1)}),[],!1,null,null,null).exports}}]);