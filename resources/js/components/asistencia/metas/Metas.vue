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
                        <v-col cols="12" sm="4" md="2" xs="12">
                            <v-btn color="info" @click="listarData()" block>
                                <v-icon>mdi-refresh</v-icon>
                                        Actualizar
                            </v-btn>
                        </v-col>
                        <v-col cols="12" sm="4" md="2" xs="12">
                            <v-btn @click="d_actividad = true" dark color="red darken-3" block>
                                <v-icon>mdi-plus</v-icon>
                                Agregar actividad
                            </v-btn>
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
                            @editItem="editItem"
                            @viewItem="viewItem"
                            @deleteItem="deleteItem"
                        />
                    </v-card>
            </div>

            <v-card>
                <v-card-title>
                    Oficinas a cargo
                </v-card-title>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="8">
                            <v-list dense>
                                <v-list-item-group color="primary">
                                    <v-row>
                                        <v-col cols="12" md="4" v-for="oficina in oficinas_h" v-bind:key="oficina.id">
                                            <v-list-item @click="listPersonal(oficina.id)">
                                                <v-list-item-content>
                                                    <v-list-item-title>{{oficina.nombre_oficina}}</v-list-item-title>
                                                    <v-list-item-subtitle>{{oficina.provincia}} - {{oficina.distrito}}</v-list-item-subtitle>
                                                    
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-col>
                                    </v-row>
                                </v-list-item-group>
                            </v-list>
                        </v-col>
                        <v-col cols="12" md="4">
                            <v-card>
                                <v-card-title>Personal</v-card-title>
                                <v-list dense v-if="personal.length > 0">
                                    <v-list-item-group color="primary">
                                        <v-list-item v-for="persona in personal" v-bind:key="persona.id">
                                            <v-list-item-content>
                                                <v-list-item-title>{{persona.nombres}} {{persona.ap_paterno}} {{persona.ap_materno}}</v-list-item-title>
                                                <v-list-item-subtitle>{{persona.nombre_plaza}} <i> [{{persona.regimen_base}}] </i></v-list-item-subtitle>
                                            </v-list-item-content>
                                        </v-list-item>
                                    </v-list-item-group>
                                </v-list>
                                <template v-else>
                                    <i>Seleccione una dependencia</i>
                                </template>
                            </v-card>
                        </v-col>
                    </v-row>
                    
                </v-card-text>
            </v-card>
        </v-main>

        <v-dialog 
            v-model="d_actividad"
            scrollable  
            v-if="d_actividad"
            :overlay="false"
            max-width="800px"
            transition="dialog-transition"
        >
            <v-card>
                <v-toolbar color="red darken-1" dark>
                    <v-toolbar-title>Adicionar Meta </v-toolbar-title>
                </v-toolbar>
                <v-card-text>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md12>
                            <v-text-field
                                v-model="activeItem.actividad"
                                label="Actividad"
                                :readonly="(activeItem.id != undefined && activeItem.id > 0 && activeItem.b_sij*1) == true"
                            />

                        </v-flex>
                        <v-flex xs12 sm12 md12>
                            <v-switch
                                v-model="activeItem.b_area"
                                label="Activo"
                            />
                        </v-flex>  
                    </v-layout>

                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                        <v-btn color="success" @click="save()"><v-icon>mdi-content-save</v-icon> {{btn_registrar}}</v-btn>
                        <v-btn color="red" dark @click="d_actividad = false"><v-icon>mdi-close</v-icon> Cancelar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <snackbar :snack="snack" ></snackbar>
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
                { text: 'Actividad', value:'actividad'},
                { text: 'SIJ', value:'b_sij', type:'boolean_t'},
                { text: 'Activo en mi area?', value:'b_area', type:'boolean_t'},
                { text: 'Opciones', value: 'edit,delete', acciones: 'registrar', type:'opciones' }
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
            d_actividad:false,
            oficinas_h:[],
            personal:[],
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
            return this.activeItem.id > 0 ? 'Actualizar Datos': 'Registrar'
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
        }  
    },
    created(){
        this.listarData();
    },
    methods: {
        listarData(pag=1, search=null, perpage=15){
            this.itemsPerPage = perpage;
            var url = 'metasApi?metas=true&page='+pag+'&perpage=all';
            this.pagina_actual = pag;

            axios.get(url)
                .then(response =>{
                    this.data = response.data.metas.data;
                    this.oficinas_h = response.data.oficinas_h;
                    this.n_paginas = response.data.metas.last_page;
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
                    } else{
                        //this.listarData();
                    }
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
            this.activeItem = item;
            this.activeItem.b_area *=1
            this.d_actividad = true;
        },
        viewItem(item){},
        deleteItem(item){
            if (item.id!= null){
                axios.put('setActivity/'+item.id)
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.d_actividad = false;
                            //this.listarData();
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (rrors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
            } 
        },
        save(){
            if(this.activeItem.actividad != ''){
                axios.post('setActivity', {actividad : this.activeItem})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.d_actividad = false;
                            this.activeItem = {}
                            //this.listarData();
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
            } else{
                this.showSnack('error', 'Debe poner la descripción de la actividad')
            }
            
        },
        close(){
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
    }
}
</script>