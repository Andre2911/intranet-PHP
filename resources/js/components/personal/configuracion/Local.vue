<template>
   <div id="app">
      <v-app>
         <v-card>
            <v-toolbar color="blue" dark>
               <v-toolbar-title>Locales de la Corte Superior de Justicia de Puno</v-toolbar-title>
            </v-toolbar>
            <v-card-text>
               <v-btn color="primary" @click="newItem">
                  <v-icon>add_circle</v-icon>
                  Nuevo Local
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
            <v-dialog
              v-model="dialog_edit"
              scrollable 
              :overlay="true"
              max-width="700px"
              transition="dialog-transition"
            >
               <new-local-component 
                  :editedItem="editedItem"
                  :editIndex="editIndex"
                  :ubigeos="ubigeos"
                  @close="close"
                  @save="save"
               />
            </v-dialog>
            <v-snackbar v-model="snack" top="top" right="right" :timeout="  3000" :color="snackColor">
               {{ snackText }}
            <v-btn flat @click="snack = false">Close</v-btn>
         </v-snackbar>
         </v-card>
      </v-app>
   </div>
</template>

<script type="text/javascript">
   export default {
      data() {
         return {
            loading: true,
            headers:[
               {text: 'ID', value: 'cod_local', sortable: false},
               {text: 'Alias', value: 'alias', sortable: true,},
               {text: 'Dirección', value: 'dsc_local'},
               {text: 'Provincia', value: 'n_provincia'},
               {text: 'Distrito', value: 'n_distrito'},
               {text: 'Ubigeo', value: 'cod_ubigeo'},
               {text: 'Opciones', value: 'edit,delete'},
            ],
            datos:[],
            ubigeos:[],
            pagination:{ rowsPerPage: 15,descending:true},
            dialog_edit: false,
            editedItem: {

            },
            defaultItem: {
            },
            editIndex: -1,
            snack: false,
            snackText: '',
            snackColor: '',
         }
      },
      computed:{

      },
      watch: {

      },
      created(){
         this.getData()
      }, 
      methods:{
         getData(){
            var url = 'localesApi?init=true'
            axios.get(url)
                  .then(response =>{
                     this.datos = response.data['locales'];
                     this.ubigeos = response.data['ubigeos'];
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         
         },
         newItem(){
            this.editedItem = this.defaultItem
            //this.editedItem.c_departamento = "21"
            this.dialog_edit= true
            this.editIndex = -1            
         },
         viewItem(){

         },
         editItem(item){
            this.dialog_edit= true
            this.editedItem = item
            this.editIndex = this.datos.indexOf(item)
         },
         deleteItem(){

         },
         close(){
            this.dialog_edit = false
         },
         save(e){
            this.getData();
            this.showSnack(e.color, e.text)
            //this.$refs.search.focus()
            this.dialog_edit = false
         },
         showSnack(color, text){
            this.snackColor = color
            this.snackText = text
            this.snack = true
         },
      }
   }
   
</script>