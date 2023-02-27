<template>
   	<div id="app">
      	<v-app>
         	<v-card>
            	<v-toolbar color="red darken-4" dark>
            	   <v-toolbar-title>Activación y Bloqueo de usuarios de asistencia</v-toolbar-title>
				</v-toolbar>
				<v-content id="contentApp" grid-list-md>
					<v-layout wrap>
                        <v-flex xs12 md5>
                            <v-text-field v-model="search" clearable @keydown.enter="listarBloqueos(1)">
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-btn color="primary" dark @click="listarBloqueos(1)">
                                <v-icon>search</v-icon>
                                Buscar
                            </v-btn>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-btn color="success" @click="d_personal=true">
                                <v-icon>
                                    add_circle
                                </v-icon>
                                Agregar bloqueo
                            </v-btn>
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
				            <td v-for="header in headers">
				                <template v-if="header.value=='active_block'">
                                    <template v-if="props.item[header.value]*1 == 0">
                                        <v-chip color="primary" dark @click="update_lock(props.item)"> 
                                            Activo
                                        </v-chip>
                                    </template>
                                    <template v-else>
                                        <v-chip color="red" dark @click="update_lock(props.item)">
                                            Bloqueado
                                        </v-chip>
                                    </template>
				               	</template>
                                <template v-else-if="header.value=='options'">
                                    <v-btn color="info" @click="update_lock(props.item)" flat>
                                        <v-icon>edit</v-icon>
                                        <template v-if="props.item['active_block']*1 == 0">
                                            Bloquear
                                        </template>
                                        <template v-else>
                                            Desbloquear
                                        </template>
                                    </v-btn>
                                </template>
				                <template v-else>
				                	{{props.item[header.value]}}
				               	</template>
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
        <v-dialog v-model="d_personal" max-width="800">
            <select-personal-component @selectPersonal="lockUser"/>
        </v-dialog>
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
	               	{text: 'DNI', value: 'user_id'},
	              	{text: 'Apellidos y nombre', value: 'persona'},
                    {text: 'Estado', value: 'active_block'},                       
                    {text: 'Opciones', value: 'options'},                       
	            ],
                datos:[],
                d_personal:false,
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
                this.listarBloqueos(val);
            },
            date(val){
                this.listarBloqueos(1)
            },
            search(val){
                if(val == null){
                    this.listarBloqueos();
                }
            }
        },
     	created(){
            this.listarBloqueos();
	    },
	    methods: {
	        listarBloqueos(pag = 1, perpage = 25){
	            var url = '../userLockApi?listarBloqueo=true&page='+pag + '&date='+this.date

                this.exporta = false;

                if(perpage == 'all'){
                    this.search = '';
                    this.exporta = true;
	                url = '../userLockApi?listarBloqueo=true&page=' + pag + '&date='+this.date + '&perPage=all';
                } 

                if(this.search != '' && this.search != null){
	                url = '../userLockApi?listarBloqueo=true&page=' + pag + '&date='+this.date + '&search='+this.search
                } 

                this.loading=true
	            axios.get(url)
	                .then(response =>{
                        this.datos = response.data.usuarios['data']
                        this.pagination.totalItems = response.data.usuarios['total'];
                        this.pagination.rowsPerPage = response.data.usuarios['per_page'];
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
            lockUser(item){
                axios.post('../userLockApi?Bloquear=true', item)
                    .then(response =>{
                        if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
							this.d_personal = false;
	                        this.listarBloqueos();
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
	                    } else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
	                        this.iniciarDia();
                        }
                        this.loading=false
	                });
            },
            update_lock(item){
                console.log(item)
                axios.put('../userLockApi/'+item.id, item)
                    .then(response =>{
                        if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
	                        this.listarBloqueos();
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