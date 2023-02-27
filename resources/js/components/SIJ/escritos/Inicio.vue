<template>
    <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
      <v-main id="contentApp">

        <v-card>
            <v-toolbar color="grey" dark>
               <v-toolbar-title>SIJ - Consulta de Escritos</v-toolbar-title>
               <v-spacer/>
            </v-toolbar>
            <v-card-text>
                    <sij-parametros :type="'Escritos'" :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @getData="getEscritos"/>
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
                                        <v-col cols="12" md="12">
                                            <highcharts :options="chartDataTime" ref="spline" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartOptions" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartParte" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartEscritosEstado" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartEscEstInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartEscritosEsp" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartEspEstado" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="12">
                                            <highcharts :options="chartEspAteTime" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="12">
                                            <highcharts :options="chartEspPendTime" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartMP" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartUsuarioMP" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
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
            v-model="d_escrito"
        >
            <sij-escritos-view :activeItem='activeItem' :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @close="d_escrito = false"/>
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
            menu: 2,
            submenu: -1,
            modulo: 'SIJ',
            drawer:true,
            audiencias:[],
            tab: null,
            d_escrito:false,
            filters:{
                PERIODO: [],
                NOM_USUARIO_SECRETARIO: [],
                DEN_TIPO_PARTE: [],
                DEN_TIPO_ESCRITO: [],
                DEN_ESTADO_ESCRITO: [],
                ESTADO_DIG_ESCRITO: [],
            },
            headers:[
               {text: 'Periodo', value: 'PERIODO'},
               {text: 'Expediente', value: 'NUM_EXPEDIENTE'},
               {text: 'Instancia', value: 'DEN_INSTANCIA_SIJ'},
               {text: 'Secretario', value: 'NOM_USUARIO_SECRETARIO'},
               {text: 'Documento', value: 'DEN_TIPO_ESCRITO'},
               {text: 'F. Ingreso', value: 'FECHA_INGRESO_ESCRITO', type:'datetime'},
               {text: 'Tipo Parte', value: 'DEN_TIPO_PARTE'},
               {text: 'Estado escrito', value: 'DEN_ESTADO_ESCRITO'},
               {text: 'F. asociación', value: 'FECHA_ASOC_DOC_DIGIT_ESCRITO'},
               {text: 'Dig. MP', value: 'ESTADO_DIG_ESCRITO'},
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

        chartDataTime(){
            return {
                chart: {
                    zoomType: 'x'
                },
                title: {  text: 'Escritos'  },
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: { // don't display the dummy year
                        month: '%e. %b',
                        year: '%b'
                    },
                    title: {
                        text: 'Fecha'
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Cantidad de Escritos'
                    },
                    labels: {
                        overflow: 'justify'
                    }
                },
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%e. %b}: {point.y} Escritos'
                },
                series: this.seriesPeriodo,
                plotOptions: {
                    series: {
                        marker: {
                            enabled: true
                        }
                    }
                },
                colors: ['#C62828','#6CF', '#39F', '#06C', '#036', '#000', '#B38','#4DD0E','#00838F'],

            }
        }, 
        chartOptions() { 
            return {
                    chart: {  type: 'bar'},
                    title: {  text: 'CANT. DE ESCRITOS INGRESADOS POR INSTANCIAS'  },
                    xAxis: {
                        categories: this.catInstancias,
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
                        },
                    },
                    series: this.seriesInstancia,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal'
                        }
                    },
                    
            }
        },
        chartParte() { 
            return {
                    chart: {  type: 'bar'},
                    title: {  text: 'CANT. DE ESCRITOS INGRESADOS POR TIPO DE PARTE'  },
                    xAxis: {
                        categories: ['Tipo Parte'],
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    series: this.seriesParte,
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
        chartEscritosEstado() { 
            return {
                    chart: {  
                        type: 'pie',
                        options3d: {
                            enabled: true,
                            alpha: 45,
                            beta: 0
                        }
                    },
                    title: {  text: '% DE ESCRITOS SEGÚN ESTADO' },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '% escritos segun estado'
                        }
                    },
                    series: this.seriesEstado,
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            depth: 35,
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
                    title: {  text: 'CANT. DE ESCRITOS SEGÚN ESTADO POR INSTANCIAS' },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
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
                    title: {  text: 'CANT. DE ESCRITOS INGRESADOS POR SECRETARIO'  },
                    xAxis: {
                        categories: ['Especialistas']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
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
                    title: {  text: 'CANT. DE ESCRITOS POR ESTADO Y ESPECIALISTA' },
                    xAxis: {
                        categories: this.catEspecialistas
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
                        }
                    },
                    series: this.seriesEspEstado,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            }
                        }
                    },
              }
        },
        chartEspAteTime() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'Escritos atendidos por Usuario Respuesta' },
                    xAxis: {
                        categories: this.catUsuarioRpta
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
                        }
                    },
                    series: this.seriesEspAtendidos,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            }
                        }
                    },
              }
        },
        chartEspPendTime() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'Escritos Pendientes por Secretario' },
                    xAxis: {
                        categories: this.catEspecialistas
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
                        }
                    },
                    series: this.seriesEspPendientes,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            }
                        }
                    },
              }
        },
        chartMP() { 
            return {
                    chart: {  
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {  text: '% DE ESCRITOS INGRESADOS POR TIPO DE USUARIO DE MESA DE PARTES' },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '% escritos segun estado'
                        }
                    },
                    series: this.seriesMP,
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
        chartUsuarioMP() { 
            return {
                    chart: {  type: 'bar'},
                    title: {  text: 'CANT. DE ESCRITOS INGRESADOS POR USUARIO DE MESA DE PARTES'  },
                    xAxis: {
                        categories: this.catUsuarioMP,
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de escritos'
                        },
                    },
                    series: this.seriesUsuarioMP,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                            }
                        }
                    },
            }
        },

        seriesPeriodo(){
            var series =[];
            var dataSeries = [];
            var instancias = [];

            if(this.audiencias[0] != undefined){

                instancias = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.DEN_INSTANCIA_SIJ);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.DEN_INSTANCIA_SIJ);
                    }
                    return allAudiencias
                }, [])


                dataSeries = this.audiencias.reduce(function (allEscritos, escrito) {
                    var date = escrito.FECHA_INGRESO_ESCRITO.substr(0,10);
                    const [year, month, day] = date.split('-')

                    var dateUTC = Date.UTC(year, month-1, day)
                    
                    let indexOfAud = allEscritos.map(function(e) { return e[0]; }).indexOf(dateUTC);
                    if (indexOfAud == -1) {
                        allEscritos.push([dateUTC, 1]);
                    } else{
                        allEscritos[indexOfAud][1]++
                    }
                    return allEscritos
                }, [])

                series.push({name: 'Escritos', data: dataSeries});

                if(instancias.length > 1){
                    instancias.forEach(element => {
                        var dataSeriesI = []
                        dataSeriesI = this.audiencias.reduce(function (allEscritos, escrito) {
                            var date = escrito.FECHA_INGRESO_ESCRITO.substr(0,10);
                            const [year, month, day] = date.split('-')
                            var dateUTC = Date.UTC(year, month-1, day)
                            let indexOfAud = allEscritos.map(function(e) { return e[0]; }).indexOf(dateUTC);
                            if (indexOfAud != -1) {
                                if(escrito.DEN_INSTANCIA_SIJ == element){
                                    allEscritos[indexOfAud][1]++
                                }
                            }
                            else {
                                if(escrito.DEN_INSTANCIA_SIJ == element){
                                    allEscritos.push([dateUTC, 1]);
                                }
                            }
                            return allEscritos
                        }, [])

                        series.push({name: element, data: dataSeriesI});


                    });
                }
                




            }   

            
            return series;
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf('Escritos');
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:'Escritos', data:[]};

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
        catUsuarioRpta(){
            var catAud =[];

            if(this.audiencias[0] != undefined){
                catAud = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.COD_USUARIO_RESPUESTA);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.COD_USUARIO_RESPUESTA);
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
        catUsuarioMP(){
            var catAud =[];

            if(this.audiencias[0] != undefined){
                catAud = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.NOM_USUARIO_REG_ESCRITO);
                    if (indexOfAud == -1) {
                        if (audiencia.NOM_USUARIO_REG_ESCRITO == null){
                            allAudiencias.push('Escritos Sin digitalizar');
                        } else{
                            allAudiencias.push(audiencia.NOM_USUARIO_REG_ESCRITO);
                        }
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_ESCRITO);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0]++
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_ESCRITO, data:[1], color: ((audiencia.DEN_ESTADO_ESCRITO == 'PENDIENTE')? 'rgba(237, 12, 12, 0.5)': (audiencia.DEN_ESTADO_ESCRITO == 'ATENDIDO')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_ESCRITO);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_ESCRITO, data:[], color: ((audiencia.DEN_ESTADO_ESCRITO == 'PENDIENTE')? 'rgba(237, 12, 12, 0.5)': (audiencia.DEN_ESTADO_ESCRITO == 'ATENDIDO')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_ESCRITO);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < especialistas.length; i++) {
                            if(audiencia.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_ESCRITO, data:[], color: ((audiencia.DEN_ESTADO_ESCRITO == 'PENDIENTE')? 'rgba(237, 12, 12, 0.5)': (audiencia.DEN_ESTADO_ESCRITO == 'ATENDIDO')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

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

        seriesEspAtendidos(){
            var series =[];
            /******** MAPEAR ESPECIALISTAS */
            var especialistas = [];
            if(this.audiencias[0] != undefined){
                especialistas = this.audiencias.reduce(function (allEscritos, escrito) {
                    let indexOfAud = allEscritos.map(function(e) { return e; }).indexOf(escrito.COD_USUARIO_RESPUESTA);
                    if (indexOfAud == -1) {
                        allEscritos.push(escrito.COD_USUARIO_RESPUESTA);
                    }
                    return allEscritos
                }, [])
            }   
            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allEscritos, escrito) {

                    if(escrito.DEN_ESTADO_ESCRITO == 'ATENDIDO'){
                        /******* CLASIFICAMOS LA DEMORA */
                        let retraso = escrito.NUM_DIAS_RESPUESTA;
                        /*
                            0  - 10 
                            11 - 20
                            21 - 30
                            31 - 00
                        */
                       let t_retraso = '';
                       let c_retraso = '';
                       if(retraso <= 5){
                           t_retraso = '0 - 5 días';
                           c_retraso = 'rgba(0, 191, 165, 0.4)';
                       } else if(retraso <= 10){
                           t_retraso = '5 - 10 días';
                           c_retraso = 'rgba(12, 237, 12, 0.4)';
                       } else if(retraso <= 20){
                           t_retraso = '11 - 20 días';
                           c_retraso = 'rgba(243, 255, 51, 0.5)';
                       } else if(retraso <= 30){
                           t_retraso = '21 - 30 días';
                           c_retraso = 'rgba(255, 175, 51, 0.5)';
                       } else{
                           t_retraso = '31 a más';
                           c_retraso = 'rgba(237, 12, 12, 0.5)';
                       }

                        let indexOfAud = allEscritos.map(function(e) { return e.name; }).indexOf(t_retraso);
                        if (indexOfAud != -1) {
                            for (let i = 0; i < especialistas.length; i++) {
                                if(escrito.COD_USUARIO_RESPUESTA == especialistas[i]){
                                    allEscritos[indexOfAud]['data'][i]++
                                }
                            }
                        }
                        else {
                            let objAudiencia = {name:t_retraso, data:[], color: c_retraso};

                            for (let i = 0; i < especialistas.length; i++) {
                                if(escrito.COD_USUARIO_RESPUESTA == especialistas[i]){
                                    objAudiencia['data'][i] = 1
                                }else{
                                    objAudiencia['data'][i] = 0
                                }
                            }
                            allEscritos.push(objAudiencia);
                        }
                    }
                        return allEscritos
                    
                }, [])
            } 
            return series;
        },

        seriesEspPendientes(){
            var series =[];
            /******** MAPEAR ESPECIALISTAS */
            var f_today = new Date();

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

                    if(audiencia.DEN_ESTADO_ESCRITO == 'PENDIENTE'){

                        /******* CLASIFICAMOS LA DEMORA */

                        let f_ingreso = new Date(audiencia.FECHA_INGRESO_ESCRITO);

                        let resta = f_today.getTime() - f_ingreso.getTime();
                        let retraso = Math.round(resta/ (1000*60*60*24));
                        
                        /*
                            0  - 10 
                            11 - 20
                            21 - 30
                            31 - 00
                        */
                       let t_retraso = '';
                       let c_retraso = '';
                       if(retraso <= 10){
                           t_retraso = '0 - 10 días';
                           c_retraso = 'rgba(12, 237, 12, 0.4)';
                       } else if(retraso <= 20){
                           t_retraso = '11 - 20 días';
                           c_retraso = 'rgba(243, 255, 51, 0.5)';
                       } else if(retraso <= 30){
                           t_retraso = '21 - 30 días';
                           c_retraso = 'rgba(255, 175, 51, 0.5)';
                       } else{
                           t_retraso = '31 a más';
                           c_retraso = 'rgba(237, 12, 12, 0.5)';
                       }

                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(t_retraso);
                        if (indexOfAud != -1) {
                            for (let i = 0; i < especialistas.length; i++) {
                                if(audiencia.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                    allAudiencias[indexOfAud]['data'][i]++
                                }
                            }
                        }
                        else {
                            let objAudiencia = {name:t_retraso, data:[], color: c_retraso};

                            for (let i = 0; i < especialistas.length; i++) {
                                if(audiencia.NOM_USUARIO_SECRETARIO == especialistas[i]){
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

        seriesMP(){
            var series =[{name:'# de escritos', data:[]}];
            var estados =[];
            if(this.audiencias[0] != undefined){
                estados = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_VENT_REG_ESCRITO);
                    if (indexOfAud != -1) {
                        allAudiencias[indexOfAud]['data'][0]++
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_VENT_REG_ESCRITO, data:[1]};
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


        seriesUsuarioMP(){
            var series =[];
            /******** MAPEAR INSTANCIAS */
            var usuarios = [];
            if(this.audiencias[0] != undefined){
                usuarios = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.NOM_USUARIO_REG_ESCRITO);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.NOM_USUARIO_REG_ESCRITO);
                    }
                    return allAudiencias
                }, [])
            }   

            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf('Escritos');
                        if (indexOfAud != -1) {
                            for (let i = 0; i < usuarios.length; i++) {
                                if(audiencia.NOM_USUARIO_REG_ESCRITO == usuarios[i]){
                                    allAudiencias[indexOfAud]['data'][i]++
                                }
                            }
                        }
                        else {
                            let objAudiencia = {name:'Escritos', data:[]};

                            for (let i = 0; i < usuarios.length; i++) {
                                if(audiencia.NOM_USUARIO_REG_ESCRITO == usuarios[i]){
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
    },
    methods: {
        getEscritos(c_provincia, c_sede, c_org_jurisd, c_especialidad, c_instancia, f_inicio, f_final){

            this.params['c_provincia'] = c_provincia;
            this.params['c_sede'] = c_sede;
            this.params['c_org_jurisd'] = c_org_jurisd;
            this.params['c_especialidad'] = c_especialidad;
            this.params['c_instancia'] = c_instancia;
            this.params['f_inicio'] = f_inicio;
            this.params['f_final'] = f_final;


            let url = '../consultasSIJ/listarEscritos?c_provincia=' + c_provincia + 
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

                        this.nombre_archivo = 'Escritos_' + c_instancia + '_' + c_especialidad + '_' +this.parseDate(f_inicio)+' - '+this.parseDate(f_final)+'.xls'
                        this.json_data = this.audiencias;

                        this.json_fields = {
                            'Periodo': 'PERIODO',
                            'Expediente': 'NUM_EXPEDIENTE',
                            'Instancia': 'DEN_INSTANCIA_SIJ',
                            'Secretario': 'NOM_USUARIO_SECRETARIO',
                            'Documento': 'DEN_TIPO_ESCRITO',
                            'F. Ingreso':  {
                                field: 'FECHA_INGRESO_ESCRITO',
                                callback: value => {
                                    return `${value.substr(0,19)}`
                                }
                            },
                            'Tipo Parte': 'DEN_TIPO_PARTE',
                            'Estado escrito':'DEN_ESTADO_ESCRITO',
                            'Días Respuesta': 'NUM_DIAS_RESPUESTA',
                            'F. asociación': {
                                field: 'FECHA_ASOC_DOC_DIGIT_ESCRITO',
                                callback: value => {
                                    return `${value.substr(0,19)}`
                                }
                            },
                            'Dig. MP': 'ESTADO_DIG_ESCRITO',
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


            let url = '../consultasSIJ/getEscrito?c_provincia=' + this.params.c_provincia + 
                                                    '&c_sede=' + this.params.c_sede + 
                                                    '&c_org_jurisd=' + this.params.c_org_jurisd + 
                                                    '&c_especialidad=' + this.params.c_especialidad +
                                                    '&c_instancia=' + this.params.c_instancia + 
                                                    '&f_ini=' + this.parseDate(this.params.f_inicio) + 
                                                    '&f_fin=' + this.parseDate(this.params.f_final) +
                                                    '&x_formato=' + item.NUM_EXPEDIENTE +
                                                    '&f_ing_escr=' + item.FECHA_INGRESO_ESCRITO +
                                                    '&f_dig_escr=' + item.FECHA_ASOC_DOC_DIGIT_ESCRITO +
                                                    '&n_unico=' + item.COD_UNICO_EXPEDIENTE;

            this.showLoad('grey darken-2', 'Realizando consulta');
            this.audiencia = [];
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.activeItem = response.data['data'][0];
                        this.d_escrito = true
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


            //console.log(item);
            //this.activeItem = item
            //this.d_escrito = true
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