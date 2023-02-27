<template>
    <v-container>
        <v-card>
            <v-card-title>
                Perfil Profesional
            </v-card-title>
            <v-card-text>
                <v-stepper v-model="e1" non-linear>
                    <v-stepper-header>
                        <v-stepper-step
                            editable
                            step="PP"
                        >
                            Datos académicos
                        </v-stepper-step>

                        <v-divider></v-divider>
                        <v-stepper-step
                            editable
                            step="C"
                        >
                            Disposición a cargos
                        </v-stepper-step>

                        <v-divider></v-divider>

                        <v-stepper-step step="S" editable>
                            Disponibilidad de viajar
                        </v-stepper-step>
                    </v-stepper-header>

                    <v-stepper-items>
                        <v-stepper-content step="PP">
                            <v-card
                            >
                                <v-row>
                                    <v-col cols="12" md="12">
                                        <span><b>Título Profesional</b></span>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-autocomplete
                                            v-model="pregrado.grado"
                                            label="Ultimo grado profesional"
                                            :items="grados"
                                            dense
                                        >
                                        </v-autocomplete>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-text-field
                                            v-model="pregrado.carrera"
                                            label="Carrera"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="12" md="5">
                                        <v-text-field
                                            v-model="pregrado.universidad"
                                            label="Universidad"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-menu
                                            v-model="datetime"
                                            :close-on-content-click="false"
                                            :nudge-right="40"
                                            transition="scale-transition"
                                            offset-y
                                            min-width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                v-model="pregrado.f_titulo"
                                                label="Fecha expedición"
                                                prepend-icon="mdi-calendar"
                                                readonly
                                                v-on="on"
                                                dense
                                                ></v-text-field>
                                            </template>
                                            <v-date-picker 
                                                v-model="date_titulo"
                                                locale="es-419"
                                                @input="datetime = false"
                                            ></v-date-picker>
                                        </v-menu>
                                    </v-col>
                                    <v-col cols="12" md="9">
                                        <v-btn color="info" block @click="add_pregrado()" :disabled="pregrado.grado == null || pregrado.carrera == null || pregrado.carrera == '' || pregrado.universidad == null || pregrado.universidad == '' || pregrado.f_titulo == null">
                                            <v-icon>mdi-plus</v-icon>
                                            Agregar
                                        </v-btn>
                                    </v-col>
                                    <v-col cols="12" md="12" class="pt-0">
                                        <dataTable 
                                            :headers="headers_t"
                                            :data="pregrados"
                                            :filters="filters"
                                            tipo="1" 
                                            dosearch='false'
                                            @deleteItem="deletePregrado"
                                        />
                                    </v-col>
                                    
                                    <v-col cols="12" md="12">
                                        <v-divider/>
                                        <span><b>Estudios de Postgrado</b></span>
                                    </v-col>
                                    <v-col cols="12" md="2">
                                        <v-select
                                            v-model="postgrado.grado"
                                            :items="postgrado_tipo"
                                            label="PostGrado"
                                            dense
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" md="5">
                                        <v-text-field
                                            v-model="postgrado.esp"
                                            label="Especialidad / Mención"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="12" md="5">
                                        <v-text-field
                                            v-model="postgrado.universidad"
                                            label="Universidad"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="12" md="2">
                                        <v-select
                                            v-model="postgrado.estado"
                                            :items="estados"
                                            label="Situación"
                                            dense
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-menu
                                            v-model="datetime_p"
                                            :close-on-content-click="false"
                                            :nudge-right="40"
                                            transition="scale-transition"
                                            offset-y
                                            min-width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                v-model="f_postgrado"
                                                label="Fecha expedición"
                                                prepend-icon="mdi-calendar"
                                                readonly
                                                v-on="on"
                                                dense
                                                :disabled="postgrado.estado != 'CONCLUIDO'"
                                                ></v-text-field>
                                            </template>
                                            <v-date-picker 
                                                v-model="date_postgrado"
                                                locale="es-419"
                                                @input="datetime_p = false"
                                            ></v-date-picker>
                                        </v-menu>
                                    </v-col>
                                    <v-col cols="12" md="7">
                                        <v-btn color="info" block @click="add_postgrado()" :disabled="postgrado.grado == null || postgrado.esp == null || postgrado.esp == '' || postgrado.universidad == null || postgrado.universidad == '' || postgrado.estado == null">
                                            <v-icon>mdi-plus</v-icon>
                                            Agregar
                                        </v-btn>
                                    </v-col>
                                    <v-col cols="12" md="12" class="pt-0">
                                        <dataTable 
                                            :headers="headers_p"
                                            :data="postgrados"
                                            :filters="filters"
                                            tipo="1" 
                                            dosearch="false"
                                            @deleteItem="deletePostgrado"
                                        />
                                    </v-col>
                                </v-row>
                                
                                <v-card-actions>
                                    <v-btn
                                        color="primary"
                                        @click="updateAcademico()"
                                        >
                                        <v-icon>mdi-refresh</v-icon>
                                        Actualizar datos y Continuar
                                    </v-btn>
                                </v-card-actions>
                            </v-card>

                            
                        </v-stepper-content>

                        <v-stepper-content step="C">
                            
                            <v-card>
                                <v-row>
                                    <v-col cols="12" md="12" class="ma-0 pb-1">
                                        <v-alert border="top"
                                            colored-border
                                            type="info"
                                            class="mb-0"
                                            elevation="2">
                                            Según su Perfil Académico, seleccione los cargos y especialidades que puede o desearía asumir
                                        </v-alert>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-col cols="12" md="12">
                                            <span><b>Cargos</b></span>
                                        </v-col>
                                        <v-col cols="12" md="12">
                                            
                                            <v-list
                                                flat
                                            >
                                                <v-list-item-group
                                                    v-model="cargos"
                                                    multiple
                                                >
                                                    <v-list-item v-for="cargo in cargos_list" v-bind:key="cargo.id">
                                                        <template v-slot:default="{ active }">
                                                            <v-list-item-action>
                                                            <v-checkbox
                                                                :input-value="active"
                                                                color="primary"
                                                            ></v-checkbox>
                                                            </v-list-item-action>
                                                            <v-list-item-content>
                                                                <v-list-item-title>{{cargo.text}}</v-list-item-title>
                                                            </v-list-item-content>
                                                        </template>
                                                    </v-list-item>
                                                </v-list-item-group>
                                            </v-list>
                                        </v-col>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-col cols="12" md="12">
                                            <span><b>Especialidades</b></span>
                                        </v-col>
                                        <v-col cols="12" md="12">
                                            <v-list
                                                flat
                                            >
                                                <v-list-item-group
                                                    v-model="especialidades"
                                                    multiple
                                                >
                                                    <v-list-item v-for="especialidad in especialidades_list" v-bind:key="especialidad.id">
                                                        <template v-slot:default="{ active }">
                                                            <v-list-item-action>
                                                            <v-checkbox
                                                                :input-value="active"
                                                                color="primary"
                                                            ></v-checkbox>
                                                            </v-list-item-action>
                                                            <v-list-item-content>
                                                                <v-list-item-title>{{especialidad.text}}</v-list-item-title>
                                                            </v-list-item-content>
                                                        </template>
                                                    </v-list-item>
                                                </v-list-item-group>
                                            </v-list>
                                        </v-col>
                                    </v-col>
                                    
                                </v-row>
                                <v-card-title>
                                    <v-btn
                                        color="primary"
                                        @click="updateEspecialidad()"
                                        >
                                        <v-icon>mdi-refresh</v-icon>
                                        Actualizar y Continuar
                                    </v-btn>
                                </v-card-title>
                            </v-card>

                            
                        </v-stepper-content>

                        <v-stepper-content step="S">
                            <v-card>
                                <v-row>
                                    <v-col cols="12" md="12">
                                        <span><b>Sedes</b></span>
                                    </v-col>

                                    <v-col cols="12" md="12" class="ma-0 pb-1">
                                        <v-alert border="top"
                                            colored-border
                                            type="info"
                                            class="mb-0"
                                            elevation="2">
                                            Seleccione las sedes en las que puede laborar
                                        </v-alert>
                                    </v-col>
                                    <v-col cols="12" md="12">
                                            <span><b>Sedes</b></span>
                                        </v-col>
                                        <v-col cols="12" md="12">
                                            <v-list
                                                flat
                                            >
                                                <v-list-item-group
                                                    v-model="sedes"
                                                    multiple
                                                >
                                                    <v-list-item v-for="sede in sedes_list" v-bind:key="sede.ubigeo_id">
                                                        <template v-slot:default="{ active }">
                                                            <v-list-item-action>
                                                            <v-checkbox
                                                                :input-value="active"
                                                                color="primary"
                                                            ></v-checkbox>
                                                            </v-list-item-action>
                                                            <v-list-item-content>
                                                                <v-list-item-title>{{sede.n_distrito}}</v-list-item-title>
                                                            </v-list-item-content>
                                                        </template>
                                                    </v-list-item>
                                                </v-list-item-group>
                                            </v-list>
                                        </v-col>
                                </v-row>
                                
                                <v-card-actions>
                                    <v-btn
                                    color="primary"
                                    @click="updateSedes()"
                                    >
                                    <v-icon>mdi-refresh</v-icon>
                                    Actualizar datos de sedes
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-stepper-content>
                    </v-stepper-items>
                </v-stepper>
            </v-card-text>
        </v-card>
    </v-container>
</template>
<script>
export default {
    data() {
        return {
            e1: 'PP',
            postgrado_tipo : [
                'Maestria', 'Doctorado'
            ], 
            estados: ['CONCLUIDO', 'EN PROCESO'],
            grados: ['EGRESADO', 'BACHILLER', 'TÍTULO PROFESIONAL'],
            headers_t:[
                {text: 'Grado Profesional', value: 'grado_profesional'},
                {text: 'Carrera Profesional', value: 'carrera'},
                {text: 'Universidad', value: 'universidad'},
                {text: 'Fecha Expedición', value: 'f_expedicion'},
                {text: 'Opciones', value: 'delete', type: 'opciones'},
            ],
            headers_p:[
                {text: 'Postgrado', value: 'grado_profesional'},
                {text: 'Especialidad / Mención', value: 'carrera'},
                {text: 'Universidad', value: 'universidad'},
                {text: 'Situación', value: 'estado'},
                {text: 'Fecha Expedición', value: 'f_expedicion'},
                {text: 'Opciones', value: 'delete', type: 'opciones'},
            ],
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
            sedes_list: [],
            cargos:[],
            especialidades:[],
            sedes:[],
            pregrado:{},
            pregrados:[],
            old_pregrados:[],
            postgrados:[],
            old_postgrados:[],
            filters:[],
            datetime: false,
            date_titulo: this.parseDate(new Date().toLocaleDateString()),
            datetime_p: false,
            f_postgrado: null,
            date_postgrado: this.parseDate(new Date().toLocaleDateString()),
            postgrado:{},
        }
    },
    watch:{
        date_titulo (val) {
            this.pregrado.f_titulo = this.formatDate(this.date_titulo)
        },
        date_postgrado (val) {
            this.f_postgrado = this.formatDate(this.date_postgrado)
        },
        
    },
    created() {
        this.getPerfil();
    },
    
    methods: {
        getPerfil(){
            this.sedes_list = [];
            this.sedes = [];
            this.especialidades = [];
            this.cargos = [];
            this.$emit('showLoad', 'primary', 'Cargando datos', true);
            axios.get('perfilProfesional?init=true')
                .then(response => {
                    this.sedes_list = response.data.sedes;
                    if(response.data.perfil_pregrado.length > 0){
                        this.pregrados = response.data.perfil_pregrado;
                        this.old_pregrados = [];

                        for (let index = 0; index < response.data.perfil_pregrado.length; index++) {
                            this.old_pregrados.push(response.data.perfil_pregrado[index])
                        }
                    } else{
                        this.old_postgrados = []
                    }
                    if(response.data.perfil_postgrado.length > 0){
                        this.postgrados = response.data.perfil_postgrado;
                        this.old_postgrados = [];

                        for (let index = 0; index < response.data.perfil_postgrado.length; index++) {
                            this.old_postgrados.push(response.data.perfil_postgrado[index])
                        }
                    } else{
                        this.old_postgrados = [];
                    }

                    if(response.data.perfil_cargos.length > 0){
                        for (let index = 0; index < response.data.perfil_cargos.length; index++) {
                            this.cargos.push(this.cargos_list.indexOf(this.cargos_list.find(cargo => cargo.id == response.data.perfil_cargos[index]['id'])))
                        }
                    }

                    if(response.data.perfil_especialidades.length > 0){
                        for (let index = 0; index < response.data.perfil_especialidades.length; index++) {
                            this.especialidades.push(response.data.perfil_especialidades[index]['id']*1)
                        }
                    }

                    if(response.data.perfil_sedes.length > 0){

                        for (let index = 0; index < response.data.perfil_sedes.length; index++) {
                            this.sedes.push(this.sedes_list.indexOf(this.sedes_list.find(sede => sede.ubigeo_id == response.data.perfil_sedes[index]['ubigeo_id'])))
                        }

                    }

                    this.$emit('showLoad', '', '', false);

                    
                })
                .catch(errors =>{
                        console.log(errors);
                        if (errors.response.status === 401) {
                                window.location = "../login";
                        } else if(errors.response.data.error == 'Unauthenticated'){
                            window.location = "../login";
                        }
                        this.$emit('showLoad', '', '', false);
                    });
        },
        add_postgrado(){
            let pg = {
                grado_profesional: this.postgrado.grado,
                carrera : this.postgrado.esp,
                universidad : this.postgrado.universidad,
                estado : this.postgrado.estado,
                f_expedicion : this.f_postgrado,
            }
            this.postgrados.push(pg);
            this.postgrado.grado = null
            this.postgrado.esp = ''
            this.postgrado.universidad = ''
            this.postgrado.estado = null
            this.f_postgrado = null
        },
        add_pregrado(){
            let pg = {
                grado_profesional: this.pregrado.grado,
                carrera : this.pregrado.carrera,
                universidad : this.pregrado.universidad,
                f_expedicion : this.pregrado.f_titulo,
            }
            this.pregrados.push(pg);
            this.pregrado.grado = null
            this.pregrado.carrera = ''
            this.pregrado.universidad = ''
            this.f_postgrado = null
        },
        deletePregrado(item){
            const index = this.pregrados.indexOf(item)
            this.pregrados.splice(index, 1)
        },
        deletePostgrado(item){
            const index = this.postgrados.indexOf(item)
            this.postgrados.splice(index, 1)
        },
        updateAcademico(){

                var upd_pregrados = false

                if(this.old_pregrados.length != this.pregrados.length){
                    upd_pregrados = true;
                }

                for (let index = 0; index < this.pregrados.length; index++) {
                    if(upd_pregrados == true || 
                        this.pregrados[index].carrera != this.old_pregrados[index].carrera  || 
                        this.pregrados[index].grado_profesional != this.old_pregrados[index].grado_profesional || 
                        this.pregrados[index].universidad != this.old_pregrados[index].universidad || 
                        this.pregrados[index].f_expedicion != this.old_pregrados[index].f_expedicion
                    ){
                        upd_pregrados = true;
                    }
                }
                

                if(upd_pregrados){
                
                    axios.post('perfilProfesional', {pregrados:this.pregrados})
                    .then(response => {
                        if(response.data['statusBD']){
                            this.$emit('showSnack', 'success', response.data.messageDB)
                            this.getPerfil();
                            this.e1 = 'C'
                        } else{
                            this.$emit('showSnack', 'error', response.data.messageDB)

                        }
  
                    })
                    .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                    window.location = "../login";
                            } else if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "../login";
                            }
                        });
                }

                var upd_postgrados = false

                if(this.old_postgrados.length != this.postgrados.length){
                    upd_postgrados = true;
                }

                for (let index = 0; index < this.postgrados.length; index++) {
                    if( upd_postgrados == true ||
                        this.postgrados[index].carrera != this.old_postgrados[index].carrera  || 
                        this.postgrados[index].grado_profesional != this.old_postgrados[index].grado_profesional || 
                        this.postgrados[index].universidad != this.old_postgrados[index].universidad || 
                        this.postgrados[index].f_expedicion != this.old_postgrados[index].f_expedicion
                    ){
                        upd_postgrados = true;
                    }
                } 

                if(upd_postgrados){
                    axios.post('perfilProfesional', {postgrados:this.postgrados})
                    .then(response => {
                        if(response.data['statusBD']){
                            this.$emit('showSnack', 'success', response.data.messageDB)
                            this.getPerfil();
                            this.e1 = 'C'
                       } else{
                            this.$emit('showSnack', 'error', response.data.messageDB)

                        }
                    })
                    .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                    window.location = "../login";
                            } else if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "../login";
                            }
                        });
                }

                if(!upd_postgrados && !upd_pregrados){
                            this.e1 = 'C'
                }

        },
        updateEspecialidad(){
            var cargosSelected = []

            for (let index = 0; index < this.cargos.length; index++) {
                cargosSelected.push(this.cargos_list[this.cargos[index]]);
            }
            console.log(cargosSelected);
            axios.post('perfilProfesional', {cargos:cargosSelected, especialidades: this.especialidades})
                    .then(response => {
                        if(response.data['statusBD']){
                            this.$emit('showSnack', 'success', response.data.messageDB)
                            this.getPerfil();
                            this.e1 = 'S'
                       } else{
                            this.$emit('showSnack', 'error', response.data.messageDB)

                        }
                    })
                    .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                    window.location = "../login";
                            } else if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "../login";
                            }
                        });
        },
        updateSedes(){
            var sedesSelected = []

            for (let index = 0; index < this.sedes.length; index++) {
                sedesSelected.push(this.sedes_list[this.sedes[index]]);
            }
            axios.post('perfilProfesional', {sedes:sedesSelected})
                    .then(response => {
                        if(response.data['statusBD']){
                            this.$emit('showSnack', 'success', response.data.messageDB)
                            this.getPerfil();
                            //this.e1 = 'C'
                       } else{
                            this.$emit('showSnack', 'error', response.data.messageDB)

                        }
                    })
                    .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                    window.location = "../login";
                            } else if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "../login";
                            }
                        });
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
    },
}
</script>