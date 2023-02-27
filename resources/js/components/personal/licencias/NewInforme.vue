<template>
   <v-card>
      <v-toolbar color="grey" dark>
         <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text class="pb-0">
         <v-container grid-list-md class="pb-0">
            <v-form v-model="valid" ref="form">
               <v-layout wrap>
                  <v-flex xs12 sm10 md10>
                     <v-autocomplete
                           v-model="editedItem.personal_id"
                           :items="personal"
                           label="Personal"
                           :rules="[rules.required]"
                           item-text="nombrecompleto"
                           item-value="numero_documento"
                           ref="personal_id"
                        />
                  </v-flex>
                  <v-flex xs12 sm2 md2>
                     <h4>{{ n_dias }} días</h4>
                  </v-flex>
                  <v-flex xs12 sm4 md4>
                     <v-autocomplete
                        v-model="editedItem.cod_doc"
                        :items="documentos"
                        label="Tipo Documento"
                        item-text="den_doc"
                        item-value="cod_doc"
                        required
                        :rules="[rules.required]"
                        color="deep-purple"
                     ></v-autocomplete>
                  </v-flex>
                  <v-flex xs12 sm4 md4>
                     <v-text-field
                        v-model="editedItem.num_doc"
                        label="N° Documento"
                        required
                        :rules="[rules.required]"
                        color="deep-purple"
                     ></v-text-field>
                  </v-flex>
                  <v-flex xs12 sm4 md4>
                     <v-menu
                        :close-on-content-click="false"
                        v-model="fecha_documento"
                        transition="scale-transition"
                        offset-y
                        >
                           <template v-slot:activator="{ on }">
                              <v-text-field
                              slot="activator"
                              v-model="editedItem.doc_fecha"
                              label="Fecha Documento"
                              v-on="on"
                              persistent-hint
                              prepend-icon="mdi-calendar"
                              readonly
                              :rules="[rules.required]"
                              ></v-text-field>
                           </template>
                           <v-date-picker v-model="datedoc" no-title @input="fecha_documento = false"></v-date-picker>
                        </v-menu>
                  </v-flex>
                  <v-flex xs12 sm7 md7>
                     <v-autocomplete
                           v-model="editedItem.licencia_tipo_id"
                           :items="tiposlicencia"
                           label="Licencia / Permiso"
                           :rules="[rules.required]"
                           item-text="fulldescripcion"
                           item-value="id"
                     />
                  </v-flex>
                  <v-flex xs12 sm2 md2 v-if="d_goce">
                     <v-switch
                        v-model="c_goce"
                        :label="det_goce"
                     />
                  </v-flex>
                  <v-flex xs12 sm2 md2>
                     <v-autocomplete
                           v-model="editedItem.sentido"
                           :items="sentidos"
                           label="Sentido del Informe"
                           :rules="[rules.required]"
                        />
                  </v-flex>

                  <template v-if="licencia_vacaciones">
                     <v-flex xs12 sm1 md1>
                        <v-text-field
                           v-model="anio_vac"
                           label="Año"
                           type="number"
                        > 
                        </v-text-field>
                     </v-flex>
                  </template>

                  <template v-if="licencia_vacaciones">
                     <v-flex xs12 sm10 md10>
                        <h5>Vacaciones acumuladas</h5>
                        <ul>
                           <li v-for="vacaciones in historial_vac" v-bind:key="vacaciones.id">Vacaciones del {{ formatDate(vacaciones.fecha_ini) }} al {{ formatDate(vacaciones.fecha_fin) }} ( {{vacaciones.n_dias}} día(s) - Licencia) </li>
                        </ul>
                        <ul>
                           <li v-for="vacaciones in historial_vac2" v-bind:key="vacaciones.id">Vacaciones del {{ formatDate(vacaciones.fecha_ini) }} al {{ formatDate(vacaciones.fecha_fin) }} ( {{vacaciones.n_dias}} día(s) ) </li>
                        </ul>
                     </v-flex>
                     <v-flex xs12 sm2 md2>
                        <h4>{{d_acumulados}} días acumulados</h4>
                     </v-flex>
                  </template>
               </v-layout>
               <v-layout wrap>
                  <v-flex xs12 sm2 md2>
                     <v-menu
                        :close-on-content-click="false"
                        v-model="fecha_inf"
                        transition="scale-transition"
                        offset-y
                        >
                           <template v-slot:activator="{ on }">
                              <v-text-field
                              slot="activator"
                              v-model="editedItem.fecha_inf"
                              label="Fecha de Informe"
                              v-on="on"
                              persistent-hint
                              prepend-icon="mdi-calendar"
                              readonly
                              :rules="[rules.required]"
                              ></v-text-field>
                           </template>
                           <v-date-picker v-model="dateinf" no-title @input="fecha_inf = false"></v-date-picker>
                        </v-menu>
                  </v-flex>
                  <v-flex xs12 sm12 md6>
                     <v-autocomplete
                        v-model="editedItem.formato_id"
                        :items="filtroFormatos"
                        label="Informe"
                        item-text="descripcion"
                        item-value="id"
                        :clearable=true
                      > 
                     </v-autocomplete>
                     <input type="hidden" 
                        v-model="archivotmp"
                     /> 
                  </v-flex>
                  <v-flex xs12 sm2 md2>
                     <v-menu
                        :close-on-content-click="false"
                        v-model="fecha_ini"
                        transition="scale-transition"
                        offset-y
                        >
                           <template v-slot:activator="{ on }">
                              <v-text-field
                              slot="activator"
                              v-model="editedItem.fecha_ini"
                              label="Inicio"
                              v-on="on"
                              persistent-hint
                              prepend-icon="mdi-calendar"
                              readonly
                              :rules="[rules.required]"
                              ></v-text-field>
                           </template>
                           <v-date-picker v-model="dateini" no-title @input="fecha_ini = false"></v-date-picker>
                        </v-menu>
                  </v-flex>
                  <v-flex xs12 sm2 md2>
                     <v-menu
                        :close-on-content-click="false"
                        v-model="fecha_fin"
                        transition="scale-transition"
                        offset-y
                        >
                           <template v-slot:activator="{ on }">
                              <v-text-field
                              slot="activator"
                              v-model="editedItem.fecha_fin"
                              label="Inicio"
                              v-on="on"
                              persistent-hint
                              prepend-icon="mdi-calendar"
                              readonly
                              :rules="[rules.required]"
                              ></v-text-field>
                           </template>
                           <v-date-picker v-model="datefin" no-title @input="fecha_fin = false"></v-date-picker>
                        </v-menu>
                  </v-flex>
                  <template v-if="editedItem.id != undefined || editedItem.id != null">
                     <v-flex xs12 sm12 md12 >
                        <v-subheader color="purple">Resolución del CED</v-subheader>
                     </v-flex>
                     <v-flex xs12 sm3 md3 >
                        <v-menu
                        :close-on-content-click="false"
                        v-model="fecha_res"
                        transition="scale-transition"
                        offset-y
                        >
                           <template v-slot:activator="{ on }">
                              <v-text-field
                              slot="activator"
                              v-model="editedItem.fecha_resced"
                              label="Fecha de resolución CED"
                              v-on="on"
                              persistent-hint
                              prepend-icon="mdi-calendar"
                              readonly
                              filled
                              color="purple"
                              ></v-text-field>
                           </template>
                           <v-date-picker v-model="dateres" no-title @input="fecha_res = false"></v-date-picker>
                        </v-menu>
                     </v-flex>
                     <v-flex xs12 sm5 md5>
                        <v-text-field
                           v-model="editedItem.n_resolucionced"
                           label="N° de resolucion del CED"
                           color="purple"
                           filled
                           hint="RA N° 00000-0000-CED-CSJPU/PJ"
                           persistent-hint
                           />
                     </v-flex>
                     <v-flex xs12 sm4 md4>
                        <v-autocomplete
                           v-model="editedItem.confirmada"
                           :items="estadosCED"
                           label="Decisión CED"
                           item-text="descripcion"
                           item-value="id"
                           :clearable=true
                           filled
                           color="purple"
                        />
                     </v-flex>
                  </template>
                  
               </v-layout>
            </v-form>
         </v-container>
      </v-card-text>      
      <v-divider/>
      <v-card-actions>
            <v-spacer class="mt-0"></v-spacer>
            <v-btn color="red darken-1" text @click="close">
               <v-icon
                  class="mr-2"
                  color="red"
               >
                  mdi-cancel
               </v-icon> 
               Cancel
            </v-btn>
            <template  v-if="editedItem.id != undefined || editedItem.id != null">
               <v-btn color="info darken-1" text @click="save">
                   <v-icon
                  class="mr-2"
                  color="info"
                  >
                     mdi-content-save-edit
                  </v-icon> 
                  Actualizar
               </v-btn>
            </template>
            <template v-else>
               <v-btn color="blue darken-1" text @click="save">
                  <v-icon
                  class="mr-2"
                  color="info"
                  >
                     mdi-content-save
                  </v-icon> 
                  Guardar
               </v-btn>
            </template>
         </v-card-actions>
   </v-card>
</template>

<script>
   export default {
      props: ['editedItem', 'editIndex', 'tiposlicencia', 'personal','documentos', 'formatos', 'dialog_edit'],
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
            rules: {
               required: v => !!v || 'Descripcion Requerido',
            },
            sentidos:[
               'PROCEDENTE',
               'IMPROCEDENTE',
               'DESISTIDO',
               'SUSPENDIDO'
            ],
            estadosCED:[
               {id:0, descripcion:'DENEGADA'},
               {id:1, descripcion:'CONFIRMADA'},
            ],
            datedoc: this.parseDate(new Date().toLocaleDateString()),
            dateini: this.parseDate(new Date().toLocaleDateString()),
            datefin: this.parseDate(new Date().toLocaleDateString()),
            dateinf: this.parseDate(new Date().toLocaleDateString()),
            dateres: this.parseDate(new Date().toLocaleDateString()),
            fecha_documento: false,
            fecha_ini:false,
            fecha_fin:false,
            fecha_inf:false,
            fecha_res:false,
            anio_vac: new Date().getFullYear(),
            historial_vac:[],
            historial_vac2:[],
            d_acumulados: 0,
            c_goce:false,
         }
      },
      computed: {
         formTitle () {
           return this.editIndex === -1 ? 'Nuevo Informe' : 'Editar datos del informe'
         },
         filtroFormatos(){
            let plantillas = this.formatos;
            return plantillas.filter(p => p.licencia_tipo == this.editedItem.licencia_tipo_id && p.sentido == this.editedItem.sentido);
         },
         computedDateFormatted () {
            return this.formatDate(this.date)
         },
         archivotmp(){
            let plantillas = this.formatos;
            return plantillas.filter(p => p.licencia_tipo == this.editedItem.licencia_tipo_id && p.sentido == this.editedItem.sentido); 
         },
         licencia_vacaciones(){
            if (this.tiposlicencia != undefined) {
               let plantillas = this.tiposlicencia;
               let plantillaSelected  = plantillas.filter(p => p.id == this.editedItem.licencia_tipo_id);
               if (plantillaSelected.length > 0 && plantillaSelected[0]['a_vac']) {
                  this.editedItem.anio_vac = this.anio_vac;

                  if (this.editedItem.personal_id != undefined) {
                     this.getHistorial(this.editedItem.personal_id, this.anio_vac);
                  } else{
                     this.showSnack('warning', 'Seleccione un Personal', false)
                     this.$emit('showmsg', this.snackValues)
                     this.$refs.personal_id.focus();
                  }
                  return true;
               } else{

                  this.anio_vac =(new Date().getFullYear()+1)*1;
                  this.editedItem.anio_vac = null;
                  return false;   
               }
            }
         },
         d_goce(){
            if (this.tiposlicencia != undefined) {
               let licencias = this.tiposlicencia;
               let licenciaSelected  = licencias.filter(p => p.id == this.editedItem.licencia_tipo_id);
               if (licenciaSelected.length > 0){
                  if(licenciaSelected[0]['c_goce']*1 == 1 && licenciaSelected[0]['s_goce']*1 != 1){
                     this.c_goce = true
                  } else if(licenciaSelected[0]['c_goce']*1 != 1 && licenciaSelected[0]['s_goce']*1 == 1){
                     this.c_goce = false
                  } else if(licenciaSelected[0]['c_goce']*1 == 1 && licenciaSelected[0]['s_goce']*1 == 1){
                     this.c_goce = false
                  } else if(licenciaSelected[0]['a_vac']*1 == 1){
                     this.c_goce = true
                  }
                  return (licenciaSelected[0]['c_goce']*1 == 1 && licenciaSelected[0]['s_goce']*1 == 1)? true:false;
               } else{
                  return false;
               }
            } else{
               return false;
            }
         },
         n_dias(){
            console.log(this.fecha_ini+this.fecha_fin)   
            if(this.dialog_edit > 0){
               return this.calcularDias(this.parseDate(this.editedItem.fecha_ini), this.parseDate(this.editedItem.fecha_fin))*1+1
            } else{
               return 0;
            }
         }, 
         personal_activo(){

         },
         det_goce(){
            return this.c_goce? 'Con goce':'Sin goce'
         }
         
      },
      watch:{
         editIndex(val){            
            if (val<0) {
               console.log("aqui")
               var fecha = new Date().toLocaleDateString()
               this.dateini = this.parseDate(fecha)
            } else{               
               
            }
         },
         dialog_edit(val){
            if (val>0) {
               this.datedoc = this.parseDate(this.editedItem.doc_fecha)
               this.dateini = this.parseDate(this.editedItem.fecha_ini)
               this.datefin = this.parseDate(this.editedItem.fecha_fin)
               this.dateres = this.parseDate(this.editedItem.fecha_resced)
            } else{               
               
            } 
         },
         datedoc (val) {
            this.editedItem.doc_fecha = this.formatDate(this.datedoc)
         },
         dateini (val) {
            this.editedItem.fecha_ini = this.formatDate(this.dateini)
         },
         datefin (val) {
            this.editedItem.fecha_fin = this.formatDate(this.datefin)
         },
         dateinf (val) {
            this.editedItem.fecha_inf = this.formatDate(this.dateinf)
         },
         dateres (val) {
            this.editedItem.fecha_resced = this.formatDate(this.dateres)
         },
         archivotmp(val){
            if (val.length > 0) {
               this.editedItem.archivo = val[0].archivo   
            }  
         },
         anio_vac(val){
            if (val*1 > 2000 && this.licencia_vacaciones) {
               console.log('buscar licencias')
            }
            
         }
      }, 
      created(){

      },
      methods: {
         close(){
            this.$emit('close')
            this.datos = this.default
         },
         save(){
            if (this.editIndex > -1) {
               this.editedItem.archivo = (this.archivotmp[0] != undefined)? this.archivotmp[0].archivo: null;
               this.editedItem.c_goce = this.c_goce;
               this.editedItem.n_dias = this.n_dias;
               
               axios.put(`licenciasApi/${this.editedItem.id}?informe=true`, this.editedItem)
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
               if (this.$refs.form.validate()) {
                  this.editedItem.archivo = (this.archivotmp[0] != undefined)? this.archivotmp[0].archivo: null;
                  this.editedItem.c_goce = this.c_goce;
                  this.editedItem.n_dias = this.n_dias;

                  axios.post('licenciasApi?saveinforme=true', this.editedItem)
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
            }
         }, 
         getHistorial(personal_id, anio_vac){
            this.d_acumulados = 0;
            var url = 'licenciasApi?historial=true&personal_id='+personal_id+'&anio_vac='+anio_vac;
            axios.get(url)
               .then((response) => {
                  this.historial_vac = response.data['informes'];
                  this.historial_vac2 = response.data['vacaciones'];
                  for (var i = 0; i < this.historial_vac.length; i++) {
                     this.d_acumulados += this.historial_vac[i].n_dias*1
                  }

                  for (var i = 0; i < this.historial_vac2.length; i++) {
                     this.d_acumulados += this.historial_vac2[i].n_dias*1
                  }
              });
         },
         showSnack(color, text, show = true){
            this.snackValues.color = color
            this.snackValues.text = text
            this.snackValues.show = show
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
   };

</script>