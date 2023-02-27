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
                            <v-flex xs12 sm6 md4 v-bind:key="modulo.id" v-if="hasRole(modulo.role) || modulo.role == 'All'">
                                <v-hover>
                                    <template v-slot:default="{ hover }">
                                        <v-card
                                        :key="modulo.id"
                                        class="ma-3 pa-1 none-decoration text-decoration-none"
                                        outlined :color="modulo.color" dark
                                        tile shaped
                                        hover
                                        min-height="150px"
                                        v-if="hasRole(modulo.role) || modulo.role == 'All'"
                                        elevation="15" link
                                        :href="modulo.link"
                                    >
                                    
                                        <div class="d-flex flex-no-wrap justify-space-between">
                                        <div>
                                            <v-card-subtitle
                                                class="headline pb-0 text-white"
                                                >{{modulo.name}}
                                            </v-card-subtitle>

                                            <v-card-subtitle class="pt-0"> Ingresar al módulo</v-card-subtitle>
                                            <v-card-actions>
                                            <!--<v-btn text>Ingresar</v-btn>-->
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
            modulo: 'dashboard',
            drawer:true,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            modulos:[
                {id:1, name:'Asistencia', link:'asistencia/', color:'deep-orange accent-1', icon:'mdi-clipboard-list', 
                        role:'All'},
                {id:4, name:'Personal', link:'personal/', color:'teal lighten-1', icon:'mdi-account-group-outline', 
                        role:'Personal.administrador|Personal.licencias|Personal|Webmaster|Administrador'},
                
                {id:5, name:'Buscador de Jurisprudencia', link:'jurisprudencia/', color:'#afb42b', icon:'mdi-gavel', 
                        role:'Webmaster|Jurisprudencia.administrador|Jurisprudencia.usuario'},
                
                {id:9, name:'Reportes SIJ', link:'utilsij/', color:'#0277BD', icon:'mdi-vote', 
                        role:'SIJ.usuario|Webmaster'},
                {id:99, name:'Configurar', link:'admin/', color:'blue-grey darken-4', icon:'mdi-tools',
                         role:'Administrador|administrador2'},
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