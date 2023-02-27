<template>
   <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
        <v-main id="contentApp">
            <v-card>
               <v-toolbar color="grey" dark>
                  <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                  <v-spacer/>
               </v-toolbar>
               <v-card-text>
                  <v-row>
                       
                        <v-col cols="12" md="3">
                            <v-btn block color="cyan darken-3" @click="getDatosExpediente()" dark :disabled="datos.length == 0">
                              <v-icon>mdi-magnify</v-icon>
                              Consultar expediente
                           </v-btn>
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-btn block color="success" @click="registrarRepo()" dark :disabled="datos.length == 0 ">
                              <v-icon>mdi-content-save</v-icon>
                              Registrar documentos
                           </v-btn>
                        </v-col>
                    </v-row>

                    <dataTable
                        :headers="headers"
                        :data="datos"
                        :filters="filters"
                        tipo=1
                        :search="search"
                        @editItem="editItem"
                     />
                     
               </v-card-text>
            </v-card>
                       
            <v-dialog
               v-model="d_expediente"
               max-width="350px"
            >
               <v-card>
                  <v-card-title color="red darken-4">
                     N° de expediente
                  </v-card-title>
                  <v-card-text>
                     <v-text-field v-model="activeItem.expediente" label="N° de Expediente"/>
                  </v-card-text>
                  <v-card-actions>
                     <v-spacer></v-spacer>
                     <v-btn color="red darken-1" text @click="d_expediente = false">
                        Cancelar
                        <v-icon
                           class="mr-2"
                           color="red"
                        >
                           mdi-cancel
                        </v-icon> 
                     </v-btn>
                     <v-btn color="info" text @click="d_expediente = false">
                        Guardar
                        <v-icon
                           class="mr-2"
                           color="info"
                        >
                           mdi-content-save
                        </v-icon> 
                     </v-btn>
                  </v-card-actions>
                  </v-card-actions>
               </v-card>
            </v-dialog>
            <snackbar :snack="snack" />
            <dialogLoader :dialogLoad="dialogLoad"/>
        </v-main>
   </v-app>
</template>
<script>
   const pause = ms => new Promise(resolve => setTimeout(resolve, ms))
   export default {
      props:['data-user'],
      data() {
         return {
            menu: 3,
            submenu: 0,
            modulo: 'repolaboral',
            drawer:true,
            search:'',
            d_expediente:false,
            datos:[],
            active: null,
            editedIndex:-1,
            activeItem: {},
            headers: [
               { text: 'Ruta de Archivo', value: 'r_archivo', sortable: true, align: 'left'},
               { text: 'F. Modificación', value: 'f_modificacion_file', sortable: true, align: 'left'},
               { text: 'Tamaño', value: 'size', sortable: true, align: 'left'},
               { text: 'Archivo', filename: 'r_archivo', type:'archivo', ruta: 'storage/app/repositorio/laboral/', icon:'mdi-file-word', sortable: true, align: 'left'},
               { text: 'Expediente', value: 'expediente', sortable: true, align: 'left'},
               { text: 'Especialidad', value: 'c_especialidad', sortable: true, align: 'left'},
               { text: 'Materia', value: 'c_materia', sortable: true, align: 'left'},
               { text: 'Opciones', value:'edit', type:'opciones'}
               
            ],
            filters:{},
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            dialogLoad:{
                color:'',
                text:'',
                show:false,
            }
         }
      },
      computed:
      {
         formTitle ()
         {
            return 'Repositorio de Resoluciones';

         },
         
      },
      watch:
      {      
      },
      created ()
      {
         this.getResoluciones();
      },
      methods:
      {
         getResoluciones(){
            let url = 'repo_laboralApi?listar_actas=true'
            this.showLoad('primary','Cargando datos');

            this.data = [];
            axios.get(url)
                .then(response =>{
                    this.datos = response.data.resoluciones;
                    this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    this.showLoad('','', false);
                });

         },

         async getDatosExpediente () {
            if (this.datos.length > 0) {
               this.showLoad('primary','Cargando datos');
              
                  await pause(500);
                  //var urlConsulta = '/apisybase/azangaro/index.php/Api/datosExpedientebyXFMas';
                  var urlConsulta = '../consultaExpedienteMasivo';
                  axios.post(urlConsulta, {'resoluciones' : this.datos})
                  .then(response => {
                     if(response.data.status == 200){
                        this.datos = response.data.data;
                     }                         
                     this.showLoad('','', false);

                  })

            } else{
            }

         },

         registrarRepo(){
            let url = 'updateRepo'
            this.showLoad('primary','Cargando datos');

            this.data = [];
            axios.post(url, {resoluciones:this.datos})
                .then(response =>{
                    //this.datos = response.data.resoluciones;
                     if(response.data['statusBD']){
                        this.showSnack('success', response.data['messageDB'])

                     } else{
                        this.showSnack('error', response.data['messageDB'])
                     }
                     this.showLoad('','', false);
                     this.getResoluciones();
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){
                       this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos')}
                    this.showLoad('','', false);
                });
         },
         save(e){
         },
         updatedb(){
            
         },
         viewItem(item){         },
         editItem(item){   
            this.activeItem = item;
            this.d_expediente = true
            console.log(item)
         },
         deleteItem(item){},
         close_detalle(){
            this.d_resoluciones = false
         },
         cDrawer(val){
            if(val != undefined ){
                this.drawer = val;
            } else{
                this.drawer = !(this.drawer);
            }
         },
         showSnack(color, text, show = true)
         {
            this.snackColor = color
            this.snackText = text
            this.snack = show
         },
         showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
      }
   };
</script>