<template>
   	<div id="app">
      	<v-app>
         	<v-card>
            	<v-toolbar color="red darken-4" dark>
            	   <v-toolbar-title>Asistencia de Personal</v-toolbar-title>
				</v-toolbar>
				<v-content id="contentApp" grid-list-md>
					<v-layout wrap>
                        <v-flex xs12 md2>
                            <v-menu
                                v-model="fecha_consulta"
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
                                    v-model="date"
                                    label="Fecha de Consulta"
                                    prepend-icon="event"
                                    readonly
                                    v-on="on"
                                ></v-text-field>
                                </template>
                                <v-date-picker v-model="date" @input="fecha_consulta = false"></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs12 md5 offset-md1>
                            <v-text-field v-model="search" clearable @keydown.enter="listarAsistencia(1)">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-btn color="primary" dark @click="listarAsistencia(1)">
                                <v-icon>search</v-icon>
                                Buscar
                            </v-btn>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-btn color="success" dark :href="'reporteasistencia/'+date" target="_blank">
                                <v-icon>file_copy</v-icon>
                                Reporte PDF
                            </v-btn>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-btn color="info" @click="listarAsistencia(1,'all')">Mostrar todo</v-btn>
                        </v-flex>
                        <v-flex xs12 md2>
                            <download-excel
                                 :data   = "json_data"
                                 :fields = "json_fields"
                                 :name    = "nombre_archivo"
                                 >
                                    <v-btn 
                                       color="success"                                       
                                       :disabled="!exporta"
                                    >
                                       <i class="fa fa-file-excel-o"></i>
                                        &nbsp; Descargar XLS
                                    </v-btn>
                           </download-excel>
                        </v-flex>
					</v-layout>
                    <v-data-table
						:headers="headers"
						:items="datos"
    					:loading="loading"
                        :pagination.sync="pagination"
                        hide-actions
			      	>
				      	<template slot="items" slot-scope="props">
				            <td v-for="header in headers" @click="select === true ? itemSelected(props.item) :''">
				                <template v-if="header.value=='tipo'">
				                	<template v-if="props.item[header.value] == 1 || props.item[header.value] == '1'">
                                        Trabajo Presencial
                                    </template>
                                    <template v-else>
                                        Trabajo Remoto
                                    </template>
				               	</template>
				                <template v-else>
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
                    <div class="text-xs-center pt-2">
                        <v-pagination v-model="page_pagination" :length="pages"></v-pagination>
                    </div>
    			</v-content>
    
			</v-card>
			<v-snackbar v-model="snack" top="top" right="right" :timeout=" 3000" :color="snackColor">
               {{ snackText }}
	            <v-btn flat @click="snack = false">Close</v-btn>
	         </v-snackbar>
		</v-app>
	</div>
</template>

<script>
	export default {
      	props:[],
      	data() {
         	return {
	            asistenciaID: '',
	            snack: false,
	            snackText: '',
	            search:'',
	            snackColor: '',
				headers:[
	               	{text: 'Fecha', value: 'fecha'},
	              	{text: 'Ingreso', value: 'hora_inicio'},
                    {text: 'Salida', value: 'hora_fin'},
                    {text: 'Tipo', value: 'tipo'},
	              	{text: 'Apellidos y nombre', value: 'persona'},
                       
	            ],
	            datos:[],
                loading:true,
                pagination: {
                    rowsPerPage: 25,
                    descending:true
                },
                page_pagination:1,
                date: this.parseDate(new Date().toLocaleDateString()),
                fecha_consulta: false,
                exporta: false,
                json_fields: {},
                json_data: [],
                json_meta: [
                [
                    {
                        'key': 'charset',
                        'value': 'utf-8'
                    }
                ]
                ],
                nombre_archivo: this.parseDate(new Date().toLocaleDateString())+'.xls'
	    	}
        },
        computed: {
            pages () {
                if (this.pagination.rowsPerPage == null ||
                    this.pagination.totalItems == null
                ) return 0

                return Math.ceil(this.pagination.totalItems / this.pagination.rowsPerPage)
            }
        },
        watch: {
            page_pagination(val){
                this.listarAsistencia(val);
            },
            date(val){
                this.listarAsistencia(1)
            },
            search(val){
                if(val == null){
                    this.listarAsistencia();
                }
            }
        },
     	created(){
            //this.listarAsistencia();
	    },
	    methods: {
	        listarAsistencia(pag = 1, perpage = 25){
	            var url = '../asistenciaApi?listarAllDay=true&page='+pag + '&date='+this.date

                this.exporta = false;

                if(perpage == 'all'){
                    this.search = '';
                    this.exporta = true;
	                url = '../asistenciaApi?listarAllDay=true&page=' + pag + '&date='+this.date + '&perPage=all';
                } 

                if(this.search != '' && this.search != null){
	                url = '../asistenciaApi?listarAllDay=true&page=' + pag + '&date='+this.date + '&search='+this.search
                } 

                this.loading=true
	            axios.get(url)
	                .then(response =>{
                        this.datos = response.data.asistencias['data']
                        this.pagination.totalItems = response.data.asistencias['total'];
                        this.pagination.rowsPerPage = response.data.asistencias['per_page'];
                        this.loading=false
                        if(perpage == 'all'){
                            this.json_data = this.datos;
                            this.json_fields = {
                                'Fecha': 'fecha',
                                'Ingreso': 'hora_inicio',
                                'Salida': 'hora_fin',
                                'Apellidos y nombres': 'persona',
                                'Modalidad de trabajo': 'tipo_trabajo',
                            };
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
	    }
     }
	
</script>