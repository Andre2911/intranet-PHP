<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">
            <v-card
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Mis metas mensuales</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-app-bar>
                <v-card-text>

                    <v-card flat>
                        <v-row>
                            <v-col cols=12 md="6">
                                <v-list two-line subheader dense>
                                    <v-subheader>
                                        I. Datos del trabajador
                                    </v-subheader>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.ap_paterno}} {{dataUser.usuario.ap_materno}}</v-list-item-title>
                                            <v-list-item-subtitle>Apellidos</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.nombres}}</v-list-item-title>
                                            <v-list-item-subtitle>Nombres</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-subheader>II. Datos Laborales</v-subheader>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.n_plaza_fisica}}</v-list-item-title>
                                            <v-list-item-subtitle>Plaza Física</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.n_oficina_fisica}}</v-list-item-title>
                                            <v-list-item-subtitle>Ubicación</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list>
                            </v-col>
                            <v-col cols=12 md="3">
                                <v-list two-line subheader dense>
                                    <v-subheader>
                                        Supervisor
                                    </v-subheader>
                                    <v-list-item>
                                        <v-list-item-content v-if="supervisor != null">
                                            <v-list-item-title> {{supervisor.ap_paterno}} {{supervisor.ap_materno}} {{supervisor.nombres}} </v-list-item-title>
                                            <v-list-item-subtitle>Supervisor</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list>
                            </v-col>
                            <v-col cols=12 md="3">
                                <v-row>
                                    <v-col cols="12" sm="12" md="12" xs="12">
                                        <v-btn color="info" @click="listarData()" block>
                                            <v-icon>mdi-refresh</v-icon>
                                                    Actualizar
                                        </v-btn>
                                    </v-col>
                                    <v-col cols="12" sm="12" md="12" xs="12">
                                        <v-btn @click="d_actividad = true" dark color="red darken-3" block>
                                            <v-icon>mdi-plus</v-icon>
                                            Registrar metas del Mes
                                        </v-btn>
                                    </v-col>
                                </v-row>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-alert
                                :value="true"
                                color="warning"
                                icon="mdi-alert"
                                outlined
                                class="ml-2 mt-3 mr-2 "
                                small
                            >
                                <p>Estimado usuario, para cumplir con el envio del Anexo 04 al Área de Personal, Ud. debe generar el documento y dar su conformidad para que luego su Jefe Supervisor pueda visualizarlo y darle el VB° al anexo 04, dicha acción la puede realizar haciendo click en el icono <v-icon color="info">mdi-file-eye</v-icon> y posteriormente presionar el Boton "DAR CONFORMIDAD DEL EVALUADO"
                                </p><br>
                                <i>Cabe mencionar que las cantidades del "Nivel de logro esperado" contenidas en el anexo 04 son el proporcional de las metas fijadas a inicios de mes en relación a los días en los cuales Ud. marco "asistencia remota", asimismo, el producto alcanzado es la sumatoria del avance de metas de los dias en que marcó "asistencia remota"</i>
                            </v-alert>

                            </v-alert>
                        </v-row>
                    </v-card>
                    
                </v-card-text>
            </v-card>
            <div id="printSection">
                    <v-card>
                        <dataTableFull
                            :data="filteredData"
                            :headers="headers"
                            :filters="filters"
                            :n_paginas="n_paginas"
                            :itemsPerPage="itemsPerPage"
                            :tipo=1
                            @change_page="listarData"
                            @editItem="editItem"
                            @viewItem="viewItem"
                            @deleteItem="deleteItem"
                        />
                    </v-card>
            </div>
        </v-main>

        <v-dialog 
            v-model="d_actividad"
            scrollable  
            :overlay="false"
            max-width="1000px"
            transition="dialog-transition"
            v-if="d_actividad"
        >
            <v-card>
                <v-toolbar color="red darken-1" dark>
                    <v-toolbar-title>Programación de las metas del mes

                        <template v-if="metam.anio != undefined && metam.mes != undefined">
                           de  {{meses[metam.mes-1]['label']}} del {{metam.anio}}
                        </template>
                        
                    </v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-icon @click="d_actividad = false">mdi-close</v-icon>
                </v-toolbar>
                <v-card-text  class="pa-0">
                    <v-stepper
                        v-model="programa"
                        vertical
                    >
                        <v-stepper-step
                            :complete="programa > 1"
                            step="1"
                            >
                            Seleccione un periodo
                        </v-stepper-step>

                        <v-stepper-content step="1">
                            <v-layout wrap>
                                <v-col cols="12" md="2" class="pt-0 pb-0">
                                    <v-autocomplete
                                        v-model="metam.anio"
                                        :items="anios"
                                        label="Año"
                                    >
                                    </v-autocomplete>  
                                </v-col>
                                <v-col cols="12" md="3" class="pt-0 pb-0">
                                    <v-autocomplete
                                        v-model="metam.mes"
                                        :items="meses"
                                        label="Mes"
                                        item-value="value"
                                        item-text="label"
                                    >
                                    </v-autocomplete>  
                                </v-col>
                            </v-layout>
                            <v-btn
                                color="primary"
                                @click="consultaPeriodo()"
                            >
                                Siguiente
                            </v-btn>
                        </v-stepper-content>

                        <v-stepper-step
                            :complete="programa > 2"
                            step="2"
                            >
                            Programe sus metas
                        </v-stepper-step>

                        <v-stepper-content step="2">
                             <v-layout wrap>
                                <v-col cols="12" md="8" class="pt-0 pb-0">
                                    <v-autocomplete
                                        v-model="actividad"
                                        :items="actividades"
                                        label="Actividad"
                                        item-value="id"
                                        item-text="actividad"
                                    >
                                    </v-autocomplete>  
                                </v-col>
                                <v-col cols="12" md="2" class="pt-0 pb-0">
                                    <v-text-field
                                        v-model="cantidad"
                                        type="number"
                                        label="Cantidad"
                                    />

                                </v-col>
                                <v-col cols="12" md="2" class="pt-0 pb-0">
                                    <v-btn color="primary" @click="addMeta()" :disabled="activeItem.anio != undefined && metasxmes[0]['jefe_vb']*1 ==1"><v-icon dark>mdi-plus</v-icon> Agregar</v-btn>
                                </v-col>
                                <v-col cols="12" md="12">
									<v-data-table
										:headers="headers_a"
										:items="metasxmes"
										class="elevation-1"
                                        hide-default-footer
                                        :disable-filtering="true"
                                        :disable-sort="true"
                                        :items-per-page="-1"
									>
                                        <template v-slot:body="{ items, headers }">
                                            <template v-if="items.length==0">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                        No existen datos
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </template>
                                            <template v-else>
                                                <tbody>
                                                    <tr v-for="item in items" :key="item.id">
                                                        <td v-for="header in headers" :key="header.value">
                                                            <template v-if="header.type == 'opciones'">
                                                                <template v-if="activeItem.anio == undefined || (activeItem.jefe_vb != undefined && activeItem.jefe_vb == 0)">
                                                                    <v-icon color="red" @click="deleteMeta(item.id)">mdi-delete</v-icon>
                                                                </template>
                                                                <template v-else>
                                                                    <v-progress-linear
                                                                        color="primary"
                                                                        :value="(item['avance']*1 / item['meta_cantidad']*1)*100"
                                                                        striped
                                                                        height="20"
                                                                        dark
                                                                        background-color="blue lighten-3"

                                                                    >
                                                                        <template v-slot:default="{ value }">
                                                                            <strong>
                                                                                {{item['avance']}}/{{item['meta_cantidad']}}
                                                                                ({{ roundAvance(value) }}%)
                                                                            </strong>
                                                                        </template>
                                                                    </v-progress-linear>
                                                                </template>
                                                            </template>
                                                            <template v-else>
                                                                {{item[header.value]}}
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </template>
                                        </template>


										<template slot="no-data">
											Agregue las actividades que realizará en el mes e indique la cantidad y luego click en el boton + AGREGAR
										</template>
									</v-data-table>
                                </v-col>
                            </v-layout>
                            <v-btn color="success" @click="save()"
                                :disabled="activeItem.anio != undefined && metasxmes[0]['jefe_vb']*1 ==1"    
                            >
                                <v-icon>mdi-content-save</v-icon> 
                                {{btn_registrar}}
                            </v-btn>
                        </v-stepper-content>


                    </v-stepper>
                   </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                        <v-btn color="red" dark @click="d_actividad = false"><v-icon>mdi-close</v-icon> Cancelar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog 
            v-model="d_anexo"
            scrollable  
            :overlay="false"
            max-width="1000px"
            transition="dialog-transition"
            v-if="d_anexo"
        >
            <v-card>
                <v-toolbar color="red darken-1" dark>
                    <v-toolbar-title>Anexo 04
                    </v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-icon @click="d_anexo = false">mdi-close</v-icon>
                </v-toolbar>
                <v-card-text  class="pa-0">
                    <template v-if="activeItem.enable_anexo*1 == 1">
                        <template v-if="activeItem.anexo == null" >
                            <iframe :src="'anexo04/'+dataUser.usuario.numero_documento+'/'+activeItem.anio+'/'+activeItem.mes" width="100%" height="560px"/>
                        </template>
                        <template v-else >
                            <iframe :src="'../storage/anexo04/'+activeItem.anexo+'?var='+aleatorio" width="100%" height="560px"/>
                        </template>
                        
                    </template>
                    <template v-else>
                        <v-alert
                            :value="true"
                            color="info"
                            icon="mdi-alert"
                            outlined
                            class="ml-2 mt-3 mr-2 "
                            small
                        >
                            El anexo 04 estará disponible al mes siguiente de la programación de las metas.
                        </v-alert>
                    </template>
                    
                    
                </v-card-text>

                 <v-card-actions  class="pt-0 mb-2">
                     <v-row>
                        <template v-if="activeItem.enable_anexo*1 == 1">
                            <v-col cols="12" md="12" class="pt-0 pb-3" v-if="activeItem.anexo == null">
                                <v-textarea
                                    v-model="observaciones"
                                    label="Observaciones y/o recomendaciones a consignar en el Anexo 04"
                                    rows="1"
                                    prepend-icon="mdi-comment"
                                    class="pt-4 pb-0 ma-0"
                                    auto-grow
                                    counter
                                    maxlength="300"
                                    clearable
                                />
                            </v-col>
                        </template>
                        <v-col cols="12" md="12" class="pt-0 mb-2">
                            <v-row>
                                <v-btn color="primary" v-if="activeItem.anexo == null && activeItem.enable_anexo*1 == 1" @click="generarAnx()"><v-icon>mdi-check</v-icon> Dar conformidad del evaluado</v-btn>

                                <v-spacer></v-spacer>
                                <v-btn color="red" dark @click="d_anexo = false"><v-icon>mdi-close</v-icon> Cancelar</v-btn>
                            </v-row>
                        </v-col>
                     </v-row>
                </v-card-actions>
                
            </v-card>
        </v-dialog>

        <snackbar :snack="snack" ></snackbar>
        <dialogLoader :dialogLoad="dialogLoad"/>
        <footer-component/>
    </v-app>
</template>
<script>
export default {
    props:['dataUser'],
    data() {
        return {
            menu: 3,
            submenu: -1,
            drawer:true,
            modulo: 'asistencia',
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            pagina_actual:1,
            n_paginas:1,
            search:'',
            headers: [
                { text: 'Anio', value:'anio'},
                { text: 'Periodo', value:'PERIODO'},
                { text: 'Visto Bueno', value:'jefe_vb', type: 'array_chip', array:'0=Registrado sin VB/info,1=Registrado y Revisado/success'},
                { text: 'Anexo04', value:'c_anexo', type: 'array_chip', array:'1=Generado y enviado para VB°/info,2=Enviado a Personal con VB°/success'},
                { text: 'Opciones', value: 'edit,view', acciones: 'registrar', type:'opciones' }
            ],
            headers_a: [
                { text: 'Actividad / Meta', value:'actividad'},
                { text: 'Cantidad', value:'meta_cantidad'},
                { text: '________Opciones________', value: 'edit,delete', acciones: 'registrar', type:'opciones' }
            ],
            rules: {
               entero: v => v > 0 || 'El campo solo admite números',
               required: v => !!v || 'Descripcion Requerido',
               dni: v=> v != undefined && v.length == 8 || 'Longitud incorrecta'
            },
            itemsPerPage:15,
            activeItem:{
            },
            data: [
            ],
            filters: {
            },
            programa:1,
            d_actividad:false,
            d_anexo:false,
            metam:{},
            anios:[],
            meses:[],
            actividades:[],
            metasxmes:[],
            actividad:null,
            cantidad:'',
            supervisor: null,
            dialogLoad: {show:false,text: '',color: ''}, 
            aleatorio:0,
            observaciones:null,

        }
    },
    computed: {
         filteredData() {
            if(this.data.length > 0){
                return this.data.filter(d => {
                    return Object.keys(this.filters).every(f => {
                        return this.filters[f].length < 1 || this.filters[f].includes(d[f])
                    })
                })
            }
         },
         btn_registrar(){
            return this.activeItem.anio > 0 ? 'Actualizar Datos': 'Registrar'
        },
    },
    watch:{
        d_actividad(val){
            if(!val){
                this.activeItem = {};
                this.activeItem.actividad = '';
                this.activeItem.b_area = true;
                this.programa = 1;
                this.metasxmes = [];
                this.metam = {},
                this.listarData();
            }
        },
        d_anexo(val){
            if(!val){
                this.activeItem = {};
                this.activeItem.actividad = '';
                this.activeItem.b_area = true;
                this.programa = 1;
                this.metasxmes = [];
                this.metam = {},
                this.listarData();
            }
        }
    },
    created(){
        this.getAnios();
        this.listarData();
    },
    methods: {
        listarData(pag=1, search=null, perpage=15){
            this.showLoading(true, 'info', 'Cargando Datos');

            this.itemsPerPage = perpage;
            var url = 'metasApi?metas_area=true&page='+pag+'&perpage=all';
            this.pagina_actual = pag;

            axios.get(url)
                .then(response =>{
                    this.actividades = response.data.metas.data;
                    this.data = response.data.metasmes.data;
                    this.supervisor = response.data.jefe_supervisor;
                    this.n_paginas = response.data.metas.last_page;
                    this.showLoading(false);

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401 || errors.response.status === 419 ) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                        window.location = "../login";
                    } else if (errors.response.status === 403) {
                        console.log(errors.response)
                             window.location = "../inicio";
                    } else{
                        //this.listarData();
                    }
                    this.showLoading(false);

                });
        },
        getAnios(){
            var d = new Date();
            var n = d.getFullYear();
            for (var i = n; i >= 2021; i--) {
               this.anios.push(i);
            }
            var mesesdesc = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre']
            for (var i = 1; i <= 12; i++) {
               this.meses.push({value:i, label:mesesdesc[i-1]});
            }

         },
        printSection() {
            this.$htmlToPaper("printSection");
        },
        newItem(){},
        editItem(item){
            this.metam.anio = item.anio*1
            this.metam.mes = item.mes*1
            this.programa = 2
            this.metasxmes = []

            axios.post('getMetasXMes', {periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.activeItem = item;
                            this.metasxmes = response.data.metasmes;
                            this.d_actividad = true;
                            //this.listarData();
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });


        },
        viewItem(item){
            this.metam.anio = item.anio*1
            this.metam.mes = item.mes*1
            axios.post('getMetasXMes', {periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.activeItem = item;
                                                       
                            this.d_anexo = true;
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });

            

        },
        deleteItem(item){
            
        },
        save(){
            if(this.metasxmes.length > 0){
                this.showLoading(true, 'primary', 'Guardando datos')

                axios.post('setMetasXMes', {actividades : this.metasxmes, periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.d_actividad = false;
                            //this.listarData();
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                        this.showLoading(false)

                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                        this.showLoading(false)

                    });
            } else{
                this.showSnack('error', 'Debe programar las actividades y metas (cantidad) para el mes seleccionado')
            }
        },

        addMeta(){
            if(this.actividad == null || this.cantidad == 0){
                this.showSnack('warning', 'Debe seleccionar una actividad e indicar su respectiva cantidad propuesta para el mes seleccionado')
            } else{

                let indexOfMeta = this.metasxmes.map(function(e) { return e.id; }).indexOf(this.actividad);
                let indexOfActivity = this.actividades.map(function(e) { return e.id; }).indexOf(this.actividad);

                if(indexOfMeta != -1){
                    this.showSnack('warning', 'Ya seleccionó esta actividad / meta con anterioridad')
                } else{
                    var item = {
                        id:this.actividad,
                        actividad:this.actividades[indexOfActivity].actividad,
                        meta_cantidad:this.cantidad*1
                    }
                    this.metasxmes.push(item)

                    this.actividad = null;
                    this.cantidad = '';
                }
               
                
            }
        },
        deleteMeta(id){
            
            let indexOfMeta = this.metasxmes.map(function(e) { return e.id; }).indexOf(id);
            console.log(indexOfMeta)
            if(indexOfMeta != -1){
                this.metasxmes.splice(indexOfMeta,1);
            } else{
                this.showSnack('warning', 'Ya se elimino esta actividad')
            }
                
        },  
        consultaPeriodo(){

            axios.post('getMetasXMes', {periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            if(response.data.last_periodo == null){
                                this.programa = 2    
                            }
                            else if(this.metam.anio < response.data.last_periodo['anio'] ){
                                this.showSnack('error', 'No se puede registrar metas para un mes anterior a los ya programados')
                            } else if(this.metam.anio == response.data.last_periodo['anio'] && this.metam.mes < response.data.last_periodo['mes'] ){
                                this.showSnack('error', 'No se puede registrar metas para un mes anterior a los ya programados')
                            } else{
                                if(response.data.metasmes.length > 0){
                                    this.metasxmes = response.data.metasmes;
                                    this.activeItem = this.metam
                                }
                                this.programa = 2
                            }
                            //this.listarData();
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });

        },

        generarAnx(){
            this.showLoading(true, 'primary', 'Generando anexo')

            axios.post('anexo04/'+ this.dataUser.usuario.numero_documento+'/'+this.activeItem.anio+'/'+this.activeItem.mes, {generate:true, observaciones:this.observaciones})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.showSnack('success', response.data['messageDB'])
                            this.d_anexo = false;
                            this.genrandom()
                            this.listarData();
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                        this.showLoading(false, '', '')
                    })
                    .catch(errors =>{
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        } if(errors.response.status == 403){
                            this.showSnack('error', 'No fue posible generar el anexo, contacte con el administrador')
                        }
                        this.showLoading(false, '', '')

                    });

        },
        genrandom(){
            this.aleatorio = Math.random();
        },
        close(){
        },
        roundAvance(avance){
            return Math.round(avance*10)/10;
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
        showLoading(show = true, color = null, text=''){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
        
    }
}
</script>