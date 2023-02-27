<template>
   <div id="app">
      <v-app>
         <v-card>
            <v-toolbar color="blue" dark>
               <v-toolbar-title>Oficinas de la Corte Superior de Justicia de Puno</v-toolbar-title>
            </v-toolbar>
            <v-card-text>
               <v-btn color="primary" @click="newItem">
                  <v-icon>add_circle</v-icon>
                  Nueva oficina
               </v-btn>
               <show-personal-component
                  :headers="headers"
                  :datos="datos"       
                  :pagination="pagination"           
                  @viewItem="viewItem"
                  @editItem="editItem"
                  @deleteItem="deleteItem"
               />
            </v-card-text>
         </v-card>
         <v-dialog
           v-model="dialog_ver"
           scrollable
           max-width="700px"
           transition="dialog-transition"
         >
            <view-oficina-component 
               :editedItem="editedItem"
               :editIndex="editIndex"
            />  
         </v-dialog>
         <v-dialog
           v-model="dialog_edit"
           scrollable 
           :overlay="true"
           max-width="700px"
           transition="dialog-transition"
         >
            <new-oficina-component 
               :editedItem="editedItem"
               :editIndex="editIndex"
               :locales="locales" 
               :oficinas="oficinas"
               @close="close"
               @save="save"
            />
         </v-dialog>
         <v-snackbar v-model="snack" top="top" right="right" :timeout="  3000" :color="snackColor">
               {{ snackText }}
            <v-btn flat @click="snack = false">Close</v-btn>
         </v-snackbar>
      </v-app>
   </div>
</template>
<script>
   export default {
      data() {
         return {
            loading: true,
            pagination: {
               rowsPerPage: 10,
            },
            headers:[
               {text: 'ID', value: 'ID'},
               {text: 'Provincia', value: 'provincia'},
               {text: 'Distrito', value: 'distrito'},
               {text: 'Descripcion', value: 'nombre'},
               {text: 'Raiz', value: 'raiz'},
               {text: 'Opciones', value: 'visibility,edit,delete'},
            ],
            datos: [],
            dialog_ver: false,
            dialog_edit: false,
            editedItem:{},
            editIndex: -1,
            locales: [],
            oficinas: [],
            defaultItem: [],
            snackColor: null,
            snack: null,
            snackText: null,
         }
      },
      computed: {
         
      },
      watch: {
         dialog_ver(val){
            if (val) {
               
            } else {
               this.editIndex = -1
            }
         },
         dialog_edit(val){
            if (val) {
               
            } else {
               this.editIndex = -1
            }
         }
      },
      created () {
         this.getData()
         this.getLocales()
      },
      methods: {
         getData(url = 'oficinasApi?completo=true'){
            axios.get(url)
                  .then(response =>{
                     this.datos = response.data['oficinas'];
                     this.loading = false;
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },
         getLocales(url = 'oficinasApi?init=true'){
            if (this.locales.length >0 ) return
            axios.get(url)
                  .then(response =>{
                     this.locales = response.data['locales'];
                     this.oficinas = response.data['oficinas'];
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },
         viewItem(item){
            this.dialog_ver = true
            this.editedItem =item
            this.editIndex = this.datos.indexOf(item)

         },
         editItem(item){
            this.editIndex = this.datos.indexOf(item)
            this.editedItem =item
            this.dialog_edit = true
         }, 
         deleteItem(item){
            confirm('¿Estas seguro que desea dar de baja esta oficina ?') && 
               axios.delete(`oficinasApi/${item.ID}`)
                    .then((response) => {
                        if (response.data['statusBD']) {
                           this.showSnack('success', response.data['messageDB'])
                           this.getData()
                           this.close();
                        } else{
                           this.showSnack('red', response.data['messageDB'])
                        }
                       
                    });
         },
         newItem(){
            this.dialog_edit = true
         },
         close(){
            this.editIndex = -1;
            this.dialog_edit = false
         },
         save(e){
            this.getData();
            this.showSnack(e.color, e.text)
            this.dialog_edit = false
         },
         showSnack(color, text){
            this.snackColor = color
            this.snackText = text
            this.snack = true
         },
      },
   };
</script>