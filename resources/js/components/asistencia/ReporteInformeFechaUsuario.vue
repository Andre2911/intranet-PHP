<template>
   	<v-app id="inspire">
				<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
         	<v-card>
            	<v-toolbar color="grey" dark>
            	   <v-toolbar-title @click="counterClick+=1">Informe de Labor efectiva del Personal</v-toolbar-title>
				</v-toolbar>
				<v-container id="contentApp">
					<v-layout wrap>
                        <v-flex xs12 md2>
                            <v-menu
                                v-model="fecha_consulta"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                transition="scale-transition"
                                offset-y
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="date"
                                    label="Fecha de Inicio"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-date-picker v-model="date" @input="fecha_consulta = false"></v-date-picker>
                            </v-menu>
                        </v-flex>

                        <v-flex xs12 md2>
                            <v-menu
                                v-model="fecha_consulta2"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                transition="scale-transition"
                                offset-y
                                min-width="290px"
                            >
                                <template v-slot:activator="{ on }">
                                <v-text-field
                                    v-model="date2"
                                    label="Fecha fin"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-date-picker v-model="date2" @input="fecha_consulta2 = false"></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs12 md6>
                            <v-autocomplete
                                v-model="personal"
                                :items="listaPersonal"
                                label="Personal"
                                prepend-icon="mdi-account-circle"
                                item-text="nombrecompleto"
                                item-value="numero_documento"
                            />
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-btn color="success" dark :href="'reporteinformeusuario/'+personal+'/'+date+'/'+date2" target="_blank">
                                <v-icon>mdi-file-pdf</v-icon>
                                Reporte PDF
                            </v-btn>
                        </v-flex>


                        <template v-if="habilitaReg">
                            <v-flex xs12 md12>
                                <div>
                                    <v-alert type="info" :value="true" outline>
                                        <span>Los puntos rojos indican los días que no se marco salida, por lo que tampoco se lleno el informe</span>
                                    </v-alert>
                                </div>
                            </v-flex>
                            <v-flex xs12 md12>
                                <v-layout wrap>
                                    <v-flex xs12 md2>
                                        <v-menu
                                            v-model="fecha_regularizacion"
                                            :close-on-content-click="false"
                                            :nudge-right="40"
                                            lazy
                                            transition="scale-transition"
                                            offset-y
                                            full-width
                                            min-width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                            <v-text-field
                                                v-model="dater"
                                                label="Fecha de Consulta"
                                                prepend-icon="event"
                                                readonly
                                                v-on="on"
                                            ></v-text-field>
                                            </template>
                                            <v-date-picker v-model="dater" @input="fecha_regularizacion = false" 
                                                :events="functionEvents"

                                            ></v-date-picker>
                                        </v-menu>
                                    </v-flex>
                                    <v-flex xs12 md2>
                                        <v-btn color="info" @click="d_actividades = true" :disabled="asistenciaID == null">
                                            <v-icon>search</v-icon>
                                            Regularizar
                                        </v-btn>
                                    </v-flex>
                                </v-layout>
                                <v-data-table
                                :headers="headers"
                                :search="search"
                                :items="datos"
                                :loading="loading"
                                :pagination.sync="pagination"
                                >
                                <template slot="items" slot-scope="props">
                                    <td v-for="header in headers" v-bind:key="header.text">
                                        <template>
                                            {{props.item[header.value]}}
                                        </template>
                                    </td>
                                    <td class="justify-center px-0">
                                    
                                    </td>
                                </template>
                                <template slot="no-data">
                                    No se obtuvo ningun registro...
                                </template>
                                </v-data-table>
                            </v-flex>
                        </template>
					</v-layout>
                </v-container>
    
			</v-card>
            <snackbar :snack="snack" />

            <v-dialog v-model="d_actividades" max-width="800" transition="dialog-transition">
                <v-card>
                    <v-toolbar color="red darken-1" dark>
                        <v-toolbar-title>INFORME DE CUMPLIMIENTO DE LABOR EFECTIVA - TRABAJO REMOTO </v-toolbar-title>
                    </v-toolbar>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-alert
                                :value="true"
                                color="primary"
                                icon="new_releases"
                                >
                                Agregue 1 o MÁS actividades y/o tareas realizadas el día de hoy, considerando el tiempo que le tomo en MINUTOS
                            </v-alert>
                            <v-layout wrap>
                                <v-flex xs10 sm7>
                                    <v-text-field label="Actividad" maxlength="350" v-model="act1" clearable counter/>
                                </v-flex>
                                <v-flex xs2 sm3>
                                    <v-text-field label="Tiempo (EN MINUTOS)" v-model="time1" clearable type="number"
                        step="1"/>
                                </v-flex>
                                <v-flex xs2 sm2>
                                    <v-btn color="primary" @click="agregarActividad()"><v-icon>add_circle</v-icon> Agregar</v-btn> 
                                </v-flex>
                                <v-flex xs12 sm12>
                                    <v-data-table
                                        :headers="headers2"
                                        :items="actividades"
                                        hide-actions
                                        class="elevation-1"

                                    >
                                        <template slot="items" slot-scope="props">
                                            <td v-for="header in headers2" v-bind:key="header.text" @click="select === true ? itemSelected(props.item) :''">
                                                <template>
                                                    {{props.item[header.value]}}
                                                </template>
                                            </td>
                                            <td class="justify-center px-0">
                                            
                                            </td>
                                        </template>
                                        <template slot="no-data">
                                            Agregue las actividades realizadas en el día de hoy y click en el boton + AGREGAR
                                        </template>
                                    </v-data-table>
                                    <h2><b>Tiempo total {{tiempo_actividades}} minutos </b></h2>
                                    <template v-if="tiempo_actividades>60">[{{calcula_hora(tiempo_actividades)}}]</template>
                                </v-flex>
                                <v-flex xs12 sm12>
                                    <v-btn block color="primary" dark :disabled="tiempo_actividades == 0" @click="registrarSalidaRemoto()">Registrar Salida</v-btn>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                </v-card>
            </v-dialog>
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
				modulo: 'asistencia',
				drawer:true,
	            date: this.parseDate(new Date().toLocaleDateString()),
                fecha_consulta: false,
                date2: this.parseDate(new Date().toLocaleDateString()),
                fecha_consulta2: false,
                dater: this.parseDate(new Date().toLocaleDateString()),
                fecha_regularizacion: false,
                exporta: false,
                personal: '',
                listaPersonal: [],
                counterClick:0,
                habilitaReg: false,
                pagination: {
					rowsPerPage: 100,
					descending: true,
				},
				headers:[
	               	{text: 'Fecha', value: 'created_at'},
	              	{text: 'descripcion', value: 'descripcion'},
	               	{text: 'Salida', value: 'tiempo'},
	            ],
                datos:[],
                remoteDays:[],
                loading:false,
                d_actividades: false,
	            actividades:[],
                act1:'',
				time1:0,
				headers2:[
	               	{text: 'Actividades o tareas', value: 'actividad'},
	              	{text: 'Tiempo en minutos', value: 'tiempo'},
                       
                ],
                asistenciaID:null,
                snack: {
					snackShow:false,
					snackText: '',
					snackColor: '',
				},   
	    	}
        },
        computed: {
            tiempo_actividades(){
				var tiempo = 0;
				for (let index = 0; index < this.actividades.length; index++) {
					tiempo += this.actividades[index]['tiempo']*1
				}
				return tiempo;
			}
        },
        watch: {
            dater(val){
                this.consultaLabor()
            },
            counterClick(val){
                if (val == 3) {
                    this.habilitaReg = true;
                    this.consultaRemoto();
                    this.consultaLabor()
                }
            }
        },
     	created(){
             this.getPersonal();
	    },
	    methods: {
            functionEvents (date) {
                const [year,month, day] = date.split('-')
                let filtro = this.remoteDays.filter(d => d.fecha == date);
                if(filtro.length > 0){
                    if(filtro[0]['hora_fin'] == null)
                        return 'red';
                    else
                        return 'green';
                }
                
                return false
            },
            getPersonal(){
                axios.get('reporte/listpersonal')
                    .then(response =>{
                        console.log(response);
                        this.listaPersonal = response.data['personal']
                        this.personal = response.data['usuario']
	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.error == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
	                    } else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
	                        this.iniciarDia();
                        }
                        this.loading=false
	                });
            },
            consultaLabor(){
                axios.get('listLabor/' + this.dater)
                    .then(response =>{
                        if(response.data['labor'].length > 0){
                            this.datos = response.data['labor'][0]['actividades']
                            if(this.datos.length == 0)
                            {
                                this.asistenciaID =response.data['labor'][0]['id']
                            }
                        } else{
                            this.datos = [];
                            if(response.data['labor'][0] != undefined && response.data['labor'][0]['id'] != null){
                                this.asistenciaID =response.data['labor'][0]['id']
                            } else{
                                this.asistenciaID = null;
                            }
                        }
	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.error == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
	                    } else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
	                        this.iniciarDia();
                        }
                        this.loading=false
	                });
            },
            consultaRemoto(){
                axios.get('listLaborRemota')
                    .then(response =>{
                        this.remoteDays = response.data['remoto'];
                        
                        /*let allEventos = response.data['remoto']
                        let arraytmp = []
                        allEventos.forEach(function(element){
                            arraytmp.push(element.fecha)
                        })

                        this.remoteDays = arraytmp
                        */
	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.error == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
	                    } else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
	                        this.iniciarDia();
                        }
                        this.loading=false
	                });
            },
            agregarActividad(){
				if(this.act1 != '' && this.act1 != null && this.time1 > 0 && this.time1 != null){
					var item = {
						actividad:this.act1,
						tiempo:this.time1*1
					}
					this.actividades.push(item)
					this.act1 = '';
					this.time1 = 0;
				}
				
            },
            
            registrarSalidaRemoto(){
				axios.post('regularizaActividades/'+this.asistenciaID,  {actividades:this.actividades})
	                .then(response =>{
	                    if (response.data['statusBD']) {
                            this.actividades = []
							this.d_actividades = false;
	                        this.showSnack('success', response.data['messageDB'])
	                        this.consultaLabor();
	                    } else{
	                        this.showSnack('red', response.data['messageDB'])
	                    }
	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.error == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
	                     }
	                     this.consultaLabor();
	                });
			},
	        showSnack(color, text){
	            this.snackColor = color
	            this.snackText = text
	            this.snack = true
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
            calcula_hora(minutos){
				return Math.floor(minutos/60) + 'hora(s) y ' + minutos % 60 + ' minuto(s)';
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
	    }
     }
	
</script>