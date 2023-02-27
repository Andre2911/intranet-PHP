<template>
	<v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
      <v-main id="contentApp">  
         <v-card>
            <v-toolbar color="grey" dark>
               <v-toolbar-title>Vacaciones</v-toolbar-title>
            </v-toolbar>
            <v-card-text>
               <v-layout wrap>
                  <v-flex xs6 sm2 md2>
                     <v-btn color="primary" @click="d_vacaciones = true">	
                        <v-icon>mdi-pencil</v-icon>
                        Registrar vacaciones
                     </v-btn>
                  </v-flex>
                  <v-spacer/>
                  <v-flex xs6 sm1 md1>
                     <v-text-field
                        v-model="anio_vac"
                        label="Año vacacional"
                        type="number"
                     >
                     </v-text-field>
                  </v-flex>
                  <v-flex xs6 sm2 md2>
                     <v-btn color="info" @click="getVacaciones()">	
                        <v-icon>mdi-list</v-icon>
                        Listar Vacaciones
                     </v-btn>
                  </v-flex>
               </v-layout>
               <dataTable
                        :headers="headers"
                        :filters="filters"
                        :data="datos"
                        :tipo="1"
                        :pagination="pagination"
                        @viewItem="viewItem"
                        @editItem="editItem"
                        @deleteItem="deleteItem"
                     />
            </v-card-text>
         </v-card>
         <v-dialog
            v-model="d_vacaciones"
            scrollable 
            :overlay="true"
            max-width="1200px"
            transition="dialog-transition"
         >
            <v-card >
                  <v-toolbar color="red darken-4" dark>
                     <v-toolbar-title>Registrar Vacaciones</v-toolbar-title>
                  </v-toolbar>
                  <v-card-text>
                     <v-container grid-list-md>

                        <v-layout wrap>
                           <v-flex xs12 sm2 md2>
                              <v-text-field
                                 v-model="anio_vac"
                                 label="Año vacacional"
                                 type="number"
                              >
                              </v-text-field>
                           </v-flex>
                           <v-flex xs12 sm10 md10>
                              <v-text-field
                                 v-model="documento_r"
                                 label="N° Documento"
                                 required
                                 :rules="[rules.required]"
                                 color="deep-purple"
                              ></v-text-field>
                           </v-flex>

                           <v-flex xs12 sm5 md5>
                              <v-autocomplete
                                 v-model="personal_id"
                                 :items="personal"
                                 label="Personal"
                                 item-text="nombrecompleto"
                                 item-value="id"
                                 ref="personal_id"
                                 clearable
                              />
                           </v-flex>
                           <v-flex xs12 sm3 md2>
                              <v-menu
                                 v-model="datetime_ini"
                                 :close-on-content-click="false"
                                 :nudge-right="40"
                                 transition="scale-transition"
                                 offset-y
                                 min-width="290px"
                              >
                                 <template v-slot:activator="{ on }">
                                       <v-text-field
                                       v-model="f_inicio"
                                       label="Fecha de Inicio"
                                       prepend-icon="mdi-calendar"
                                       readonly
                                       v-on="on"
                                       ></v-text-field>
                                 </template>
                                 <v-date-picker 
                                       v-model="date_inicio"
                                       locale="es-419"
                                       @input="datetime_ini = false"
                                 ></v-date-picker>
                              </v-menu>
                           </v-flex>
                           <v-flex xs12 sm3 md2>
                              <v-menu
                                 v-model="datetime_final"
                                 :close-on-content-click="false"
                                 :nudge-right="40"
                                 transition="scale-transition"
                                 offset-y
                                 min-width="290px"
                              >
                                 <template v-slot:activator="{ on }">
                                       <v-text-field
                                       v-model="f_final"
                                       label="Fecha final"
                                       prepend-icon="mdi-calendar"
                                       readonly
                                       v-on="on"
                                       ></v-text-field>
                                 </template>
                                 <v-date-picker 
                                       v-model="date_final"
                                       locale="es-419"
                                       @input="datetime_final = false"
                                 ></v-date-picker>
                              </v-menu>
                           </v-flex>
                           <v-flex xs12 sm1 md1>
                              <h4>{{ n_dias }} días</h4> 
                           </v-flex>
                           <v-flex xs12 sm2 md2>
                              <v-btn block color="primary" @click="agregarPersonal()"><v-icon>mdi-plus-circle</v-icon> Agregar</v-btn> 
                           </v-flex>
                           <v-flex xs12 sm12>
                              <v-text-field
                                 v-model="search_d"
                                 label="Buscar"
                                 append-outer-icon="mdi-magnify"
                              />
                              <v-data-table
                                 :headers="headers2"
                                 :items="personal_list"
                                 class="elevation-1"
                                 :search="search_d"
                              >
                                 <template v-slot:body="{ items, headers }">
                                    <template v-if="items.length==0">
                                       <tbody>
                                          <tr>
                                             <td colspan="5">
                                                Agregue la(s) persona(s) fecha de inicio y fin de las vacaciones y luego click en el boton + AGREGAR
                                             </td>
                                          </tr>
                                       </tbody>
                                    </template>
                                    <template v-else>
                                       <tbody>
                                          <tr v-for="item in items" :key="item.id">
                                             <td v-for="header in headers" v-bind:key="header.text">
                                                <template v-if="header.value == 'opciones'">
                                                   <v-icon class="mr-2" color="red" @click="removeItem(item)">mdi-delete</v-icon>
                                                </template>
                                                <template v-else>
                                                   {{item[header.value]}}
                                                </template>
                                             </td>
                                          </tr>
                                       </tbody>

                                    </template>
                                 </template>
                              </v-data-table>
                           </v-flex>
                           <v-flex xs12 sm12>
                              <v-divider/>
                           </v-flex>
                           <v-flex xs12 sm12>
                              <v-btn block color="primary" :dark="!documento_r == ''&& !personal_list.length == 0" :disabled="documento_r == '' || personal_list.length == 0" @click="RegistrarVacaciones()">Registrar Vacaciones</v-btn>
                           </v-flex>
                        </v-layout>
                     </v-container>
                  </v-card-text>
            </v-card>
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
            submenu: 1,
            modulo: 'personal',
            drawer:true,
            loading: true,
            pagination: {
               rowsPerPage: 20,
            },
            rules: {
               required: v => !!v || 'Descripcion Requerido',
            },
            d_vacaciones:false,
            datos:[],
            search:'',
            search_d:'',
            snack: false,
            snackText: '',
            snackColor: '',
            headers:[
               {text: 'id', value: 'id'},
               {text: 'DNI', value: 'numero_documento'},
               {text: 'Apellidos y nombres', value: 'nombrecompleto'},
               {text: 'Régimen', value: 'regimen_base'},
               {text: 'Plaza Titular', value: 'nombre_plaza'},
               {text: 'Dependencia', value: 'dependencia'},
               {text: 'F. Inicio', value: 'f_inicio'},
               {text: 'F. Fin', value: 'f_fin'},
               {text: 'Días Vac.', value: 'DateDiff'},
               {text: 'Opciones', value: 'delete',  type:'opciones'},

            ],
            headers2:[
               {text: 'DNI', value: 'dni'},
               {text: 'Apellidos y nombres', value: 'nombrecompleto'},
               {text: 'Fecha de Inicio', value: 'f_inicio'},
               {text: 'Fecha de Fin', value: 'f_final'},
               {text: 'Días', value: 'n_dias'},
               {text: 'Opciones', value: 'delete'},
            ],
            anio_vac: new Date().getFullYear(),
            anio_vac_r: new Date().getFullYear(),
            documento_r: '',
            personal:[],
            personal_list:[],
            personal_id:'',
            datetime_ini: false,
            f_inicio: this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10))),
            date_inicio: this.parseDate(new Date().toLocaleDateString()),
            datetime_final: false,
            f_final: this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10))),
            date_final: this.parseDate(new Date().toLocaleDateString()),
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
            filters:[]
         }
      },
      computed:{
         n_dias(){
            if(this.f_inicio != undefined){
               return this.calcularDias(this.parseDate(this.f_inicio), this.parseDate(this.f_final))*1+1
            } else{
               return 0;
            }
         }, 
      },
      watch:{
         date_inicio (val) {
            this.f_inicio = this.formatDate(this.date_inicio)
         },
         date_final (val) {
            this.f_final = this.formatDate(this.date_final)
         },
      },
      created() {
         this.getDataIni();
         this.getVacaciones();
      },
      methods:{
         getDataIni(){
            axios.get('licenciasApi?list_personal=true')
      				.then(response =>{
                     this.personal = response.data['personal'];                     
                     this.loading = false;
                     this.dialogLoad = false;
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },
      	getVacaciones(){
      		axios.get('licenciasApi?list_vacaciones=true&anio=' + this.anio_vac)
      				.then(response =>{
                     this.datos = response.data['personal'];                     
                     this.loading = false;
                     this.dialogLoad = false;

                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
      	},
         agregarPersonal(){
				if(this.personal_id != '' && this.f_inicio != null && this.f_final != null){

               if(this.n_dias > 0){
                  let duplicado = this.personal_list.map(function(e) { return e.persona_id; }).indexOf(this.personal_id);
                  if(duplicado != -1){
                     this.showSnack('warning', 'Precaución: el personal ya fue ingresado anteriormente')
                  } 

                  let personalSelected = this.personal.map(function(e) { return e.id; }).indexOf(this.personal_id);

                  let duplicado2 =  this.personal_list.map(function(e) { return e.id; }).indexOf(this.personal[personalSelected]['numero_documento']+this.f_inicio);

                  if(duplicado2 == -1){
                     var item = {
                        id             :this.personal[personalSelected]['numero_documento']+this.f_inicio,
                        persona_id     :this.personal[personalSelected]['id'],
                        dni            :this.personal[personalSelected]['numero_documento'],
                        nombrecompleto :this.personal[personalSelected]['nombrecompleto'],
                        plaza_id       :this.personal[personalSelected]['plaza_tit_id'],
                        oficina_id     :this.personal[personalSelected]['oficinaf_id'],
                        f_inicio       :this.f_inicio,
                        f_final        :this.f_final,
                        n_dias         :this.n_dias,
                     }
                     this.personal_list.push(item)
                     this.personal_id = '';
                     this.search_d = '';
                  } else{
                     this.showSnack('error', 'Ya existe el personal y la fecha registrados')
                  }
               } else{
                  this.showSnack('error', 'La fecha de inicio debe ser anterior a la fecha final')
               }
               
               

               
				} else{
               this.showSnack('warning', 'Debe de seleccionar un personal')
            }
				
			},
         removeItem(item){
            console.log(item);
            const index = this.personal_list.map(function(e) { return e.id; }).indexOf(item.id)
            if (index >= 0) this.personal_list.splice(index, 1)
         },

         RegistrarVacaciones(){
            
            axios.post('registerVacaciones', {'anio_vac' : this.anio_vac, 'documento_r': this.documento_r, 'personal': this.personal_list})
                    .then(response =>{
                           if (response.data['statusBD']) {
                              this.showSnack('success', response.data['messageDB'])
                              this.d_vacaciones = false;
                              this.personal_list = [];
                              this.documento_r = '';
                              this.getVacaciones();
                           } else{
                              this.showSnack('error', response.data['messageDB'])
                           }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });


         },
         viewItem(item){},
         editItem(item){},
         deleteItem(item){
            confirm('¿Estas seguro que desea eliminar este registro?') && axios.put('deleteVacaciones/'+item.id, )
                    .then(response =>{
                           if (response.data['statusBD']) {
                              this.showSnack('success', response.data['messageDB'])
                              this.d_vacaciones = false;
                              this.personal_list = [];
                              this.documento_r = '';
                              this.getVacaciones();
                           } else{
                              this.showSnack('error', response.data['messageDB'])
                           }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
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
         calcularDias($fecha_ini, $fecha_fin){
            var fecha_ini = new Date($fecha_ini);
            var fecha_fin = new Date($fecha_fin);
            var diasdif= fecha_fin.getTime()-fecha_ini.getTime();
            var contdias = Math.round(diasdif/(1000*60*60*24));
            return contdias;
         }, 
      }
   }
	
</script>