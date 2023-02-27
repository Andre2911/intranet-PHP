<template>
   <v-card>
      <v-toolbar color="blue" dark>
         <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
         <v-container grid-list-md>
            <v-form v-model="valid" ref="form">
               <v-layout wrap>
                  <v-flex xs12 sm12 md3 offset-md6>
                     <v-text-field 
                        v-model="editedItem.id"
                        label="DNI"
                        :rules="[rules.required]"
                        :minlength=8
                        :maxlength=8
                        outline
                        required
                        ref="dni"
                     />
                  </v-flex>
                  <v-flex xs12 sm12 md3>
                     <v-btn color="info" @click="getDNI">
                        <v-icon>search</v-icon>
                        Buscar
                     </v-btn>
                  </v-flex>
                  <v-flex xs12 sm12 md3>
                     <v-text-field 
                        v-model="editedItem.pers_paterno"
                        label="Apellido Paterno"
                        :rules="[rules.required]"
                        required
                     />
                  </v-flex>
                  <v-flex xs12 sm12 md3>
                     <v-text-field 
                        v-model="editedItem.pers_materno"
                        label="Apellido Materno"
                        :rules="[rules.required]"
                        required
                     />
                  </v-flex>
                  <v-flex xs12 sm12 md6>
                     <v-text-field 
                        v-model="editedItem.pers_nombres"
                        label="Nombres"
                        :rules="[rules.required]"
                        required
                     />
                  </v-flex>
                  <v-flex xs12 sm6 md12>
                        <v-autocomplete
                           v-model="editedItem.pers_cargofun"
                           :items="plazasCaf"
                           label="Cargo Funcional"
                           item-text="PLAZA"
                           item-value="ID"
                           :clearable=true
                           ref="pers_cargofun"
                         > 
                         </v-autocomplete>
                     </v-flex>
                     <v-flex xs12 sm12 md4>
                        <v-text-field 
                           v-model="editedItem.pers_fechain"
                           label="Fecha de Ingreso"
                        />
                     </v-flex>
                     <v-flex xs12 sm12 md2>
                        <v-text-field 
                           v-model="editedItem.pers_casjud"
                           label="Casilla Judicial"
                        />
                     </v-flex>
                     <v-flex xs12 sm12 md2>
                        <v-autocomplete
                           v-model="editedItem.pers_cargo"
                           :items="cargos"
                           label="Cargo"
                           item-text="cargo_abr"
                           item-value="cargo_id"
                        />
                     </v-flex>
                     <v-flex xs12 sm12 md4>
                        <v-autocomplete
                           v-model="editedItem.pers_regimen"
                           :items="regimens"
                           label="Regimen"
                           item-text="regimen_det"
                           item-value="regimen_id"
                        />
                     </v-flex>
                     <v-flex xs12 sm12 md6>
                        <v-text-field 
                           v-model="editedItem.pers_correo"
                           label="Correo"
                        />
                     </v-flex>
                     <v-flex xs12 sm12 md6>
                        <v-flex xs12 sm12 md3>
                     <v-checkbox v-model="editedItem.pers_activo" label="Activo"></v-checkbox>
                  </v-flex>
                     </v-flex>
               </v-layout>
            </v-form>
         </v-container>
      </v-card-text>
      <v-card-actions>
         <v-spacer></v-spacer>
         <v-btn color="blue darken-1" flat @click="close">Cancel</v-btn>
         <v-btn color="blue darken-1" flat @click="save">Guardar</v-btn>
      </v-card-actions>
   </v-card>
</template>

<script>
   export default {
      props:['editIndex', 'editedItem','plazasCaf', 'cargos', 'regimens', 'dialogLoad'],
      data() {
         return {
            valid:false,
            updateData:false,
            rules: {
               entero: v => v > 0 || 'El numero debe ser mayor a 0',
               required: v => !!v || 'Descripcion Requerido',
               dni: v=> v.length == 8 || 'Longitud incorrecta'
            },
            snackValues: {
               color:'',
               text:'',
               show:'',
            },

         }
      },
      computed: {
         formTitle () {
           return this.editIndex === -1 ? 'Nuevo Personal' : 'Editar datos del personal'
         },
      },
      watch: {
         editIndex (val) {
            if(val<0){
               this.updateData = false;
            } else {
               //this.editedItem = Object.assign({}, this.editedItemP);
               this.updateData = true
            }
         }
      },
      methods: {
         save(){
            if (this.$refs.form.validate()) {
               if (this.updateData) {
                  axios.put(`personalApi/${this.editedItem.id}`, this.editedItem)
                     .then((response) => {
                        if (response.data['statusBD']) {
                           this.showSnack('success', response.data['messageDB'])
                           this.close();
                           this.$emit('save', this.snackValues);
                        } else{
                           this.showSnack('red', response.data['messageDB'])
                           this.$emit('save', this.snackValues);
                        }
                       
                    });
                } else{
                  axios.post('personalApi', this.editedItem)
                     .then((response) => {
                        if (response.data['statusBD']) {
                           this.showSnack('success', response.data['messageDB'])
                           this.close();
                           this.$emit('save', this.snackValues);
                        } else{
                           this.showSnack('red', response.data['messageDB'])
                           this.$emit('save', this.snackValues);
                        }
                       
                    });
                }
            }
         },
         close () {
            this.$emit('close');
         },
         showSnack(color, text, esconder=true){
            this.snackValues.color = color
            this.snackValues.text = text
            this.snackValues.show = esconder
         },
         getDNI() {
            if (this.editedItem.id === undefined || this.editedItem.id.length < 8) {
               this.showSnack('warning', 'Inserte un Numero de DNI válido')
               this.$emit('showMessage', this.snackValues)
               this.$refs.dni.focus();
            } else{
               this.$emit('loadmodal', true)
               var url = '../../consultaDNI/'+this.editedItem.id;
               axios.get(url)
                  .then(response =>{
                     this.editedItem.pers_paterno = response.data.datos[2];
                     this.editedItem.pers_materno = response.data.datos[3]
                     this.editedItem.pers_nombres = response.data.datos[5]
                     this.$emit('loadmodal', false)
                     this.$refs.pers_cargofun.focus();
                     
                  })
                  .catch(errors =>{
                     console.log(errors);
                     this.$emit('loadmodal', false)
                     this.showSnack('warning', 'Error al consultar, intente denuevo')
                     this.$emit('showMessage', this.snackValues)
                  });
            }
            
            
         },
      }
   };
</script>