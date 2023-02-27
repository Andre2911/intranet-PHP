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
                    <v-btn @click="d_oficinas = true" color="red darken-3">
                        <v-icon>mdi-plus</v-icon>
                        Agregar Excepcion
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
            <select-oficina-component
               @selectOficina="selectOficina" 
            />
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
            menu: this.hasRole('Asistencia.JSupervisor')? 6 : 4,
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
                { text: 'Oficina', value:'nombre_oficina'},
                { text: 'Distrito', value:'distrito'},
                { text: 'Opciones', value: 'delete', acciones: 'registrar', type:'opciones' }
            ],
            itemsPerPage:15,
            data: [
            ],
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
         }
    },
    created(){
        this.listarData();
    },
    methods: {
        listarData(pag=1, search=null, perpage=15){
            this.itemsPerPage = perpage;
            var url = 'asistenciaApi?excepciones=true&page=' + pag + '&date='+this.date + '&perpage=all';
            this.pagina_actual = pag;

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
        selectOficina(item){
            console.log(item);
            this.d_oficinas = false;
            if (item.id != null){
                axios.post('doexcepcion', {'of_ID' : item.id})
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
                        if (errors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
            } 
        },
        
        printSection() {
            this.$htmlToPaper("printSection");
        },
        newItem(){},
        editItem(item){},
        viewItem(item){},
        deleteItem(item){
            if (item.id!= null){
                axios.put('doexcepcion/'+item.id)
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