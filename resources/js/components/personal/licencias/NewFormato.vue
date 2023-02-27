<template>
   <v-card>
      <v-toolbar color="grey" dark>
         <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
         <v-container grid-list-md>
            <v-form v-model="valid" ref="form">
               <v-layout wrap>
                  <v-flex xs12 sm9 md9>
                     <v-autocomplete
                           v-model="editedItem.licencia_tipo"
                           :items="tiposlicencia"
                           label="Tipo de Licencia"
                           item-text="fulldescripcion"
                           item-value="id"
                           :rules="[rules.required]"
                        />
                  </v-flex>
                  <v-flex xs12 sm3 md3>
                     <v-autocomplete
                           v-model="editedItem.sentido"
                           :items="sentidos"
                           label="Sentido del Informe"
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
                  
                  <v-flex xs12 sm12 md12>
                     <input type="file" ref="file" class="form-control" v-on:change="onImageChange"/>
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
      props: ['editedItem', 'editIndex', 'tiposlicencia', 'oficinas'],
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
            sentidos:[
               'PROCEDENTE',
               'IMPROCEDENTE',
               'DESISTIDO',
               'SUSPENDIDO'
            ],
            solicitantes:[
               '728/CAS',
               'MAGISTRADO'
            ],
            file:null,
            rules: {
               required: v => !!v || 'Descripcion Requerido',
            },
         }
      },
      computed: {
         formTitle () {
            if (this.editIndex === -1) {
            }
           return this.editIndex === -1 ? 'Nuevo Formato de Informe' : 'Editar datos del formato de Informe'
         },
         
      },
      watch:{
         editIndex(val){            
            if (val<0) {
               
            } else{               
               
            }
         },
      },      
      methods: {
         close(){
            this.$refs.file.value = null;
            this.$emit('close')
            this.datos = this.default
         },
         save(e){
            e.preventDefault();
            let currentObj = this;

            const config ={   headers: {  'content-type': 'multipart/form-data'  }  }

            let formData = new FormData();
            formData.append('file', this.file);
            formData.append('formato_id', this.editedItem.id);
            formData.append('descripcion', this.editedItem.descripcion);
            formData.append('licencia_tipo', this.editedItem.licencia_tipo);
            formData.append('sentido', this.editedItem.sentido);
            
            if (this.editIndex > -1) {
               axios.post('licenciasApi?updateformatos=true', formData, config)
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
               axios.post('licenciasApi?saveformatos=true', formData, config)
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
            this.$refs.file.value = null;
         },
         onImageChange(e)
         {
            this.file = e.target.files[0];
         },
         showSnack(color, text){
            this.snackValues.color = color
            this.snackValues.text = text
            this.snackValues.show = true
         },
      }
   };

</script>