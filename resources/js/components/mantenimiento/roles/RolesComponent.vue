<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-content id="contentApp">

            <v-card
                class="mx-auto"
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Administrar Roles</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="success" @click="newItem()">
                        <v-icon>mdi-plus</v-icon>
                                Nuevo
                    </v-btn>
                    <v-btn color="info" @click="getDocumentos()">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                </v-app-bar>
                <v-container>
                    <v-card>
                        <v-card-title>
                            <v-text-field
                                v-model="search"
                                label="Buscar"
                                single-line
                                hide-details
                                @keydown.enter="getDocumentos(1, search)"
                            ></v-text-field>
                            <v-btn color="info" @click="getDocumentos(1, search)">
                                <v-icon>mdi-magnify</v-icon>
                                Buscar
                            </v-btn>
                            <v-spacer></v-spacer>
                            
                        </v-card-title>
                            <dataTable
                                :data="filteredData"
                                :headers="headers"
                                :filters="filters"
                                :n_paginas="n_paginas"
                                @change_page="getDocumentos"
                                @editItem="editItem"
                                @viewItem="viewItem"
                                @deleteItem="deleteItem"
                            />
                        
                    </v-card>
                </v-container>
            </v-card>
        </v-content>
        <v-dialog
            v-model="d_new_edit"
            scrollable
            max-width="960PX"
            transition="dialog-transition"
         >
            <new-roles-component 
                :activeItem="activeItem" 
                @close="close" 
                :editedItem="editedItem"
                @showSnack="showSnack"
            />
        </v-dialog>
        <snackbar :snack="snack" />
        
    </v-app>
</template>
<script>
export default {
    props:['dataUser'],
    data() {
        return {
            menu: 1,
            submenu: 1,
            modulo: 'admin',
            drawer:true,
            search:'',
            tab:null,
            ubigeo:'',
            pagina_actual:1,
            n_paginas:1,
            d_new_edit:false,
            headers: [
                { text: 'ID', value:'id'},
                { text: 'Rol', value:'name'},
                { text: 'Descripción', value: 'description'},
                { text: 'Opciones', value: 'edit', acciones: 'registrar', type:'opciones' }
            ],
            data: [
            ],
            filters: {
               ap_paterno: [],
               ap_materno: [],
               nombres: [],
            },
            activeItem:{},
            defaultItem:{
                tipo_documento_id:null,
                numero_documento:null,
            },
            editedItem:-1,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            apisybase: null,
        }
    },
    watch: {
        /*search(val){
            this.getDocumentos(1,val)
        }*/
        d_new_edit(val){
            if(!val){
                var vacio = {};
                this.activeItem = vacio;
                this.editedItem = -1;
            }
        }
    },
    computed: {
         filteredData() {
            return this.data.filter(d => {
               return Object.keys(this.filters).every(f => {
                  return this.filters[f].length < 1 || this.filters[f].includes(d[f])
               })
            })
         }
      },
    created(){
        this.getDocumentos();
    },
    methods: {
        getDocumentos(pag=1, search=null){
            this.pagina_actual = pag;

            var url = 'rolesApi?listar=true&page='+pag;

            if(search != null || this.search != ''){
                url = 'rolesApi?listar=true&page='+pag+'&search='+this.search;
            }
            axios.get(url)
                .then(response =>{
                    this.data = response.data.roles.data
                    var n_resultados = response.data.roles.total;
                    this.n_paginas = Math.ceil(n_resultados/10);

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                    }
                    if (errors.response.status === 404) {
                        alert("Servicio no encontrado")
                    } else{
                        this.getDocumentos();
                    }
                    
                });
        },
        newItem(){
            //this.activeItem = -1;
            this.d_new_edit = true;
        },
        editItem(item){
            this.d_new_edit = true;
            this.activeItem = item;
            this.editedItem = item.id
        },
        viewItem(item){},
        deleteItem(item){},
        save(e){
        },
        close(){
            this.d_new_edit = false;
            this.getDocumentos();
        },
        cDrawer(val){
            if(val != undefined ){
                this.drawer = val;
            } else{
                this.drawer = !(this.drawer);
            }
        },
        showSnack(color, text, show = true){
            console.log(color)
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
        },
    }
}
</script>