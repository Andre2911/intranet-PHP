<template>
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
                            ref="search" 
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
                            @selectItem="selectItem"
                        />
                    
                </v-card>
            </v-container>
                <v-dialog
                    v-model="d_persona"
                    scrollable
                    max-width="960PX"
                    transition="dialog-transition"
                >
                    <new-persona-component 
                        :activeItem="activeItem" 
                        @close="close" 
                        :editedItem="editedItem"
                        @showSnack="showSnack"
                    />
                </v-dialog>
            <snackbar :snack="snack" />
        </v-card>

</template>
<script>
export default {
    data() {
        return {
            menu: 1,
            submenu: 2,
            modulo: 'admin',
            drawer:true,
            search:'',
            tab:null,
            ubigeo:'',
            pagina_actual:1,
            n_paginas:1,
            d_persona:false,
            headers: [
                { text: 'Documento', value:'tipo_documento', subvalue:'sigla', type:'subvalue'},
                { text: 'Nro. Doc.', value:'numero_documento'},
                { text: 'Ap. Paterno', value: 'ap_paterno' },
                { text: 'Ap. Materno', value: 'ap_materno' },
                { text: 'Nombres', value: 'nombres'},
                { text: 'Plaza Física', value: 'nombre_plazafisica'},
                { text: 'Oficina', value: 'nombre_oficina'},
                { text: 'Activo', value: 'estatus', type: 'boolean' },
                { text: 'Opciones', value: 'edit,select', acciones: 'registrar', type:'opciones' }
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
            apisybase: null,
        }
    },
    watch: {
        /*search(val){
            this.getDocumentos(1,val)
        }*/
        d_persona(val){
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
    mounted() {
        this.$refs.search.focus();
    },
    created(){
        this.getDocumentos();
    },
    methods: {
        getDocumentos(pag=1, search=null){
            var url = 'personaApi?listar=true&page='+pag;
            this.pagina_actual = pag;

            if(search != null || this.search != ''){
                url = 'personaApi?listar=true&page='+pag+'&search='+this.search;
            }
            axios.get(url)
                .then(response =>{
                    this.data = response.data.personas.data
                    var n_resultados = response.data.personas.total;
                    this.n_paginas = response.data.personas.last_page;
                    this.$refs.search.focus();

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                     }
                     this.getDocumentos();
                });
        },
        newItem(){
            //this.activeItem = -1;
            this.d_persona = true;
            this.activeItem = {ap_paterno:'', ap_materno:'', nombres:'', tipo_documento_id:1}

        },
        editItem(item){
            this.d_persona = true;
            this.activeItem = item;
            this.editedItem = item.id
            this.activeItem.tipo_documento_id = item.tipo_documento_id*1
            this.activeItem.sexo = item.sexo*1
        },
        selectItem(item){
            this.$emit('selectedItem',item);
        },
        viewItem(item){},
        deleteItem(item){},
        save(e){
        },
        close(){
            this.d_persona = false;
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
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
        },
    }
}
</script>