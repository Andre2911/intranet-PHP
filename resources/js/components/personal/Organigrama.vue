<template>
   <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
      <v-main id="contentApp">         
         <v-card>
            <v-layout
                  justify-space-between
                  pa-3
               >
                  <v-flex xs12>
                     <v-sheet class="pa-3 grey">
                        <v-layout wrap>
                           <v-flex xs12 sm6>
                              <v-text-field
                                 v-model="search2"
                                 label="Buscar Personal"
                                 dark
                                 text
                                 solo-inverted
                                 hide-details
                                 @keypress.enter="filtro()"
                                 clearable
                                 clear-icon="mdi-close-circle-outline"
                              ></v-text-field>
                           </v-flex>
                           <v-flex xs12 sm2>
                              <v-btn @click="filtro()" color="red darken-3" dark>
                                 <v-icon>
                                    mdi-magnify
                                 </v-icon>
                                 Buscar
                              </v-btn>
                           </v-flex>
                           <v-flex xs12 sm2>
                              <v-btn @click="mostrarTodo()" color="success">
                                 <v-icon>
                                    mdi-magnify
                                 </v-icon>
                                 Mostrar Todo
                              </v-btn>
                           </v-flex>
                           <v-flex xs12 sm2>

                              <v-btn href="organigramaExc" target="_blank" color="info">
                                 <v-icon>
                                    mdi-microsoft-excel
                                 </v-icon>
                                 Cuadro Físico
                              </v-btn>
                           </v-flex>
                        </v-layout>
                     
                    
                  </v-sheet>
                     <v-card-text>
                        <v-treeview
                           :active.sync="active"
                           :items="items"
                           :search="search"
                           :open-all="open"
                           :open.sync="openid"
                           activatable
                           open-on-click
                           transition
                        >
                        <template v-slot:prepend="{ item, active}">
                           <v-icon
                              v-if="item.children"
                              v-text="`${item.parent_id === 0 ? 'mdi-business-center' : 'mdi-folder-open'}`"
                           ></v-icon>
                           <v-icon
                              v-else-if="!item.children"
                              :color="active ? 'success' : (item.jefe*1 == 1)? 'primary':'info'"
                              v-text="`${ item.jefe*1 == '1' ? 'mdi-account-circle' : 'mdi-badge-account'}`"
                           >
                           </v-icon>
                        </template>
                     </v-treeview>
                  </v-card-text>
               </v-flex>
            </v-layout>  
            <v-dialog v-model="datosload" max-width="900">
               <v-card>
               <v-col
                  class="text-center"
               >
                  <!--<v-scroll-y-transition mode="out-in">-->
                     <div
                        v-if="!selected"
                        class="title grey--text text--lighten-1 font-weight-light"
                        style="align-self: center;"
                     >
                        Select a User
                     </div>
                     <div
                        v-else
                        :key="selected.id"
                        max-width="900"
                     >
                           <v-row>
                              <v-col>
                                 <v-card-text>
                                    <v-avatar
                                          size="125"
                                       >
                                       <v-icon size="125">mdi-account</v-icon>
                                    </v-avatar>
                                    <h3 class="headline mb-2">
                                       {{ selected.ap_paterno }} {{ selected.ap_materno}} {{ selected.nombres}}
                                    </h3>
                                    <div class="blue--text">{{ selected.email }}</div>
                                    <div class="blue--text subheading font-weight-bold">{{ selected.numero_documento }}</div>
                                    <v-divider></v-divider>
                                    <v-subheader>Plaza Titular</v-subheader>
                                    <v-layout wrap>
                                       <v-flex tag="strong" xs4 text-right mr-2>Cargo Funcional:</v-flex>
                                       <v-flex xs7 text-left>{{ selected.nombre_plaza_t }}</v-flex>
                                       <v-flex tag="strong" xs4 text-right mr-2 mb-2>Regimen:</v-flex>
                                       <v-flex xs7 text-left>{{ selected.regimen }}</v-flex>
                                       <v-flex tag="strong" xs4 text-right mr-2>Dependencia:</v-flex>
                                       <v-flex xs7 text-left>{{ selected.nombre_oficina_t }}</v-flex>
                                    </v-layout>
                                 </v-card-text>      
                              </v-col>
                              <v-divider vertical></v-divider>
                              <v-col
                              cols="12"
                              md="6"
                              >
                                 <v-card-text>
                                    <v-subheader>
                                       Plaza física
                                    </v-subheader>
                                    <v-layout wrap>
                                       <v-flex tag="strong" xs4 text-right mr-2>Cargo Funcional:</v-flex>
                                       <v-flex xs7 text-left>{{ selected.nombre_plaza_f }}</v-flex>
                                       <v-flex tag="strong" xs4 text-right mr-2 mb-2>Regimen:</v-flex>
                                       <v-flex xs7 text-left>{{ selected.regimenf }}</v-flex>
                                       <v-flex tag="strong" xs4 text-right mr-2>Dependencia:</v-flex>
                                       <v-flex xs7 text-left>{{ selected.nombre_oficina_f }}</v-flex>
                                    </v-layout>
                                    <!--<v-layout>
                                       <v-btn  class="ma-2"
                                          outlined
                                          block
                                          color="info"
                                          @click="d_plaza = true"
                                       >
                                          <v-icon>mdi-refresh</v-icon> 
                                          Cambiar plaza
                                       </v-btn>
                                    </v-layout>
                                    <v-layout>
                                       <v-btn  class="ma-2"
                                          outlined
                                          block
                                          color="warning"
                                          @click="d_cancelPlazaf = true"
                                       >
                                          <v-icon>mdi-cancel</v-icon> 
                                          Liberar plaza
                                       </v-btn>
                                    </v-layout>
                                    -->
                                 </v-card-text>
                              </v-col>
                           </v-row>
                     </div>
                  <!--</v-scroll-y-transition>-->
               </v-col>
               <v-flex xs12>
                  <v-card color="primary lighten-2"
         dark>
                     <v-btn text @click="datosload = false">Cerrar</v-btn>  
                  </v-card>
               </v-flex>
               </v-card>
            </v-dialog>

            <v-dialog v-model="d_plaza" max-width="1200">
               <move-organigrama-plaza v-if="d_plaza" @selectPlaza="selectPlaza"/>
            </v-dialog>
            <v-dialog v-model="d_confirmPlazaf" max-width="500">
               <v-card v-if="d_confirmPlazaf">
                  <v-card-title>
                     Movimiento de plaza
                  </v-card-title>
                  <v-card-text>
                     Desea realizar la asignación de la Plaza 
                     <b> [{{temp_plaza.c_plaza}}] {{temp_plaza.nombre_plaza}} - {{temp_plaza.nombre_oficina}} </b>
                      a <strong>{{persona.ap_paterno}} {{persona.ap_materno}} {{persona.nombres}}</strong>
                  </v-card-text>
                  <v-card-actions>
                     <v-btn outlined color="red" @click="d_confirmPlazaf = false">
                        Cancelar
                        <v-icon>mdi-cancel</v-icon>
                     </v-btn>
                     <v-spacer/>
                     <v-btn outlined color="info" @click="confirmarPlazaF()">
                        Confirmar
                        <v-icon>mdi-check</v-icon>
                     </v-btn>
                  </v-card-actions>
               </v-card>
            </v-dialog>

            <v-dialog v-model="d_cancelPlazaf" max-width="500">
               <v-card v-if="d_cancelPlazaf">
                  <v-card-title>
                     Movimiento de plaza
                  </v-card-title>
                  <v-card-text>
                     Desea liberar la Plaza 
                     <b> {{persona.nombre_plaza_f}} </b>

                     Consignar fecha de Movimiento
                     <v-menu
                        ref="menufecha"
                        v-model="menufecha"
                        :close-on-content-click="false"
                        transition="scale-transition"
                        offset-y
                        max-width="290px"
                        min-width="auto"
                     >
                        <template v-slot:activator="{ on, attrs }">
                           <v-text-field
                           v-model="dateFormatted"
                           label="Fecha movimiento"
                           hint="DD/MM//YYYY"
                           persistent-hint
                           prepend-icon="mdi-calendar"
                           v-bind="attrs"
                           @blur="fecha_movimiento = parseDate(dateFormatted)"
                           v-on="on"
                           ></v-text-field>
                        </template>
                        <v-date-picker
                           v-model="fecha_movimiento"
                           no-title
                           locale="es-419"
                           @input="menufecha = false"
                        ></v-date-picker>
                     </v-menu>

                  </v-card-text>
                  <v-card-actions>
                     <v-btn outlined color="red" @click="d_cancelPlazaf = false">
                        Cancelar
                        <v-icon>mdi-cancel</v-icon>
                     </v-btn>
                     <v-spacer/>
                     <v-btn outlined color="info" @click="confirmarPlazaF()">
                        Confirmar
                        <v-icon>mdi-check</v-icon>
                     </v-btn>
                  </v-card-actions>
               </v-card>
            </v-dialog>

         </v-card>
      </v-main>
      <footer-component/>

   </v-app>
</template>
<script>
   const pause = ms => new Promise(resolve => setTimeout(resolve, ms))
   const avatars = [
    '?accessoriesType=Blank&avatarStyle=Circle&clotheColor=PastelGreen&clotheType=BlazerSweater&eyeType=Default&eyebrowType=Default&facialHairColor=BrownDark&facialHairType=Blank&hairColor=BrownDark&mouthType=Default&skinColor=Light&topType=ShortHairShortWaved',
  ]
   export default {
      props:['data-user'],
      data(){
         return {
            menu: 0,
            submenu: -1,
            modulo: 'personal',
            drawer:true,
            caseSensitive:false,
            search:null,
            search2:null,
            items: [],
            active: [],
            datos:{},
            open: true,
            openid: [],
            arbol: [],
            personal: [],
            //itemsDefault: [],
            avatar: null,
            datosload:false,
            d_plaza:false,
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
            persona:{},
            d_confirmPlazaf:false,
            d_cancelPlazaf:false,
            temp_plaza:{},
            fecha_movimiento: new Date().toISOString().substr(0, 10),
            dateFormatted: this.formatDate(new Date().toISOString().substr(0, 10)),
            menufecha: false,
         };
      },
      watch:{
         selected: 'randomAvatar'
      },      
      computed:{
         filter () {
           return this.caseSensitive
             ? (item, search, textKey) => item[textKey].indexOf(search) > -1
             : undefined
         },
         selected(){
            if (!this.active.length) return undefined
            const id = this.active[0];
            if(this.persona != null){
               this.datosload = true;
               if(this.active[0] != this.persona.id){
                  this.getDataPersonal(id)
               }
            } else{
               if(this.active[0].parent_id == null){
                  this.getDataPersonal(id)
               }
            }
            return (this.persona != null)? this.persona : false;
         },
         computedDateFormatted () {
            return this.formatDate(this.fecha_movimiento)
         },
      },
      watch:{
         datosload(val){
            if(!val){
               this.persona = null
               this.active = []
            }
         },
         fecha_movimiento (val) {
            this.dateFormatted = this.formatDate(this.fecha_movimiento)
         },
      },
      created(){
         this.getOficina()               
      },
      methods: {
         getOficina(url='oficinasApi?tipo=1'){
            this.search = null
            axios.get(url)
                  .then(response =>{
                     this.items = response.data['organigrama']
                     this.personal = response.data['personal']
                     
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },

         mostrarTodo(){
            this.getOficina();
            for (var i = 0; i < 5000 ; i++) {
                  this.openid.push(i);
            }
         },
         filtro(url='oficinasApi?filtro_org='+this.search2){
            if(this.search2 != '' && this.search2 != null){
               this.search = this.search2;
               axios.get(url)
                     .then(response =>{
                        this.items = response.data['organigrama']
                        //this.personal = response.data['personal']
                        for (var i = 0; i < 5000 ; i++) {
                           this.openid.push(i);
                        }
                     })
                     .catch(errors =>{
                        console.log(errors);
                     });
            } else{
               this.search = null;
               this.getOficina();
            }
            
         },

         selectPlaza(item){
            this.temp_plaza = item;
            this.d_confirmPlazaf = true;
         },
         confirmarPlazaF(){

         },
         async getDataPersonal(id){
            return fetch('personalApi?consultarOrg=true&persona=' + id)
                .then(res => res.json())
                .then(json => (this.persona = json['persona']))
                .catch(err => console.warn(err))
         },

         async fetchUsers (item) {
            await pause(500)
            return fetch('oficinasApi?getPersonalOfID=' + item.of_id)
                .then(res => res.json())
                .then(json => (item.children.push(...json)))
                .catch(err => console.warn(err))
         }, 
         randomAvatar () {
            this.avatar = avatars[Math.floor(Math.random() * avatars.length)]
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

            const [day,month, year] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
         },
      }
   };

</script>