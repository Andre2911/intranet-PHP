<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">

            <v-card
                class="mx-auto"
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Personas Registradas</v-toolbar-title>
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
                                :pagina_selected="pagina_actual"
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
            <new-plazas-component 
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
    props:['dataUser'],
    data() {
        return {
            menu: 1,
            submenu: 3,
            modulo: 'admin',
            drawer:true,
            search:'',
            tab:null,
            ubigeo:'',
            pagina_actual:1,
            n_paginas:0,
            itemsPerPage:15,
            d_plaza:false,
            headers: [
                { text: 'COD Plaza', value:'c_plaza'},
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
            snack: {snackShow:false, snackText: '',snackColor: ''},
            dialogLoad:{show:false, text:'', color:''},
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
            this.showLoad('grey darken-2', 'Realizando consulta');
            var url = 'plazasApi?listar=true&page='+pag+"&perpage="+perpage;
            this.pagina_actual = pag;
            this.itemsPerPage = perpage;

            if(search != null || this.search != ''){
                url = 'plazasApi?listar=true&page='+pag+'&search='+this.search+'&vacias='+this.vacias+"&perpage="+perpage;
            }
            this.itemsPerPage = perpage;

            axios.get(url)
                .then(response =>{
                    this.data = response.data.personas.data
                    this.n_paginas = response.data.personas.last_page*1;
                    this.itemsPerPage = response.data.personas.per_page;
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