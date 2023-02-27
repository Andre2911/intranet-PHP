<template>
   <v-card>
      <v-toolbar color="grey" dark>
         <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
         <v-container grid-list-md>
            <v-form v-model="valid" ref="form">
               <v-layout wrap>
                  <v-flex xs12 sm4 md4>
                     <v-autocomplete
                           v-model="editedItem.tipo"
                           :items="tiposl"
                           label="Tipo de Licencia"
                           :rules="[rules.required]"
                        />
                  </v-flex>
                  <v-flex xs12 sm4 md4>
                     <v-autocomplete
                           v-model="editedItem.solicitante"
                           :items="solicitantes"
                           label="Solicitante"
                           :rules="[rules.required]"
                        />
                  </v-flex>
                  <v-flex xs12 sm12 md12>
                     <v-text-field
                        v-model="editedItem.descripcion"
                        label="Descripción de la licencia"
                        :rules="[rules.required]"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm12 md4>
                     <v-checkbox v-model="editedItem.c_goce" label="CON goce de haber?"></v-checkbox>
                  </v-flex>
                  <v-flex xs12 sm12 md4>
                     <v-checkbox v-model="editedItem.s_goce" label="SIN goce de haber?"></v-checkbox>
                  </v-flex>
                  <v-flex xs12 sm12 md4>
                     <v-checkbox v-model="editedItem.a_vac" label="A cuenta de vacaciones?"></v-checkbox>
                  </v-flex>
               </v-layout>
            </v-form>
         </v-container>
      </v-card-text>      
      <v-card-actions>
            <v-btn color="red darken-1" text @click="close">Cancel
               <v-icon>mdi-cancel</v-icon>
            </v-btn>
            <v-spacer/>
            <v-btn color="blue darken-1" text @click="save">Guardar
               <v-icon>mdi-content-save</v-icon>
            </v-btn>
         </v-card-actions>
   </v-card>
   
</template>

<script>
   export default {
      props: ['editedItem', 'editIndex', 'locales', 'oficinas'],
      data() {
         return {
            valid: false,
            snackValues: {
               color:'',
               text:'',
               show:'',
            },
            datos: {},
            default : {},
            tiposl:[
               'LICENCIA',
               'PERMISO'
            ],
            solicitantes:[
               '728/CAS',
               'MAGISTRADO'
            ],
            rules: {
               required: v => !!v || 'Descripcion Requerido',
            },
         }
      },
      computed: {
         formTitle () {
            if (this.editIndex === -1) {
               this.editedItem.c_goce = false;
               this.editedItem.s_goce = false;
               this.editedItem.a_vac = false;
            }
           return this.editIndex === -1 ? 'Nuevo tipo de Licencia' : 'Editar datos del tipo de Licencia'
         },
         
      },
      watch:{
         
      },      
      methods: {
         close(){
            this.$emit('close')
            this.datos = this.default
         },
         save(){
            if (this.editIndex > -1) {
               axios.put(`licenciasApi/${this.editedItem.id}?tipos=true`, this.editedItem)
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
               axios.post('licenciasApi?savetipos=true', this.editedItem)
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
         },
         showSnack(color, text){
            this.snackValues.color = color
            this.snackValues.text = text
            this.snackValues.show = true
         },
      }
   };

</script>