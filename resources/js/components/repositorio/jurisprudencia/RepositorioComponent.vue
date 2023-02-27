<template>
   <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
        <v-main id="contentApp">
            <v-card>
               <v-toolbar color="grey" dark>
                  <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                  <v-spacer/>
                  <v-btn  :disabled="c_instancia == null || proceso == 0" @click="getResoluciones()" dense>
                     <v-icon>mdi-refresh</v-icon>
                     Resetear Proceso
                  </v-btn>
               </v-toolbar>
               <v-card-text>
                  <v-row class="mb-2">
                        <v-col cols="12" md="2">
                            <v-autocomplete
                              v-model="res_anio"
                              :items="anios"
                              label="Año"
                              dense
                           />
                        </v-col>
                        <v-col cols="12" md="2">
                           <v-autocomplete
                              v-model="res_mes"
                              :items="meses"
                              label="Mes"
                              item-value="value"
                              item-text="label"
                              dense
                           />
                        </v-col>
                        <v-col cols="12" md="2">
                            <v-autocomplete
                                v-model="x_nom_provincia"
                                label="PROVlNCIA"
                                :items="filtroProvincia"
                                item-text="x_nom_provincia"
                                item-value="x_nom_provincia"
                                dense
                            />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-autocomplete
                                v-model="c_sede"
                                label="Sede Judicial"
                                :items="filtroSede"
                                item-text="x_desc_sede"
                                item-value="c_sede"
                                :disabled="x_nom_provincia == null"
                                dense
                            />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-autocomplete
                                v-model="c_org_jurisd"
                                label="Organo Jurisdiccional"
                                :items="filtroOrgJur"
                                item-text="x_nom_org_jurisd"
                                item-value="c_org_jurisd"
                                :disabled="c_sede == null"
                                dense
                                />
                        </v-col>
                        <v-col cols="12" md="3" class="pt-0 pb-0">
                            <v-autocomplete
                                v-model="c_especialidad"
                                label="Especialidad"    
                                :items="filtroEspecialidad"
                                item-text="x_desc_especialidad"
                                item-value="c_especialidad"
                                :disabled="c_org_jurisd == null"
                                multiple
                                chips
                                dense
                            >
                              <template v-slot:append-outer>
                                 <v-slide-x-reverse-transition
                                       mode="out-in"
                                 >
                                       <v-icon
                                          @click="selectAllEspecialidad"
                                       >mdi-spellcheck</v-icon>
                                 </v-slide-x-reverse-transition>
                              </template>
                            </v-autocomplete>
                        </v-col>
                        <v-col cols="12" md="6" class="pt-0 pb-0">
                            <v-autocomplete
                                v-model="c_instancia"
                                label="Instancia"    
                                :items="filtroInstancia"
                                item-text="x_nom_instancia"
                                item-value="c_instancia"                        
                                :disabled="c_especialidad == null"
                                multiple
                                chips
                                dense
                            >
                            <template v-slot:append-outer>
                                 <v-slide-x-reverse-transition
                                       mode="out-in"
                                 >
                                       <v-icon
                                          @click="selectAllInstancia"
                                       >mdi-spellcheck</v-icon>
                                 </v-slide-x-reverse-transition>
                              </template>
                            </v-autocomplete>
                        </v-col>
                        <v-col cols="12" md="2" class="pt-0 pb-0">
                           
                        </v-col>
                        <v-col cols="12" md="12" class="row">
                           <v-col cols="12" md="3" class="pt-0 pb-0">
                              <v-btn block :disabled="c_instancia == null || proceso != 0" @click="getResoluciones()" dense>
                                 <v-icon>mdi-magnify</v-icon>
                                 Buscar resoluciones
                              </v-btn>
                           </v-col>
                           <v-col cols="12" md="3" class="pt-0 pb-0">
                              <v-btn block color="cyan darken-3" @click="getNew()" dark :disabled="proceso != 1">
                                 <v-icon>mdi-content-save</v-icon>
                                 Filtrar Nuevos
                              </v-btn>
                           </v-col>
                           <v-col cols="12" md="3" class="pt-0 pb-0">
                              <v-btn block color="cyan darken-3" @click="getFiles()" dark :disabled="proceso != 2">
                                 <v-icon>mdi-content-save</v-icon>
                                 Copiar Resoluciones al Server
                              </v-btn>
                           </v-col>
                           <v-col cols="12" md="3" class="pt-0 pb-0">
                              <v-btn block color="cyan darken-3" @click="updatedb()" dark :disabled="proceso != 3">
                                 <v-icon>mdi-content-save</v-icon>
                                 Indexar Resoluciones a la BD
                              </v-btn>
                           </v-col>
                        </v-col>
                    </v-row>
                     <show-repositorio-component
                        :headers="headers"
                        :datos="filteredData"
                        :allData="datos"
                        :filters="filters"
                        :pagination="pagination"
                        @viewItem="viewItem"
                        @editItem="editItem"
                        @deleteItem="deleteItem"
                     />
               </v-card-text>
            </v-card>
                       
            <snackbar :snack="snack" />
            <dialogLoader :dialogLoad="dialogLoad"/>
            <v-dialog
               v-model="progreso"
               persistent :width="290" transition="dialog-transition"
            >
               <v-card dark>
                  <v-card-text>
                     Progreso:
                     <v-progress-linear v-model="v_progreso"
                     color="error"
                     height="20"
                     >{{ v_progreso }}</v-progress-linear>
                  </v-card-text>
               </v-card>
               
            </v-dialog>
        </v-main>
   </v-app>
</template>
<script>
   const pause = ms => new Promise(resolve => setTimeout(resolve, ms))
   export default {
      props:['data-user'],
      data() {
         return {
            menu: 1,
            submenu: 0,
            modulo: 'jurisprudencia',
            drawer:true,
            listaPlantillas:[],
            search:'',
            d_newRegistro:false,
            d_resoluciones:false,
            idresolucion: '',
            tipos:[],
            datos:[],
            resolucion:{},
            resDefault:{},
            active: null,
            isProgramer : false,
            editedIndex:-1,
            pagination: {
               rowsPerPage: 20,
            },
            headers: [
               { text: 'Org. Jurisdiccional', value: 'DEN_ORG_JURISDICCIONAL', sortable: true, align: 'left'},
               { text: 'Juzgado', value: 'DEN_INSTANCIA_SIJ', sortable: true, align: 'left'},
               { text: 'Expediente', value: 'NUM_EXPEDIENTE', sortable: true, align: 'left'},
               { text: 'Incidente', value: 'NUM_INC_EXPEDIENTE', sortable: true, align: 'left'},
               { text: 'Acto Procesal', value: 'DEN_TIPO_ACTO_PROCESAL', sortable: true, align: 'left'},
               { text: 'N° Resolución', value: 'DEN_RESOL_PROYECTO', sortable: true, align: 'left'},
               { text: 'Ruta', value: 'RUTA_FTP_ARCHIVO_RESOLUCION', sortable: true, align: 'left'},
               { text: 'WORD', value: 'NOM_ARCHIVO_DOC_RESOLUCION', sortable: true, align: 'left'},
               { text: 'PDF', value: 'NOM_ARCHIVO_PDF_RESOLUCION', sortable: true, align: 'left'},
               
            ],
            filters:{
                DEN_TIPO_ACTO_PROCESAL: [],
            },
            res_anio:'',
            res_mes:'',
            anios:[],
            meses:[],
            instancias:[],
            x_nom_provincia: null,
            c_sede:null,
            c_org_jurisd:null,
            c_especialidad:null,
            c_instancia:null,
            progreso: false,
            v_progreso: 0,
            n_files: 0,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            dialogLoad:{
                color:'',
                text:'',
                show:false,
            },
            proceso:0,
            n_file:0,
            ruta_ftp:'',
         }
      },
      computed:
      {
         filteredData() {
            if(this.datos[0] != undefined){
                return this.datos.filter(d => {
                    return Object.keys(this.filters).every(f => {
                        return this.filters[f].length < 1 || this.filters[f].includes(d[f])
                    })
                })
            } else{
                return [];
            }
            
         },
         formTitle ()
         {
            return 'Repositorio de Resoluciones';

         },
         filtroProvincia(){
            if(this.instancias[0] != undefined){
                let provincias = this.instancias;
                if(provincias.length > 0){
                    return provincias.filter(d => d.x_nom_provincia);
                } else{
                    return [];
                }
            } else{
                return [];
            }
         },
         filtroSede(){
            if(this.instancias[0] != undefined){
               let sede = this.instancias;
               return sede.filter(d => d.x_nom_provincia == this.x_nom_provincia);
            }
         },
         filtroOrgJur(){
            if(this.instancias[0] != undefined){
               let orgJur = this.instancias;
               return orgJur.filter(d => d.x_nom_provincia == this.x_nom_provincia  && d.c_sede == this.c_sede);
            }
         },
         filtroEspecialidad(){
               let especialidad = this.instancias;
               return especialidad.filter(d => d.x_nom_provincia == this.x_nom_provincia  && d.c_sede == this.c_sede && d.c_org_jurisd == this.c_org_jurisd);
         },
         filtroInstancia(){
               let instancias = this.instancias;
               let especialidades = this.c_especialidad;

               let res = [];
               let instanciasf1 = [];

               if(this.c_especialidad != null){
                  instanciasf1 = instancias.filter(d => 
                           d.x_nom_provincia == this.x_nom_provincia  && 
                           d.c_sede == this.c_sede && 
                           d.c_org_jurisd == this.c_org_jurisd);

                  res = instanciasf1.filter(el => {
                     return especialidades.find(element => {
                           return element === el.c_especialidad;
                     });
                  });
               }

               return res;
         },
         c_provincia(){
            if(this.c_instancia != null){
                let instanciasf1 = []
                let instanciasSelected = this.c_instancia;

                instanciasf1 = this.filtroInstancia.filter(el => {
                    return instanciasSelected.find(element => {
                        return element === el.c_instancia;
                    });
                });


                if(instanciasf1.length == 1){
                    return instanciasf1[0]['c_provincia'];
                } else if(instanciasf1.length > 1){
                    return instanciasf1[0]['c_provincia'];
                } else{
                    return null;
                }
            } else{
                return null;
            }
         }
      },
      watch:
      {      
         res_anio(val){
            this.res_mes = '';
            /*if (this.res_mes != '') {
               this.getData(val, this.res_mes);
            }*/
            this.proceso = 0;
            this.datos = [];
            this.n_files = 0
            
         },
         res_mes(val){
            if (this.res_anio != '' && this.res_mes != '') {
               //this.getData(this.res_anio, val);   
               this.proceso = 0;
               this.datos = [];
               this.n_files = 0
            }
         },
         n_file(val){
            if(val == this.n_files){
               this.showLoad('','',false)
               this.showSnack('success', 'Los archivos han sido copiados al servidor')
               this.n_file = 0;
            }
         },
         x_nom_provincia(val){
            this.c_instancia = null
        },
        c_sede(val){
            this.c_instancia = null
        },
        c_org_jurisd(val){
            this.c_instancia = null
        },
        c_especialidad(val){
            this.c_instancia = null
        }
      },
      created ()
      {
         this.getConfig();
         this.getAnios();
         this.getInstancias();
      },
      methods:
      {
         getConfig(){
            this.showLoad('grey', 'Cargando datos');
            axios.get('config?ruta=true')
                .then(response =>{
                     this.ruta_ftp = response.data.ruta.ruta;
                     this.showLoad('','', false);
                })
                .catch(errors =>{
                     console.log(errors);
                     if (errors.response.status === 401) {
                           window.location = "../login";
                     }
                     this.showLoad('','', false);
                });
         },
         getInstancias(){
            this.showLoad('grey', 'Cargando datos');
            let url = '../consultasSIJ/listarInstancias';
            axios.get(url)
                .then(response =>{
                    this.instancias = response.data.data;
                    this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    this.instancias = []
                    this.showLoad('','', false);

                });
         },
         getResoluciones(){
            this.showLoad('grey', 'Realizando consulta');
            this.proceso = 0;
            this.datos = [];
            this.n_files = 0
            let url = '../consultasSIJ/listarResoluciones?c_provincia=' + this.c_provincia + 
                                                                                    '&c_sede=' + this.c_sede + 
                                                                                    '&c_org_jurisd=' + this.c_org_jurisd + 
                                                                                    '&c_especialidad=' + this.c_especialidad +
                                                                                    '&c_instancia=' + this.c_instancia + 
                                                                                    '&f_ini=' + this.getFirstDay(this.res_anio, this.res_mes) + 
                                                                                    '&f_fin=' + this.getLastDay(this.res_anio, this.res_mes);

            //this.showLoad('grey darken-2', 'Realizando consulta', true);
            this.filters.DEN_TIPO_ACTO_PROCESAL = [];
            this.data = [];
            axios.get(url)
                .then(response =>{
                  if(response.data.status == 200){
                     if(response.data.data.length > 0){
                        this.datos = response.data.data;
                        this.proceso = 1;
                     } else{
                        this.showSnack('warning', 'No se obtuvo datos para los parámetros seleccionados')
                     }
                  } else{
                        this.showSnack('error', 'Hubo un error al hacer la consulta al SIJ')
                  }
                  this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    this.showLoad('','', false);

                    if (errors.response.status === 401) {
                                window.location = "../login";
                        } else if(errors.response.data.error == 'Unauthenticated'){
                            window.location = "../login";
                        }
                });

         },
         getNew(){
            this.showLoad('grey', 'Consultando datos');

            axios.post('filterDB', {data: this.filteredData, anio:this.res_anio, mes:this.res_mes})
                .then(response =>{
                     if(response.data.length > 0){
                        this.datos = response.data;
                        this.showLoad('','', false);
                        this.proceso = 2;
                        for (let i = 0; i < this.datos.length; i++) {
                           if(this.datos[i]['NOM_ARCHIVO_PDF_RESOLUCION'] != null){
                              this.n_files++
                           }
                           if(this.datos[i]['NOM_ARCHIVO_DOC_RESOLUCION']){
                              this.n_files++
                           }
                           
                        }
                     } else{
                        this.proceso = 0;
                        this.datos = [];
                        this.n_files = 0
                        this.showLoad('','', false);

                     }
                     
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    this.showLoad('','', false);
                });
         },
         getFiles(){
            this.showLoad('primary', 'Copiando archivos 0/'+this.n_files);

            for (let i = 0; i < this.datos.length; i++) {
               if(this.datos[i]['NOM_ARCHIVO_PDF_RESOLUCION'] != null){
                  this.fetchFiles(this.datos[i]['RUTA_FTP_ARCHIVO_RESOLUCION'], this.datos[i]['NOM_ARCHIVO_PDF_RESOLUCION'], this.datos[i]['USUARIO_FTP_ARCHIVO_RESOLUC'], this.datos[i]['CLAVE_FTP_ARCHIVO_RESOLUCION'], this.datos[i]['IP_FTP_ARCHIVO_RESOLUCION']);
               }
               if(this.datos[i]['NOM_ARCHIVO_DOC_RESOLUCION']){
                  this.fetchFiles(this.datos[i]['RUTA_FTP_ARCHIVO_RESOLUCION'], this.datos[i]['NOM_ARCHIVO_DOC_RESOLUCION'], this.datos[i]['USUARIO_FTP_ARCHIVO_RESOLUC'], this.datos[i]['CLAVE_FTP_ARCHIVO_RESOLUCION'], this.datos[i]['IP_FTP_ARCHIVO_RESOLUCION']);
               }
               
            }
            this.proceso = 3;
         },
         async fetchFiles(ruta, filename, ftpUsername, ftpPass, ftpIP){
            if (ruta != '') {
              
               await pause(500);
               var urlConsulta = '../consultasSIJ/downloadFileFTP/?ruta='+ruta+'&filename='+filename+'&destino='+this.ruta_ftp+'&ftpUsername='+ftpUsername+'&ftpPassword='+ftpPass+'&ftpIP='+ftpIP;
               axios.get(urlConsulta)
                  .then(response => {
                     //this.datos = response.data;
                     if(response.data.data.Status == 1){
                        console.log(response.data.data)
                        this.n_file += 1
                        this.dialogLoad.text = 'Copiando archivos '+this.n_file+'/'+this.n_files
                     }else{

                     }
                  });

            } else{
               this.progreso = false;
            }
         },
         getAnios(){
            var d = new Date();
            var n = d.getFullYear();
            for (var i = n; i >= 2009; i--) {
               this.anios.push(i);
            }

            var mesesdesc = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre']
            for (var i = 1; i <= 12; i++) {
               this.meses.push({value:i, label:mesesdesc[i-1]});
            }

         },
         getFirstDay(anio, mes){
            var mes = mes-1;
            var date = new Date(anio, mes + 1, 0);
            var str_date = date.toLocaleDateString()
            const [day, month, year] = str_date.split('/')
            return `${year}-${month.padStart(2, '0')}-01`
         },
         getLastDay(anio, mes){
            var mes = mes-1;
            var date = new Date(anio, mes + 1, 0);
            var str_date = date.toLocaleDateString()
            const [day, month, year] = str_date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`

         },
         updatedb(){
            this.showLoad('grey', 'Grabando datos');
            
            axios.post('repositorioApi?updatedb=true', {'resoluciones' : this.datos})
               .then((response) => {
                  if (response.data['statusBD']) {
                     this.showSnack('success', response.data['messageDB'])
                     this.proceso = 0;
                     this.datos = [];
                     this.n_files = 0
                  } else{
                     this.showSnack('error', response.data['messageDB'])
                  }
                  this.showLoad('','', false);

              });
         },
         selectAllEspecialidad(){
            var especialidades = [];
            if(this.filtroEspecialidad[0] != undefined){
                especialidades = this.filtroEspecialidad.reduce(function (allEspecialidades, especialidad) {
                    let indexOfAud = allEspecialidades.map(function(e) { return e; }).indexOf(especialidad.c_especialidad);
                    if (indexOfAud == -1) {
                        allEspecialidades.push(especialidad.c_especialidad);
                    }
                    return allEspecialidades
                }, [])
            }   

            this.c_especialidad = [...especialidades]
         },
         selectAllInstancia(){
            var instancias = [];
            if(this.filtroInstancia[0] != undefined){
                instancias = this.filtroInstancia.reduce(function (allInstancias, instancia) {
                    let indexOfAud = allInstancias.map(function(e) { return e; }).indexOf(instancia.c_instancia);
                    if (indexOfAud == -1) {
                        allInstancias.push(instancia.c_instancia);
                    }
                    return allInstancias
                }, [])
            }   

            this.c_instancia = [...instancias]
         },
         viewItem(item){         },
         editItem(item){         },
         deleteItem(item){},
         cDrawer(val){
            if(val != undefined ){
                this.drawer = val;
            } else{
                this.drawer = !(this.drawer);
            }
         },
         showSnack(color, text, show = true)
         {
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
         },
         showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
      }
   };
</script>