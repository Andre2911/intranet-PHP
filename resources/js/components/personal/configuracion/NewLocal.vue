<template>
   <v-card>
      <v-toolbar color="blue" dark>
         <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
         <v-container grid-list-md>
            <v-form v-model="valid" ref="form">
               <v-layout wrap>
                  <v-flex xs12 sm12 md12>
                     <v-text-field
                        v-model="editedItem.alias"
                        label="Descripción o Alias del local"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm12 md10>
                     <v-text-field
                        v-model="editedItem.dsc_local"
                        label="Direccion del local"
                        required
                        :rules="[rules.required]"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm12 md2>
                     <v-text-field 
                        v-model="editedItem.ubigeo_id" 
                        label="Ubigeo"
                        :readonly=true
                        :disabled=true
                        :counter="6"
                        required
                     />
                  </v-flex>
                  <v-flex xs12 sm12 md4>
                     <v-autocomplete
                         v-model="editedItem.c_departamento"
                         :items="filtroDepartamento"
                         label="Departamento"
                         item-text="n_departamento"
                         item-value="c_departamento"
                       > 
                       </v-autocomplete>
                  </v-flex>
                  <v-flex xs12 sm6 md4>
                     <v-autocomplete
                        v-model="editedItem.c_provincia"
                        :items="filtroProvincia"
                        label="Provincia"
                        item-text="n_provincia"
                        item-value="c_provincia"
                        :rules="[(v) => !!v || 'Seleccionar un Item']"
                         > 
                     </v-autocomplete>
                  </v-flex>
                  <v-flex xs12 sm6 md4>
                     <v-autocomplete
                        v-model="editedItem.c_distrito"
                        :items="filtroDistrito"
                        label="Distrito"
                        item-text="n_distrito"
                        item-value="c_distrito"
                        :rules="[(v) => !!v || 'Seleccionar un Item']"
                        @change="getUbigeo()"
                      > 
                      </v-autocomplete>
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
      props: ['editedItem', 'editIndex', 'locales', 'ubigeos', ],
      data() {
         return {
            valid: false,
            snackValues: {
               color:'',
               text:'',
               show:'',
               distrito: '',
            },
            rules: {
               entero: v => v > 0 || 'El numero debe ser mayor a 0',
               required: v => !!v || 'Descripcion Requerido',
            },
            datos: {},
            default : {},
         }
      },
      computed: {
         formTitle () {
           return this.editIndex === -1 ? 'Nuevo Local' : 'Editar datos del Local'
         },
         filtroDepartamento()
         {
            let departamentos = this.ubigeos;
            if((this.editedItem.c_provincia) != null && (this.editedItem.c_distrito) != null)
            {
               //this.editedItem.ubigeo_id = this.editedItem.c_departamento + this.editedItem.c_provincia+this.editedItem.c_distrito;
            }
           return departamentos.filter(d => d.c_departamento);

         },
         filtroProvincia()
         {
            console.log('fsdfds')
           let provincias = this.ubigeos;
           return provincias.filter(p => p.c_departamento == this.editedItem.c_departamento);

         },
         filtroDistrito()
         {
           let provincias = this.ubigeos;
           let distritos = provincias.filter(p => p.c_departamento == this.editedItem.c_departamento);
           return distritos.filter(d => d.c_provincia == this.editedItem.c_provincia);
           
         }
         
      },
      watch:{
         editIndex(val){            
            if (val<0) {
               this.$refs.form.reset();
               this.$refs.form.resetValidation();
               
            } else{               
               
            }
         },
         
         
      },      
      methods: {
         close(){
            this.$refs.form.reset();
            this.$refs.form.resetValidation();
            this.$emit('close')
         },
         save(){
            if (this.editIndex > -1) {
               if (this.$refs.form.validate()) {
                  axios.put(`localesApi/${this.editedItem.cod_local}`, this.editedItem)
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
            } else {
               if (this.$refs.form.validate()) {
                  axios.post('localesApi', this.editedItem)
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
         getUbigeo(){
            if((this.editedItem.c_departamento) != null && (this.editedItem.c_provincia) != null)
            {
               this.editedItem.ubigeo_id = this.editedItem.c_departamento + this.editedItem.c_provincia+this.editedItem.c_distrito;
               let provincias = this.ubigeos;
               let distritos = provincias.filter(p => p.c_departamento == this.editedItem.c_departamento);
               let distrito = distritos.filter(d => d.c_provincia == this.editedItem.c_provincia && d.c_distrito == this.editedItem.c_distrito);
               this.editedItem.dsc_ubigeo = distrito[0].n_distrito;
            }
         },
         showSnack(color, text){
            this.snackValues.color = color
            this.snackValues.text = text
            this.snackValues.show = true
         },
      }
   };

</script>