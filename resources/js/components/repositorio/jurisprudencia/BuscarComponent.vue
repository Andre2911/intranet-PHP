<template>
   <v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
        <v-main id="contentApp">
            <v-card>
               <v-toolbar color="grey" dark>
                  <v-toolbar-title>Buscador de Jurisprudencia :: Repositorio de Resoluciones</v-toolbar-title>
               </v-toolbar>
            </v-card>
            <v-container fluid grid-list-sm>
               <v-layout wrap>
                  <v-flex xs4>
                     <v-autocomplete
                        v-model="modo_busq"
                        :items="modos_busq"
                        label="Tipo de busqueda"
                        item-text="label"
                        item-value="id"
                        @change="limpiarRes()"
                     />
                  </v-flex>
                  <v-flex xs6>
                     <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Buscar"
                        :autofocus=true
                        single-line
                        hide-details
                        color="cyan"
                        @keydown.enter="buscarRes(1)"
                     ></v-text-field>     
                  </v-flex>
                  <v-flex xs2>
                        <v-btn block color="success" @click="buscarRes(1)">
                           <v-icon>mdi-magnify</v-icon>
                           Buscar
                        </v-btn>
                     </v-flex>
               </v-layout>

               <v-layout wrap>
                  <v-flex xs3>
                     <v-autocomplete
                        v-model="s_especialidad"
                        :items="filteredEsp"
                        label="Especialidad"
                        item-text="x_desc_especialidad"
                        item-value="c_especialidad"
                        multiple
                        outlined
                        dense
                        chips
                        small-chips
                        clearable
                     >
                        <template v-slot:selection="data">
                           <v-chip
                              outlined
                              :input-value="data.selected"
                              close
                              color="teal"
                              dark
                              small
                              class="chip--select-multi"
                              @click="data.select"
                              @click:close="remove(data.item)"
                           >
                              <v-icon>mdi-gavel</v-icon>
                              {{ data.item.c_especialidad }}
                           </v-chip>
                        </template>
                        <template v-slot:item="data">
                           <template v-if="(typeof data.item) !== 'object'">
                              <v-list-item-content v-text="data.item"></v-list-item-content>
                           </template>
                           <template v-else>
                              <v-list-item-avatar>
                                 <v-icon color="teal">mdi-gavel</v-icon>
                              </v-list-item-avatar>
                              <v-list-item-content>
                                 <v-list-item-title v-html="data.item.x_desc_especialidad"></v-list-item-title>
                              </v-list-item-content>
                           </template>
                        </template>
                        <template v-slot:no-data>
                           <v-list-item>
                              <v-list-item-title>
                                 Buscar y seleccionar Especialidad (Click aquí)
                              </v-list-item-title>
                           </v-list-item>
                        </template>
                     </v-autocomplete>
                  </v-flex>
                  <v-flex xs3>
                     <v-autocomplete
                        v-model="s_materia"
                        :items="filteredMat"
                        label="Materia"
                        item-text="x_desc_materia"
                        item-value="c_materia"
                        multiple
                        outlined
                        dense
                        chips
                        small-chips
                        clearable
                     >
                        <template v-slot:selection="data">
                           <v-chip
                              :input-value="data.selected"
                              close
                              color="teal"
                              dark
                              outlined
                              small
                              class="chip --select-multi"
                              @click="data.select"
                              @click:close="removeMateria(data.item)"
                           >
                              <v-icon>mdi-book</v-icon>
                              {{ data.item.c_materia }}
                           </v-chip>
                        </template>
                        <template v-slot:item="data">
                           <template v-if="(typeof data.item) !== 'object'">
                              <v-list-item-content v-text="data.item"></v-list-item-content>
                           </template>
                           <template v-else>
                              <v-list-item-avatar>
                                 <v-icon color="teal">mdi-book</v-icon>
                              </v-list-item-avatar>
                              <v-list-item-content>
                                 <v-list-item-title v-html="data.item.x_desc_materia"></v-list-item-title>
                              </v-list-item-content>
                           </template>
                        </template>
                        <template v-slot:no-data>
                           <v-list-item>
                              <v-list-item-title>
                                 Buscar y seleccionar Materia (Click aquí)
                              </v-list-item-title>
                           </v-list-item>
                        </template>
                     </v-autocomplete>
                  </v-flex>
                  <v-flex xs4>
                     <v-autocomplete
                        v-model="s_acto"
                        :items="filteredActo"
                        label="Acto Procesal"
                        item-text="x_desc_acto_procesal"
                        item-value="x_desc_acto_procesal"
                        multiple
                        outlined
                        dense
                        chips
                        small-chips
                        clearable
                     >
                        <template v-slot:selection="data">
                           <v-chip
                              :input-value="data.selected"
                              close
                              color="teal"
                              dark
                              outlined
                              small
                              class="chip --select-multi"
                              @click="data.select"
                              @click:close="removeActo(data.item)"
                           >
                              <v-icon>mdi-file-document-multiple</v-icon>
                              {{ data.item.x_desc_acto_procesal }}
                           </v-chip>
                        </template>
                        <template v-slot:item="data">
                           <template v-if="(typeof data.item) !== 'object'">
                              <v-list-item-content v-text="data.item"></v-list-item-content>
                           </template>
                           <template v-else>
                              <v-list-item-avatar>
                                 <v-icon color="teal">mdi-file-document-multiple</v-icon>
                              </v-list-item-avatar>
                              <v-list-item-content>
                                 <v-list-item-title v-html="data.item.x_desc_acto_procesal"></v-list-item-title>
                              </v-list-item-content>
                           </template>
                        </template>
                        <template v-slot:no-data>
                           <v-list-item>
                              <v-list-item-title>
                                 Buscar y seleccionar Acto Procesal (Click aquí)
                              </v-list-item-title>
                           </v-list-item>
                        </template>
                     </v-autocomplete>
                  </v-flex>
               </v-layout>
               <v-layout wrap v-if="showExterno">
                  <v-flex xs3>
                     <h4>Busquedas Externas:</h4>
                  </v-flex>
                  <v-flex xs2>
                     <a :href="'http://181.177.234.7//buscarRes/public/resolucionjur?filtro=S&search='+search+'&demandante=&demandado=&numexpediente=&anoingreso=&idtipoproceso=0&anopublica=&pg=1'" target="_blank">
                        <v-btn dark color="warning"> TC</v-btn>
                     </a>
                  </v-flex>

                  <v-flex xs2>
                     <a :href="'http://legis.pe/?s='+search" target="_blank">
                        <v-btn dark color="primary"> Legis.pe</v-btn>
                     </a>
                  </v-flex>

                  <v-flex xs2>
                     <form action="https://laley.pe/library" method="POST" target="_blank">
                        <input type="hidden" name="content" id="content" :value="search">
                        <input type="hidden" name="section" id="section" value="0">
                        <input type="hidden" name="startdate" id="startdate" value="24-12-2018">
                        <input type="hidden" name="enddate" id="enddate" value="24-12-2019">
                        <v-btn dark color="success" type="submit"> Laley.pe</v-btn>
                     </form>
                     
                  </v-flex>


               </v-layout>


               <v-card>
                     <v-alert
                        :value="true"
                        v-if="n_resultados > 0"
                        color="success"
                        icon="mdi-check-circle-outline"
                        outlined
                        dark
                     >
                     {{n_resultados}} resultados obtenidos
                     </v-alert>
                     <show-repositorio-buscar
                        :headers="headers"
                        :datos="datos"
                        :totalDatos="totalDatos"
                        :options="options"
                        :palabras="palabras"
                        :modo_busq="modo_busq"
                        :n_resultados="n_resultados"
                        :n_paginas="n_paginas"
                        @viewItem="viewItem"
                        @editItem="editItem"
                        @deleteItem="deleteItem"
                        @buscarRes="buscarRes"
                     />
               </v-card>
            </v-container>
            <v-snackbar v-model="snack" top="top" right="right" :timeout="3000" :color="snackColor">
               {{ snackText }}
               <v-btn text @click="snack = false">Close</v-btn>
            </v-snackbar>
            <v-dialog v-model="dialogLoad" persistent :width="290" transition="dialog-transition">
               <v-card dark>
                  <v-card-text>
                     Cargando datos...
                     <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
                  </v-card-text>
               </v-card>
            </v-dialog>
        </v-main>
   </v-app>
</template>
<script>
   export default {
      props:['data-user'],
      data() {
         return {
            menu: 0,
            submenu: -1,
            modulo: 'jurisprudencia',
            drawer:true,
            search:'',
            snack: false,
            snackText: '',
            snackColor: '',
            d_resoluciones:false,
            idresolucion: '',
            tipos:[],
            totalDatos:0,
            datos:[],
            active: null,
            isProgramer : false,
            plantilla:'',
            docPlantilla:'',
            dialogLoad:false,
            modo_busq:1,
            n_resultados:0,
            n_paginas:0,
            modos_busq:[
               { id:1, label:'Contengan solamente estas palabras'},
               { id:2, label:'Contengan alguna de estas palabras'},
               { id:3, label:'Contengan la frase completa'},
            ],
            options: {},
            headers: [

               {
                  text: 'Resolución',
                  value: 'resolucion',
                  sortable: true,
                  align: 'left'
               },
               {
                  text: 'Asunto',
                  value: 'asunto',
                  sortable: true,
                  align: 'left'
               },
               {
                  text: 'Fecha',
                  value: 'fecha',
                  sortable: true,
                  align: 'left'
               },
               {
                  text: 'Archivo',
                  value: 'filenamepdf',
                  sortable: true,
                  align: 'left'
               },
               {  
                  text: 'Opciones', 
                  value: 'visibility,edit,delete'
               },
            ],
            palabras:[],
            especialidad:[],
            s_especialidad:[],
            materia:[],
            s_materia:[],
            acto:[],
            s_acto:[],
            showExterno:false,
            filters_esp: {
               c_especialidad: [],
            },
            filters_mat: {
               c_materia: [],
            },
            filters_acto: {
               c_acto_procesal: [],
            },
            filters_full:[],
         }
      },
      computed:
      {
         filteredEsp() {
            return this.especialidad.filter(d => {
                return Object.keys(this.filters_esp).every(f => {
                    return this.filters_esp[f].length < 1 || this.filters_esp[f].includes(d[f])
                })
            })
         },
         filteredMat() {
            if(this.s_especialidad.length > 0){
               var primerfiltro = this.materia.filter(d => {
                    return Object.keys(this.filters_mat).every(f => {
                        return this.filters_mat[f].length < 1 || this.filters_mat[f].includes(d[f])
                    })
                })
                return primerfiltro.filter(p => {
                     var is_in = false;
                     for(var i = 0; i < this.s_especialidad.length; i++){
                        /*** Especialidad - Materia */
                        var filtroEM = this.filters_full.filter(ff => ff.c_especialidad == this.s_especialidad[i] && ff.c_materia == p.c_materia);

                        if(filtroEM.length>0){
                            is_in = true;
                        }
                     }
                     return is_in;
                }) 

            }else{
                return this.materia.filter(d => {
                    return Object.keys(this.filters_mat).every(f => {
                        return this.filters_mat[f].length < 1 || this.filters_mat[f].includes(d[f])
                    })
                })
            }
         },
         filteredActo() {
           var filtroAM = [];
            if(this.s_materia.length > 0 || this.s_especialidad.length > 0){
               var primerfiltro = this.acto.filter(d => {
                  return Object.keys(this.filters_acto).every(f => {
                     return this.filters_acto[f].length < 1 || this.filters_acto[f].includes(d[f])
                  }) 
               })
               console.log(primerfiltro);
               return primerfiltro.filter(p => {
                  var is_in = false;
                  
                  if(this.s_materia.length > 0 && this.s_especialidad.length == 0){
                     for(var i = 0; i < this.s_materia.length; i++){
                           filtroAM = this.filters_full.filter(ff => ff.c_materia == this.s_materia[i] && ff.c_acto_procesal == p.c_acto_procesal);

                           if(filtroAM.length>0){
                                 is_in = true;
                           }
                     }
                  }
                  else if(this.s_materia.length == 0 && this.s_especialidad.length > 0){
                     for(var i = 0; i < this.s_especialidad.length; i++){
                           filtroAM = this.filters_full.filter(ff => ff.c_especialidad == this.s_especialidad[i] && ff.c_acto_procesal == p.c_acto_procesal);

                           if(filtroAM.length>0){
                                 is_in = true;
                           }
                     }
                  } else{
                     for(var i = 0; i < this.s_especialidad.length; i++){
                        for(var j = 0; j < this.s_materia.length; j++){
                           filtroAM = this.filters_full.filter(ff => ff.c_especialidad == this.s_especialidad[i] && ff.c_materia == this.s_materia[j] && ff.c_acto_procesal == p.c_acto_procesal);
                           if(filtroAM.length>0){
                              is_in = true;
                           }
                        }
                     }
                  }
                  
                  return is_in;
                }) 
            }
            else{
                return this.acto.filter(d => {
                    return Object.keys(this.filters_acto).every(f => {
                        return this.filters_acto[f].length < 1 || this.filters_acto[f].includes(d[f])
                    })
                })
            }
        }
      },
      watch:
      { 
         search(val){
            this.showExterno = false
         },

         options: {
            handler () {
               this.getDataFromApi()
               .then(data => {
                  this.datos = data.items
                  this.totalDatos = data.total
               })
            },
            deep: true,
         }, 
         s_especialidad: {
            handler () {
               this.s_materia= []
               this.s_acto= []
            },
            deep: true,
         }, 
         s_materia: {
            handler () {
               this.s_acto= []
            },
            deep: true,
         }, 
      },
      created ()
      {
         this.getData();
      },
      methods:
      {
         getData()
         {
            let urlPlantillas='repositorioApi?init=true';
            axios.get(urlPlantillas)
            .then(datos =>
            {
               this.especialidad = datos.data.especialidad;
               this.materia = datos.data.materia;
               this.acto = datos.data.acto;
            })
            .catch(  errors  =>  { console.log(errors);  }  );
         },
         viewItem(item){
            this.idresolucion = item.id_ingreso;
            this.d_resoluciones = true;

         },
         editItem(item){},
         deleteItem(item){},
         close_detalle(){
            this.d_resoluciones = false
         },
         showSnack(color, text, show = true)
         {
            this.snackColor = color
            this.snackText = text
            this.snack = show
         },
         buscarRes(pag = 1){ 
            this.dialogLoad = true;
            this.showExterno = true;
            this.filters_full = []
            if (this.search == '') {
               this.dialogLoad = false;
               this.showSnack('warning', 'Ingrese las palabras a buscar')
               return false;
            }
            let urlResolucion = '';
            if (this.s_especialidad != '' || this.s_materia != '' || this.s_acto != '') {
               urlResolucion='repositorioApi';

               var params = { modo: this.modo_busq,
                               search:true,
                               buscar: this.search,
                               pag: pag,
                               esp: this.s_especialidad,
                               mat: this.s_materia,
                               acto: this.s_acto
                              }

               axios.post(urlResolucion, params)
                     .then(datos =>
                     {
                        this.dialogLoad = false;
                        this.datos = datos.data.resoluciones;
                        this.palabras = datos.data.palabras;
                        this.n_resultados = datos.data.n_resultados[0]['n_resultados'];
                        for(var i = 0; i < datos.data.filtros.length; i++){
                            this.filters_esp.c_especialidad.push(datos.data.filtros[i]['c_especialidad'])
                            this.filters_mat.c_materia.push(datos.data.filtros[i]['c_materia'])
                            this.filters_full.push({'c_materia':datos.data.filtros[i]['c_materia'], 'c_especialidad': datos.data.filtros[i]['c_especialidad'], 'c_acto_procesal': datos.data.filtros[i]['c_acto_procesal']})
                            if(!this.filters_acto.c_acto_procesal.includes(datos.data.filtros[i]['c_acto_procesal'])){
                                this.filters_acto.c_acto_procesal.push(datos.data.filtros[i]['c_acto_procesal'])
                            }
                        }


                        if (this.n_resultados == 0) {
                           //this.datos = [];
                        }
                        this.n_paginas = Math.ceil(this.n_resultados/10);
                     })
                     .catch(  errors  =>  { console.log(errors);  }  );
            } else {
               urlResolucion='repositorioApi?modo='+this.modo_busq+'&search=true&buscar='+this.search+'&pag='+pag;
               axios.get(urlResolucion)
                     .then(datos =>
                     {
                        this.dialogLoad = false;
                        this.datos = datos.data.resoluciones;
                        this.palabras = datos.data.palabras;
                        this.n_resultados = datos.data.n_resultados[0]['n_resultados'];
                        this.n_paginas = Math.ceil(this.n_resultados/10);

                        for(var i = 0; i < datos.data.filtros.length; i++){
                            this.filters_esp.c_especialidad.push(datos.data.filtros[i]['c_especialidad'])
                            this.filters_mat.c_materia.push(datos.data.filtros[i]['c_materia'])
                           this.filters_full.push({'c_materia':datos.data.filtros[i]['c_materia'], 'c_especialidad': datos.data.filtros[i]['c_especialidad'], 'c_acto_procesal': datos.data.filtros[i]['c_acto_procesal']})

                            if(!this.filters_acto.c_acto_procesal.includes(datos.data.filtros[i]['c_acto_procesal'])){
                                this.filters_acto.c_acto_procesal.push(datos.data.filtros[i]['c_acto_procesal'])
                            }
                        }

                        
                     })
                     .catch(  errors  =>  { console.log(errors);  }  );
            }
            
         },
         limpiarRes(){
            this.datos = [];
            this.n_paginas = 0;
            this.n_resultados = 0;
         },
         remove (item) {
            const index = this.s_especialidad.indexOf(item.c_especialidad)
            if (index >= 0) this.s_especialidad.splice(index, 1)
         },
         removeMateria(item){
            const index = this.s_materia.indexOf(item.c_materia)
            if (index >= 0) this.s_materia.splice(index, 1) 
         },
         removeActo(item){
            const index = this.s_acto.indexOf(item.x_desc_acto_procesal)
            if (index >= 0) this.s_acto.splice(index, 1) 
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
      }
   };
</script>