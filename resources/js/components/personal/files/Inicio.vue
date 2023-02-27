<template>
   <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
      <v-main id="contentApp">         
            <v-card>
                <v-toolbar color="grey" dark>
                    <v-toolbar-title>
                        Files de personal
                    </v-toolbar-title>
                </v-toolbar>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12" md="4">
                                <v-autocomplete
                                    v-model="cargo_id"
                                    label="Cargo"
                                    :items="cargos_list"
                                    clearable
                                    item-text="text"
                                    item-value="id"
                                />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-autocomplete
                                    v-model="especialidad_id"
                                    label="Especialidad"
                                    :items="especialidades_list"
                                    item-text="text"
                                    item-value="id"
                                    clearable
                                />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-autocomplete
                                    v-model="sede_id"
                                    label="Sede"
                                    :items="sedes_list"
                                    item-text="n_distrito"
                                    item-value="ubigeo_id"
                                    clearable
                                />
                            </v-col>

                            <v-col>
                                <dataTable
                                    :headers="headers"
                                    :data="files"
                                    :filters="filters"
                                    tipo="1"
                                    @viewItem="viewDetail"
                                />
                            </v-col>
                        </v-row>
                        
                    </v-container>
                </v-card-text>
            </v-card>
        </v-main>

        <v-dialog
            v-model="d_persona"
            scrollable
                max-width="1200PX"
                transition="dialog-transition"
        >
            <v-card>
                    <v-col
                        class="text-center"
                    >

                        <v-card class="pt-4 mx-auto" flat max-width="900">
                            <v-layout
                                tag="v-card-text"
                                text-xs-left
                                wrap
                            >
                                <v-flex xs6 md6 sm6 text-xs-center>
                                    <v-card-text>
                                        <v-avatar
                                          size="125"
                                        >
                                            <v-icon size="125" color="primary">mdi-badge-account</v-icon>
                                        </v-avatar>
                                        <h3 class="headline mb-2">
                                        {{activeItem.ap_paterno}} {{activeItem.ap_materno}} 
                                        <br>
                                        {{activeItem.nombres}}
                                        </h3>
                                    </v-card-text>
                                    <v-divider></v-divider>
                                    <v-flex tag="strong" xs12 text-xs-right mr-2 mb-2>Plaza Titular:</v-flex>
                                    <v-flex>
                                        {{activeItem.nombre_plazatitular}}
                                    </v-flex>
                                    <v-flex tag="strong" xs12 text-xs-right mr-2 mb-2>Plaza Física:</v-flex>
                                    <v-flex>
                                        {{activeItem.nombre_plazafisica}}
                                        <br>
                                        {{activeItem.nombre_oficina}}
                                    </v-flex>
                                </v-flex>
                                <v-flex xs6 md6 sm6>
                                    <v-layout wrap xs12>
                                    <v-flex tag="strong" xs12 text-xs-right mr-2 mb-2>Datos Académicos:
                                        <v-list dense>
                                            <v-list-item three-line v-for="dato_acd in activeItem.opbl_academico" v-bind:key="dato_acd.id">
                                                <v-list-item-content>
                                                    <v-list-item-title>{{dato_acd.grado_profesional}}: {{dato_acd.carrera}}</v-list-item-title>
                                                    <v-list-item-subtitle>
                                                    {{dato_acd.universidad}}
                                                    </v-list-item-subtitle>
                                                    <v-list-item-subtitle>
                                                    {{dato_acd.f_expedicion}} {{dato_acd.estado}}
                                                    </v-list-item-subtitle>
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-list>
                                    </v-flex>
                                    
                                    <v-flex tag="strong" xs12 text-xs-right mr-2 mb-2>Plazas que puede asumir : <br>
                                        <template>
                                            <v-chip v-for="dato_cargo in activeItem.opbl_cargo" v-bind:key="dato_cargo.id">
                                                {{dato_cargo.nombre_cargo}}
                                            </v-chip>
                                        </template>

                                    </v-flex>
                                    

                                    <v-flex tag="strong" xs12 text-xs-right mr-2 mb-2>Especialidades que puede asumir:<br>
                                        <template>
                                            <v-chip v-for="dato_esp in activeItem.opbl_especialidad" v-bind:key="dato_esp.especialidad_id">
                                                {{findEspecialidad(dato_esp.especialidad_id)}}
                                            </v-chip>
                                        </template>
                                    </v-flex>
                                    <v-flex tag="strong" xs12 text-xs-right mr-2 mb-2>Sedes donde puede ir a laborar:<br>
                                        <template>
                                            <v-chip v-for="sede in activeItem.opbl_sede" v-bind:key="sede.ubigeo_id">
                                                {{sede.n_distrito}}
                                            </v-chip>
                                        </template>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                        </v-layout>
                    </v-card>
                </v-col>
            </v-card>
        </v-dialog>
        
        <snackbar :snack="snack" />
        <dialogLoader :dialogLoad="dialogLoad"/>
        <footer-component/>
    </v-app>
</template>

<script>
export default {
    props:['data-user'],
    data(){
        return {
            menu: 4,
            submenu: -1,
            modulo: 'personal',
            drawer:true,
            cargos_list: [
                {id: 73, text: 'Auxiliar Judicial'},
                {id: 72, text: 'Auxiliar Administrativo II'},
                {id: 71, text: 'Auxiliar Administrativo I'},
                {id: 70, text: 'Auxiliar Asistente en Servicios de Comunicaciones I'},
                {id: 65, text: 'Técnico Administrativo I'},
                {id: 64, text: 'Secretaria/o I'},
                {id: 63, text: 'Revisor'},
                {id: 62, text: 'Chofer I'},
                {id: 60, text: 'Técnico Administrativo II'},
                {id: 59, text: 'Asistente en Servicios administrativos'},
                {id: 56, text: 'Técnico Judicial'},
                {id: 55, text: 'Asistente Jurisdiccional de Sala'},
                {id: 54, text: 'Asistente Jurisdiccional de Juzgado'},
                {id: 53, text: 'Asistente Judicial'},
                {id: 52, text: 'Secretaria/o III'},
                {id: 51, text: 'Asistente Jurisdiccional de CDG'},
                {id: 50, text: 'Asistente de sistemas'},
                {id: 49, text: 'Asistente Administrativo I'},
                {id: 46, text: 'Secretario Judicial'},
                {id: 45, text: 'Especialista Judicial de Juzgado'},
                {id: 44, text: 'Especialista Judicial de Audiencia de Sala'},
                {id: 43, text: 'Especialista Judicial de Audiencia de Juzgado'},
                {id: 42, text: 'Sub Administrador'},
                {id: 40, text: 'Cajero I'},
                {id: 38, text: 'Asistente Administrativo II'},
                {id: 34, text: 'Secretario de Sala'},
                {id: 33, text: 'Relator I'},
                {id: 32, text: 'Especialista Judicial de Sala'},
            ],
            especialidades_list: [
                {id:0, text:'Cargos administrativos'},
                {id:1, text:'Juzgados Penales'},
                {id:2, text:'Juzgados Civiles'},
                {id:3, text:'Juzgados Laborales'},
                {id:4, text:'Juzgados de Paz Letrado'},
                {id:5, text:'Juzgados de Familia'},
                {id:6, text:'Sala Penal'},
                {id:7, text:'Sala Civil'},
                {id:8, text:'Sala Laboral'},
                {id:9, text:'Sala Mixta'},
            ],
            sedes_list:[],
            cargo_id:null,
            especialidad_id: null,
            sede_id:null,
            headers:[
                {text: 'Apellidos y nombres', value: 'persona'},
                {text: 'Plaza Titular', value: 'nombre_plazatitular'},
                {text: 'Plaza Física', value: 'nombre_plazafisica'},
                {text: 'Dependencia Física', value: 'nombre_oficina'},
                {text: 'Opciones', value: 'view', type: 'opciones'},
            ],
            files:[],
            filters:{},
            activeItem:{},
            d_persona:false,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            dialogLoad:{
                show:false,
                text:'',
                color:'',
            },
        }
    },
    watch:{
        cargo_id(val){
            this.consultaFiles();
        },
        especialidad_id(val){
            this.consultaFiles();
        },
        sede_id(val){
            this.consultaFiles();
        }
    },
    created() {
        this.getData();
    },
    methods: {

        getData(){
            this.showLoad('primary', 'Cargando datos', true);
            axios.get('filespersonalApi?init=true')
                .then(response => {
                    this.sedes_list = response.data.sedes;
                    this.files = response.data.files;
                    this.showLoad('', '', false);

                })
                .catch(errors =>{
                        console.log(errors);
                        if (errors.response.status === 401) {
                                window.location = "../login";
                        } else if(errors.response.data.error == 'Unauthenticated'){
                            window.location = "../login";
                        }
                        this.showLoad('', '', false);
                    });
        
        },

        consultaFiles(){
            this.showLoad('primary', 'Cargando datos', true);
            axios.post('filespersonalApi', {cargo_id: this.cargo_id, especialidad_id: this.especialidad_id, sede_id : this.sede_id})
                .then(response => {
                    this.files = response.data.files;
                    this.showLoad('', '', false);

                })
                .catch(errors =>{
                        console.log(errors);
                        if (errors.response.status === 401) {
                                window.location = "../login";
                        } else if(errors.response.data.error == 'Unauthenticated'){
                            window.location = "../login";
                        }
                        this.showLoad('', '', false);
                    });
        
        },
        viewDetail(item){
            this.showLoad('primary', 'Cargando datos', true);

            axios.get('filespersonalApi?personal_id='+item.id)
                .then(response => {
                    this.activeItem = response.data.personal;
                    this.showLoad('', '', false);
                    this.d_persona = true;
                })
                .catch(errors =>{
                        console.log(errors);
                        if (errors.response.status === 401) {
                                window.location = "../login";
                        } else if(errors.response.data.error == 'Unauthenticated'){
                            window.location = "../login";
                        }
                        this.showLoad('', '', false);
                    });
        },

        findEspecialidad(esp_id){
            if(esp_id != null){
                var especialidad = this.especialidades_list.find(esp => esp.id == esp_id*1);
                return especialidad['text'];
            } else{
                return '-';
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
        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
    },

}
</script>