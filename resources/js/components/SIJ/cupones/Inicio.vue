<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">

            <v-card>
                <v-toolbar color="grey" dark>
                <v-toolbar-title>SIJ - Consulta de cupones</v-toolbar-title>
                <v-spacer/>
                </v-toolbar>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="12">
                            <dataTable
                                    :headers="headers"
                                    :data="filteredData"
                                    :allData="audiencias"
                                    :filters="filters"
                                    :n_paginas="n_paginas"
                                    :itemsPerPage="itemsPerPage"
                                    :tipo="2"
                                    :dense="true"
                                    @viewItem="viewDetails"
                                />
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
            <sij-audiencias-view :activeItem='activeItem' @close="d_audiencia = false"/>
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
            modulo: 'Cupones',
            drawer:true,
            audiencias:[],
            audiencia:{},
            tab: null,
            d_audiencia:false,
            filters:{
                DEN_ESTADO_ORDEN_PAGO: [],
            },
            headers:[
               {text: 'Expediente', value: 'NUM_EXPEDIENTE'},
               {text: 'Instancia', value: 'DEN_INSTANCIA_SIJ'},
               {text: 'Secretario', value: 'NOM_USUARIO_SECRETARIO'},
               {text: 'Fecha Reg. DJ.', value: 'FECHA_REG_DEPOSITO_JUDICIAL', type:'datetime'},
               {text: 'Usuario Registro DJ', value: 'NOM_USUARIO_REG_DEP_JUDICIAL'},
               {text: 'Beneficiario', value: 'APE_PAT_BENEFICIARIO', type:'concat', subvalue_concat:'APE_PAT_BENEFICIARIO,APE_MAT_BENEFICIARIO' },
               {text: 'Estado OP', value: 'DEN_ESTADO_ORDEN_PAGO'},
               //{text: 'Opciones',type:'opciones', value: 'view'}
            ],
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
    },
    created() {
        this.getCupones();
    },
    methods: {
        getCupones(){
    
            let url = '../consultasSIJ/listarCupones?nap_secretario='+this.dataUser.usuario.ap_paterno+'&nam_secretario='+this.dataUser.usuario.ap_materno+'&nno_secretario='+this.dataUser.usuario.nombres

            this.showLoad('grey darken-2', 'Realizando consulta');
            this.audiencias = [];
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.audiencias = response.data['data'];

                        this.filters.DEN_ESTADO_ORDEN_PAGO= []
                        
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