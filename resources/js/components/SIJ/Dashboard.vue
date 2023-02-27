<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
        <v-main id="contentApp" class="mt-5">
            <v-row>
                <v-col cols="12">
                    <v-layout wrap align="center"
                    justify="center">
                        <template 
                                v-for="modulo in modulos"   
                        >
                            <v-flex xs12 sm4 v-bind:key="modulo.id" v-if="hasRole(modulo.role) || modulo.role == 'All'">
                                <v-hover>
                                    <template v-slot:default="{ hover }">
                                        <v-card
                                            :key="modulo.id"
                                            class="ma-3 pa-1 text-decoration-none"
                                            outlined :color="modulo.color" dark
                                            tile shaped
                                            hover
                                            v-if="hasRole(modulo.role) || modulo.role == 'All'"
                                            elevation="15" link
                                            :href="modulo.link"
                                        >
                                        
                                            <div class="d-flex flex-no-wrap justify-space-between">
                                                <div>
                                                    <v-card-title
                                                    class="headline"
                                                    >{{modulo.name}}</v-card-title>

                                                    <v-card-subtitle> Ingresar al módulo</v-card-subtitle>
                                                    <v-card-actions>
                                                    <v-btn text>Ingresar</v-btn>
                                                    </v-card-actions>
                                                </div>
                                                <v-avatar
                                                    class="ma-3"
                                                    size="125"
                                                    tile
                                                >
                                                    <v-icon size="125">{{modulo.icon}}</v-icon>
                                                </v-avatar>
                                            </div>
                                            <v-fade-transition>
                                                <v-overlay
                                                    v-if="hover"
                                                    absolute
                                                    color="#036358"
                                                >
                                                    <v-btn>Ingresar</v-btn>
                                                </v-overlay>
                                            </v-fade-transition>
                                        </v-card>
                                    </template>
                                </v-hover>
                            </v-flex>
                        </template>
                    </v-layout>
                </v-col>
            </v-row>
            <snackbar :snack="snack" />
        </v-main>
        <footer-component/>
    </v-app>
</template>
<script>
export default {
    props:['data-user'],
    data() {
        return {
            menu: 0,
            submenu: 0,
            modulo: 'SIJ',
            drawer:true,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            modulos:[
                {id:1, name:'Audiencias', link:'audiencias/', color:'#0277BD', icon:'mdi-headset-dock', role:'All'},
                {id:2, name:'Escritos', link:'escritos/', color:'red lighten-1', icon:'mdi-file-document-multiple', role:'All'},
                {id:3, name:'Resoluciones', link:'resoluciones/', color:'teal darken-1', icon:'mdi-signature-freehand', role:'All'},
                {id:4, name:'Notificaciones', link:'notificaciones/', color:'purple accent-1', icon:'mdi-email-send-outline', role:'All'},
                {id:5, name:'Usuarios', link:'usuarios/', color:'blue-grey darken-1', icon:'mdi-account-group-outline', role:'Webmaster|Administrador|SIJ.supervisor|Asistencia.JSupervisor'},
            ]
        }
    },
    methods:{
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