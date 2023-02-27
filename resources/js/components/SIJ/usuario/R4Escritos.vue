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
                            <highcharts :options="chartParte" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                        <v-col cols="12" md="6">
                            <highcharts :options="chartEscritosEstado" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                        <v-col cols="12" md="6">
                            <highcharts :options="chartEscEstInst" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                        <v-col cols="12" md="6">
                            <highcharts :options="chartEscritosEsp" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                        <v-col cols="12" md="6">
                            <highcharts :options="chartEspEstado" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                        <v-col cols="12" md="12">
                            <highcharts :options="chartEspPendTime" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                        <v-col cols="12" md="6">
                            <highcharts :options="chartMP" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                        <v-col cols="12" md="6">
                            <highcharts :options="chartUsuarioMP" ref="lineCharts" v-if="datos[0] != undefined"></highcharts>
                        </v-col>
                    </v-row>
                </v-tab-item>
        </v-tabs-items>
        <v-dialog
            v-model="d_escrito"
        >
            <sij-datos-view :activeItem='activeItem' :dataUser="dataUser" @showSnack="showSnack"  @showLoad="showLoad" @close="d_escrito = false"/>
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
               {text: 'Parte', value: 'APE_NOM_DEN_PARTE'},
               {text: 'Estado escrito', value: 'DEN_ESTADO_ESCRITO'},
               {text: 'F. asociación', value: 'FECHA_ASOC_DOC_DIGIT_ESCRITO'},
               {text: 'Dig. MP', value: 'ESTADO_DIG_ESCRITO'},
               {text: 'Sumilla', value: 'SUMILLA_ESCRITO'},
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
                    title: {  text: 'CANT. DE ESCRITOS INGRESADOS POR ESPECIALISTA'  },
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
        chartEspPendTime() { 
            return {
                    chart: {  type: this.modo},
                    title: {  text: 'Escritos Pendientes por Especialista' },
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
        seriesInstancia(){
            var series =[];
            /******** MAPEAR INSTANCIAS */
            var instancias = [];
            if(this.datos[0] != undefined){
                instancias = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.DEN_INSTANCIA_SIJ);
                    if (indexOfAud == -1) {
                        alldatos.push(dato.DEN_INSTANCIA_SIJ);
                    }
                    return alldatos
                }, [])
            }   

            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf('Escritos');
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                alldatos[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objdato = {name:'Escritos', data:[]};

                        for (let i = 0; i < instancias.length; i++) {
                            if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                objdato['data'][i] = 1
                            }else{
                                objdato['data'][i] = 0
                            }
                        }
                        alldatos.push(objdato);
                    }
                    return alldatos
                }, [])
            } 
            return series;
        },
        catInstancias(){
            var catAud =[];

            if(this.datos[0] != undefined){
                catAud = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.DEN_INSTANCIA_SIJ);
                    if (indexOfAud == -1) {
                        alldatos.push(dato.DEN_INSTANCIA_SIJ);
                    }
                    return alldatos
                }, [])
            } 
            return catAud;
        },
        catEspecialistas(){
            var catAud =[];

            if(this.datos[0] != undefined){
                catAud = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.NOM_USUARIO_SECRETARIO);
                    if (indexOfAud == -1) {
                        alldatos.push(dato.NOM_USUARIO_SECRETARIO);
                    }
                    return alldatos
                }, [])
            } 
            return catAud;
        },
        catUsuarioMP(){
            var catAud =[];

            if(this.datos[0] != undefined){
                catAud = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.NOM_USUARIO_REG_ESCRITO);
                    if (indexOfAud == -1) {
                        if (dato.NOM_USUARIO_REG_ESCRITO == null){
                            alldatos.push('Escritos Sin digitalizar');
                        } else{
                            alldatos.push(dato.NOM_USUARIO_REG_ESCRITO);
                        }
                    }
                    return alldatos
                }, [])
            } 
            return catAud;
        },
        seriesParte(){
            var series =[];
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf(dato.DEN_TIPO_PARTE);
                    if (indexOfAud != -1) {
                        alldatos[indexOfAud]['data'][0]++
                    }
                    else {
                        let objdato = {name:dato.DEN_TIPO_PARTE, data:[1]};
                        alldatos.push(objdato);
                    }
                    return alldatos
                }, [])
            } 
            return series;
        },
        seriesEstado(){
            var series =[{name:'# de escritos', data:[]}];
            var estados =[];
            if(this.datos[0] != undefined){
                estados = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ESTADO_ESCRITO);
                    if (indexOfAud != -1) {
                        alldatos[indexOfAud]['data'][0]++
                    }
                    else {
                        let objdato = {name:dato.DEN_ESTADO_ESCRITO, data:[1], color: ((dato.DEN_ESTADO_ESCRITO == 'PENDIENTE')? 'rgba(237, 12, 12, 0.5)': (dato.DEN_ESTADO_ESCRITO == 'ATENDIDO')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};
                        alldatos.push(objdato);
                    }
                    return alldatos
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
                instancias = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.DEN_INSTANCIA_SIJ);
                    if (indexOfAud == -1) {
                        alldatos.push(dato.DEN_INSTANCIA_SIJ);
                    }
                    return alldatos
                }, [])
            }   
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ESTADO_ESCRITO);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < instancias.length; i++) {
                            if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                alldatos[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objdato = {name:dato.DEN_ESTADO_ESCRITO, data:[], color: ((dato.DEN_ESTADO_ESCRITO == 'PENDIENTE')? 'rgba(237, 12, 12, 0.5)': (dato.DEN_ESTADO_ESCRITO == 'ATENDIDO')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

                        for (let i = 0; i < instancias.length; i++) {
                            if(dato.DEN_INSTANCIA_SIJ == instancias[i]){
                                objdato['data'][i] = 1
                            }else{
                                objdato['data'][i] = 0
                            }
                        }
                        alldatos.push(objdato);
                    }
                    return alldatos
                }, [])
            } 
            return series;
        },
        seriesEspecialista(){
            var series =[];
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (alldatos, dato) {
                    if(dato.NOM_USUARIO_SECRETARIO != null){
                        let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf(dato.NOM_USUARIO_SECRETARIO);
                        if (indexOfAud != -1) {
                            alldatos[indexOfAud]['data'][0]++
                        }
                        else {
                            let objdato = {name:dato.NOM_USUARIO_SECRETARIO, data:[1]};
                            alldatos.push(objdato);
                        }
                    } else{
                        let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf('Pendientes / Anulados');
                        if (indexOfAud != -1) {
                            alldatos[indexOfAud]['data'][0]++
                        }
                        else {
                            let objdato = {name:'Pendientes / Anulados', data:[1]};
                            alldatos.push(objdato);
                        }
                    }
                    
                    return alldatos
                }, [])
            } 
            return series;
        },
        seriesEspEstado(){
            var series =[];
            /******** MAPEAR INSTANCIAS */

            var especialistas = [];

            if(this.datos[0] != undefined){
                especialistas = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.NOM_USUARIO_SECRETARIO);
                    if (indexOfAud == -1) {
                        alldatos.push(dato.NOM_USUARIO_SECRETARIO);
                    }
                    return alldatos
                }, [])
            }   
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf(dato.DEN_ESTADO_ESCRITO);
                    if (indexOfAud != -1) {
                        for (let i = 0; i < especialistas.length; i++) {
                            if(dato.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                alldatos[indexOfAud]['data'][i]++
                            }
                        }
                    }
                    else {
                        let objdato = {name:dato.DEN_ESTADO_ESCRITO, data:[], color: ((dato.DEN_ESTADO_ESCRITO == 'PENDIENTE')? 'rgba(237, 12, 12, 0.5)': (dato.DEN_ESTADO_ESCRITO == 'ATENDIDO')? 'rgba(12, 237, 12, 0.4)': 'rgba(38, 25, 12, 0.3)')};

                        for (let i = 0; i < especialistas.length; i++) {
                            if(dato.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                objdato['data'][i] = 1
                            }else{
                                objdato['data'][i] = 0
                            }
                        }
                        alldatos.push(objdato);
                    }
                    return alldatos
                }, [])
            } 
            return series;
        },
        seriesEspPendientes(){
            var series =[];
            /******** MAPEAR ESPECIALISTAS */
            var f_today = new Date();

            var especialistas = [];
            if(this.datos[0] != undefined){
                especialistas = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.NOM_USUARIO_SECRETARIO);
                    if (indexOfAud == -1) {
                        alldatos.push(dato.NOM_USUARIO_SECRETARIO);
                    }
                    return alldatos
                }, [])
            }   
            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (alldatos, dato) {

                    if(dato.DEN_ESTADO_ESCRITO == 'PENDIENTE'){

                        /******* CLASIFICAMOS LA DEMORA */

                        let f_ingreso = new Date(dato.FECHA_INGRESO_ESCRITO);

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

                        let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf(t_retraso);
                        if (indexOfAud != -1) {
                            for (let i = 0; i < especialistas.length; i++) {
                                if(dato.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                    alldatos[indexOfAud]['data'][i]++
                                }
                            }
                        }
                        else {
                            let objdato = {name:t_retraso, data:[], color: c_retraso};

                            for (let i = 0; i < especialistas.length; i++) {
                                if(dato.NOM_USUARIO_SECRETARIO == especialistas[i]){
                                    objdato['data'][i] = 1
                                }else{
                                    objdato['data'][i] = 0
                                }
                            }
                            alldatos.push(objdato);
                        }
                    }
                        return alldatos
                    
                }, [])
            } 
            return series;
        },
        seriesMP(){
            var series =[{name:'# de escritos', data:[]}];
            var estados =[];
            if(this.datos[0] != undefined){
                estados = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf(dato.DEN_VENT_REG_ESCRITO);
                    if (indexOfAud != -1) {
                        alldatos[indexOfAud]['data'][0]++
                    }
                    else {
                        let objdato = {name:dato.DEN_VENT_REG_ESCRITO, data:[1]};
                        alldatos.push(objdato);
                    }
                    return alldatos
                }, [])
            } 

            for (let i = 0; i < estados.length; i++) {
                estados[i]['y'] = estados[i]['data'][0]*1; //*100/this.datos.length*1 
            }
            series[0]['data'] = estados;
            return series;
        },
        seriesUsuarioMP(){
            var series =[];
            /******** MAPEAR INSTANCIAS */
            var usuarios = [];
            if(this.datos[0] != undefined){
                usuarios = this.datos.reduce(function (alldatos, dato) {
                    let indexOfAud = alldatos.map(function(e) { return e; }).indexOf(dato.NOM_USUARIO_REG_ESCRITO);
                    if (indexOfAud == -1) {
                        alldatos.push(dato.NOM_USUARIO_REG_ESCRITO);
                    }
                    return alldatos
                }, [])
            }   

            if(this.datos[0] != undefined){
                series = this.datos.reduce(function (alldatos, dato) {
                        let indexOfAud = alldatos.map(function(e) { return e.name; }).indexOf('Escritos');
                        if (indexOfAud != -1) {
                            for (let i = 0; i < usuarios.length; i++) {
                                if(dato.NOM_USUARIO_REG_ESCRITO == usuarios[i]){
                                    alldatos[indexOfAud]['data'][i]++
                                }
                            }
                        }
                        else {
                            let objdato = {name:'Escritos', data:[]};

                            for (let i = 0; i < usuarios.length; i++) {
                                if(dato.NOM_USUARIO_REG_ESCRITO == usuarios[i]){
                                    objdato['data'][i] = 1
                                }else{
                                    objdato['data'][i] = 0
                                }
                            }
                            alldatos.push(objdato);
                        }
                    return alldatos
                }, [])
            } 
            return series;
        },
    },
    methods: {
        viewDetails(item){
            let url = '../consultasSIJ/getdato?c_provincia=' + item.c_provincia + 
                                                                                    '&c_sede=' + item.c_sede + 
                                                                                    '&c_org_jurisd=' + item.c_org_jurisd + 
                                                                                    '&c_especialidad=' +item.c_especialidad +
                                                                                    '&c_instancia=' + item.c_instancia + 
                                                                                    '&f_ini=' + this.parseDate(this.f_inicio) + 
                                                                                    '&f_fin=' + this.parseDate(this.f_final) +
                                                                                    '&x_formato=' + item.NUM_EXPEDIENTE +
                                                                                    '&f_ini_aud=' + item.FECHA_INI_ACTIVIDAD +
                                                                                    '&f_fin_aud=' + item.FECHA_FIN_ACTIVIDAD;

            this.$emit('showLoad', 'grey darken-2', 'Realizando consulta')
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.activeItem = response.data['data'][0];
                        this.d_escrito = true
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