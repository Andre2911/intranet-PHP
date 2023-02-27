<template>
    <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
        <v-main id="contentApp">

            <v-card>
                <v-toolbar color="grey" dark>
                    <v-toolbar-title>SIJ - Consulta de operaciones por Usuario</v-toolbar-title>
                    <v-spacer/>
                </v-toolbar>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="2">
                            <v-menu
                                v-model="datetime_ini"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                transition="scale-transition"
                                offset-y
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                    <v-text-field
                                    v-model="f_inicio"
                                    label="Fecha de Inicio"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-on="on"
                                    dense
                                    ></v-text-field>
                                </template>
                                <v-date-picker 
                                    v-model="date_inicio"
                                    locale="es-419"
                                    @input="datetime_ini = false"
                                ></v-date-picker>
                            </v-menu>
                        </v-col>
                        <v-col cols="12" md="2">
                            <v-menu
                                v-model="datetime_final"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                transition="scale-transition"
                                offset-y
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                    <v-text-field
                                    v-model="f_final"
                                    label="Fecha final"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-on="on"
                                    dense
                                    ></v-text-field>
                                </template>
                                <v-date-picker 
                                    v-model="date_final"
                                    locale="es-419"
                                    @input="datetime_final = false"
                                ></v-date-picker>
                            </v-menu>
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-autocomplete
                                v-model="usuario"
                                label="Usuario"
                                dense
                                clearable
                                :items="usuarios"
                                item-text="nombrecompleto"
                                item-value="numero_documento"
                            />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-autocomplete
                                v-model="reporte"
                                label="Reporte"
                                dense
                                clearable
                                :items="reportes"
                                item-text="descripcion"
                                item-value="id"
                            />
                        </v-col>
                        <v-col cols="12" md="2" class="pt-0 pb-0">
                            <v-btn block :disabled="usuario == null || reporte == null" @click="getData()" color="primary" dense>
                                <v-icon>mdi-magnify</v-icon>
                                Buscar 
                            </v-btn>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col cols="12" md="12">
                            <sij-usuario-general
                                v-if="reporte == 1 && datos.length > 0"
                                :datos="datos"
                                :f_inicio="f_inicio"
                                :f_final="f_final"
                                :dataUser="dataUser"
                                :usuario="usuario"
                                @showSnack="showSnack"  @showLoad="showLoad" 
                            />
                            <sij-usuario-resoluciones
                                v-if="(reporte == 2 || reporte == 3 || reporte == 4 || reporte == 5) && datos.length > 0"
                                :datos="datos"
                                :f_inicio="f_inicio"
                                :f_final="f_final"
                                :dataUser="dataUser"
                                @showSnack="showSnack"  @showLoad="showLoad" 
                            />

                            <sij-usuario-audiencias
                                v-if="(reporte == 6 || reporte == 7 || reporte == 8) && datos.length > 0"
                                :datos="datos"
                                :f_inicio="f_inicio"
                                :f_final="f_final"
                                :dataUser="dataUser"
                                @showSnack="showSnack"  @showLoad="showLoad" 
                            />
                            <sij-usuario-escritos
                                v-if="(reporte == 9) && datos.length > 0"
                                :datos="datos"
                                :f_inicio="f_inicio"
                                :f_final="f_final"
                                :dataUser="dataUser"
                                @showSnack="showSnack"  @showLoad="showLoad" 
                            />
                        </v-col>    
                    </v-row>


                </v-card-text>
            </v-card>
            <snackbar :snack="snack" />
            <dialogLoader :dialogLoad="dialogLoad"/>
        </v-main>
    </v-app>
</template>

<script>
import {Chart} from 'highcharts-vue'

export default {
    props:['dataUser'],
    components: {
        highcharts: Chart 
    },
    data() {
        return {
            menu: 5,
            submenu: -1,
            modulo: 'SIJ',
            drawer:true,
            datos:[],
            data:{},
            usuarios:[],
            usuario:null,
            reporte:null,
            datetime_ini: false,
            f_inicio: this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10))),
            date_inicio: this.parseDate(new Date().toLocaleDateString()),
            datetime_final: false,
            f_final: this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10))),
            date_final: this.parseDate(new Date().toLocaleDateString()),
            reportes: [
                {id:1, descripcion:'Datos generales'},
                {id:2, descripcion:'Proyección de decretos, autos y resoluciones'},
                {id:3, descripcion:'Descargo de decretos, autos y resoluciones'},
                {id:4, descripcion:'Descargo con Proveido'},
                {id:5, descripcion:'Generación de oficios y exhortos'},
                {id:6, descripcion:'Programación de audiencias'},
                {id:7, descripcion:'Realización de audiencias'},
                {id:8, descripcion:'Subida de actas'},
                {id:9, descripcion:'Registro de escritos'},
            /*    {id:10, descripcion:'Generación de cédulas'},
                {id:11, descripcion:'Generación de Guias de Entrega de cédulas'},
                {id:12, descripcion:'Recepción de cédulas notificadas'},
                {id:13, descripcion:'Elevación de auto'},
                {id:14, descripcion:'Bajada de auto'},
                {id:15, descripcion:'Endose de cupones'},
                */
            ],
            

            snack: {snackShow:false, snackText: '',snackColor: ''},
            dialogLoad:{show:false, text:'', color:''},
        }
    },
    watch: {
        date_inicio (val) {
            this.f_inicio = this.formatDate(this.date_inicio)
        },
        date_final (val) {
            this.f_final = this.formatDate(this.date_final)
        },
        usuario (val){
            this.datos = []
        },
        reporte (val){
            this.datos = []
        }
    },
    created() {
        this.iniParams()
    },
    methods:{
        iniParams(){
            this.showLoad('grey', 'Cargando datos');
            if(this.hasRole('Asistencia.JSupervisor')){
                var url = 'utilSIJApi/?list_personal_sup=true'
            } else{
                var url = 'utilSIJApi/?list_personal=true'
            }
            axios.get(url)
                .then(response =>{
                    this.usuarios = response.data['personal'];
                    this.showLoad('', '', false);
                })
                .catch(errors =>{
                    this.showLoad('', '', false);
                    console.log(errors);
                });
        },
        getData(){
            var url_meta = 'consulta' + this.reporte;
            let url = 'SIJgetRegUsuario?url_meta=' + url_meta + 
																'&dni_u=' + this.usuario + 
																'&f_ini=' + this.parseDate(this.f_inicio) + 
                                                                '&f_fin=' + this.parseDate(this.f_final);

            this.datos = []
            this.showLoad('grey', 'Realizando consulta');
            axios.get(url)
                    .then(response =>{
                        if(response.data.data != null && response.data.status == 200){
                            if (response.data.data.length > 0){
                                this.datos = response.data.data
                                if(this.reporte == 1){
                                    this.showSnack('success', 'Consulta realizada')
                                } else{
                                    this.showSnack('success', 'Se obtuvieron ' + response.data.data.length + ' registros')
                                }
                            } else{
                                this.showSnack('success', 'Se obtuvieron 0 registros')
                            }
                        } else if(response.data.status == 0){
                            this.showSnack('warning','No se puede establecer la conexion SIJ, de realizar trabajo remoto verifique su conexión VPN (Forticlient)')
                            window.location = "../login";
                        } else if(response.data.status == 404){
                            this.showSnack('warning','No se puede realizar la consulta SIJ, consulte al administrador')
                        }
                        this.showLoad('', '', false);

                    })
                    .catch(errors =>{
                        if (errors.response.status === 401) {
                            window.location = "../login";
                        }
                        this.showLoad('', '', false);
                        
                    });
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
        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
        formatDate (date) {
            if (!date) return null

            const [year, month, day] = date.split('-')
            return `${day}/${month}/${year}`
        },
        parseDate (date) {
            if (!date) return null
                const [day, month, year] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
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