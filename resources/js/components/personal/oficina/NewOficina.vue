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
                        v-model="datos.of_nombre"
                        label="Nombre de la Oficina o Dependencia"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm12 md12>
                     <v-autocomplete
                           v-model="datos.parent_id"
                           :items="oficinas"
                           label="Jerarquia"
                           item-text="raiz"
                           item-value="ID"
                        />
                  </v-flex>
                  <v-flex xs12 sm12 md9>
                     <v-autocomplete
                           v-model="datos.cod_local"
                           :items="locales"
                           label="Local"
                           item-text="dsc_local"
                           item-value="cod_local"
                        />
                  </v-flex>
                  <v-flex xs12 sm12 md3>
                     <v-text-field
                       v-model="datos.nro_bloque"
                       label="N° Bloque"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm12 md4>
                     <v-checkbox v-model="datos.have_childrens" label="Tiene Sub areas?"></v-checkbox>
                  </v-flex>
                  <v-flex xs12 sm12 md3>
                     <v-checkbox v-model="datos.have_personal" label="Tiene Personal"></v-checkbox>
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
         }
      },
      computed: {
         formTitle () {
            if (this.editIndex === -1) {
               this.datos.have_childrens = false;
               this.datos.have_personal = false;
            }
           return this.editIndex === -1 ? 'Nueva Dependencia' : 'Editar datos de la Dependencia'
         },
         
      },
      watch:{
         editIndex(val){            
            if (val<0) {
               console.log("aqui")
               this.datos = this.default
            } else{               
               this.getOficina()
            }
         },
      },      
      methods: {
         close(){
            this.$emit('close')
            this.datos = this.default
         },
         save(){
            if (this.editIndex > -1) {
               axios.put(`oficinasApi/${this.datos.of_id}`, this.datos)
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
               
               axios.post('oficinasApi', this.datos)
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