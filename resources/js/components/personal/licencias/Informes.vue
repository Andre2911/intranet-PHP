<template>
   <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
      <v-main id="contentApp">  
         <v-card>
            <v-toolbar color="grey" dark>
               <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
            </v-toolbar>
            <v-card-title>

               <v-layout wrap>
                     <v-flex xs12 sm10 md10>
                        <v-autocomplete
                           v-model="personal_id"
                           :items="personal"
                           label="Personal"
                           item-text="nombrecompleto"
                           item-value="numero_documento"
                           ref="personal_id"
                           clearable
                           />
                     </v-flex>
                     <v-flex xs6>
                        <v-autocomplete
                           v-model="licencia_id"
                           :items="tiposlicencia"
                           label="Licencia / Permiso"
                           item-text="fulldescripcion"
                           item-value="id"
                           clearable
                        />
                     </v-flex>
                  </v-layout>
                  <v-btn color="info" @click="updateData(1)">
                     <v-icon>mdi-magnify</v-icon>
                     Buscar
                  </v-btn>
                  <v-spacer></v-spacer>
                     <v-btn color="primary" @click="newItem">
                        <v-icon>mdi-plus-circle</v-icon>
                        Nuevo Informe
                     </v-btn>
                     
            </v-card-title>
            <v-card-text>
               <dataTable
                     :data="data"
                     :headers="headers"
                     :filters="filters"
                     :n_paginas="n_paginas"
                     :itemsPerPage="itemsPerPage"
                     :pagina_selected="pagina_actual"
                     @change_page="updateData"
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
           max-width="1200px"
           transition="dialog-transition"
         >
            <personal-licencias-newInforme 
               :editedItem="editedItem"
               :editIndex="editIndex"
               :tiposlicencia="tiposlicencia"
               :personal="personal"
               :formatos="formatos"
               :documentos="documentos"
               :dialog_edit="dialog_edit"
               @close="close"
               @save="save"
               @showmsg="showmsg"
            />
         </v-dialog>
         <dialogLoader :dialogLoad="dialogLoad"/>
         <snackbar :snack="snack" />
      </v-main>
   </v-app>
</template>

<script>
   export default {
      props:['data-user'],
      data() {
         return {
            menu: 2,
            submenu: 0,
            modulo: 'personal',
            drawer:true,
            loading: true,
            data:[],
            search:'',
            headers:[
            	{text: 'N° Informe', value: 'n_informe'},
               {text: 'Solicitante', value: 'nombrecompleto'},
               {text: 'Documento', value: 'doc_completo'},
               {text: 'Licencia/Permiso', value: 'descripcion'},
               {text: 'Sentido', value: 'sentido'},
               {text: 'Inicio', value: 'fecha_ini'},
               {text: 'Final', value: 'fecha_fin'},
               {text: 'Días', value: 'n_dias'},
               {text: 'Vac', value: 'anio_vac'},
               {text: 'Decisión CED', value: 'confirmada', type: 'array_chip', array:'0=Denegada/error,1=Confirmada/success'},
               {text: 'Opciones', value: 'view,edit', acciones: 'registrar', type:'opciones' },
            ],
            pagina_actual:1,
            n_paginas:0,
            itemsPerPage:10,
            editIndex:-1,
            editedItem:{
            	fecha_permiso: this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10))),
            },
            dialog_edit:false,
            licencia_id:null,
            personal_id:null,
            active: null,
            tiposlicencia:[],
            personal:[],     
            personal_activo:[],     
            documentos:[],
            formatos:[], 
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
            return 'Informes de Licencias';

         },
      },
      watch:
      {     
         dialog_edit(val){
            if(!val){
               this.editedItem.fecha_permiso = (this.editedItem.fecha_permiso != undefined && this.editedItem.fecha_permiso != null )? this.parseDate(this.editedItem.fecha_permiso) : null
               this.editedItem.fecha_ini = (this.editedItem.fecha_ini != undefined && this.editedItem.fecha_ini != null )? this.parseDate(this.editedItem.fecha_ini) : null
               this.editedItem.fecha_fin = (this.editedItem.fecha_fin != undefined && this.editedItem.fecha_fin != null )? this.parseDate(this.editedItem.fecha_fin) : null
               this.editedItem.fecha_inf = (this.editedItem.fecha_inf != undefined && this.editedItem.fecha_inf != null )? this.parseDate(this.editedItem.fecha_inf) : null
               this.editedItem.doc_fecha = (this.editedItem.doc_fecha != undefined && this.editedItem.doc_fecha != null )? this.parseDate(this.editedItem.doc_fecha) : null
               this.editedItem.fecha_resced = (this.editedItem.fecha_resced != undefined && this.editedItem.fecha_resced != null )? this.parseDate(this.editedItem.fecha_resced) : null
            }
         }

       },
      created ()
      {
         this.getData()
      },
      methods:
      {
         getData(pag=1, search=null, perpage=15){
            let urlPlantillas='licenciasApi?init=true&page='+pag;
            this.pagina_actual = pag;
            this.itemsPerPage = perpage;
            this.showLoading(true, 'primary', 'Cargando datos');

            axios.get(urlPlantillas)
                  .then(response =>{
                     this.data = response.data.informes.data;
                     this.n_paginas = response.data.informes.last_page;
                     this.itemsPerPage = response.data.informes.per_page;

                     this.personal = response.data.personal;
                     this.personal_activo = response.data.personal_activo;
                     this.tiposlicencia = response.data.licencias;
                     this.documentos = response.data.documentos;
                     this.formatos = response.data.formatos;
                     this.showLoading(false, '', '');

                  })
                  .catch(errors =>{
                  	if (errors.response.status === 401) {
                         window.location = "/";
                     }
                     if (errors.response.status === 500) {
                         this.getData();
                     }
                     console.log(errors);
                     this.showLoading(false, '', '');

                  });
         },

         updateData(pag=1, search=null, perpage=15){
            this.showLoading(true, 'primary', 'Cargando datos');
            let urlPlantillas='licenciasApi?informes=true&page='+pag;

            if(this.personal_id != null && this.personal_id != '' && this.licencia_id != null && this.licencia_id != ''){
               urlPlantillas='licenciasApi?informes=true&page='+pag+'&personal_id='+this.personal_id+'&licencia_id='+this.licencia_id ;
            }else if(this.personal_id != null && this.personal_id != ''){
               urlPlantillas='licenciasApi?informes=true&page='+pag+'&personal_id='+this.personal_id ;
            } else if(this.licencia_id != null && this.licencia_id != ''){
               urlPlantillas='licenciasApi?informes=true&page='+pag+'&licencia_id='+this.licencia_id ;
            }
            
            this.pagina_actual = pag;
            this.itemsPerPage = perpage;

            axios.get(urlPlantillas)
                  .then(response =>{
                     this.data = response.data.informes.data;
                     this.n_paginas = response.data.informes.last_page;
                     this.pagina_actual = response.data.informes.current_page
                     this.itemsPerPage = response.data.informes.per_page;
                     this.showLoading(false, 'primary', 'Cargando datos');

                  })
                  .catch(errors =>{
                  	if (errors.response.status === 401) {
                         window.location = "/";
                     }
                     if (errors.response.status === 500) {
                         this.getData();
                     }
                     console.log(errors);
                     this.showLoading(false, 'primary', 'Cargando datos');

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
            //this.getData();
         }, 
         save(e){
            this.updateData(this.pagina_actual)
            this.showSnack(e.color, e.text, true)
            this.editedItem.archivo = null;
            if (e.show) {
            	this.dialog_edit = false	
            }
            
         },
         showmsg(e){
         	this.showSnack(e.color, e.text, true)
         },
         newItem(item){
            this.editIndex = -1;
            this.dialog_edit = true;
            this.getTipoLicencias();
            this.editedItem = Object.assign({}, this.defaultItem)
            this.editedItem.archivo = null;
            this.editedItem.c_goce = false;
            this.editedItem.doc_fecha= this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10)))
            this.editedItem.fecha_ini= this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10)))
            this.editedItem.fecha_fin= this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10)))
            this.editedItem.fecha_inf= this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10)))
         },
         viewItem(item){
         	let urlDocumento = 'licenciasApi?getDocumento=true'+ '&informe=' + item.id + '&formato=' + item.archivo;
            let informe = window.open(urlDocumento, 'ventana', 'width=600, height = 200 , scrollbars = no', false);
            informe.document.write("descargando el informe..." + item.n_informe + "<br>verificar en su carpeta de archivos descargados");
            informe.location.href = urlDocumento;
            setTimeout(function ()
            {
               informe.close();
            }, 5000)

         },
         editItem(item){
            this.editIndex = this.data.indexOf(item)
            this.editedItem =item
            this.editedItem.fecha_permiso = this.formatDate(item.fecha_permiso)
            this.editedItem.fecha_ini = this.formatDate(item.fecha_ini)
            this.editedItem.fecha_fin = this.formatDate(item.fecha_fin)
            this.editedItem.fecha_inf = this.formatDate(item.fecha_inf)
            this.editedItem.doc_fecha = this.formatDate(item.doc_fecha)
            this.editedItem.fecha_resced = this.formatDate(item.fecha_resced)
            this.editedItem.cod_doc = item.cod_doc.padStart(2,'0')
            this.editedItem.confirmada = item.confirmada*1
            //this.getTipoLicencias();
            this.dialog_edit = true
         },
         deleteItem(item){},
         formatDate (date) {
            if (!date) return null

            const [year, month, day] = date.split('-')
            return `${day}/${month}/${year}`
         },
         parseDate (date) {
            if (!date) return null
               const [day, month, year] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
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
