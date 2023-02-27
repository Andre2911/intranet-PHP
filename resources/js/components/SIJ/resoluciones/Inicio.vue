<template>
    <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
      <v-main id="contentApp">

        <v-card>
            <v-toolbar color="grey" dark>
               <v-toolbar-title>SIJ - Consulta de Resoluciones</v-toolbar-title>
               <v-spacer/>
            </v-toolbar>
            <v-card-text>
                    <sij-parametros :type="'Resoluciones'" :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @getData="getResoluciones"/>
                    <v-row>
                        <v-col cols="12" md="12">
                            <v-tabs
                                v-model="tab"
                                fixed-tabs
                                color="red darken-4"
                            >
                                <v-tab key="table">
                                    Datos
                                </v-tab>
                                <v-tab key="stat">
                                    Gráficos
                                </v-tab>
                            </v-tabs>

                            <v-tabs-items v-model="tab">
                                <v-tab-item
                                    key="table"
                                >
                                <dataTableSIJ
                                        :headers="headers"
                                        :data="filteredData"
                                        :allData="audiencias"
                                        :filters="filters"
                                        :n_paginas="n_paginas"
                                        :itemsPerPage="itemsPerPage"
                                        :tipo="2"
                                        :dense="true"
                                        :json_data="json_data"
                                        :json_fields="json_fields"
                                        :nombre_archivo="nombre_archivo"
                                        @viewItem="viewDetails"
                                    />
                                </v-tab-item>
                                <v-tab-item
                                    key="stat"
                                >
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartOptions" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartEscEstInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartResEstado" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartEscritosEsp" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartEspEstado" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartSankey" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartHito" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartHitoInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                    </v-row>
                                </v-tab-item>
                            </v-tabs-items>
                        </v-col>
                    </v-row>
            </v-card-text>
        </v-card>
        <snackbar :snack="snack" />
        <dialogLoader :dialogLoad="dialogLoad"/>
      </v-main>
      <v-dialog
            v-model="d_resolucion"
        >
            <sij-resoluciones-view :activeItem='activeItem' :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @close="d_resolucion = false"/>
        </v-dialog>
    </v-app>

</template>
<script>
import {Chart} from 'highcharts-vue'

export default {
    props:['dataUser'],
    components: {
        highcharts: Chart 
    },
    data() {
        return {
            menu: 3,
            submenu: -1,
            modulo: 'SIJ',
            drawer:true,
            
            audiencias:[],
            tab: null,
            d_resolucion:false,
            filters:{
                NOM_USUARIO_SECRETARIO: [],
                DEN_ESTADO_ACTO_PROCESAL: [],
                NOM_USUARIO_PROYECTO: [],
                NOM_USUARIO_DESCARGO: [],
                DEN_ACTO_PROCESAL_HITO: [],
            },
            headers:[
               {text: 'Expediente', value: 'NUM_EXPEDIENTE'},
               {text: 'Instancia', value: 'DEN_INSTANCIA_SIJ'},
               {text: 'Secretario', value: 'NOM_USUARIO_SECRETARIO'},
               {text: 'Acto Procesal', value: 'DEN_TIPO_ACTO_PROCESAL'},
               {text: 'N° Acto P.', value: 'NUM_RESOL_PROYECTO'},
               {text: 'Estado Acto Procesal', value: 'DEN_ESTADO_ACTO_PROCESAL'},
               {text: 'Usuario Proyecto', value: 'NOM_USUARIO_PROYECTO'},
               {text: 'Fecha Res. Proyecto', value: 'FECHA_RESOL_PROYECTO', type:'datetime'},
               {text: 'Usuario Descarga', value: 'NOM_USUARIO_DESCARGO'},
               {text: 'Fecha Res. Descargo', value: 'FECHA_RESOL_DESCARGO', type:'datetime'},
               {text: 'Hito', value: 'DEN_ACTO_PROCESAL_HITO'},
               {text: 'Opciones',type:'opciones', value: 'view'}

            ],
            json_fields: {},
            json_data: [],
            nombre_archivo:'',
            n_paginas:1,
            itemsPerPage:1500,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            dialogLoad:{
                show:false,
                text:'',
                color:'',
            },
            options: ['spline', 'line', 'bar', 'pie'],
            modo: 'bar',
            activeItem:{},
            params:{},
        }
    },
    computed:{
        filteredData() {
            if(this.audiencias[0] != undefined){
                return this.audiencias.filter(d => {
                    return Object.keys(this.filters).every(f => {
                        return this.filters[f].length < 1 || this.filters[f].includes(d[f])
                    })
                })
            } else{
                return [];
            }
            
        },
        chartOptions() { 
            return {
                    chart: {  type: 'bar'},
                    title: {  text: 'CANT. DE RESOLUCIONES POR INSTANCIAS'  },
                    xAxis: {
                        categories: ['Instancias'],
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de proyectos de resoluciones'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    series: this.seriesInstancia,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                            }
                        },
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
            }
        },
        
        chartResEstado() { 
            return {
                    chart: {  
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {  text: '% DE RESOLUCIONES SEGÚN ESTADO' },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '% resoluciones segun estado'
                        }
                    },
                    series: this.seriesEstado,
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                connectorColor: 'silver'
                            }
                        }
                    },
              }
        },
        chartEscEstInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE RESOLUCIONES SEGÚN ESTADO POR INSTANCIAS' },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de resoluciones'
                        }
                    },
                    series: this.seriesEstadoInst,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal'
                        }
                    },
              }
        },
        chartEscritosEsp() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE RESOLUCIONES POR ESPECIALISTA'  },
                    xAxis: {
                        categories: ['Especialistas']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de resoluciones'
                        }
                    },
                    series: this.seriesEspecialista,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                            }
                        }
                    },
              }
        },
        chartEspEstado() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE RESOLUCIONES POR ESTADO Y ESPECIALISTA' },
                    xAxis: {
                        categories: this.catEspecialistas
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de resoluciones'
                        }
                    },
                    series: this.seriesEspEstado,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                            },
                            stacking: 'normal'
                        }
                    },
              }
        },
        chartHito() { 
            return {
                    chart: {  
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {  text: '% DE RESOLUCIONES CON HITO ESTADISTICO' },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '% resoluciones segun estado'
                        }
                    },
                    series: this.seriesHitos,
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b> {point.name}</b>: {point.y} / {point.percentage:.1f}%  ',
                                connectorColor: 'silver'
                            }
                        }
                    },
              }
        },
        chartHitoInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE RESOLUCIONES POR HITOS ESTADISTICOS E INSTANCIAS' },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de resoluciones'
                        }
                    },
                    series: this.seriesHitosInst,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                            },
                            stacking: 'normal'
                        }
                    },
              }
        },
        chartSankey() { 
            return {
                    chart: {  type: 'sankey'},
                    title: {  text: 'CANT. RESOLUCIONES PROYECTADAS VS DESCARGADAS' },
                    series: this.seriesSankey,
                    accessibility: {
                        point: {
                            valueDescriptionFormat: '{index}. {point.from} to {point.to}, {point.weight}.'
                        }
                    },
              }
        },
        seriesInstancia(){
            var series =[];
            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_INSTANCIA_SIJ);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0]++
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_INSTANCIA_SIJ, data:[1]};
                        allAudiencias.push(objAudiencia);
                    }
                    return allAudiencias
                }, [])
            } 
            return series;
        },
        catInstancias(){
            var catAud =[];

            if(this.audiencias[0] != undefined){
                catAud = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.DEN_INSTANCIA_SIJ);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.DEN_INSTANCIA_SIJ);
                    }
                    return allAudiencias
                }, [])
            } 
            return catAud;
        },
        catEspecialistas(){
            var catAud =[];

            if(this.audiencias[0] != undefined){
                catAud = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.NOM_USUARIO_SECRETARIO);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.NOM_USUARIO_SECRETARIO);
                    }
                    return allAudiencias
                }, [])
            } 
            return catAud;
        },
        seriesParte(){
            var series =[];
            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_TIPO_PARTE);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0]++
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_TIPO_PARTE, data:[1]};
                        allAudiencias.push(objAudiencia);
                    }
                    return allAudiencias
                }, [])
            } 
            return series;
        },
        seriesEstado(){
            var series =[{name:'# de escritos', data:[]}];
            var estados =[];
            if(this.audiencias[0] != undefined){
                estados = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_ACTO_PROCESAL);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0]++
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_ACTO_PROCESAL, data:[1], color: ((audiencia.DEN_ESTADO_ACTO_PROCESAL == 'PROYECTO DE RESOLUCION')? 'rgba(237, 12, 12, 0.5)': (audiencia.DEN_ESTADO_ACTO_PROCESAL == 'RESOLUCION DESCARGADA')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};
                        allAudiencias.push(objAudiencia);
                    }
                    return allAudiencias
                }, [])
            } 

            for (let i = 0; i < estados.length; i++) {
                estados[i]['y'] = estados[i]['data'][0]*1; //*100/this.audiencias.length*1 
            }
            series[0]['data'] = estados;
            return series;
        },
        seriesEstadoInst(){
            var series =[];
            /******** MAPEAR INSTANCIAS */

            var instancias = [];

            if(this.audiencias[0] != undefined){
                instancias = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.DEN_INSTANCIA_SIJ);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.DEN_INSTANCIA_SIJ);
                    }
                    return allAudiencias
                }, [])
            }   
            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_ACTO_PROCESAL);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_ACTO_PROCESAL, data:[], color: ((audiencia.DEN_ESTADO_ACTO_PROCESAL == 'PROYECTO DE RESOLUCION')? 'rgba(237, 12, 12, 0.5)': (audiencia.DEN_ESTADO_ACTO_PROCESAL == 'RESOLUCION DESCARGADA')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia['data'][i] = 1
                            }else{
                                objAudiencia['data'][i] = 0
                            }
                        }
                        allAudiencias.push(objAudiencia);
                    }
                    return allAudiencias
                }, [])
            } 
            return series;
        },

        seriesEspecialista(){
            var series =[];
            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    if(audiencia.NOM_USUARIO_SECRETARIO != null){
                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.NOM_USUARIO_SECRETARIO);
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud]['data'][0]++
                        }
                        else {
                            let objAudiencia = {name:audiencia.NOM_USUARIO_SECRETARIO, data:[1]};
                            allAudiencias.push(objAudiencia);
                        }
                    } else{
                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf('Pendientes / Anulados');
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud]['data'][0]++
                        }
                        else {
                            let objAudiencia = {name:'Pendientes / Anulados', data:[1]};
                            allAudiencias.push(objAudiencia);
                        }
                    }
                    
                    return allAudiencias
                }, [])
            } 
            return series;
        },

        seriesEspEstado(){
            var series =[];
            /******** MAPEAR INSTANCIAS */

            var especialistas = [];

            if(this.audiencias[0] != undefined){
                especialistas = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.NOM_USUARIO_SECRETARIO);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.NOM_USUARIO_SECRETARIO);
                    }
                    return allAudiencias
                }, [])
            }   
            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_ACTO_PROCESAL);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < especialistas.length; i++) {
                            if(audiencia.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_ACTO_PROCESAL, data:[], color: ((audiencia.DEN_ESTADO_ACTO_PROCESAL == 'PROYECTO DE RESOLUCION')? 'rgba(237, 12, 12, 0.5)': (audiencia.DEN_ESTADO_ACTO_PROCESAL == 'RESOLUCION DESCARGADA')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

                        for (let i = 0; i < especialistas.length; i++) {
                            if(audiencia.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                objAudiencia['data'][i] = 1
                            }else{
                                objAudiencia['data'][i] = 0
                            }
                        }
                        allAudiencias.push(objAudiencia);
                    }
                    return allAudiencias
                }, [])
            } 
            return series;
        },

        seriesSankey(){
            var series =[{keys: ['from', 'to', 'weight'], data:[]}];

            var sankey = [];
            if(this.audiencias[0] != undefined){
                sankey = this.audiencias.reduce(function (allAudiencias, audiencia) {
                        let indexOfAud = allAudiencias.map(function(e) {  return e[0]+e[1]; }).indexOf('Proy: '+audiencia.NOM_USUARIO_PROYECTO+'Desc:'+audiencia.NOM_USUARIO_DESCARGO);
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud][2] = allAudiencias[indexOfAud][2]*1 +1
                        }
                        else {
                            let arrAudiencia = ['Proy: '+audiencia.NOM_USUARIO_PROYECTO,'Desc:'+audiencia.NOM_USUARIO_DESCARGO, 1];
                            allAudiencias.push(arrAudiencia);
                        }
                    
                    return allAudiencias
                }, [])
            } 
            series[0]['data'] = sankey;


            return series;
        },

        seriesHitos(){
            var series =[{name:'# de resoluciones', data:[]}];
            var estados =[];
            if(this.audiencias[0] != undefined){
                estados = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    if(audiencia.DEN_ACTO_PROCESAL_HITO != null && audiencia.DEN_ACTO_PROCESAL_HITO != '-'){

                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ACTO_PROCESAL_HITO);
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud]['data'][0]++
                        }
                        else {
                            let objAudiencia = {name:audiencia.DEN_ACTO_PROCESAL_HITO, data:[1]};
                            allAudiencias.push(objAudiencia);
                        }
                    }
                    return allAudiencias
                }, [])
            } 

            for (let i = 0; i < estados.length; i++) {
                estados[i]['y'] = estados[i]['data'][0]*1; //*100/this.audiencias.length*1 
            }
            series[0]['data'] = estados;
            return series;
        },
        seriesHitosInst(){
            var series =[];
            /******** MAPEAR INSTANCIAS */

            var instancias = [];

            if(this.audiencias[0] != undefined){
                instancias = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.DEN_INSTANCIA_SIJ);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.DEN_INSTANCIA_SIJ);
                    }
                    return allAudiencias
                }, [])
            }   
            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    if(audiencia.DEN_ACTO_PROCESAL_HITO != null && audiencia.DEN_ACTO_PROCESAL_HITO != '-'){

                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ACTO_PROCESAL_HITO);
                        if (indexOfAud != -1) {
                            for (let i = 0; i < instancias.length; i++) {
                                if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                    allAudiencias[indexOfAud]['data'][i]++
                                }
                            }
                        }
                        else {
                            let objAudiencia = {name:audiencia.DEN_ACTO_PROCESAL_HITO, data:[]};
                            for (let i = 0; i < instancias.length; i++) {
                                if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                    objAudiencia['data'][i] = 1
                                }else{
                                    objAudiencia['data'][i] = 0
                                }
                            }
                            allAudiencias.push(objAudiencia);
                        }
                    } 
                    return allAudiencias
                }, [])
            } 
            return series;
        },
        
    },
    
    
    methods: {
        getResoluciones(c_provincia, c_sede, c_org_jurisd, c_especialidad, c_instancia, f_inicio, f_final){

            this.params['c_provincia'] = c_provincia;
            this.params['c_sede'] = c_sede;
            this.params['c_org_jurisd'] = c_org_jurisd;
            this.params['c_especialidad'] = c_especialidad;
            this.params['c_instancia'] = c_instancia;
            this.params['f_inicio'] = f_inicio;
            this.params['f_final'] = f_final;

            let url = '../consultasSIJ/listarResolucionesrsij?c_provincia=' + c_provincia + 
                                                                                    '&c_sede=' + c_sede + 
                                                                                    '&c_org_jurisd=' + c_org_jurisd + 
                                                                                    '&c_especialidad=' + c_especialidad +
                                                                                    '&c_instancia=' + c_instancia + 
                                                                                    '&f_ini=' + this.parseDate(f_inicio) + 
                                                                                    '&f_fin=' + this.parseDate(f_final);

            this.showLoad('grey darken-2', 'Realizando consulta');
            this.audiencias = [];
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.audiencias = response.data['data'];
                        
                        this.nombre_archivo = 'Resoluciones_' +  c_instancia + '_' + c_especialidad + '_' +this.parseDate(f_inicio)+' - '+this.parseDate(f_final)+'.xls'
                        this.json_data = this.audiencias;

                        this.json_fields = {
                            'Expediente': 'NUM_EXPEDIENTE',
                            'Instancia': 'DEN_INSTANCIA_SIJ',
                            'Secretario': 'NOM_USUARIO_SECRETARIO',
                            'Acto Procesal': 'DEN_TIPO_ACTO_PROCESAL',
                            'N° Acto P.': 'NUM_RESOL_PROYECTO',
                            'Estado Acto Procesal': 'DEN_ESTADO_ACTO_PROCESAL',
                            'Usuario Proyecto': 'NOM_USUARIO_PROYECTO',
                            'Fecha Res. Proyecto': {
                                field: 'FECHA_RESOL_PROYECTO',
                                callback: value => {
                                    return `${value.substr(0,19)}`
                                }
                            },
                            'Usuario Descarga': 'NOM_USUARIO_DESCARGO',
                            'Fecha Res. Descargo': {
                                field: 'FECHA_RESOL_DESCARGO',
                                callback: value => {
                                    return `${value.substr(0,19)}`
                                }
                            },
                            'Hito': 'DEN_ACTO_PROCESAL_HITO',
                        };


                        this.showSnack('success', this.audiencias.length + ' resultados obtenidos' )
                    } else{
                        this.audiencias = []
                        
                        this.showSnack('warning',response.data['message'] )
                    }
                    this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    if (errors.response.status === 500){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    this.showLoad('','', false);
                });

        },
        viewDetails(item){


            let url = '../consultasSIJ/getResolucion?c_provincia=' + this.params.c_provincia + 
                                                                                    '&c_sede=' + this.params.c_sede + 
                                                                                    '&c_org_jurisd=' + this.params.c_org_jurisd + 
                                                                                    '&c_especialidad=' + this.params.c_especialidad +
                                                                                    '&c_instancia=' + this.params.c_instancia + 
                                                                                    '&f_ini=' + this.parseDate(this.params.f_inicio) + 
                                                                                    '&f_fin=' + this.parseDate(this.params.f_final) +
                                                                                    '&f_res_proy=' + item.FECHA_RESOL_PROYECTO +
                                                                                    '&n_unico=' + item.COD_UNICO_EXPEDIENTE;

            this.showLoad('grey darken-2', 'Realizando consulta');
            this.audiencia = [];
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.activeItem = response.data['data'][0];
                        this.d_resolucion = true
                    } else{
                        this.activeItem = []
                        this.showSnack('warning',response.data['message'] )
                    }
                    this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    if (errors.response.status === 500){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    
                    this.showLoad('','', false);
                });
        },
        cDrawer(val){
            if(val != undefined ){
                this.drawer = val;
            } else{
                this.drawer = !(this.drawer);
            }
         },
        showSnack(color, text, show = true){
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
        },
        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
        getColorEstado(estado){
            console.log(estado);
            let color = 'rgba(38, 25, 12, 0.3)';
            switch(estado){
               case 'PENDIENTE': color='rgba(186,60,61,.9)'; break;
               case 'ATENDIDO': color='rgba(63,191,127,.9)'; break;
            }
            return color;
        },
        formatDate (date) {
            if (!date) return null

            const [year, month, day] = date.split('-')
            return `${day}/${month}/${year}`
        },
        parseDate (date) {
            if (!date) return null
                const [day, month, year] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
        },
    }

}
</script>