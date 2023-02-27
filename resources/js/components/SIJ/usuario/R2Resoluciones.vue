<template>
    <div>
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
                    :allData="datos"
                    :filters="filters"
                    :n_paginas="n_paginas"
                    :itemsPerPage="itemsPerPage"
                    :tipo="2"
                    :dense="true"
                    @viewItem="viewDetails"
                />
            </v-tab-item>
            <v-tab-item
                key="stat"
            >
                <v-row>
                    <v-col cols="12" md="6">
                        <highcharts :options="chartOptions" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                    <v-col cols="12" md="6">
                        <highcharts :options="chartEscEstInst" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                    
                    <v-col cols="12" md="6">
                        <highcharts :options="chartResEstado" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                    <v-col cols="12" md="6">
                        <highcharts :options="chartEspEstado" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                    <v-col cols="12" md="6">
                        <highcharts :options="chartTipoRes" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                    <v-col cols="12" md="6">
                        <highcharts :options="chartSankey" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                    <v-col cols="12" md="6">
                        <highcharts :options="chartHito" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                    <v-col cols="12" md="6">
                        <highcharts :options="chartHitoInst" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                    </v-col>
                </v-row>
            </v-tab-item>
        </v-tabs-items>
        <v-dialog
            v-model="d_resolucion"
        >
            <sij-resoluciones-view :activeItem='activeItem' :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @close="d_resolucion = false"/>
        </v-dialog>
    </div>
</template>
<script>
import {Chart} from 'highcharts-vue'

export default {
    props:['datos', 'dataUser', 'f_inicio', 'f_final'],
    components: {
        highcharts: Chart 
    },
    data() {
        return {
            tab: null,
            d_resolucion:false,
            filters:{
                NOM_USUARIO_SECRETARIO: [],
                DEN_TIPO_ACTO_PROCESAL: [],
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
            n_paginas:1,
            itemsPerPage:1500,
            modo: 'bar',
            activeItem:{},

        }
    },
    computed:{
        filteredData() {
            if(this.datos[0] != undefined){
                return this.datos.filter(d => {
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
        chartTipoRes() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'CANT. DE RESOLUCIONES POR TIPO DE ACTO PROCESAL'  },
                    xAxis: {
                        categories: this.catActosProcesales
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Cantidad de resoluciones'
                        }
                    },
                    series: this.seriesActoProcesal,
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
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_INSTANCIA_SIJ);
                    if (indexOfDato != -1) {
                        allDatos[indexOfDato]['data'][0]++
                    }
                    else {
                        let objDato = {name:dato.DEN_INSTANCIA_SIJ, data:[1]};
                        allDatos.push(objDato);
                    }
                    return allDatos
                }, [])
            } 
            return series;
        },
        catInstancias(){
            var catAud =[];

            if(this.datos[0] != undefined){
                catAud = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e; }).indexOf(dato.DEN_INSTANCIA_SIJ);
                    if (indexOfDato == -1) {
                        allDatos.push(dato.DEN_INSTANCIA_SIJ);
                    }
                    return allDatos
                }, [])
            } 
            return catAud;
        },

        catActosProcesales(){
            var catAud =[];

            if(this.datos[0] != undefined){
                catAud = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e; }).indexOf(dato.DEN_TIPO_ACTO_PROCESAL);
                    if (indexOfDato == -1) {
                        allDatos.push(dato.DEN_TIPO_ACTO_PROCESAL);
                    }
                    return allDatos
                }, [])
            } 
            return catAud;
        },

        catEspecialistas(){
            var catAud =[];

            if(this.datos[0] != undefined){
                catAud = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e; }).indexOf(dato.NOM_USUARIO_SECRETARIO);
                    if (indexOfDato == -1) {
                        allDatos.push(dato.NOM_USUARIO_SECRETARIO);
                    }
                    return allDatos
                }, [])
            } 
            return catAud;
        },
        seriesParte(){
            var series =[];
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_TIPO_PARTE);
                    if (indexOfDato != -1) {
                        allDatos[indexOfDato]['data'][0]++
                    }
                    else {
                        let objDato = {name:dato.DEN_TIPO_PARTE, data:[1]};
                        allDatos.push(objDato);
                    }
                    return allDatos
                }, [])
            } 
            return series;
        },
        seriesEstado(){
            var series =[{name:'# de escritos', data:[]}];
            var estados =[];
            if(this.datos[0] != undefined){
                estados = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ESTADO_ACTO_PROCESAL);
                    if (indexOfDato != -1) {
                        allDatos[indexOfDato]['data'][0]++
                    }
                    else {
                        let objDato = {name:dato.DEN_ESTADO_ACTO_PROCESAL, data:[1], color: ((dato.DEN_ESTADO_ACTO_PROCESAL == 'PROYECTO DE RESOLUCION')? 'rgba(237, 12, 12, 0.5)': (dato.DEN_ESTADO_ACTO_PROCESAL == 'RESOLUCION DESCARGADA')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};
                        allDatos.push(objDato);
                    }
                    return allDatos
                }, [])
            } 

            for (let i = 0; i < estados.length; i++) {
                estados[i]['y'] = estados[i]['data'][0]*1; //*100/this.datos.length*1 
            }
            series[0]['data'] = estados;
            return series;
        },
        seriesEstadoInst(){
            var series =[];
            /******** MAPEAR INSTANCIAS */

            var instancias = [];

            if(this.datos[0] != undefined){
                instancias = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e; }).indexOf(dato.DEN_INSTANCIA_SIJ);
                    if (indexOfDato == -1) {
                        allDatos.push(dato.DEN_INSTANCIA_SIJ);
                    }
                    return allDatos
                }, [])
            }   
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ESTADO_ACTO_PROCESAL);
                    if (indexOfDato != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                allDatos[indexOfDato]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objDato = {name:dato.DEN_ESTADO_ACTO_PROCESAL, data:[], color: ((dato.DEN_ESTADO_ACTO_PROCESAL == 'PROYECTO DE RESOLUCION')? 'rgba(237, 12, 12, 0.5)': (dato.DEN_ESTADO_ACTO_PROCESAL == 'RESOLUCION DESCARGADA')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

                        for (let i = 0; i < instancias.length; i++) {
                            if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                objDato['data'][i] = 1
                            }else{
                                objDato['data'][i] = 0
                            }
                        }
                        allDatos.push(objDato);
                    }
                    return allDatos
                }, [])
            } 
            return series;
        },

        seriesActoProcesal(){
            var series =[];
            /******** MAPEAR TIPO ACTO PROCESAL */

            var actosprocesal = [];
            if(this.datos[0] != undefined){
                actosprocesal = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e; }).indexOf(dato.DEN_TIPO_ACTO_PROCESAL);
                    if (indexOfDato == -1) {
                        allDatos.push(dato.DEN_TIPO_ACTO_PROCESAL);
                    }
                    return allDatos
                }, [])
            }   

            if(this.datos[0] != undefined){

                series = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ESTADO_ACTO_PROCESAL);
                    if (indexOfDato != -1) {
                        for (let i = 0; i < actosprocesal.length; i++) {
                            if(dato.DEN_TIPO_ACTO_PROCESAL == actosprocesal[i]){
                                allDatos[indexOfDato]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objDato = {name:dato.DEN_ESTADO_ACTO_PROCESAL, data:[], color: ((dato.DEN_ESTADO_ACTO_PROCESAL == 'PROYECTO DE RESOLUCION')? 'rgba(237, 12, 12, 0.5)': (dato.DEN_ESTADO_ACTO_PROCESAL == 'RESOLUCION DESCARGADA')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

                        for (let i = 0; i < actosprocesal.length; i++) {
                            if(dato.DEN_TIPO_ACTO_PROCESAL == actosprocesal[i]){
                                objDato['data'][i] = 1
                            }else{
                                objDato['data'][i] = 0
                            }
                        }
                        allDatos.push(objDato);
                    }
                    return allDatos
                }, [])
            } 
            return series;
        },

        seriesEspEstado(){
            var series =[];
            /******** MAPEAR INSTANCIAS */

            var especialistas = [];

            if(this.datos[0] != undefined){
                especialistas = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e; }).indexOf(dato.NOM_USUARIO_SECRETARIO);
                    if (indexOfDato == -1) {
                        allDatos.push(dato.NOM_USUARIO_SECRETARIO);
                    }
                    return allDatos
                }, [])
            }   
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ESTADO_ACTO_PROCESAL);
                    if (indexOfDato != -1) {
                        for (let i = 0; i < especialistas.length; i++) {
                            if(dato.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                allDatos[indexOfDato]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objDato = {name:dato.DEN_ESTADO_ACTO_PROCESAL, data:[], color: ((dato.DEN_ESTADO_ACTO_PROCESAL == 'PROYECTO DE RESOLUCION')? 'rgba(237, 12, 12, 0.5)': (dato.DEN_ESTADO_ACTO_PROCESAL == 'RESOLUCION DESCARGADA')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

                        for (let i = 0; i < especialistas.length; i++) {
                            if(dato.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                objDato['data'][i] = 1
                            }else{
                                objDato['data'][i] = 0
                            }
                        }
                        allDatos.push(objDato);
                    }
                    return allDatos
                }, [])
            } 
            return series;
        },

        seriesSankey(){
            var series =[{keys: ['from', 'to', 'weight'], data:[]}];

            var sankey = [];
            if(this.datos[0] != undefined){
                sankey = this.datos.reduce(function (allDatos, dato) {
                        let indexOfDato = allDatos.map(function(e) {  return e[0]+e[1]; }).indexOf('Proy: '+dato.NOM_USUARIO_PROYECTO+'Desc:'+dato.NOM_USUARIO_DESCARGO);
                        if (indexOfDato != -1) {
                            allDatos[indexOfDato][2] = allDatos[indexOfDato][2]*1 +1
                        }
                        else {
                            let arrAudiencia = ['Proy: '+dato.NOM_USUARIO_PROYECTO,'Desc:'+dato.NOM_USUARIO_DESCARGO, 1];
                            allDatos.push(arrAudiencia);
                        }
                    
                    return allDatos
                }, [])
            } 
            series[0]['data'] = sankey;


            return series;
        },

        seriesHitos(){
            var series =[{name:'# de resoluciones', data:[]}];
            var estados =[];
            if(this.datos[0] != undefined){
                estados = this.datos.reduce(function (allDatos, dato) {
                    if(dato.DEN_ACTO_PROCESAL_HITO != null && dato.DEN_ACTO_PROCESAL_HITO != '-'){

                        let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ACTO_PROCESAL_HITO);
                        if (indexOfDato != -1) {
                            allDatos[indexOfDato]['data'][0]++
                        }
                        else {
                            let objDato = {name:dato.DEN_ACTO_PROCESAL_HITO, data:[1]};
                            allDatos.push(objDato);
                        }
                    }
                    return allDatos
                }, [])
            } 

            for (let i = 0; i < estados.length; i++) {
                estados[i]['y'] = estados[i]['data'][0]*1; //*100/this.datos.length*1 
            }
            series[0]['data'] = estados;
            return series;
        },
        seriesHitosInst(){
            var series =[];
            /******** MAPEAR INSTANCIAS */

            var instancias = [];

            if(this.datos[0] != undefined){
                instancias = this.datos.reduce(function (allDatos, dato) {
                    let indexOfDato = allDatos.map(function(e) { return e; }).indexOf(dato.DEN_INSTANCIA_SIJ);
                    if (indexOfDato == -1) {
                        allDatos.push(dato.DEN_INSTANCIA_SIJ);
                    }
                    return allDatos
                }, [])
            }   
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (allDatos, dato) {
                    if(dato.DEN_ACTO_PROCESAL_HITO != null && dato.DEN_ACTO_PROCESAL_HITO != '-'){

                        let indexOfDato = allDatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ACTO_PROCESAL_HITO);
                        if (indexOfDato != -1) {
                            for (let i = 0; i < instancias.length; i++) {
                                if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                    allDatos[indexOfDato]['data'][i]++
                                }
                            }
                        }
                        else {
                            let objDato = {name:dato.DEN_ACTO_PROCESAL_HITO, data:[]};
                            for (let i = 0; i < instancias.length; i++) {
                                if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                    objDato['data'][i] = 1
                                }else{
                                    objDato['data'][i] = 0
                                }
                            }
                            allDatos.push(objDato);
                        }
                    } 
                    return allDatos
                }, [])
            } 
            return series;
        },
    },
    methods: {
        viewDetails(item){
            let url = '../consultasSIJ/getResolucion?c_provincia=' + item.c_provincia + 
                                                                                    '&c_sede=' + item.c_sede + 
                                                                                    '&c_org_jurisd=' + item.c_org_jurisd + 
                                                                                    '&c_especialidad=' +item.c_especialidad +
                                                                                    '&c_instancia=' + item.c_instancia + 
                                                                                    '&f_ini=' + this.parseDate(this.f_inicio) + 
                                                                                    '&f_fin=' + this.parseDate(this.f_final) +
                                                                                    '&f_res_proy=' + item.FECHA_RESOL_PROYECTO +
                                                                                    '&n_unico=' + item.COD_UNICO_EXPEDIENTE;

            this.$emit('showLoad', 'grey darken-2', 'Realizando consulta')
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.activeItem = response.data['data'][0];
                        this.d_resolucion = true
                    } else{
                        this.activeItem = []
                        this.$emit('showSnack','warning',response.data['message'] )
                    }
                    this.$emit('showLoad','','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    if (errors.response.status === 500){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    
                    this.$emit('showLoad','','', false);
                });
        },

        parseDate (date) {
            if (!date) return null
                const [day, month, year] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
        },

        showSnack(color, text, show = true){
            this.$emit('showSnack', color, text, show)
        },
        showLoad(color, text, show = true){
            this.$emit('showLoad', color, text, show)
        },
    },
}
</script>