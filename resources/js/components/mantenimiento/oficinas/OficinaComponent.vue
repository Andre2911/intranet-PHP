<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">
            <v-card
                class="mx-auto"
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Administrar Oficinas</v-toolbar-title>
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
                    <v-card>
                        <v-card-title>
                            <v-text-field
                                v-model="search"
                                label="Buscar"
                                single-line
                                clearable
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
                                :itemsPerPage=15
                                :pagina_selected="pagina_actual"
                                @change_page="getDocumentos"
                                @editItem="editItem"
                                @viewItem="viewItem"
                                @deleteItem="deleteItem"
                            />
                        
                    </v-card>
            </v-card>
        </v-main>
        <v-dialog
            v-model="d_new_edit"
            scrollable
            max-width="960PX"
            transition="dialog-transition"
         >
            <new-oficina-component 
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
            submenu: 4,
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
                { text: 'Oficina', value:'nombre_oficina'},
                { text: 'Of. Padre', value:'parent', subvalue:'nombre_oficina', type:'subvalue'},
                { text: 'Distrito', value: 'view', subvalue:'distrito', type:'subvalue'},
                { text: 'Personal', value: 'have_personal', type:'boolean'},
                { text: 'CAP', value: 'show_cap', type:'boolean'},
                { text: 'CAF', value: 'show_caf', type:'boolean'},
                { text: 'Activo', value: 'activo', type:'boolean'},
                { text: 'Opciones', value: 'edit,delete', acciones: 'registrar', type:'opciones' }
            ],
            data: [
            ],
            filters: {
               distrito: [],
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
        getDocumentos(pag=1, search=null, perpage=15){
            this.pagina_actual = pag;
            this.showLoad('grey darken-2', 'Realizando consulta');

            var url = 'oficinasApi?listar=true&page='+pag+'&perpage='+perpage;

            if(search != null || this.search != '' || this.search != null){
                url = 'oficinasApi?listar=true&page='+pag+'&search='+this.search;
            }

            axios.get(url)
                .then(response =>{
                    this.data = response.data.oficinas.data
                    this.n_paginas = response.data.oficinas.last_page;
                    this.itemsPerPage = response.data.oficinas.per_page;
                    this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        window.location = "../login";
                    }
                    if (errors.response.status === 404) {
                        alert("Servicio no encontrado")
                    } else{
                        this.getDocumentos(this.pagin);
                    }
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
            this.activeItem.have_childrens = item.have_childrens*1;
            this.activeItem.have_personal = item.have_personal*1;
            this.activeItem.show_cap = item.show_cap*1;
            this.activeItem.show_caf = item.show_caf*1;
            this.activeItem.activo = item.activo*1;
            this.editedItem = item.id
        },
        viewItem(item){},
        deleteItem(item){
            confirm('¿Estas seguro que desea eliminar esta Dependencia?') && 
            axios.delete(`oficinasApi/${item.id}`)
               .then(response =>{
                    if (response.data['statusBD']) {
                        this.showSnack('success', response.data['messageDB'])
                    }else{
                        this.showSnack('error', response.data['messageDB'])
                    }
                  this.getDocumentos(this.pagina_actual)
               })
               .catch(errors =>{
                  console.log(errors);
               });
        },
        save(e){
        },
        close(){
            this.d_new_edit = false;
            this.getDocumentos(this.pagina_actual);
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
        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
    }
}
</script>