<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">
            <v-card
            >
                <v-app-bar dark color="grey">
                    <v-toolbar-title>Histórico de asistencias</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="info" @click="listarData()">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                    <v-btn @click="listarData(1,null, 'all')" color="primary">
                        <v-icon>mdi-filter-variant</v-icon>
                        Mostrar todo
                    </v-btn>
                    <v-btn @click="printSection()" color="success">
                        <v-icon>mdi-printer</v-icon>
                        Imprimir
                    </v-btn>
                    
                </v-app-bar>
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
                                    :itemsPerPage="itemsPerPage"
                                    @change_page="listarData"
                                    @editItem="editItem"
                                    @viewItem="viewItem"
                                    @deleteItem="deleteItem"
                                />
                            
                        </v-card>
                    </div>
            </v-card>
        </v-main>
        <snackbar :snack="snack" ></snackbar>
        <footer-component/>

    </v-app>
</template>
<script>
export default {
    props:['dataUser'],
    data() {
        return {
            menu: 1,
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
                { text: 'Fecha', value:'fecha'},
                { text: 'Ingreso', value:'hora_inicio'},
                { text: 'Inicio Refrigerio', value:'refrigerio_inicio'},
                { text: 'Regreso Refrigerio', value:'refrigerio_fin'},
                { text: 'Salida', value: 'hora_fin' },
                { text: 'Remoto Compl. Ingreso', value: 'hora_inicio2' },
                { text: 'Remoto Compl. Salida ', value: 'hora_fin2' },
                {text: 'Tipo', value: 'tipo', type: 'array', array:'1=Trabajo Presencial,2=Trabajo Remoto'},
            ],
            itemsPerPage:15,
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
        listarData(pag=1, search=null, perpage=15){
            this.itemsPerPage = perpage;
            var url = 'asistenciaApi?listar=true&page='+pag+"&perpage="+this.itemsPerPage;
            this.pagina_actual = pag;

                if(perpage == 'all'){
                    this.search = '';
                    this.exporta = true;
                    url = 'asistenciaApi?listar=true&page=' + pag + '&date='+this.date + '&perpage=all';
                    this.itemsPerPage = 5000;
                } 

            if(search != null || this.search != ''){
                url = 'asistenciaApi?listar=true&page='+pag+'&search='+this.search+"&perpage="+this.itemsPerPage;
            }
            axios.get(url)
                .then(response =>{
                    this.data = response.data.asistencias.data;
                    this.n_paginas = response.data.asistencias.last_page;
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