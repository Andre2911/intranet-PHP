<template>
	<v-app id="inspire">
				<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
			<v-card>
				<v-toolbar color="red darken-4" dark>
					<v-toolbar-title>Asistencia de Personal</v-toolbar-title>
				</v-toolbar>
				<v-content id="contentApp">
					<v-text-field v-model="search"></v-text-field>
					<v-data-table
						:headers="headers"
						:search="search"
						:items="datos"
						:loading="loading"
						:pagination.sync="pagination"
					>
						<template slot="items" slot-scope="props">
							<td v-for="header in headers" v-bind:key="header.text" @click="select === true ? itemSelected(props.item) :''">
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

				</v-content>

			</v-card>
			<v-snackbar v-model="snack" top="top" right="right" :timeout=" 3000" :color="snackColor">
				{{ snackText }}
				<v-btn flat @click="snack = false">Close</v-btn>
			</v-snackbar>
		</v-main>
	</v-app>
</template>

<script>
	export default {
   		props:['dataUser'],
      	data() {
         	return {
	            asistenciaID: '',
	            snack: false,
	            snackText: '',
				pagination: {
					rowsPerPage: 100,
					descending: true,
				},
	            search:'',
	            snackColor: '',
				headers:[
	               	{text: 'Fecha', value: 'fecha'},
	              	{text: 'Ingreso', value: 'hora_inicio'},
	              	{text: 'Ref. salida', value: 'refrigerio_inicio'},
	              	{text: 'Ref. retorno', value: 'refrigerio_fin'},
	               	{text: 'Salida', value: 'hora_fin'},
	            ],
	            datos:[],
	            loading:true,
	    	}
     	},
     	created(){
        this.listarAsistencia();
	    },
	    methods: {
	        listarAsistencia(){
	            var url = 'asistenciaApi?listar=true'
	            axios.get(url)
	                .then(response =>{
	                	this.datos = response.data.asistencias['data']
	                	this.loading=false
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
	                });
	        },
	        showSnack(color, text){
	            this.snackColor = color
	            this.snackText = text
	            this.snack = true
	         },
	    }
     }
	
</script>