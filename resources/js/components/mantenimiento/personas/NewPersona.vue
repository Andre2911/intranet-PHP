<template>
    <v-card>
        <v-toolbar color="red darken-1" dark>
            <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
        </v-toolbar>
        <v-card-text>
            <v-container grid-list-md>
                <v-form v-model="valid" ref="form">
                    <v-layout wrap>
                        <v-flex xs12 sm12 md3 offset-md-5>
                            <v-autocomplete
                                v-model="activeItem.tipo_documento_id"
                                :items="tipoDocumentos"
                                label="Tipo de Documento"
                                item-text="sigla"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-text-field 
                                v-model="activeItem.numero_documento"
                                label="Nro. de documento"
                                :rules="[rules.required, rules.dni, rules.entero]"
                                :minlength=8
                                :maxlength=8
                                outline
                                required
                                ref="dni"
                            />
                        </v-flex>
                         <v-flex xs12 sm12 md1>
                            <v-btn outlined color="info" fab @click="getDNI()">
                                <v-icon>
                                    mdi-magnify
                                </v-icon>
                            </v-btn>
                        </v-flex>
                    </v-layout>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md2>
                            <v-text-field 
                                v-model="activeItem.ap_paterno"
                                label="Apellido Paterno"
                                :rules="[rules.required]"
                                required
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-text-field 
                                v-model="activeItem.ap_materno"
                                label="Apellido Materno"
                                :rules="[rules.required]"
                                required
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md4>
                            <v-text-field 
                                v-model="activeItem.nombres"
                                label="Nombres"
                                :rules="[rules.required]"
                                required
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md2>
                            <v-autocomplete
                                v-model="activeItem.sexo"
                                :items="generos"
                                label="Sexo"
                                item-text="name"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm3 md3>
                            <v-menu
                                v-model="fecha_nacimiento"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                transition="scale-transition"
                                offset-y
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                    <v-text-field
                                        v-model="activeItem.fecha_nacimiento"
                                        label="Fecha de Nacimiento"
                                        prepend-icon="mdi-calendar"
                                        readonly
                                        v-on="on"
                                        clearable
                                    ></v-text-field>
                                </template>
                                <v-date-picker locale="es-419" v-model="date_nacimiento" @input="fecha_nacimiento = false"></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs12 sm12 md6>
                            <v-text-field 
                                v-model="activeItem.direccion"
                                label="Direccion"
                            />
                        </v-flex>
                        <v-flex xs12 sm3 md3>
                            <v-text-field 
                                v-model="activeItem.celular"
                                label="Celular"
                            />
                        </v-flex>


                        <v-flex xs12>
                            <v-divider ></v-divider>
                            <v-subheader>Datos laborales</v-subheader>
                        </v-flex>
                        <v-flex xs12 sm3 md3>
                            <v-menu
                                v-model="fecha_ingreso"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                transition="scale-transition"
                                offset-y
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                    <v-text-field
                                        v-model="activeItem.fecha_ingreso"
                                        label="Fecha de Ingreso"
                                        prepend-icon="mdi-calendar"
                                        readonly
                                        v-on="on"
                                        clearable
                                    ></v-text-field>
                                </template>
                                <v-date-picker 
                                    locale="es-419"
                                v-model="date_ingreso" @input="fecha_ingreso = false"></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs12 sm3 md3>
                            <v-text-field 
                                v-model="activeItem.escalafon"
                                label="Escalafon"
                            />
                        </v-flex>
                        <v-flex xs12 sm3 md4>
                            <v-text-field 
                                v-model="activeItem.email"
                                label="Email"
                            />
                        </v-flex>
                    </v-layout>
                </v-form>
            </v-container>
        </v-card-text>
        <v-divider ></v-divider>
        <v-card-actions>
            <v-spacer></v-spacer>
                <v-btn color="success" @click="save"><v-icon>mdi-content-save</v-icon> {{btn_registrar}}</v-btn>
                <v-btn color="red" dark @click="close()"><v-icon>mdi-close</v-icon> Cancelar</v-btn>
        </v-card-actions>
        <dialogLoader :dialogLoad="dialogLoad"/>
        <snackbar :snack="snack" />
    </v-card>
</template>
<script>
export default {
    props:['activeItem', 'editedItem'],
    data() {
        return {
            valid: false,
            tipoDocumentos:[],
            generos:[
                {id:1, name:'Masculino'},
                {id:0, name:'Femenino'}
            ],
            rules: {
               entero: v => v > 0 || 'El campo solo admite números',
               required: v => !!v || 'Descripcion Requerido',
               dni: v=> v != undefined && v.length == 8 || 'Longitud incorrecta'
            },
            date_nacimiento: new Date().toISOString().substr(0, 10),
            fecha_nacimiento: false,
            date_ingreso: new Date().toISOString().substr(0, 10),
            fecha_ingreso: false,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            dialogLoad:{
                show:false,
                text:'',
                color:'',
            }
        }
    },
    computed:{
        formTitle(){
            return this.activeItem.id > 0 ? 'Editar Persona': 'Registrar Nueva Persona'
        },
        btn_registrar(){
            return this.activeItem.id > 0 ? 'Actualizar Datos': 'Registrar'
        },
        computedDateFormatted () {
            return this.formatDate(this.date)
        },
    },
    watch:{
        date_ingreso (val) {
            console.log(val)
            this.activeItem.fecha_ingreso = val
        },
        date_nacimiento (val) {
            this.activeItem.fecha_nacimiento = val
        },
        editedItem(val){
            console.log(val)
            if(val<0){
                this.activeItem.fecha_nacimiento = this.date_nacimiento;
                this.activeItem.fecha_ingreso = this.date_ingreso;
            }
        }
    },
    created(){
        if(this.activeItem.id == undefined)
        {
            this.activeItem.fecha_nacimiento = this.date_nacimiento;
            this.activeItem.fecha_ingreso = this.date_ingreso;
        }
        this.getDocumentos();
    },
    methods:{
        getDocumentos(){
            axios.get("personaApi?documentos=true")
                .then(response =>{
                    this.tipoDocumentos = response.data.documentos
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "inicio";
                        }
                     }
                     this.getDocumentos();
                });
        },
        consultarPersona(){
            axios.get("personaApi?consultar=true&persona="+this.activeItem)
                .then(response =>{
                    this.editedItem = response.data.persona
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "inicio";
                        }
                     }
                     this.getDocumentos();
                });
        },
        save(e){
            e.preventDefault();
            
            if (this.$refs.form.validate()) {
                this.showLoad('primary', 'Guardando datos', true)

                if (this.activeItem.id < 1 || this.editedItem < 1){
                    axios.post('personaApi', this.activeItem)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.showLoad('', '', false)
                                    this.$emit('showSnack', 'success', response.data['messageDB'])
                                    this.$emit('close');
                                } else{
                                    this.$emit('showSnack', 'red', response.data['messageDB'])
                                    this.showLoad('', '', false)

                                }
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                console.log(errors.response)
                                if(errors.response.data.error == 'Unauthenticated'){
                                    window.location = "inicio";
                                }
                            }
                        });
                } else{
                    axios.put('personaApi/' + this.activeItem.id, this.activeItem)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.showLoad('', '', false)
                                    this.$emit('showSnack', 'success', response.data['messageDB'])
                                    this.$emit('close');
                                } else{
                                    this.$emit('showSnack', 'red', response.data['messageDB'])
                                    this.showLoad('', '', false)

                                }
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                console.log(errors.response)
                                if(errors.response.data.error == 'Unauthenticated'){
                                    window.location = "inicio";
                                }
                            }
                        });
                    this.showLoad('', '', false)
                }
                
            }
        },
        getDNI() {
            if (this.activeItem.numero_documento === undefined || this.activeItem.numero_documento.length < 8) {
               this.$emit('showSnack', 'warning',  'Inserte un Numero de DNI válido')
               this.$refs.numero_documento.focus();
            } else{
                    this.showLoad('primary', 'Cargando...', true)

                if(this.editedItem == -1){
                    var url = '../consultaDNI/'+this.activeItem.numero_documento;
                } else{
                    var url = '../consultaDNIu/'+this.activeItem.numero_documento;
                }
                axios.get(url)
                    .then(response =>{
                        if(response.data.IsSuccess){
                            if(this.editedItem == -1){
                                if(response.data.datos.id == undefined){
                                    this.activeItem.ap_paterno = response.data.datos.ap_paterno;
                                    this.activeItem.ap_materno = response.data.datos.ap_materno;
                                    this.activeItem.nombres = response.data.datos['nombres']
                                    this.activeItem.fecha_nacimiento = this.parseDate(response.data.datos['fecha_nacimiento'])
                                    this.date_nacimiento = this.parseDate(response.data.datos['fecha_nacimiento'])
                                    this.activeItem.direccion = response.data.datos['direccion']
                                    this.activeItem.sexo = response.data.datos['sexo'];
                                } else{
                                        this.showSnack('warning', 'La persona ya fue registrada con anterioridad', true)
                                }
                            }else{
                                this.activeItem.ap_paterno = response.data.datos.ap_paterno;
                                this.activeItem.ap_materno = response.data.datos.ap_materno;
                                this.activeItem.nombres = response.data.datos['nombres']
                                this.activeItem.fecha_nacimiento = this.parseDate(response.data.datos['fecha_nacimiento'])
                                this.date_nacimiento = this.parseDate(response.data.datos['fecha_nacimiento'])
                                this.activeItem.direccion = response.data.datos['direccion']
                                this.activeItem.sexo = response.data.datos['sexo'];
                            } 
  
                        } else{
                            this.showSnack('warning', 'No se pudo realizar la consulta', true)
                        }
                        this.showLoad('', '', false)
                    })
                    .catch(errors =>{
                        console.log(errors);
                        this.showLoad('', '', false)
                        this.$emit('showSnack', 'warning',  'Error al consultar, intente denuevo')
                    });
            }
            
            
         },
        close(){
            this.$emit('close');
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
        
        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
        showSnack(color, text, show = true){
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
        },
    }
    
}
</script>