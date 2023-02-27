<template>
        <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" />
        <v-content id="contentApp">
            <v-row>
                <v-col cols="12">
                    <v-container>
                    <v-banner
                        :sticky="true"
                        >
                        <v-avatar
                            slot="icon"
                            color="red darken-4"
                            size="50"
                            >
                            <v-icon
                                icon="mdi-lock"
                                color="white"
                            >
                                mdi-calendar
                            </v-icon>
                        </v-avatar>
                        <b>{{fecha}}</b>
                    </v-banner>
                    </v-container>
                    <v-row
                    align="center"
                    justify="center"
                    class="grey lighten-5"
                    min-height="300"
                    >
<!--------------  BOTON INGRESO *********************************************        -->
                        <v-card
                            :key="modulo.id"
                            class="ma-3 pa-1 none-decoration"
                            outlined color="primary" dark
                            tile shaped
                            elevation="15" link
                            :disabled="ingreso != ''"
                            @click="registrarIngreso()"
                        >
                        
                            <div class="d-flex flex-no-wrap justify-space-between">
                                <div>
                                    <v-card-title
                                    class="headline"
                                    >Ingreso</v-card-title>

                                    <v-card-subtitle> {{ingreso == ''? 'Registrar Ingreso':'Ingreso registrado'}}</v-card-subtitle>
                                    <v-card-actions>
                                        <v-chip v-if="ingreso != ''"><b>{{ingreso}}</b></v-chip>
                                    </v-card-actions>
                                </div>
                                <v-avatar
                                    class="ma-3"
                                    size="125"
                                    tile
                                >
                                    <v-icon size="125">mdi-account-clock</v-icon>
                                </v-avatar>
                            </div>
                        
                        </v-card>

<!--------------  BOTON SALIDA *********************************************        -->

                        <v-card
                            :key="modulo.id"
                            class="ma-3 pa-1 none-decoration"
                            outlined color="success" dark
                            tile shaped
                            elevation="15" link
                            v-if="ingreso != ''"
                            :disabled="salida != ''"
                            @click="registrarSalida()"
                        >
                        
                            <div class="d-flex flex-no-wrap justify-space-between">
                                <div>
                                    <v-card-title
                                    class="headline"
                                    >Salida</v-card-title>

                                    <v-card-subtitle> {{salida == ''? 'Registrar Salida':'salida registrada'}}</v-card-subtitle>
                                    <v-card-actions>
                                        <v-chip v-if="salida != ''"><b>{{salida}}</b></v-chip>
                                    </v-card-actions>
                                </div>
                                <v-avatar
                                    class="ma-3"
                                    size="125"
                                    tile
                                >
                                    <v-icon size="125">mdi-exit-run</v-icon>
                                </v-avatar>
                            </div>
                        
                        </v-card>
                    </v-row>
                </v-col>
            </v-row>
        </v-content>
        <snackbar :snack="snack" ></snackbar>
        <footer-component/>

    </v-app>
</template>
<script>
export default {
    data() {
        return {
            menu: 0,
            submenu: 2,
            modulo: 'asistencia',
            drawer:true,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            fecha:'',
            ingreso: '',
            salida:'',
            asistenciaID: ''
        }
    },
    created(){
        this.iniciarDia();
    },
    methods: {
        iniciarDia(){
            var url = 'asistenciaApi?init=true'
            axios.get(url)
                .then(response =>{
                    this.fecha = response.data.fecha;
                    if(response.data.asistencia.length != 0){
                        this.ingreso = response.data.asistencia[0].hora_inicio
                        if(response.data.asistencia[0].hora_fin != null){
                            this.salida = response.data.asistencia[0].hora_fin
                        }
                        this.asistenciaID = response.data.asistencia[0].id
                    }
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
                        this.iniciarDia();
                    }
                });
        },
        registrarIngreso(){
            axios.post('asistenciaApi')
            .then(response =>{
                    if (response.data['statusBD']) {
                        this.showSnack('success', response.data['messageDB'], true)
                        this.ingreso = response.data['hora_ingreso'];
                        this.iniciarDia();
                    } else{
                        this.showSnack('red', response.data['messageDB'])
                    }
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                     }
                     this.iniciarDia();
                });
        },
        registrarSalida(){
            axios.put('asistenciaApi/'+this.asistenciaID)
                .then(response =>{
                    if (response.data['statusBD']) {
                        this.showSnack('success', response.data['messageDB'])
                        this.salida = response.data['hora_fin']
                    } else{
                        this.showSnack('red', response.data['messageDB'])
                    }
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                     }
                     this.iniciarDia();
                });
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