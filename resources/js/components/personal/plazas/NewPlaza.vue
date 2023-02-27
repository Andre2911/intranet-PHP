<template>
   <v-card>
      <v-toolbar color="teal" dark>
         <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
         <v-container grid-list-md>
            <v-form v-model="valid" ref="form">
               <v-layout wrap>
                  <v-flex xs12 sm12 md12>
                     <v-text-field
                        v-model="editedItem.cargofun_det"
                        label="Nombre de la Plaza"
                        required
                        :rules="[rules.required]"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm12 md12>
                     <v-autocomplete
                           v-model="editedItem.oficina_id"
                           :items="oficinas"
                           label="Local"
                           item-text="raiz"
                           item-value="ID"
                           required
                           :rules="[(v) => !!v || 'Seleccionar un Item']"
                        />
                  </v-flex>
                  <v-flex xs12 sm12 md12>
                     <v-autocomplete
                           v-model="editedItem.cargofun_ji"
                           :items="plazasCafOc"
                           label="Jefe Inmediato Extraordinario"
                           item-text="cadena"
                           item-value="cargofun_id"
                           clearable
                        />
                  </v-flex>
                  <v-flex xs12 sm12 md3>
                     <v-text-field
                       v-model="editedItem.order_caf"
                       label="Orden del CAF"
                       type="number"
                       min=0
                       required
                       :rules="[rules.required, rules.entero]"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm12 md6>
                     <v-checkbox v-model="editedItem.jefe_area" label="El cargo es jefe de dependencia?"></v-checkbox>
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
      props: ['editedItem', 'editIndex', 'locales', 'oficinas', 'plazasCafOc'],
      data() {
         return {
            valid: false,
            snackValues: {
               color:'',
               text:'',
               show:'',
            },
            rules: {
               entero: v => v > 0 || 'El numero debe ser mayor a 0',
               required: v => !!v || 'Descripcion Requerido',
            },
            datos: [],
            default : [],
         }
      },
      computed: {
         formTitle () {
            if (this.editIndex === -1) {
               this.datos.have_childrens = false;
               this.datos.have_personal = false;
            }
           return this.editIndex === -1 ? 'Nueva Plaza' : 'Editar datos de la Plaza'
         },
      },
      watch:{
         editIndex(val){            
            if (val<0) {
               this.datos = this.default
            } else{               
               console.log(this.editedItem)
               //this.getOficina()
            }
         },
      },      
      methods: {
         close(){
            this.$emit('close')
         },
         save(){
            if (this.$refs.form.validate()) {
               if (this.editIndex > -1) {
                  axios.put(`plazasApi/${this.editedItem.cargofun_id}`, this.editedItem)
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
               } else {
                  axios.post('plazasApi', this.editedItem)
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
         getOficina(url='oficinasApi?oficinaID=' + this.editedItem.ID){
            axios.get(url)
                  .then(response =>{
                     //this.datos = response.data['oficinaData'];
                     this.datos = response.data['oficinaData']
                     console.log(this.datos)
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },
         showSnack(color, text){
            this.snackValues.color = color
            this.snackValues.text = text
            this.snackValues.show = true
         },
      }
   };

</script>