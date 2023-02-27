<template>
   <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
      <v-main id="contentApp">  
         <v-card>
            <v-toolbar color="grey" dark>
               <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
            </v-toolbar>
            <v-card-text>
               <v-btn color="primary" @click="newItem">
                  <v-icon>mdi-plus-circle</v-icon>
                  Nuevo Formato
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
           max-width="900px"
           transition="dialog-transition"
         >
            <personal-licencias-formato-new
               :editedItem="editedItem"
               :editIndex="editIndex"
               :tiposlicencia="tiposlicencia"
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
            submenu: 3,
            modulo: 'personal',
            drawer:true,
            loading: true,
            pagination: {
               rowsPerPage: 20,
            },
            data:[],
            search:'',
            headers:[
               {text: 'Tipo', value: 'tipo'},
               {text: 'Solicitado por', value: 'solicitante'},
               {text: 'Descripcion', value: 'descripcion'},
               {text: 'Archivo', value: 'archivo', type: 'archivo'},
               {text: 'Sentido de Informe', value: 'sentido'},
               {text: 'Opciones', value: 'edit,delete', type:'opciones'},
            ],
            editIndex:-1,
            editedItem:{},
            dialog_edit:false,
            d_uploadPlantilla:false,
            plantillaSelected:null,
            plantillaId:'',
            plantillaNombre:'',
            active: null,
            isProgramer : false,
            plantilla:'',
            docPlantilla:'',
            tiposlicencia:[],
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
         }
      },
      computed:
      {
         formTitle ()
         {
            return 'Administrador de Formatos de Informe';

         },
      },
      watch:
      {      },
      created ()
      {
         this.getData()
      },
      methods:
      {
         getData()
         {
            let urlPlantillas='licenciasApi?formatos=true';
            axios.get(urlPlantillas)
                  .then(response =>{
                     this.data = response.data['formatos'];
                     this.loading = false;
                     this.dialogLoad = false;

                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },

         getTipoLicencias(){
            let urlPlantillas='licenciasApi?tipos=true';
            axios.get(urlPlantillas)
                  .then(response =>{
                     this.tiposlicencia = response.data['licencias'];
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },

         close(){
            this.editIndex = -1;
            this.dialog_edit = false 
            this.getData();
         }, 
         save(e){
            console.log(e)
            this.getData();
            this.showSnack(e.color, e.text, true)
            this.editedItem.archivo = null;
            this.dialog_edit = false
         },
         download(item)
         {
            let v=[];
            console.log(item)
            v = window.open('download/' + item['doc'], 'ventana', 'width=600, height = 200 , scrollbars = no', false);
            v.document.write("descargando plantilla : " + item['doc'] + "<br>verificar en su carpeta de archivos descargados");
            v.location.href = 'download/' + item['doc'];
         },
         borrar(item)
         {
            console.log(item)
            confirm('¿Estas seguro que desea borrar la plantilla ?') &&
            axios.delete(`resolucionesApi/${item.id}`)
            .then((response) =>
            {
               if (response.data['statusBD'])
               {
                  this.getData()
               }
               else
               {
                  //this.showSnack('red', response.data['messageDB'],true)
               }
            });
         },

         showFormulario()
         {
           this.d_uploadPlantilla = true
         },

         cierra()
         {
            this.showSnack('green', "Plantilla cargada correctamente",true)
            this.getData()
            this.d_uploadPlantilla = false
         },

         showSnack(color, text, show)
         {
            this.snackColor = color
            this.snackText = text
            this.snack = show
         },
         newItem(item){
            this.editIndex = -1;
            this.dialog_edit = true;
            this.getTipoLicencias();
            this.editedItem = Object.assign({}, this.defaultItem)
         },
         viewItem(item){

         },
         editItem(item){
            this.editIndex = this.data.indexOf(item)
            this.editedItem =item
            this.getTipoLicencias();
            this.dialog_edit = true
         },
         deleteItem(item){
            confirm('¿Estás seguro que desea eliminar este tipo de Licencia?') && 
               axios.delete(`licenciasApi/${item.id}?formatos=true`)
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
   };
</script>