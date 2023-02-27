<template>
    <v-card>
        <v-toolbar color="red darken-1" dark>
            <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn @click="getOficinas()" color="red darken-1">
                <v-icon>
                    mdi-update
                </v-icon>
            </v-btn>
            <v-btn @click="close()" color="red darken-1">
                <v-icon>
                    mdi-close
                </v-icon>
            </v-btn>
        </v-toolbar>
        <v-card-text>
            <v-container grid-list-md>
                <v-form v-model="valid" ref="form">
                    <v-layout wrap>
                        <v-flex xs12 sm12 md12>
                            <v-autocomplete
                                v-model="activeItem.parent_id"
                                :items="parents"
                                label="Oficina Padre"
                                item-text="nombre_oficina"
                                item-value="id"
                                :rules="[rules.required]"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md12>
                            <v-text-field 
                                v-model="activeItem.nombre_oficina"
                                label="Nombre de Oficina"
                                :rules="[rules.required]"
                                required
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md12>
                            <v-autocomplete
                                v-model="activeItem.sede_id"
                                :items="sedes"
                                label="Sede"
                                item-text="local"
                                item-value="cod_local"
                            />
                        </v-flex>
                    </v-layout>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md3>
                            <v-checkbox
                                v-model="activeItem.have_childrens"
                                label="Area con subdependencias"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-checkbox
                                v-model="activeItem.have_personal"
                                label="Area con personal"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-checkbox
                                v-model="activeItem.show_cap"
                                label="Mostrar en CAP"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-checkbox
                                v-model="activeItem.show_caf"
                                label="Mostrar en CAF"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-checkbox
                                v-model="activeItem.activo"
                                label="Activo"
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
            parents:[],
            sedes:[],
            dialogLoad:{
                show:false,
                text:'',
                color:'',
            }
        }
    },
    computed:{
        formTitle(){
            return this.activeItem.id > 0 ? 'Editar Dependencia': 'Registrar Nueva Dependencia'
        },
        btn_registrar(){
            return this.activeItem.id > 0 ? 'Actualizar Datos': 'Registrar'
        },
    },
    watch:{
        editedItem(val){
            console.log(val)
            if(val<0){
                this.activeItem.fecha_nacimiento = this.date_nacimiento;
                this.activeItem.fecha_ingreso = this.date_ingreso;
            }
        }
    },
    created(){
        this.getOficinas();
    },
    methods:{
        getOficinas(){
            axios.get("oficinasApi?parents=true")
                .then(response =>{
                    this.parents = response.data.oficinas
                    this.sedes = response.data.sedes
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "inicio";
                        }
                     }
                     this.getOficinas();
                });
        },
        save(e){
            e.preventDefault();
            if (this.$refs.form.validate()) {
                this.showLoad('primary', 'Guardando datos', true)

                if (this.activeItem.id < 1 || this.editedItem < 1){
                    axios.post('oficinasApi', this.activeItem)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.showLoad('', '', false)
                                    this.$emit('showSnack', 'success', response.data['messageDB'])
                                    this.$emit('close');
                                } else{
                                    this.$emit('showSnack', 'red', response.data['messageDB'])
                                }
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (rrors.response != undefined && errors.response.status === 401) {
                                console.log(errors.response)
                                if(errors.response.data.error == 'Unauthenticated'){
                                    window.location = "inicio";
                                }
                            }
                        });
                } else{
                    axios.put('oficinasApi/' + this.activeItem.id, this.activeItem)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.showLoad('', '', false)
                                    this.$emit('showSnack', 'success', response.data['messageDB'])
                                    this.$emit('close');
                                } else{
                                     this.$emit('showSnack', 'red', response.data['messageDB'])
                                }
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (rrors.response != undefined && errors.response.status === 401) {
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
        close(){
            this.$emit('close');
        },

        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
    }
    
}
</script>