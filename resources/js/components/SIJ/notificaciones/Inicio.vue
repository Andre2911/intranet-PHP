<template>
    <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
      <v-main id="contentApp">

        <v-card>
            <v-toolbar color="grey" dark>
               <v-toolbar-title>SIJ - Consulta de Notificaciones</v-toolbar-title>
               <v-spacer/>
            </v-toolbar>
            <v-card-text>
                    <sij-parametros :type="'Notificaciones'" :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @getData="getNotificaciones"/>
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
                                            <highcharts :options="chartEspInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartNotiTipo" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartNotiTipoInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>

                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartNotiDir" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartNotiDirInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>

                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartNotiEst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartNotiEstInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>

                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartNotiEsp" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartSecEstado" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
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
            menu: 4,
            submenu: -1,
            modulo: 'SIJ',
            drawer:true,
            
            audiencias:[],
            tab: null,
            filters:{
                NOM_USUARIO_SECRETARIO: [],
                DEN_ESTADO_ACTO_PROCESAL: [],
            },
            headers:[
               {text: 'Periodo', value: 'PERIODO'},
               {text: 'Instancia', value: 'DEN_INSTANCIA_SIJ'},
               {text: 'Secretario', value: 'NOM_USUARIO_SECRETARIO'},
               {text: 'Especialidad', value: 'DEN_ESPECIALIDAD'},
               {text: 'Tipo de Notificación', value: 'TIPO_NOTIFICACION'},
               {text: 'Tipo de Dirección Notificación', value: 'DEN_TIPO_DIR_NOTIFICACION'},
               {text: 'Estado Notificación', value: 'DEN_ESTADO_NOTIFICACION'},
               {text: 'Cantidad de Not.', value: 'CANT_NOTIFICACIONES'},
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
                    title: {  text: 'CANT. DE NOTIFICACIONES POR INSTANCIA'  },
                    xAxis: {
                        categories: this.catInstancias,
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de notificaciones'
                        },
                    },
                    series: this.seriesInstancia,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                            },
                        }
                    },
            }
        },

        chartEspInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE NOTIFICACIONES POR ESPECIALIDAD E INSTANCIA' },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de notificaciones'
                        }
                    },
                    series: this.seriesEspInst,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            },
                        }
                    },
              }
        },
        
        chartNotiTipo() { 
            return {
                    chart: {  
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {  text: '% DE NOTIFICACIONES SEGÚN TIPO DE NOTIFICACION' },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '% notificaciones segun tipo'
                        }
                    },
                    series: this.seriesTipo,
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
        chartNotiDir() { 
            return {
                    chart: {  
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {  text: '% DE NOTIFICACIONES SEGÚN TIPO DE DIRECCION' },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '% notificaciones segun tipo de dirección'
                        }
                    },
                    series: this.seriesDir,
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
        chartNotiEst() { 
            return {
                    chart: {  
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {  text: '% DE NOTIFICACIONES SEGÚN ESTADO' },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '% notificaciones segun estado'
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
        chartNotiTipoInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE NOTIFICACIONES POR TIPO E INSTANCIAS' },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de notificaciones'
                        }
                    },
                    series: this.seriesTipoInst,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            },
                        }
                    },
              }
        },
        chartNotiDirInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE NOTIFICACIONES POR TIPO E INSTANCIAS' },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de notificaciones'
                        }
                    },
                    series: this.seriesDirInst,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            },
                        }
                    },
              }
        },
        chartNotiEstInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE NOTIFICACIONES POR ESTADO E INSTANCIAS' },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de notificaciones'
                        }
                    },
                    series: this.seriesEstInst,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            },
                        }
                    },
              }
        },
        chartNotiEsp() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: '# DE NOTIFICACIONES REALIZADAS POR ESPECIALISTA'  },
                    xAxis: {
                        categories: this.catEspecialistas
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de notificaciones'
                        }
                    },
                    series: this.seriesEspecialista,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                            },
                        }
                    },
              }
        },
        chartSecEstado() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: '# DE NOTIFICACIONES POR ESTADO Y ESPECIALISTA' },
                    xAxis: {
                        categories: this.catEspecialistas
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de notificaciones'
                        }
                    },
                    series: this.seriesSecEstado,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            },
                        }
                    },
              }
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
        seriesInstancia(){
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf('Notificaciones');
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i] = allAudiencias[indexOfAud]['data'][i]*1 +audiencia.CANT_NOTIFICACIONES*1
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:'Notificaciones', data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia['data'][i] = audiencia.CANT_NOTIFICACIONES
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
        seriesTipo(){
            var series =[{name:'# de notificaciones', data:[]}];
            var estados =[];
            if(this.audiencias[0] != undefined){
                estados = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.TIPO_NOTIFICACION);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0] = allAudiencias[indexOfAud]['data'][0]*1 + audiencia.CANT_NOTIFICACIONES*1
                    }
                    else {
                        let objAudiencia = {name:audiencia.TIPO_NOTIFICACION, data:[audiencia.CANT_NOTIFICACIONES]};
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
        seriesEstado(){
            var series =[{name:'# de notificaciones', data:[]}];
            var estados =[];
            if(this.audiencias[0] != undefined){
                estados = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_NOTIFICACION);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0] = allAudiencias[indexOfAud]['data'][0]*1 +  audiencia.CANT_NOTIFICACIONES*1
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_NOTIFICACION, data:[audiencia.CANT_NOTIFICACIONES]};
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
        seriesEspInst(){
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESPECIALIDAD);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i] = allAudiencias[indexOfAud]['data'][i]*1 +audiencia.CANT_NOTIFICACIONES*1
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESPECIALIDAD, data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia['data'][i] = audiencia.CANT_NOTIFICACIONES*1
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
        seriesDir(){
            var series =[{name:'# de notificaciones', data:[]}];
            var estados =[];
            if(this.audiencias[0] != undefined){
                estados = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_TIPO_DIR_NOTIFICACION);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0] = allAudiencias[indexOfAud]['data'][0]*1 + audiencia.CANT_NOTIFICACIONES*1
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_TIPO_DIR_NOTIFICACION, data:[audiencia.CANT_NOTIFICACIONES*1]};
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
        seriesTipoInst(){
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.TIPO_NOTIFICACION);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i] = allAudiencias[indexOfAud]['data'][i]*1 +audiencia.CANT_NOTIFICACIONES*1
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.TIPO_NOTIFICACION, data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia['data'][i] =  audiencia.CANT_NOTIFICACIONES*1
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
        seriesDirInst(){
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_TIPO_DIR_NOTIFICACION);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i] = allAudiencias[indexOfAud]['data'][i]*1 +audiencia.CANT_NOTIFICACIONES*1
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_TIPO_DIR_NOTIFICACION, data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia['data'][i] =  audiencia.CANT_NOTIFICACIONES*1
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
        seriesEstInst(){
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_NOTIFICACION);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i] = allAudiencias[indexOfAud]['data'][i]*1 +audiencia.CANT_NOTIFICACIONES*1
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_NOTIFICACION, data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia['data'][i] =  audiencia.CANT_NOTIFICACIONES*1
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
            /******** MAPEAR SECRETARIO */
            var secretarios = [];
            if(this.audiencias[0] != undefined){
                secretarios = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.NOM_USUARIO_SECRETARIO);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.NOM_USUARIO_SECRETARIO);
                    }
                    return allAudiencias
                }, [])
            }   

            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf('Notificaciones');
                    if (indexOfAud != -1) {
                        for (let i = 0; i < secretarios.length; i++) {
                            if(audiencia.NOM_USUARIO_SECRETARIO == secretarios[i]){
                                allAudiencias[indexOfAud]['data'][i] = allAudiencias[indexOfAud]['data'][i]*1 +audiencia.CANT_NOTIFICACIONES*1
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:'Notificaciones', data:[]};

                        for (let i = 0; i < secretarios.length; i++) {
                            if(audiencia.NOM_USUARIO_SECRETARIO == secretarios[i]){
                                objAudiencia['data'][i] = audiencia.CANT_NOTIFICACIONES*1
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

        seriesSecEstado(){
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_NOTIFICACION);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < especialistas.length; i++) {
                            if(audiencia.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                allAudiencias[indexOfAud]['data'][i] = allAudiencias[indexOfAud]['data'][i]*1 +audiencia.CANT_NOTIFICACIONES*1
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_NOTIFICACION, data:[]};

                        for (let i = 0; i < especialistas.length; i++) {
                            if(audiencia.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                objAudiencia['data'][i] = audiencia.CANT_NOTIFICACIONES*1
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
        
    },
    
    
    methods: {
        getNotificaciones(c_provincia, c_sede, c_org_jurisd, c_especialidad, c_instancia, f_inicio, f_final){
            let url = '../consultasSIJ/listarNotificaciones?c_provincia=' + c_provincia + 
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
                        this.nombre_archivo = 'Notificaciones_' + c_instancia + '_' + c_especialidad + '_' +this.parseDate(f_inicio)+' - '+this.parseDate(f_final)+'.xls'
                        this.json_data = this.audiencias;

                        this.json_fields = {
                            'Periodo': 'PERIODO',
                            'Instancia': 'DEN_INSTANCIA_SIJ',
                            'Secretario': 'NOM_USUARIO_SECRETARIO',
                            'Especialidad': 'DEN_ESPECIALIDAD',
                            'Tipo de Notificación': 'TIPO_NOTIFICACION',
                            'Tipo de Dirección Notificación': 'DEN_TIPO_DIR_NOTIFICACION',
                            'Estado Notificación': 'DEN_ESTADO_NOTIFICACION',
                            'Cantidad de Not.':'CANT_NOTIFICACIONES',
                        };

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