<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">
            <v-card
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Jerarquia de Oficinas</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="info" @click="listarData()">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                    <v-btn @click="d_oficinas = true" color="red darken-3">
                        <v-icon>mdi-plus</v-icon>
                        Agregar Relación
                    </v-btn>
                </v-app-bar>
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
            </v-card>
        </v-main>

        <v-dialog 
            v-model="d_oficinas"
            scrollable  
            :overlay="false"
            max-width="800px"
            transition="dialog-transition"
        >
            <v-card>
                <v-toolbar color="red darken-1" dark>
                    <v-toolbar-title>Adicionar Relación Jefe Inmediato</v-toolbar-title>
                </v-toolbar>
                <v-card-text>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md12>
                            <v-autocomplete
                                v-model="activeItem.op_oficina_id_A"
                                label="Oficina A"
                                :items="oficinas"
                                :rules="[rules.required]"
                                item-text="nombre_oficina"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md12>
                            <v-autocomplete
                                v-model="activeItem.op_oficina_id_B"
                                label="Oficina B"
                                :items="oficinas"
                                :rules="[rules.required]"
                                item-text="nombre_oficina"
                                item-value="id"
                            />
                        </v-flex>  
                    </v-layout>

                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                        <v-btn color="success" @click="save()"><v-icon>mdi-content-save</v-icon> {{btn_registrar}}</v-btn>
                        <v-btn color="red" dark @click="d_oficinas = false"><v-icon>mdi-close</v-icon> Cancelar</v-btn>
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
            menu: this.hasRole('Asistencia.JSupervisor')? 5 : 3,
            submenu: -1,
            modulo: 'asistencia',
            drawer:true,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            pagina_actual:1,
            n_paginas:1,
            search:'',
            headers: [
                { text: 'Dependencia Padre', value:'nombre_oficina_A'},
                { text: 'Distrito A', value:'distrito_A'},
                { text: 'Dependencia Hijo', value:'nombre_oficina_B'},
                { text: 'Distrito B', value:'distrito_B'},
                { text: 'Opciones', value: 'delete', acciones: 'registrar', type:'opciones' }
            ],
            rules: {
               entero: v => v > 0 || 'El campo solo admite números',
               required: v => !!v || 'Descripcion Requerido',
               dni: v=> v != undefined && v.length == 8 || 'Longitud incorrecta'
            },
            itemsPerPage:15,
            activeItem:{
                op_oficina_id_A:'',
                op_oficina_id_B:''
            },
            data: [
            ],
            oficinas:[],
            filters: {
            },
            d_oficinas:false
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
        d_oficinas(val){
            if(!val){
                this.activeItem.op_oficina_id_A = '';
                this.activeItem.op_oficina_id_B = '';
            }
        }  
    },
    created(){
        this.listarData();
    },
    methods: {
        listarData(pag=1, search=null, perpage=15){
            this.itemsPerPage = perpage;
            var url = 'metasApi?parents=true&page='+pag+'&perpage=all';
            this.pagina_actual = pag;

            axios.get(url)
                .then(response =>{
                    this.data = response.data.parents.data;
                    this.n_paginas = response.data.parents.last_page;
                    this.oficinas = response.data.oficinas;
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
        printSection() {
            this.$htmlToPaper("printSection");
        },
        newItem(){},
        editItem(item){},
        viewItem(item){},
        deleteItem(item){
            if (item.id!= null){
                axios.put('doparents/'+item.id)
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.listarData();
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
            if(this.activeItem.op_oficina_id_A != '' && this.activeItem.op_oficina_id_B != ''){
                if (this.activeItem.op_oficina_id_A != this.activeItem.op_oficina_id_B){
                    axios.post('doparents', {of_A_id : this.activeItem.op_oficina_id_A, of_B_id: this.activeItem.op_oficina_id_B})
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.listarData();
                                    this.showSnack('success', response.data['messageDB'])
                                    //this.d_oficinas = false;
                                    //this.activeItem.op_oficina_id_A = '';
                                    //this.activeItem.op_oficina_id_B = '';
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
                    this.showSnack('error', 'Las depedencias deben ser distintas')
                }
            } else{
                this.showSnack('error', 'Debe seleccionar las 2 dependencias')
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
        hasRole(role){
            var roles = role.split('|');
            for (let index = 0; index < roles.length; index++) {
                if (this.dataUser.modulos.includes(roles[index])) {
                    return this.dataUser.modulos.includes(roles[index]);
                }
            } 
            return false;
        },
    }
}
</script>