<template>
   <v-app id="app">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
      <v-main id="contentApp">  
         <v-card>
         	<v-toolbar color="grey" dark>
               <v-toolbar-title>Tipo de Licencias y Permisos</v-toolbar-title>
            </v-toolbar>
            <v-card-text>
         		<v-btn color="primary" @click="newItem">
                  <v-icon>mdi-plus-circle</v-icon>
                  Nuevo Tipo de Licencia
               </v-btn>
               <dataTable
                  :data="data"
                  :headers="headers"
                  :filters="filters"
                  :n_paginas="n_paginas"
                  :itemsPerPage="itemsPerPage"
                  :pagina_selected="pagina_actual"
                  :tipo="1"
                  @change_page="getData"
                  @editItem="editItem"
                  @viewItem="viewItem"
                  @deleteItem="deleteItem"
               />
         	</v-card-text>
         </v-card>
         <v-dialog
           v-model="dialog_edit"
           scrollable 
           :overlay="true"
           max-width="700px"
           transition="dialog-transition"
         >
            <personal-licencias-newTipo 
               :editedItem="editedItem"
               :editIndex="editIndex"
               @close="close"
               @save="save"
            />
         </v-dialog>
         <snackbar :snack="snack" />
         <dialogLoader :dialogLoad="dialogLoad"/>
      </v-main>
   </v-app>
</template>
<script>
export default {
      props:['dataUser'],
      data() {
         return {
            menu: 2,
            submenu: 2,
            modulo: 'personal',
            drawer:true,
            loading: true,
            pagination: {
               rowsPerPage: 20,
            },
            headers:[
               {text: 'Tipo', value: 'tipo'},
               {text: 'Solicitado por', value: 'solicitante'},
               {text: 'Descripcion', value: 'descripcion'},
               {text: 'Goce de Haber', value: 'c_goce', type: 'boolean'},
               {text: 'Sin Goce de Haber', value: 's_goce', type: 'boolean'},
               {text: 'A Cuenta de Vacaciones', value: 'a_vac', type: 'boolean'},
               {text: 'Opciones', value: 'edit,delete', type:'opciones'},
            ],
            data: [],
            dialog_ver: false,
            dialog_edit: false,
            editIndex: -1,
            editedItem:{},
            locales: [],
            oficinas: [],
            defaultItem:{
               id:'',
               //tipo:null,
               //solicitante:null,
               //descripcion:null,
            },
            n_paginas:1,
            itemsPerPage:100,
            pagina_actual:1,
            filters: {
            },
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            dialogLoad: {
                show:false,
                text: '',
                color: '',
            }, 
            default : {},
         }
      },
      computed: {
         
      },
      watch:{
         editIndex(val){            
            if (val<0) {
               this.editedItem = this.default
            } else{               
               
            }
         },
      },
      created(){
      	this.getData();
      },
      methods: {
         getData(url = 'licenciasApi?tipos=true'){
            axios.get(url)
                  .then(response =>{
                     this.data = response.data['licencias'];
                     this.loading = false;
                     this.dialogLoad = false;

                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },
         newItem(){
         	this.editIndex = -1;
            this.dialog_edit = true;
            this.editedItem = Object.assign({}, this.defaultItem)
         },
         viewItem(){},
         editItem(item){
         	this.editIndex = this.data.indexOf(item)
            this.editedItem =item
            this.dialog_edit = true
         },
         deleteItem(item){
         	confirm('¿Estás seguro que desea eliminar este tipo de Licencia?') && 
               axios.delete(`licenciasApi/${item.id}?tipos=true`)
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
         close(){
         	this.editIndex = -1;
           	this.dialog_edit = false 
           	this.getData();
         },	
         save(e){
            this.getData();
            this.showSnack(e.color, e.text)
            this.dialog_edit = false
         },
         showSnack(color, text, show = true){
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
         },
         showLoading(show = true, color = null, text=''){
				this.dialogLoad.color = color
				this.dialogLoad.text = text
				this.dialogLoad.show = show
         },
         cDrawer(val){
            if(val != undefined ){
                this.drawer = val;
            } else{
                this.drawer = !(this.drawer);
            }
        },
      }
   } 	

 </script>