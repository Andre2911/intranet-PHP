<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
        <v-main id="contentApp" class="mt-5">
            <v-row>
                <v-col cols="12">
                    <v-row
                    align="center"
                    justify="center"
                    class="grey lighten-5"
                    min-height="300"
                    >
                    <template 
                            v-for="modulo in modulos"   
                    >
                        <v-card
                            :key="modulo.id"
                            class="ma-3 pa-1 none-decoration"
                            outlined :color="modulo.color" dark
                            tile shaped
                            v-if="hasRole(modulo.role)"
                            elevation="15" link
                            :href="modulo.link"
                        >
                        
                            <div class="d-flex flex-no-wrap justify-space-between">
                            <div>
                                <v-card-title
                                class="headline"
                                >{{modulo.name}}</v-card-title>

                                <v-card-subtitle> Ingresar nuevo documento</v-card-subtitle>
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
                        
                        </v-card>
                    </template>
                    </v-row>
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
            submenu: -1,
            modulo: 'admin',
            drawer:true,
            modulos:[
                {id:1, name:'Usuarios', link:'usuarios/', color:'blue-grey darken-1', icon:'mdi-account', role:'Administrador|Webmaster'},
                {id:2, name:'Roles', link:'roles/', color:'blue-grey darken-1', icon:'mdi-table-account', role:'Webmaster'},
                {id:3, name:'Personas', link:'personas/', color:'blue-grey darken-1', icon:'mdi-account-group', role:'Administrador|Webmaster'},
                {id:4, name:'Plazas', link:'plazas/', color:'blue-grey darken-1', icon:'mdi-mdi-file-tree-outline', role:'Administrador|Webmaster'},
                {id:5, name:'Oficinas', link:'oficinas/', color:'blue-grey darken-1', icon:'mdi-office-building-marker', role:'Administrador|Webmaster'},
            ],
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