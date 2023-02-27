<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-content id="contentApp">

            <v-card
                class="mx-auto"
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Usuario Registrados</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="success" @click="newItem()">
                        <v-icon>mdi-plus</v-icon>
                                Nuevo
                    </v-btn>
                    <v-btn color="info" @click="getData()">
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
                                @keydown.enter="getData(1, search)"
                            ></v-text-field>
                            <v-btn color="info" @click="getData(1, search)">
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
                                :itemsPerPage="itemsPerPage"
                                :pagina_selected="pagina_actual"
                                @change_page="getData"
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
            <new-usuarios-component 
                :activeItem="activeItem" 
                @close="close" 
                :editedItem="editedItem"
                @showSnack="showSnack"
            />
        </v-dialog>
        <snackbar :snack="snack" />
        <dialogLoader :dialogLoad="dialogLoad"/>
        
    </v-app>
</template>
<script>
export default {
    props: ['dataUser'],
    data() {
        return {
            menu: 1,
            submenu: 0,
            modulo: 'admin',
            drawer:true,
            search:'',
            tab:null,
            ubigeo:'',
            pagina_actual:1,
            n_paginas:1,
            itemsPerPage:15,
            d_new_edit:false,
            headers: [
                { text: 'Usuario', value:'username'},
                { text: 'Persona', value: 'persona', subvalue_concat:'ap_paterno,ap_materno,nombres', type:'subvalues'},
                { text: 'Roles', value: 'roles', subvalue:'name', type:'subvalue_array' },
                { text: 'Estado', value: 'estado', type:'boolean'},
                { text: 'Opciones', value: 'view,edit', acciones: 'registrar', type:'opciones' }
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
            snack: {snackShow:false, snackText: '',snackColor: ''},
            dialogLoad:{show:false, text:'', color:''},
            apisybase: null,
        }
    },
    watch: {
        /*search(val){
            this.getData(1,val)
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
        this.getData();
    },
    methods: {
        getData(pag=1, search=null){
            this.showLoad('grey darken-2', 'Realizando consulta');
            var url = 'usuariosApi?listar=true&page='+pag;
            this.pagina_actual = pag;

            if(search != null || this.search != ''){
                url = 'usuariosApi?listar=true&page='+pag+'&search='+this.search;
            }
            axios.get(url)
                .then(response =>{
                    this.data = response.data.personas.data
                    var n_resultados = response.data.personas.total;
                    this.n_paginas = response.data.personas.last_page;
                    this.itemsPerPage = response.data.personas.per_page;;
                    this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                     }
                    this.getData(this.pagina_actual);
                    this.showLoad('','', false);

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
            this.getData(this.pagina_actual);
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
    }
}
</script>