<template>
    <v-card>
        <v-toolbar color="red darken-1" dark>
            <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
        </v-toolbar>
        <v-card-text>
            <v-container grid-list-md>
                <v-form v-model="valid" ref="form">
                    <v-layout wrap>
                        <v-flex xs12 sm12 md2>
                            <v-text-field 
                                v-model="activeItem.c_plaza"
                                label="Código de Plaza"
                                :rules="[rules.required]"
                                :minlength=1
                                :maxlength=6
                                outline
                                required
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md5>
                            <v-autocomplete
                                v-model="activeItem.op_cargo_id"
                                :items="cargos"
                                label="Cargo"
                                item-text="nombre_cargo"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md5>
                            <v-text-field 
                                v-model="activeItem.nombre_plaza"
                                label="Nombre de plaza"
                                :rules="[rules.required]"
                                required
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md7>
                            <v-autocomplete
                                v-model="activeItem.op_oficina_id"
                                label="Oficina Nominal"
                                :items="oficinas"
                                :rules="[rules.required]"
                                item-text="nombre_oficina"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-autocomplete
                                v-model="activeItem.op_regimen_id"
                                label="Regimen Laboral"
                                :items="regimenes"
                                :rules="[rules.required]"
                                item-text="regimen_completo"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md2>
                            <v-text-field 
                                v-model="activeItem.order_cap"
                                label="Orden CAP"
                                :rules="[rules.required, rules.entero]"
                                type="number"
                                required
                            />
                        </v-flex>
                        <v-flex xs12>
                            <v-btn @click="changePersona(activeItem)">
                                <v-icon v-if="activeItem.id != undefined && activeItem.ocupado_cap.length > 0" color="primary">mdi-refresh</v-icon>
                                <v-icon v-else >mdi-magnify</v-icon>
                                <v-icon>mdi-account</v-icon>
                                <template v-if="activeItem.id != undefined && activeItem.ocupado_cap.length > 0">
                                    [{{activeItem.ocupado_cap[0].numero_documento}}]
                                    {{activeItem.ocupado_cap[0].ap_paterno}}
                                    {{activeItem.ocupado_cap[0].ap_materno}}
                                    {{activeItem.ocupado_cap[0].nombres}}
                                </template>
                            </v-btn>
                            <v-btn @click="confirma(activeItem)? activeItem.ocupado_cap =[]: ''">
                                <v-icon>mdi-cancel</v-icon>
                            </v-btn>
                        </v-flex>

                        <v-flex xs12>
                            <v-divider ></v-divider>
                            <v-subheader>Plaza Física</v-subheader>
                        </v-flex>
                        <v-flex xs12 sm12 md7>
                            <v-autocomplete
                                v-model="activeItem.op_oficinaf_id"
                                label="Oficina Física"
                                :items="oficinas"
                                :rules="[rules.required]"
                                item-text="nombre_oficina"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md3>
                            <v-autocomplete
                                v-model="activeItem.op_regimenf_id"
                                label="Regimen Laboral"
                                :items="regimenes"
                                :rules="[rules.required]"
                                item-text="regimen_completo"
                                item-value="id"
                            />
                        </v-flex>
                        <v-flex xs12 sm12 md2>
                            <v-text-field 
                                v-model="activeItem.order_caf"
                                label="Orden CAP"
                                :rules="[rules.required, rules.entero]"
                                type="number"
                                required
                            />
                        </v-flex>
                        <v-flex xs12 sm3 md4>
                            <v-switch v-model="activeItem.jefe_area" class="ma-2" label="Jefe de depencia"></v-switch>
                        </v-flex>
                        <v-flex xs12 sm3 md4>
                            <v-switch v-model="activeItem.activo" class="ma-2" label="Plaza activa"></v-switch>
                        </v-flex>
                        <v-flex xs12>
                            <v-btn @click="changePersonaFisica()">
                                <v-icon v-if="activeItem.ocupado_caf != undefined && activeItem.ocupado_caf.length > 0" color="primary">mdi-refresh</v-icon>
                                <v-icon v-else >mdi-magnify</v-icon>
                                <v-icon>mdi-account</v-icon>

                                <template v-if="activeItem.ocupado_caf != undefined && activeItem.ocupado_caf.length > 0">
                                    [{{activeItem.ocupado_caf[0].numero_documento}}]
                                    {{activeItem.ocupado_caf[0].ap_paterno}}
                                    {{activeItem.ocupado_caf[0].ap_materno}}
                                    {{activeItem.ocupado_caf[0].nombres}}
                                </template>
                            </v-btn>
                            <v-btn @click="activeItem.ocupado_caf =[]">
                                <v-icon>mdi-cancel</v-icon>
                            </v-btn>
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
            <select-persona-component @selectedItem="selectedItem" v-if="d_persona"/>
        </v-dialog>

        <v-dialog v-model="d_persona_f"
            scrollable
            max-width="1200PX"
            transition="dialog-transition">
            <select-persona-component @selectedItem="selectedItemF" v-if="d_persona_f"/>
        </v-dialog>


    </v-card>
</template>
<script>
export default {
    props:['activeItem', 'editedItem'],
    data() {
        return {
            valid: false,
            d_persona: false,
            d_persona_f: false,
            cargos:[],
            oficinas:[],
            regimenes:[],
            rules: {
               entero: v => v > 0 || 'El campo solo admite números',
               required: v => !!v || 'Descripcion Requerido',
               dni: v=> v != undefined && v.length == 8 || 'Longitud incorrecta'
            },
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
            return this.activeItem.id > 0 ? 'Editar Plaza': 'Registrar Nueva Plaza'
        },
        btn_registrar(){
            return this.activeItem.id > 0 ? 'Actualizar Datos': 'Registrar'
        },
    },
    watch:{
        
    },
    created(){
        this.getDocumentos();
    },
    methods:{
        getDocumentos(){
            axios.get("plazasApi?tablas=true")
                .then(response =>{
                    this.oficinas = response.data.oficinas
                    this.cargos = response.data.cargos
                    this.regimenes = response.data.regimenes
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
                    axios.post('plazasApi', this.activeItem)
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
                        });
                } else{
                    axios.put('plazasApi/' + this.activeItem.id, this.activeItem)
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
                        });
                    this.showLoad('', '', false)
                }
                
            }
        },

        confirma(item){
            if(item.op_regimen_id*1 == 1 || item.op_regimen_id*1 == 2 || item.op_regimen_id*1 == 5){
                if(confirm("La plaza que quiere modificar tiene la condición de NOMBRADA, ¿Seguro que quiere continuar?")){
                    return true;
                }else{
                    return false;
                }
            } else{
                return true;    
            }
        },
        changePersona(item){
            if(item.op_regimen_id*1 == 1 || item.op_regimen_id*1 == 2 || item.op_regimen_id*1 == 5){
                if(confirm("La plaza que quiere modificar tiene la condición de NOMBRADA, ¿Seguro que quiere continuar?")){
                    this.d_persona = true;
                }else{
                    return;
                }
            } else{
                this.d_persona = true;
            }
        },
        changePersonaFisica(){
            this.d_persona_f = true;
        },
        selectedItem(item){
            if(this.activeItem.id == undefined){
                this.activeItem.ocupado_cap = [];
            }

            if(this.activeItem.ocupado_cap[0] == undefined){
                this.activeItem.ocupado_cap.push({id:'',ap_paterno:'', ap_materno: '', nombres:'', numero_documento:''})
            }
            this.activeItem.ocupado_cap[0].id = item.id
            this.activeItem.ocupado_cap[0].ap_paterno = item.ap_paterno
            this.activeItem.ocupado_cap[0].ap_materno = item.ap_materno
            this.activeItem.ocupado_cap[0].nombres = item.nombres
            this.activeItem.ocupado_cap[0].numero_documento = item.numero_documento
            this.d_persona = false;
        },
        selectedItemF(item){
            if(this.activeItem.id == undefined){
                this.activeItem.ocupado_caf = [];
            }
            if(this.activeItem.ocupado_caf[0] == undefined){
                this.activeItem.ocupado_caf.push({id:'',ap_paterno:'', ap_materno: '', nombres:'', numero_documento:''})
            }
            this.activeItem.ocupado_caf[0].id = item.id
            this.activeItem.ocupado_caf[0].ap_paterno = item.ap_paterno
            this.activeItem.ocupado_caf[0].ap_materno = item.ap_materno
            this.activeItem.ocupado_caf[0].nombres = item.nombres
            this.activeItem.ocupado_caf[0].numero_documento = item.numero_documento
            this.d_persona_f = false;
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
    }
    
}
</script>