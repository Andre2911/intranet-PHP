<template>
    <div>
        <v-row>
            <v-col cols="12" md="12">
                <template v-if="checkData()">
                    <v-expansion-panels v-model="panel">
                        <v-expansion-panel v-for="(reporte, index) in reportes" v-bind:key="index">
                            <template v-if="datos[0][reporte.dato]*1 > 0">
                                <v-expansion-panel-header>
                                    <template v-slot:default="{ open }">
                                        <v-row no-gutters>
                                            <v-col cols="7">
                                                {{reporte.descripcion}}
                                            </v-col>
                                            <v-col cols="5" class="text--secondary">
                                                <v-chip class="ma-0" :color="reporte.color" outlined>
                                                    {{datos[0][reporte.dato]}}
                                                    <small>&nbsp; {{reporte.producto}} &nbsp;</small> 
                                                    <v-icon left v-if="datos[0][reporte.dato]*1 > 0">
                                                        {{reporte.icon}}
                                                    </v-icon>
                                                </v-chip>
                                                
                                            </v-col>
                                        </v-row>
                                    </template>
                                </v-expansion-panel-header>
                                <v-expansion-panel-content>
                                    <template v-if="cargando">
                                        <div class="text-center">
                                            <v-progress-circular
                                                :size="70"
                                                :width="3"
                                                color="teal"
                                                indeterminate
                                            ></v-progress-circular>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <highcharts :options="chartDataTime" ref="spline" v-if="datosTime[0] != undefined"></highcharts>
                                    </template>
                                </v-expansion-panel-content>

                            </template>
                        </v-expansion-panel>
                    </v-expansion-panels>

                </template>
                <template v-else>
                    <v-alert
                        outlined
                        type="warning"
                        prominent
                        border="left"
                        >
                        No se encontraron datos para el periodo y persona consultada
                        </v-alert>
                </template>
            </v-col>
        </v-row>
        
    </div>
</template>
<script>
import {Chart} from 'highcharts-vue'

export default {
    props:['datos', 'dataUser', 'f_inicio', 'f_final', 'usuario'],
    components: {
        highcharts: Chart 
    },
    data: () => ({
        cargando:true,
        datosTime:[],
        panel: [],
        reportes: [
            {id:1, descripcion:'Proyección de decretos, autos y resoluciones', color:'info', icon:'mdi-file-word', producto: 'PROYECTOS', dato: 'consulta1'},
            {id:2, descripcion:'Descargo de decretos, autos y resoluciones', color:'red darken-3', icon:'mdi-file-pdf', producto: 'DESCARGOS', dato: 'consulta2'},
            {id:3, descripcion:'Descargo con Proveido', color:'red darken-3', icon:'mdi-file-pdf', producto: 'DESCARGOS', dato: 'consulta3'},
            {id:4, descripcion:'Generación de oficios y exhortos', color:'info', icon:'mdi-file-word', producto: 'OFICIOS', dato: 'consulta4'},
            {id:5, descripcion:'Programación de audiencias', color:'cyan darken-3', icon:'mdi-file-music-outline', producto: 'AUDIENCIAS', dato: 'consulta5'},
            {id:6, descripcion:'Realización de audiencias', color:'cyan darken-3', icon:'mdi-file-music-outline', producto: 'AUDIENCIAS', dato: 'consulta6'},
            {id:7, descripcion:'Subida de actas', color:'info', icon:'mdi-file-word', producto: 'ACTAS/AUD', dato: 'consulta7'},
            {id:8, descripcion:'Registro de escritos y demandas', color:'red darken-3', icon:'mdi-file-pdf', producto: 'ESCR/DEM', dato: 'consulta8'},
            {id:9, descripcion:'Generación de cédulas', color:' deep-purple lighten-3', icon:'mdi-newspaper-variant-outline', producto: 'CÉDULAS ', dato: 'consulta9'},
            {id:10, descripcion:'Generación de Guias de Entrega de cédulas', color:'teal darken-2', icon:'mdi-truck-check', producto: 'GUIAS ', dato: 'consulta10'},
            {id:11, descripcion:'Recepción de cédulas notificadas', color:'teal darken-2', icon:'mdi-email-open-multiple-outline', producto: 'CEDULAS ', dato: 'consulta11'},
            {id:12, descripcion:'Elevación de auto', color:'red darken-3', icon:'mdi-transfer-up', producto: 'AUTOS', dato: 'consulta12'},
            {id:13, descripcion:'Bajada de auto', color:'red darken-3', icon:'mdi-transfer-down', producto: 'AUTOS', dato: 'consulta13'},
            {id:14, descripcion:'Endose de cupones', color:'deep-orange darken-1', icon:'mdi-cash-check', producto: 'ENDOSES', dato: 'consulta14'},
        ],
        producto:null,
        detalle:null,
    }),
    computed:{
        chartDataTime(){
            return {
                chart: {
                    zoomType: 'x'
                },
                title: {  text: this.detalle  },
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
                        text: 'Cantidad de ' + this.producto
                    },
                    labels: {
                        overflow: 'justify'
                    }
                },
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%e. %b}: {point.y} ' + this.producto
                },
                series: this.seriesPeriodo,
                plotOptions: {
                    series: {
                        marker: {
                            enabled: true
                        }
                    }
                },
                colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

            }
        }, 
        seriesPeriodo(){
            var series =[];
            var dataSeries = [];
            this.datosTime.forEach(element => {
                var dateFecha = this.formatFecha(element.fecha);
                dataSeries.push([Date.UTC(dateFecha[0], dateFecha[1]-1, dateFecha[2]), element.total*1])
            });

            series.push({name: this.producto, data: dataSeries});
            return series;
        }
    },
    watch:{
        panel(val){
            if(val !=  undefined){
                this.consulta(val)
            }
        }
    },
    methods: {
        consulta(tipo)
        {
            if(tipo == undefined){
                return;
            }
            this.cargando = true;
            this.producto = this.reportes[tipo]['producto']
            this.detalle = this.reportes[tipo]['descripcion']
            var url_meta = 'consultaTime' + (tipo+2);
            let url = 'SIJgetRegUsuario?url_meta=' + url_meta + 
																'&dni_u=' + this.usuario + 
																'&f_ini=' + this.parseDate(this.f_inicio) + 
                                                                '&f_fin=' + this.parseDate(this.f_final);

            axios.get(url)
                    .then(response =>{
                        if(response.data.data != null && response.data.status == 200){
                            if (response.data.data.length > 0){
                                this.datosTime = response.data.data
                            } else{
                            }
                            this.cargando = false;
                        } else if(response.data.status == 0){
                            this.$emit('showSnack','warning','No se puede establecer la conexion SIJ, de realizar trabajo remoto verifique su conexión VPN (Forticlient)')
                            window.location = "../login";
                        } else if(response.data.status == 404){
                            this.$emit('showSnack','warning','No se puede realizar la consulta SIJ, consulte al administrador')
                        }
                        this.$emit('showLoad','', '', false);

                    })
                    .catch(errors =>{
                        if (errors.response.status === 401) {
                            window.location = "../login";
                        }
                        this.$emit('showLoad','', '', false);
                        
                    });
        },
        checkData(){
            if (this.datos.length > 0){
                var contador = 0
                for (let index = 0; index < this.reportes.length; index++) {
                    contador += this.datos[0][this.reportes[index]['dato']]*1
                }
                return contador > 0
            } else{
                return false
            }
        },

        formatFecha (date) {
            if (!date) return null
            const [year, month, day] = date.split('-')

            return [year, month, day];
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
    },
}
</script>