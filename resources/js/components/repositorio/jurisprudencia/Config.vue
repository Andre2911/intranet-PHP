<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
        <v-main id="contentApp">
            <v-card>
                <v-toolbar color="grey" dark>
                    <v-toolbar-title>Buscador de Jurisprudencia :: Configuración</v-toolbar-title>
                </v-toolbar>
                <v-container fluid grid-list-sm>
                    <v-layout wrap>
                        <v-flex xs12 md8>
                            <v-text-field
                                v-model="ftp"
                                label="FTP destino de archivos"

                            />
                        </v-flex>
                        <v-flex xs12 md4>
                            <v-btn block color="primary" dark @click="save()">
                                <v-icon>mdi-content-save</v-icon>
                                GUARDAR CONFIGURACIÓN
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card>
        </v-main>
        <snackbar :snack="snack" />

    </v-app>
</template>
<script>
export default {
    props:['data-user'],
    data() {
        return {
            menu: 2,
            submenu: -1,
            modulo: 'jurisprudencia',
            drawer:true,
            ftp:'',
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
        }
    },
    created() {
        this.getData();
    },
    methods:{
        getData(){
                axios.get('config?ruta=true')
                    .then(datos =>
                    {
                        this.ftp = datos.data.ruta.ruta;
                    })
                    .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
                            window.location = "../login";
	                     }
	                }); 
        }, 
        save(){
            if(this.ftp != ''){
                axios.post('config', {ftp:this.ftp})
                    .then(datos =>
                    {
                        if(datos.data['statusBD']){
                            this.showSnack('success', datos.data['messageDB'])
                        } else{
                            this.showSnack('error', datos.data['messageDB'])
                        }
                    })
                    .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
                            window.location = "../login";
	                     }
	                });
            } else{
                this.showSnack('warning', 'Debe de completar el campo FTP o carpeta compartida');
            }
            
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