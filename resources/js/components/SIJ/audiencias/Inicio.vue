<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">

            <v-card>
                <v-toolbar color="grey" dark>
                <v-toolbar-title>SIJ - Consulta de audiencias</v-toolbar-title>
                <v-spacer/>
                </v-toolbar>
                <v-card-text>
                    <sij-parametros :type="'Audiencias'" :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @getData="getAudiencias"/>
                    <v-row>
                        <v-col cols="12" md="12">

                            <v-tabs
                                v-model="tab"
                                fixed-tabs
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
                                            <highcharts :options="chartAudienciasInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartAudienciasTipo" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartAudienciasEsp" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartAudienciasEstado" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <highcharts :options="chartAudEstInst" ref="lineCharts" v-if="audiencias[0] != undefined"></highcharts>
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
            v-model="d_audiencia"
        >
            <sij-audiencias-view :activeItem='activeItem' :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @close="d_audiencia = false"/>
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
            menu: 1,
            submenu: -1,
            modulo: 'SIJ',
            drawer:true,
            audiencias:[],
            audiencia:{},
            tab: null,
            d_audiencia:false,
            filters:{
                NOM_USUARIO_SECRETARIO: [],
                DEN_ESTADO_AUDIENCIA: [],
                DEN_MOTIVO_AUDIENCIA: [],
                DEN_ESTADO_ACTIVIDAD: [],
            },
            headers:[
               {text: 'Periodo', value: 'PERIODO'},
               {text: 'Expediente', value: 'NUM_EXPEDIENTE'},
               {text: 'Instancia', value: 'DEN_INSTANCIA_SIJ'},
               {text: 'Secretario', value: 'NOM_USUARIO_SECRETARIO'},
               {text: 'Ini. Aud.', value: 'FECHA_INI_ACTIVIDAD', type:'datetime'},
               {text: 'Fin. Aud', value: 'FECHA_FIN_ACTIVIDAD', type:'datetime'},
               {text: 'Duración min.', value: 'DURACION_EN_MINUTOS'},
               {text: 'Denominación', value: 'DEN_AUDIENCIA'},
               {text: 'Estado Aud', value: 'DEN_ESTADO_AUDIENCIA'},
               {text: 'Estado Act', value: 'DEN_ESTADO_ACTIVIDAD'},
               {text: 'Motivo Aud.', value: 'DEN_MOTIVO_AUDIENCIA'},
               {text: 'Sumilla', value: 'SUMILLA_AUDIENCIA'},
               {text: 'Opciones',type:'opciones', value: 'view'}
            ],

            json_fields: {},
            json_data: [],
            nombre_archivo:'',
            n_paginas:1,
            itemsPerPage:1500,
            snack: {snackShow:false, snackText: '',snackColor: ''},
            dialogLoad:{show:false, text:'', color:''},
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
                    chart: {  type: this.modo},
                    title: {  text:  'CANT. DE AUDIENCIAS POR INSTANCIAS'  },
                    xAxis: {
                        categories: ['Instancias']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '# de audiencias por Instancia'
                        }
                    },
                    series: this.seriesDependencia,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y:.1f}'
                            }
                        }
                    },
                    /*plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },*/
              }
        },
        chartAudienciasInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE AUDIENCIAS POR INSTANCIAS Y TIPO DE AUDIENCIA'  },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de audiencias'
                        }
                    },
                    series: this.seriesAudienciasInst,
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
        chartAudienciasTipo() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE AUDIENCIAS POR TIPO DE AUDIENCIA'  },
                    xAxis: {
                        categories: this.catAudiencias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de audiencias'
                        }
                    },
                    series: this.seriesAudienciasTipo,
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                            },
                            stacking: 'normal',
                            
                        }
                    },
              }
        },
        chartAudienciasEsp() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE AUDIENCIAS POR ESPECIALISTA DE AUDIO'  },
                    xAxis: {
                        categories: ['Especialistas']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de audiencias'
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
        chartAudEstInst() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE AUDIENCIAS POR ESTADO E INSTANCIAS'  },
                    xAxis: {
                        categories: this.catInstancias
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '# de audiencias por estado e instancia'
                        }
                    },
                    series: this.seriesEstadoInst,
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
        chartAudienciasEstado() { 
            return {
                    chart: {  
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {  text: '% DE AUDIENCIAS POR ESTADO'  },
                    xAxis: {
                        categories: ['Estado']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '# audiencias segun estados'
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
        seriesDependencia(){
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
        catAudiencias(){
            var catAud =[];

            if(this.audiencias[0] != undefined){
                catAud = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.DEN_AUDIENCIA);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.DEN_AUDIENCIA);
                    }
                    return allAudiencias
                }, [])
            } 
            return catAud;
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
        seriesAudienciasTipo(){
            var series =[];
            /******** MAPEAR DEN_AUDIENCIAS */
            var audiencias = [];

            if(this.audiencias[0] != undefined){
                audiencias = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e; }).indexOf(audiencia.DEN_AUDIENCIA);
                    if (indexOfAud == -1) {
                        allAudiencias.push(audiencia.DEN_AUDIENCIA);
                    }
                    return allAudiencias
                }, [])
            } 

            if(this.audiencias[0] != undefined){
                series = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_INSTANCIA_SIJ);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < audiencias.length; i++) {
                            if(audiencia.DEN_AUDIENCIA == audiencias[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_INSTANCIA_SIJ, data:[]};

                        for (let i = 0; i < audiencias.length; i++) {
                            if(audiencia.DEN_AUDIENCIA == audiencias[i]){
                                objAudiencia.['data'][i] = 1
                            }else{
                                objAudiencia.['data'][i] = 0
                            }
                        }
                        allAudiencias.push(objAudiencia);
                    }
                    return allAudiencias
                }, [])
            } 
            return series;
        },
        seriesAudienciasInst(){
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_AUDIENCIA);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_AUDIENCIA, data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia.['data'][i] = 1
                            }else{
                                objAudiencia.['data'][i] = 0
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
                    if(audiencia.NOM_USUARIO_AUDIO != null){
                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.NOM_USUARIO_AUDIO);
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud]['data'][0]++
                        }
                        else {
                            let objAudiencia = {name:audiencia.NOM_USUARIO_AUDIO, data:[1]};
                            allAudiencias.push(objAudiencia);
                        }
                    } else{
                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf('Por atender / No atendidos');
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud]['data'][0]++
                        }
                        else {
                            let objAudiencia = {name:'Por atender / No atendidos', data:[1]};
                            allAudiencias.push(objAudiencia);
                        }
                    }
                    return allAudiencias
                }, [])
            } 
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
                    let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_AUDIENCIA);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                allAudiencias[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objAudiencia = {name:audiencia.DEN_ESTADO_AUDIENCIA, data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(audiencia.DEN_INSTANCIA_SIJ == instancias[i]){
                                objAudiencia.['data'][i] = 1
                            }else{
                                objAudiencia.['data'][i] = 0
                            }
                        }
                        allAudiencias.push(objAudiencia);
                    }
                    return allAudiencias
                }, [])

                let indexOfAud = series.map(function(e) { return e.name; }).indexOf('-');
                if(indexOfAud != -1){
                    series[indexOfAud]['name'] = 'Por atender / No atendidos';
                }

            } 
            return series;
        },
        seriesEstado(){
            var series =[{name:'# de audiencias', data:[]}];
            var estados =[];
            if(this.audiencias[0] != undefined){
                estados = this.audiencias.reduce(function (allAudiencias, audiencia) {
                    if(audiencia.DEN_ESTADO_AUDIENCIA != '-'){
                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf(audiencia.DEN_ESTADO_AUDIENCIA);
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud]['data'][0]++
                        }
                        else {
                            let objAudiencia = {name:audiencia.DEN_ESTADO_AUDIENCIA, data:[1]};
                            allAudiencias.push(objAudiencia);
                        }
                     } else{
                        let indexOfAud = allAudiencias.map(function(e) { return e.name; }).indexOf('Por atender / No atendidos');
                        if (indexOfAud != -1) {
                            allAudiencias[indexOfAud]['data'][0]++
                        }
                        else {
                            let objAudiencia = {name:'Por atender / No atendidos', data:[1]};
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
    },
    methods: {
        getAudiencias(c_provincia, c_sede, c_org_jurisd, c_especialidad, c_instancia, f_inicio, f_final){

            this.params['c_provincia'] = c_provincia;
            this.params['c_sede'] = c_sede;
            this.params['c_org_jurisd'] = c_org_jurisd;
            this.params['c_especialidad'] = c_especialidad;
            this.params['c_instancia'] = c_instancia;
            this.params['f_inicio'] = f_inicio;
            this.params['f_final'] = f_final;

            let url = '../consultasSIJ/listarAudiencias?c_provincia=' + c_provincia + 
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

                        this.filters.NUM_EXPEDIENTE= []
                        this.filters.NOM_USUARIO_SECRETARIO= []
                        this.filters.DEN_ESTADO_AUDIENCIA= []
                        this.filters.DEN_MOTIVO_AUDIENCIA= []

                        this.nombre_archivo = 'Audiencias_'+c_instancia + '_' + c_especialidad + '_' +this.parseDate(f_inicio)+' - '+this.parseDate(f_final)+'.xls'
                        this.json_data = this.audiencias;

                        this.json_fields = {
                            'Periodo': 'PERIODO',
                            'Expediente': 'NUM_EXPEDIENTE',
                            'Instancia': 'DEN_INSTANCIA_SIJ',
                            'Secretario': 'NOM_USUARIO_SECRETARIO',
                            'Ini. Aud.': {
                                field: 'FECHA_INI_ACTIVIDAD',
                                callback: value => {
                                    return `${value.substr(0,19)}`
                                }
                            },
                            'Fin. Aud.': {
                                field: 'FECHA_FIN_ACTIVIDAD',
                                callback: value => {
                                    return `${value.substr(0,19)}`
                                },
                            },
                            'Duración min': 'DURACION_EN_MINUTOS',
                            'Denominación': 'DEN_AUDIENCIA',
                            'Estado Audiencia':'DEN_ESTADO_AUDIENCIA',
                            'Estado Actividad': 'DEN_ESTADO_ACTIVIDAD',
                            'Motivo Audiencia': 'DEN_MOTIVO_AUDIENCIA',
                            'Sumilla': 'SUMILLA_AUDIENCIA',
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
            let url = '../consultasSIJ/getAudiencia?c_provincia=' + this.params.c_provincia + 
                                                                                    '&c_sede=' + this.params.c_sede + 
                                                                                    '&c_org_jurisd=' + this.params.c_org_jurisd + 
                                                                                    '&c_especialidad=' + this.params.c_especialidad +
                                                                                    '&c_instancia=' + this.params.c_instancia + 
                                                                                    '&f_ini=' + this.parseDate(this.params.f_inicio) + 
                                                                                    '&f_fin=' + this.parseDate(this.params.f_final) +
                                                                                    '&x_formato=' + item.NUM_EXPEDIENTE +
                                                                                    '&f_ini_aud=' + item.FECHA_INI_ACTIVIDAD +
                                                                                    '&f_fin_aud=' + item.FECHA_FIN_ACTIVIDAD;

            this.showLoad('grey darken-2', 'Realizando consulta');
            this.audiencia = [];
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.activeItem = response.data['data'][0];
                        this.d_audiencia = true
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


            //this.activeItem = item
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