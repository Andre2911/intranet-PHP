<template>
        <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" />
        <v-content id="contentApp">
            <v-card
                class="mx-auto"
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Personas Registradas</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="info" @click="listarData()">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                </v-app-bar>
                <v-container>
                    <v-flex xs12>
                        <v-btn @click="printSection" color="success">
                            <v-icon>mdi-printer</v-icon>
                            Imprimir
                        </v-btn>
                    </v-flex>
                    <div id="printSection">
                        
                        <v-card>
                            <v-card-title>
                                <v-text-field
                                    v-model="search"
                                    label="Buscar"
                                    single-line
                                    hide-details
                                    @keydown.enter="listarData(1, search)"
                                ></v-text-field>
                                <v-btn color="info" @click="listarData(1, search)">
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
                                    @change_page="listarData"
                                    @editItem="editItem"
                                    @viewItem="viewItem"
                                    @deleteItem="deleteItem"
                                />
                            
                        </v-card>
                    </div>
                </v-container>
            </v-card>
        </v-content>
        <snackbar :snack="snack" ></snackbar>
        <footer-component/>

    </v-app>
</template>
<script>
export default {
    data() {
        return {
            menu: 2,
            submenu: 0,
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
                { text: 'Fecha', value:'fecha'},
                { text: 'Apellidos y Nombres', value:'persona'},
                { text: 'Ingreso', value:'hora_inicio'},
                { text: 'Salida', value: 'hora_fin' },
            ],
            data: [
            ],
            filters: {
            },
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
         }
    },
    created(){
        this.listarData();
    },
    methods: {
        listarData(pag=1, search=null){
            var url = 'asistenciaApi?reporte=true&page='+pag;
                        this.pagina_actual = pag;

            if(search != null || this.search != ''){
                url = 'asistenciaApi?reporte=true&page='+pag+'&search='+this.search;
            }
            axios.get(url)
                .then(response =>{
                    this.data = response.data.asistencias.data;
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
                        this.listarData();
                    }
                });
        },
        printSection() {
            this.$htmlToPaper("printSection");
        },
        newItem(){
        },
        editItem(item){},
        viewItem(item){},
        deleteItem(item){},
        save(e){
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