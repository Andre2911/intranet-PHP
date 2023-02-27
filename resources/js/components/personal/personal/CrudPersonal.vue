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
                    <v-btn color="info" @click="getData(1)">
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
                                :data="data"
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
            menu: 0,
            submenu: -1,
            modulo: 'personal',
            drawer:true,
            search:'',
            tab:null,
            ubigeo:'',
            pagina_actual:1,
            n_paginas:0,
            itemsPerPage:10,
            d_plaza:false,
            headers: [
                { text: 'DNI', value:'numero_documento'},
                { text: 'Ap. Paterno', value: 'ap_paterno' },
                { text: 'Ap. Materno', value: 'ap_materno' },
                { text: 'Nombres', value: 'nombres'},
                { text: 'Plaza Física', value: 'nombre_plazafisica'},
                { text: 'Oficina', value: 'nombre_oficina'},
                { text: 'Opciones', value: 'edit', acciones: 'registrar', type:'opciones' }
            ],
            data: [
            ],
            filters: {
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
            dialogLoad: {
					show:false,
					text: '',
					color: '',
				}, 
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
    },
    created(){
        this.getData();
    },
    methods: {
        getData(pag=1, search=null, perpage=10){
            this.showLoading(true, 'grey', 'Cargando datos', );

            var url = 'personalApi?listar=true&page='+pag+'&perpage='+perpage;
            this.pagina_actual = pag;

            if(search != null || this.search != ''){
                url = 'personalApi?listar=true&page='+pag+'&search='+this.search+'&perpage='+perpage;
            }
            this.itemsPerPage = perpage;

            axios.get(url)
                .then(response =>{
                    this.data = response.data.personas.data
                    this.n_paginas = response.data.personas.last_page;

                this.showLoading(false);

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
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