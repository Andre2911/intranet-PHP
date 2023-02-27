<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">
            <v-card
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Metas del área</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-app-bar>
                <v-card-text>
                    <v-row>
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
                        <v-col cols="12" sm="4" md="2" xs="12">
                            <v-btn :disabled="metam.anio == undefined || metam.mes == undefined" color="info" @click="listarData()" block>
                                <v-icon>mdi-magnify</v-icon>
                                        Consultar
                            </v-btn>
                        </v-col>
                        <v-col cols="12" sm="4" md="2" xs="12">
                            <v-btn :disabled="metam.anio == undefined || metam.mes == undefined" color="info" @click="listarDataXLS()" block>
                                <v-icon>mdi-magnify</v-icon>
                                        Generar Datos XLS
                            </v-btn>
                        </v-col>
                        <v-col cols="12" sm="4" md="2" xs="12">
                            <download-excel
                                :data   = "json_data"
                                :fields = "json_fields"
                                :name    = "nombre_archivo"
                                >
                                    <v-btn 
                                    color="success"                                       
                                    :disabled="!exporta"
                                    >
                                    <v-icon>mdi-file-excel</v-icon>
                                        &nbsp; Descargar XLS
                                    </v-btn>
                            </download-excel>
                        </v-col>
                    </v-row>
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
                            @viewItem="viewItem"
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
        >
            <v-card>
                <v-toolbar color="red darken-1" dark>
                    <v-toolbar-title>Programación de las metas del mes

                        <template v-if="metam.anio != undefined && metam.mes != undefined">
                           de  {{meses[metam.mes-1]['label']}} del {{metam.anio}}
                        </template>

                    </v-toolbar-title>
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
                            <v-col cols="12" md="12" class="pt-0 pb-0" v-if="activeItem.numero_documento != undefined">
                                <h3>{{activeItem.ap_paterno}} {{activeItem.ap_materno}} {{activeItem.nombres}} </h3>
                            </v-col>
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
                                    <v-btn color="primary" @click="addMeta()"
                                    :disabled="activeItem.numero_documento != undefined && metasxmes[0] != undefined && metasxmes[0]['jefe_vb']*1 == 1"
                                    ><v-icon dark>mdi-plus</v-icon> Agregar</v-btn>
                                </v-col>
                                <v-col cols="12" md="12">
									<v-data-table
										:headers="headers_a"
										:items="metasxmes"
										hide-default-footer
										class="elevation-1"
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
                                                                <template v-if="activeItem.numero_documento != undefined && metasxmes[0]['jefe_vb']*1 == 0">
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
                                                                                    ({{ roundAvance( value ) }}%)
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
                                :disabled="activeItem.numero_documento != undefined && metasxmes[0] != undefined && metasxmes[0]['jefe_vb']*1 == 1"    
                            >
                                <v-icon>mdi-content-save</v-icon> 
                                {{btn_registrar}}
                            </v-btn>

                            <v-btn color="warning" @click="aperturar()"
                                v-if="activeItem.numero_documento != undefined && metasxmes[0] != undefined && metasxmes[0]['jefe_vb']*1 == 1 && activeItem.anexo == null"
                                :disabled="!(activeItem.numero_documento != undefined && metasxmes[0] != undefined && metasxmes[0]['jefe_vb']*1 == 1)"    
                            >
                                <v-icon>mdi-content-save</v-icon> 
                                Aperturar Metas
                            </v-btn>

                            <v-btn color="primary" @click="d_anexo = true" v-if="activeItem.anexo != null">
                                <v-icon>mdi-file-pdf</v-icon>Ver Anexo 04
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
                    <iframe :src="'../storage/anexo04/'+activeItem.anexo+'?var='+aleatorio" width="100%" height="560px"/>
                </v-card-text>

                 <v-card-actions>
                     <v-btn color="primary" v-if="activeItem.anexo != null  && activeItem.enable_anexo*1 != 1" @click="vbAnx()"><v-icon>mdi-check</v-icon> Dar conformidad del evaluador</v-btn>

                    <v-spacer></v-spacer>
                        <v-btn color="red" dark @click="d_anexo = false"><v-icon>mdi-close</v-icon> Cancelar</v-btn>
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
            menu: 4,
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
                { text: 'Ap. Paterno', value:'ap_paterno'},
                { text: 'Ap. Materno', value:'ap_materno'},
                { text: 'Nombres', value:'nombres'},
                { text: 'Oficina', value:'nombre_oficina'},
                { text: 'Meta', value:'metames', type: 'array_chip', array:'0=Registrado sin VB/info,1=Registrado y Revisado/success'},
                { text: 'Avance', value:'avance'},
                { text: 'Anexo', value:'enable_anexo', type: 'array_chip', array:'0=Sin VB°/info,1=VB°/success'},
                { text: 'Opciones', value: 'view', acciones: 'registrar', type:'opciones' }
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
                actividad:'',
                b_area:true
            },
            data: [
            ],
            filters: {
            },
            metam:{},
            anios:[],
            meses:[],
            programa:1,
            d_actividad:false,
            d_anexo: false,
            actividades:[],
            actividad:null,
            metasxmes:[],
            cantidad:'',
            dialogLoad: {show:false,text: '',color: ''}, 
            aleatorio:0,
            exporta:false,
            json_fields: {},
            json_data: [],
            json_meta: [
                [
                    {
                        'key': 'charset',
                        'value': 'utf-8'
                    }
                ]
            ],
            nombre_archivo:'',
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
            return this.activeItem.id > 0 ? 'Dar VB°': 'Registrar'
        },
    },
    watch:{
        d_actividad(val){
            if(!val){
                this.activeItem = {};
                this.activeItem.actividad = '';
                this.activeItem.b_area = true;
                this.listarData();
            }
        },
        metam: {
            handler () {
               this.exporta = false;
            },
            deep: true,
        }, 
    },
    created(){
        this.getAnios();
    },
    methods: {
        listarData(pag=1, search=null, perpage=15){
            this.exporta = false;

            this.itemsPerPage = perpage;
            this.pagina_actual = pag;
            this.showLoading(true, 'primary', 'Consultando datos')

            axios.post('getMetasTrabajadores', {periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.data = response.data.oficinas_h
                            this.actividades = response.data.metas.data
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
        },
        listarDataXLS(pag=1, search=null, perpage=15){
            this.exporta = false;

            this.itemsPerPage = perpage;
            this.pagina_actual = pag;
            this.showLoading(true, 'primary', 'Consultando datos')

            axios.post('getMetasTrabajadoresXls', {periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.showSnack('success', response.data['messageDB'])
                            this.nombre_archivo = 'Reporte_'+this.metam.anio+'_'+this.metam.mes+'.xls'
                            this.json_data =  response.data.reporte;
                            this.json_fields = {
                                'DNI': 'numero_documento',
                                'Ap. Paterno': 'ap_paterno',
                                'Ap. Materno': 'ap_materno',
                                'Nombres': 'nombres',
                                'Distrito': 'distrito',
                                'Dependencia': 'nombre_oficina',
                                'Año': 'anio',
                                'Mes': 'mes',
                                'Actividad': 'actividad',
                                'Meta':'meta_cantidad',
                                'Avance': 'meta_avance'
                            };
                            if(this.json_data.length > 0){
                                this.exporta = true;
                            }
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
        },
        listPersonal(ofID){
            var url = 'metasApi?personal=true&ofID='+ofID;
            axios.get(url)
                .then(response =>{
                    this.personal = response.data.personal;
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                    } else if (errors.response.status === 403) {
                        console.log(errors.response)
                             window.location = "../inicio";
                    } 
                });
        },
        printSection() {
            this.$htmlToPaper("printSection");
        },
        newItem(){},
        editItem(item){
        },
        viewItem(item){
            this.showLoading(true, 'primary', 'Cargando datos')

            if(item.metames == null){
                this.showSnack('warning', 'El usuario no ha programado sus metas para el mes seleccionado')
                this.showLoading(false, '', ' ')
            } else{
                this.activeItem = item;
                this.metam.anio = this.metam.anio*1
                this.metam.mes = this.metam.mes*1
                this.programa = 2
                axios.post('getMetasXMes', {user_id: this.activeItem.numero_documento, periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.metasxmes = response.data.metasmes;
                            this.d_actividad = true;
                            //this.listarData();
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                        this.showLoading(false, '', ' ')
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                        this.showLoading(false, '', ' ')
                    });
            }
            
        },
        deleteItem(item){
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

        vbAnx(){
            this.showLoading(true, 'primary', 'Generando anexo')

            axios.get('vbanexo04/'+ this.activeItem.numero_documento+'/'+this.activeItem.anexo)
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.showSnack('success', response.data['messageDB'])
                            this.d_anexo = false;
                            this.activeItem.enable_anexo = 1;
                            this.genrandom()
                            //this.listarData();
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

        deleteMeta(id){
            
            let indexOfMeta = this.metasxmes.map(function(e) { return e.id; }).indexOf(id);
            if(indexOfMeta != -1){
                this.metasxmes.splice(indexOfMeta,1);

            } else{
                this.showSnack('warning', 'Ya se elimino esta actividad')
            }
                
        },  
        save(){
            if(this.activeItem.actividad != ''){
                this.showLoading(true, 'primary', 'Guardando datos')
                axios.post('setMetasXMes', {trabajador_id: this.activeItem.numero_documento, actividades : this.metasxmes, periodo : this.metam})
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
                        if (errors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                        this.showLoading(false)

                    });
            } else{
                this.showSnack('error', 'Debe poner la descripción de la actividad')
            }
            
        },
        aperturar(){
            if(this.activeItem.actividad != ''){
                this.showLoading(true, 'primary', 'Guardando datos')
                axios.post('openMetasXMes', {trabajador_id: this.activeItem.numero_documento, periodo : this.metam})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.d_actividad = false;
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                        this.showLoading(false)
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                        this.showLoading(false)

                    });
            } else{
                this.showSnack('error', 'Debe poner la descripción de la actividad')
            }
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