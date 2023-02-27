<template>
   <v-app id="inspire">
      <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
      <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
      <v-main id="contentApp">         
            <v-card>
                <v-toolbar color="grey" dark>
                    <v-toolbar-title>
                        Cadena {{ (meses[cadena_mes-1]!=undefined)? meses[cadena_mes-1]['label'] : '' }} {{ cadena_anio }}
                    </v-toolbar-title>
                    <v-spacer/>
                    <v-col cols="12" md="2">
                        <v-autocomplete
                            v-model="cadena_anio"
                            :items="anios"
                            label="Año"
                            class="mt-5"
                        >
                        </v-autocomplete>  
                    </v-col>
                    <v-col cols="1" md="0">
                        <v-icon 
                        disabled
                        ></v-icon>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-autocomplete
                            v-model="cadena_mes"
                            :items="meses"
                            label="Mes"
                            item-value="value"
                            item-text="label"
                            class="mt-5"
                        >
                        </v-autocomplete>  
                    </v-col>
                    
                    <v-btn 
                        color="red accent-3"
                        @click="showCadena()"
                        :disabled="(cadena_mes == '' || cadena_anio == '') || items.length > 0"
                    >
                        <v-icon>
                            mdi-file-link-outline
                        </v-icon>
                        {{btn_periodo}}
                    </v-btn>

                    <v-btn 
                        color="info"
                        @click="updateCadena()"
                        v-if="(cadena_mes != '' && cadena_anio != '') && items.length > 0"
                    >
                        <v-icon>
                            mdi-file-link-outline
                        </v-icon>
                        {{btn_update}}
                    </v-btn>
                </v-toolbar>
                <v-card-text>
                        <v-treeview
                            :active.sync="active"
                            :items="items"
                            :open-all="open"
                            :open.sync="openid"
                            activatable
                            transition
                        >
                        <template v-slot:prepend="{ item}">
                           <v-icon
                              v-if="item.children"
                              v-text="`${item.op_plaza_tit_id === null ? 'mdi-badge-account' : 'mdi-account-switch'}`"
                           ></v-icon>
                           <v-icon
                              v-else-if="!item.children"
                              :color="(item.persona_tit_id === null && item.persona_fun_id === null)? 'red' :(item.persona_tit_id != null && item.persona_fun_id == null)? 'red':'primary' "
                              v-text="`${ (item.persona_ti_id === null && item.persona_fun_id === null) ? 'mdi-badge-account-alert' : (item.op_plaza_tit_id != null && item.persona_fun_id == null)? 'mdi-account-off':'mdi-account-network'}`"
                           >
                           </v-icon>
                        </template>
                        <template v-slot:label="{item}">
                            <v-list two-line @click="datos.load=true">
                                <v-list-item> 
                                    <v-list-item-content>
                                        <v-list-item-title
                                            :class="((item.persona_tit_id === null && item.persona_fun_id === null) ||(item.persona_tit_id != null && item.persona_fun_id == null))? 'red--text':''"
                                        >{{item.nombre_plaza}}</v-list-item-title>
                                        <v-list-item-subtitle> {{item.nombre_oficina}} </v-list-item-subtitle>
                                        <v-list-item-subtitle> 
                                            <i>Titular: {{item.persona_tit }} 
                                            <template v-if="item.motivo=='Licencia'">
                                                (Licencia: {{item.fecha_ini}} - {{item.fecha_fin}})
                                            </template>
                                            </i>
                                        </v-list-item-subtitle>
                                        <v-list-item-subtitle> 
                                            <b>
                                                <template v-if="(item.persona_tit_id !== null && item.persona_fun_plaza_id !== null) || (item.persona_tit_plaza_id === null && item.persona_fun_plaza_id !== null)">Encargatura:</template> 
                                                <template v-else-if="item.persona_tit_plaza_id === null && item.persona_fun_plaza_id === null">Plazo Fijo:</template> 
                                                <template v-else>Suplente:</template> 
                                                
                                                {{item.persona_fun }}  </b>
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </template>
                     </v-treeview>
                </v-card-text>
            </v-card>
            <v-dialog v-model="datosload" :width="900">
                <v-card>
                    <v-col
                        class="text-center"
                    >
                        <v-card v-if="!selected" class="pt-4 mx-auto" flat max-width="900">
                            Select a User
                        </v-card>
                        <v-card v-else :key="selected.id" class="pt-4 mx-auto" flat max-width="900">
                            <v-layout
                                tag="v-card-text"
                                text-xs-left
                                wrap
                            >
                                <v-flex xs6 md6 sm6 text-xs-center>
                                    <v-card-text>
                                        <v-avatar
                                          size="125"
                                            >
                                            <v-icon size="125">mdi-badge-account</v-icon>
                                        </v-avatar>
                                        <h3 class="headline mb-2">
                                        {{ selected.nombre_plaza }}
                                        </h3>
                                        <div class="blue--text">{{ selected.nombre_oficina }}</div>
                                        <div class="blue--text subheading font-weight-bold">Código de Plaza: {{ selected.c_plaza }}</div>
                                    </v-card-text>
                                    <v-divider></v-divider>
                                        <v-flex tag="strong" xs12 text-xs-right mr-2 mb-2>Titular de la Plaza:</v-flex>
                                        <v-flex>
                                            <h4>{{selected.persona_tit}}</h4>
                                            <template v-if="selected.motivo=='Licencia'">
                                                <br>
                                                (Licencia: {{selected.fecha_ini}} - {{selected.fecha_fin}})
                                            </template>
                                        </v-flex>
                                        <v-flex>
                                    
                                </v-flex>
                                </v-flex>
                                <v-flex xs6 md6 sm6>
                                    <v-layout wrap xs12>
                                        <v-flex xs12 sd12 sm12 text-xs-center>
                                            <v-flex tag="strong" xs12 mr-2 mb-2>Suplencia:</v-flex>
                                            <v-flex xs12>{{ selected.persona_fun }}</v-flex>
                                        </v-flex>
                                        <!--<v-flex xs6 md6 sm6>
                                            <v-menu
                                                :close-on-content-click="false"
                                                v-model="fecha_ini"
                                                transition="scale-transition"
                                                offset-y
                                                >
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field
                                                    slot="activator"
                                                    v-model="suplencia_fecha_ini"
                                                    label="Fecha de Inicio"
                                                    persistent-hint
                                                    prepend-icon="mdi-event"
                                                    readonly
                                                    v-on="on"
                                                    ></v-text-field>
                                                </template>
                                                <v-date-picker v-model="dateini" no-title @input="fecha_ini = false"></v-date-picker>
                                            </v-menu>
                                        </v-flex>
                                        <v-flex xs6 md6 sm6>
                                            <v-menu
                                            :close-on-content-click="false"
                                            v-model="fecha_fin"
                                            transition="scale-transition"
                                            offset-y
                                            >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                slot="activator"
                                                v-model="suplencia_fecha_fin"
                                                label="Fecha de Fin"
                                                persistent-hint
                                                prepend-icon="mdi-event"
                                                readonly
                                                v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-date-picker v-model="datefin" no-title @input="fecha_fin = false"></v-date-picker>
                                            </v-menu>
                                        </v-flex>

                                        -->
                                    </v-layout>
                                   
                                    <v-layout
                                        tag="v-card-text"
                                        text-xs-center
                                        wrap
                                    >
                                            <v-flex v-if="selected.persona_fun_plaza_id != null && !make_mov">
                                                <v-btn
                                                    color="info"
                                                    @click="d_personalLoad = true"
                                                >
                                                    <v-icon>
                                                        mdi-vector-link
                                                    </v-icon>
                                                    Reemplazar nudo
                                                </v-btn>
                                                <v-btn
                                                    color="red"
                                                    @click="actualizaPlaza('unlink')"
                                                    dark
                                                >
                                                    <v-icon>
                                                        mdi-link-off
                                                    </v-icon>
                                                    Romper cadena
                                                </v-btn>
                                            </v-flex>
                                            <v-flex v-else-if="selected.persona_fun_plaza_id == null && selected.persona_fun_id != null && !make_mov">
                                                <v-btn
                                                    color="warning"
                                                    @click="d_personalLoad = true"
                                                    dark
                                                >
                                                    <v-icon>
                                                        mdi-update
                                                    </v-icon>
                                                    Cambiar suplente
                                                </v-btn>
                                                <v-btn
                                                    color="red"
                                                    @click="actualizaPlaza('unlink')"
                                                    dark
                                                >
                                                    <v-icon>
                                                        mdi-link-off
                                                    </v-icon>
                                                    Liberar Plaza
                                                </v-btn>
                                            </v-flex>
                                            <v-flex v-else-if="selected.persona_fun_plaza_id == null && selected.persona_fun_id == null && !make_mov">
                                                <v-btn
                                                    color="success"
                                                    @click="d_personalLoad = true"
                                                    dark
                                                >
                                                    <v-icon>
                                                        mdi-link-variant-plus
                                                    </v-icon>
                                                    Agregar suplente
                                                </v-btn>
                                            </v-flex>
                                            <v-flex v-if="make_mov">
                                                <br>
                                                <v-spacer/>
                                                <v-btn
                                                    color="info"
                                                    dark
                                                    @click="actualizaPlaza('updateNodo')"
                                                >
                                                    <v-icon>
                                                        mdi-content-save
                                                    </v-icon>
                                                    Grabar
                                                </v-btn>
                                                <v-btn
                                                    color="red"
                                                    dark
                                                    @click="showCadena(), make_mov = false"
                                                >
                                                    <v-icon>
                                                        mdi-cancel
                                                    </v-icon>
                                                    Cancelar
                                                </v-btn>
                                            </v-flex>
                                    </v-layout>
                                </v-flex>
                            </v-layout>
                        </v-card>
                    <!--</v-scroll-y-transition>-->
                    </v-col>
                    <v-flex xs12>
                        <v-card color="primary lighten-2" dark>
                            <v-btn block text @click="datosload = false">Cerrar</v-btn>  
                        </v-card>
                    </v-flex>
                </v-card>
            
            </v-dialog>

            <v-dialog v-model="d_personalLoad"
                scrollable
                max-width="1200PX"
                transition="dialog-transition"
            >
                <personal-cadena-suplente  @selectedItem="selectedItem"/>
            </v-dialog>

            <v-dialog v-model="d_suplente"
                scrollable
                max-width="1200PX"
                transition="dialog-transition"
            >
            </v-dialog>
            
            <snackbar :snack="snack" />
            <dialogLoader :dialogLoad="dialogLoad"/>
        </v-main>
        <footer-component/>

   </v-app>
</template>
<script>
export default {
    props:['data-user'],
    data(){
        return {
            menu: 3,
            submenu: -1,
            modulo: 'personal',
            drawer:true,
            items:[],
            active: [],
            open: true,
            openid: [],
            datosload:false,
            plazas:[],
            anios:[],
            meses:[],
            periodos:[],
            last_periodo:{},
            cadena_anio: '',
            cadena_mes:'',
            fecha_ini:false,
            fecha_fin:false,
            dateini: this.parseDate(new Date().toLocaleDateString()),
            datefin: this.parseDate(new Date().toLocaleDateString()),
            suplencia_fecha_ini:'',
            suplencia_fecha_fin:'',
            d_personalLoad:false,
            d_suplente:false,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            dialogLoad:{
                show:false,
                text:'',
                color:'',
            },
            make_mov:false,
        }
    },
    computed:{
        selected(){
            if (!this.active.length) return undefined
            
            const id = this.active[0]
            this.datosload = true
				//console.log(this.items.find(personal => personal.id === id))
//            var plaza = this.plazas.find(plaza => plaza.id === id)

            return this.plazas.find(plaza => plaza.id === id)
            
            //console.log(this.personal.find(user => user.id === id))
        },
        btn_periodo(){
            return (
                (this.last_periodo['anio']*1 == this.cadena_anio*1 && this.last_periodo['mes']*1 == this.cadena_mes*1)? 'Consultar Cadena temporal' :
                this.periodos.find(periodo => periodo.anio == this.cadena_anio && periodo.mes == this.cadena_mes)
                ? 'Consultar Cadena':'Crear Cadena');
        },
        btn_update(){
            return (this.periodos.find(periodo => periodo.anio == this.cadena_anio && periodo.mes == this.cadena_mes))? 'Actualizar Cadena':'Grabar Cadena Temporal';
        },
    },
    watch:{
        cadena_anio(val){
            this.items = []
            this.active = []
        },
        cadena_mes(val){
            this.items = []
            this.active = []
        }, 
        datosload(val){
            if(!val){
                this.make_mov = false
                this.showCadena();
                this.active = []
            }
        }
    },
    created() {
        this.getCadena();
        this.getAnios();            
    },
    methods: {
        getCadena(){
            let url = ''
         	if (this.cadena_anio != '' && this.cadena_mes != '') {
         		url = 'cadenaApi?cadena=true'+'&anio=' + this.cadena_anio + '&mes=' + this.cadena_mes;
         	} else{
         		url = 'cadenaApi?init=true'
         	}
            this.search = null
            axios.get(url)
                  .then(response =>{
                    this.items = response.data['cadena'];
                    this.plazas = response.data['c_plana'];
                    this.periodos = response.data['periodos']
                    this.last_periodo = response.data['last_periodo'][0]
                    for (var i = 0; i < 5000 ; i++) {
                        this.openid.push(i);
                    }
                  })
                  .catch(errors =>{
                     console.log(errors);
                     if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "inicio";
                        }
                     }
                  });
        },
        showCadena(){
            var url = ''
      		if (this.btn_periodo == 'Crear Cadena') {
      			var periodo = {anio : this.cadena_anio, mes : this.cadena_mes};
         		url = 'cadenaApi?crear=true'+'&anio=' + this.cadena_anio + '&mes=' + this.cadena_mes;
            } else if (this.btn_periodo == 'Consultar Cadena temporal') {
                var periodo = {anio : this.cadena_anio, mes : this.cadena_mes};
         		url = 'cadenaApi?consultar=true'+'&anio=' + this.cadena_anio + '&mes=' + this.cadena_mes;
            }
            this.showLoad('grey darken-2', 'Realizando consulta');

	            axios.get(url)
                    .then(response =>{
                        this.items = response.data['cadena'];
                        this.plazas = response.data['c_plana'];
                        for (var i = 0; i < 5000 ; i++) {
                            this.openid.push(i);
                        }
                        this.showLoad('', '', false);

                    //this.getCadena();
                    })
                    .catch(errors =>{
                        this.showLoad('', '', false);
                        console.log(errors);
                        if (errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
      		
        },
        updateCadena(){
      		if (this.btn_update == 'Grabar Cadena') {
      			var periodo = {anio : this.cadena_anio, mes : this.cadena_mes};

	            axios.post('cadenaApi?creartmp=true', periodo)
	            .then(response =>{
	                this.items = response.data['cadena'];
                    this.plazas = response.data['c_plana'];
                    for (var i = 0; i < 5000 ; i++) {
                        this.openid.push(i);
                    }
	               //this.getCadena();
	            })
	            .catch(errors =>{
	                console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "inicio";
                        }
                    }
	            });
      		}  else{
      			this.getCadena();
      		}
        },
        actualizaPlaza(accion){
            var url = ''
            if(accion == 'unlink'){
                url = 'cadenaApi?creartmp=true'
                confirm('¿Estas seguro que desea romper la cadena y eliminar los nodos debajo de esta plaza?') && 
                axios.put('cadenaApi/' + this.selected.id,{'plaza': this.selected, 'accion': accion})
	            .then(response =>{
                    if(response.data['response']['statusBD']){
                        this.items = response.data['cadena'];
                        this.plazas = response.data['c_plana'];
                        for (var i = 0; i < 5000 ; i++) {
                            this.openid.push(i);
                        }
                        this.showSnack('success', response.data['response']['messageDB'])

                    } else{
                        this.showSnack('warning', response.data['response']['messageDB'])
                    }
	                /*
                    */
	               //this.getCadena();
	            })
	            .catch(errors =>{
	                console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "inicio";
                        }
                     }
	            });
            } else if(accion == 'updateNodo'){
                axios.put('cadenaApi/' + this.selected.id,{'plaza': this.selected, 'accion': accion})
	            .then(response =>{
                    if(response.data['response']['statusBD']){
                        this.items = response.data['cadena'];
                        this.plazas = response.data['c_plana'];
                        for (var i = 0; i < 5000 ; i++) {
                            this.openid.push(i);
                        }
                        this.showSnack('success', response.data['response']['messageDB'])
                        this.datosload = false;
                    } else{
                        this.showSnack('warning', response.data['response']['messageDB'])
                    }
	                /*
                    */
	               //this.getCadena();
	            })
	            .catch(errors =>{
	                console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "inicio";
                        }
                     }
	            });
            }
                

        },
        selectedItem(item){
            /***************CONSULTAR SI ESTA EN LA CADENA */

            var plaza_cadena = this.plazas.find(plaza => plaza.persona_fun_id*1 === item.id || plaza.persona_tit_id*1 === item.id);

            if(plaza_cadena != undefined){
                this.showSnack('warning', 'La persona seleccionada ya esta dentro de la cadena')
            } else{
                this.selected.persona_fun = item.ap_paterno + ' ' + item.ap_materno + ' ' + item.nombres
                this.selected.persona_fun_id = item.id
                this.selected.persona_fun_plaza_id = item.op_plaza_tit_id
                this.make_mov=true
            }

            this.d_personalLoad = false;


            /***************** CONSULTAR REGIMEN */
        },
        getAnios(){
            var d = new Date();
            var n = d.getFullYear();
            for (var i = n; i >= 2021; i--) {
               this.anios.push(i);
            }
            var mesesdesc = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre']
            for (var i = 1; i <= 12; i++) {
               this.meses.push({value:i, label:mesesdesc[i-1]});
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
        cDrawer(val){
            if(val != undefined ){
                this.drawer = val;
            } else{
                this.drawer = !(this.drawer);
            }
        },
        showSnack(color, text, show = true){
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
        },
        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
    },

}
</script>
        