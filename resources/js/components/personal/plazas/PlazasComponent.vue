<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">

            <v-card
                class="mx-auto"
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Plazas actuales</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="red accent-3" @click="d_usuario = true">
                        <v-icon>mdi-plus</v-icon>
                                Nuevo Usuario
                    </v-btn>
                    <v-btn color="info" @click="getData(pagina_actual)">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                </v-app-bar>
                    <v-card>
                        <v-card-title>
                            <v-text-field
                                v-model="search"
                                label="Buscar"
                                clearable
                                single-line
                                hide-details
                                @keydown.enter="getData(1, search)"
                            ></v-text-field>
                            <v-btn color="info" @click="getData(1, search)">
                                <v-icon>mdi-magnify</v-icon>
                                Buscar
                            </v-btn>
                            <v-spacer></v-spacer>
                            <v-checkbox v-model="vacias" label="Plazas vacias"/>
                            
                        </v-card-title>
                            <dataTable
                                :data="filteredData"
                                :headers="headers"
                                :filters="filters"
                                :n_paginas="n_paginas"
                                :itemsPerPage="itemsPerPage"
                                @change_page="getData"
                                @editItem="editItem"
                                @viewItem="viewItem"
                                @deleteItem="deleteItem"
                            />
                        
                    </v-card>
            </v-card>
        </v-main>
        <v-dialog
            v-model="d_plaza"
            scrollable
            max-width="960PX"
            transition="dialog-transition"
         >
            <adm-plazas-fisica 
                :activeItem="activeItem" 
                @close="close" 
                :editedItem="editedItem"
                @showSnack="showSnack"
            />
        </v-dialog>
        <v-dialog v-model="d_usuario"
            scrollable
            max-width="960PX"
            transition="dialog-transition">
            <personal-plazas-new-usuario
                :activeItem="activeUser" 
                @close="close" 
                :editedItem="editedUser"
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
            submenu: -1,
            modulo: 'personal',
            drawer:true,
            search:'',
            tab:null,
            ubigeo:'',
            pagina_actual:1,
            n_paginas:0,
            itemsPerPage:15,
            d_plaza:false,
            d_usuario:false,
            vacias:false,
            headers: [
                { text: 'COD Plaza', value:'c_plaza'},
                { text: 'Régimen', value: 'regimen', subvalue:'regimen_base', type:'subvalue' },
                { text: 'Denominación', value:'nombre_plaza'},
                { text: 'Ubic. Nominal', value: 'oficina_cap', subvalue_concat:'nombre_oficina,distrito', type:'subvalues' },
                { text: 'Ubic. Fisica', value: 'oficina_caf', subvalue_concat:'nombre_oficina,distrito', type:'subvalues' },
                { text: 'Nominal', value: 'ocupado_cap', subvalue_concat:'ap_paterno,ap_materno,nombres', type:'subvalue_array_concat' },
                { text: 'Fisico', value: 'ocupado_caf', subvalue_concat:'ap_paterno,ap_materno,nombres', type:'subvalue_array_concat' },
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
            activeUser:{},
            editedUser:-1,
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
            this.getData(1,val)
        }*/
        d_plaza(val){
            if(!val){
                var vacio = {};
                this.activeItem = vacio;
                this.editedItem = -1;
            }
        },
        d_usuario(val){
           if(!val){
                this.activeUser = {};
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
        getData(pag=1, search=null, perpage=15){
            var url = 'plazasApi?listar=true&page='+pag+'&vacias='+this.vacias+"&perpage="+perpage;
            this.pagina_actual = pag;
            this.itemsPerPage = perpage;

            if(search != null || this.search != ''){
                url = 'plazasApi?listar=true&page='+pag+'&search='+this.search+'&vacias='+this.vacias+"&perpage="+perpage;
            }

            axios.get(url)
                .then(response =>{
                    this.data = response.data.personas.data
                    this.n_paginas = response.data.personas.last_page*1;
                    this.itemsPerPage = response.data.personas.per_page*1;

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                        if(errors.response.data.error == 'Unauthenticated' || errors.response.data.message == 'Unauthenticated.' ){
                             window.location = "../login";
                        }
                     }
                     this.getData();
                });
        },
        newItem(){
            //this.activeItem = -1;
            this.d_plaza = true;
        },
        editItem(item){
            this.d_plaza = true;
            this.activeItem = item;
            this.editedItem = item.id
            this.activeItem.jefe_area = item.jefe_area*1
            this.activeItem.activo = item.activo*1
        },
        viewItem(item){},
        deleteItem(item){},
        save(e){
        },
        close(){
            this.d_plaza = false;
            this.getData(this.pagina_actual);
            this.d_usuario = false;
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