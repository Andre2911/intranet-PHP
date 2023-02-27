<template>
    <v-card>
        <v-toolbar color="red darken-1" dark>
            <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
        </v-toolbar>
        <v-card-text>
            <v-container grid-list-md>
                <v-form v-model="valid" ref="form">
                    <v-layout wrap>
                        <v-flex xs12>
                            <v-subheader>Persona</v-subheader>
                            <v-btn @click="changePersona()">
                                <v-icon v-if="activeItem.persona_id != undefined" color="primary">mdi-refresh</v-icon>
                                <v-icon v-else >mdi-magnify</v-icon>
                                <v-icon>mdi-account</v-icon>
                                <template v-if="activeItem.persona_id != undefined">
                                    [{{activeItem.persona.numero_documento}}]
                                    {{activeItem.persona.ap_paterno}}
                                    {{activeItem.persona.ap_materno}}
                                    {{activeItem.persona.nombres}}
                                </template>
                            </v-btn>
                        </v-flex>

                        <v-flex xs12>
                            <v-divider></v-divider>
                            <v-subheader>Persona</v-subheader>
                            
                        </v-flex>

                        <v-flex xs12 sm12 md3>
                            <v-text-field 
                                v-model="activeItem.username"
                                label="Nombre de usuario"
                                :rules="[rules.required]"
                                required readonly
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-text-field 
                                v-model="activeItem.password"
                                label="Contraseña"
                                :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                                :type="show1 ? 'text' : 'password'"
                                autocomplete="new-password"
                                counter
                                :rules="(activeItem.id == undefined)? [rules.required]: []"
                                required
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
        <v-dialog v-model="d_persona"
            scrollable
            max-width="1200PX"
            transition="dialog-transition">
            <select-persona-component @selectedItem="selectedItem"/>
        </v-dialog>
        <v-dialog v-model="d_rol"
            scrollable
            max-width="960PX"
            transition="dialog-transition">
            <select-roles-component @selectedItem="selectedRol"/>
        </v-dialog>
    </v-card>
</template>
<script>
export default {
    props:['activeItem', 'editedItem'],
    data() {
        return {
            valid: false,
            rules: {
               entero: v => v > 0 || 'El campo solo admite números',
               required: v => !!v || 'Descripcion Requerido',
               dni: v=> v != undefined && v.length == 8 || 'Longitud incorrecta'
            },
            dialogLoad:{
                show:false,
                text:'',
                color:'',
            },
            d_persona:false,
            d_rol:false,
            show1: false,
        }
    },
    computed:{
        formTitle(){
            return this.activeItem.id > 0 ? 'Editar Usuario': 'Registrar Nuevo Usuario'
        },
        btn_registrar(){
            return this.activeItem.id > 0 ? 'Actualizar Datos': 'Registrar'
        },
        
    },
    watch:{
    },
    created(){
    },
    methods:{
        save(e){
            e.preventDefault();
            
            if (this.$refs.form.validate()) {
                this.showLoad('primary', 'Guardando datos', true)

                if (this.activeItem.id == undefined || this.editedItem < 1){
                    
                    if (this.activeItem.persona_id == null && this.activeItem.persona_id == undefined) {
                        this.$emit('showSnack', 'red', 'Seleccione una persona asociado al usuario')
                        this.showLoad('primary', 'Guardando datos', false)

                        this.d_persona = true;
                        return;
                    }
                    axios.post('../admin/usuariosApi', this.activeItem)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.$emit('showSnack', 'success', response.data['messageDB'])
                                    this.$emit('close');
                                } else{
                                    this.$emit('showSnack', 'red', response.data['messageDB'])
                                }
                            this.showLoad('', '', false)
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                console.log(errors.response)
                                if(errors.response.data.error == 'Unauthenticated'){
                                    window.location = "inicio";
                                }
                            }
                            this.$emit('showSnack', 'red', response.data['messageDB'])

                            this.showLoad('primary', 'Guardando datos', false)

                        });
                } else{
                    axios.put('usuariosApi/' + this.activeItem.id, this.activeItem)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.showLoad('', '', false)
                                    this.$emit('showSnack','success', response.data['messageDB'])
                                    this.$emit('close');
                                } else{
                                    this.showSnack('red', response.data['messageDB'])
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
        changePersona(){
            this.d_persona = true;
        },
        selectedItem(item){
            this.activeItem.persona_id = item.id
            if (this.activeItem.id == undefined) {
                this.activeItem.persona = {}
            }
            console.log(item)
            this.activeItem.persona.id = item.id
            this.activeItem.persona.ap_paterno = item.ap_paterno
            this.activeItem.persona.ap_materno = item.ap_materno
            this.activeItem.persona.nombres = item.nombres
            this.activeItem.persona.numero_documento = item.numero_documento
            this.activeItem.email = item.email
            this.activeItem.username = item.numero_documento
            this.d_persona = false;
            
        },
        selectedRol(item){
            this.d_rol = false;
            
            var existe = false;
            if (this.activeItem.roles == undefined) {
                this.activeItem.roles = []
            }
            for (var index = 0; index < this.activeItem.roles.length; index++) {
                if (this.activeItem.roles[index].id == item.id) {
                    existe = true;
                }
            }
            if (existe) {
                this.$emit('showSnack','red', 'El usuario ya cuenta con este rol')
            } else{
                
                this.activeItem.roles.push(item)
            }
        },
        addRol(){
            this.d_rol = true;
        },
        removeRol(item){
            const index = this.activeItem.roles.indexOf(item);
            this.activeItem.roles.splice(index, 1); 
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
    }
    
}
</script>