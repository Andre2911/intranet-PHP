<template>
   	<v-app id="inspire">
				<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
         	<v-card>
                 <v-app-bar dark color="grey">
                    <v-toolbar-title>Asistencia de Personal</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="red accent-2" @click="listarAsistencia()">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                </v-app-bar>
            	<v-card>
                    <v-card-title>
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
                                        label="Fecha de Consulta"
                                        prepend-icon="mdi-calendar"
                                        readonly
                                        v-on="on"
                                    ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="date" @input="fecha_consulta = false"></v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs12 md5 offset-md1>
                                <v-text-field v-model="search" label="Buscar [DNI o nombres]" clearable @keydown.enter="listarAsistencia(1)">
                                </v-text-field>
                            </v-flex>
                            <v-flex xs12 md2>
                                <v-btn color="primary" dark @click="listarAsistencia(1)">
                                    <v-icon>mdi-magnify</v-icon>
                                    Buscar
                                </v-btn>
                            </v-flex>
                            <v-flex xs12 md2>
                                <v-btn color="success" dark :href="'reporteasistencia/'+date" target="_blank">
                                    <v-icon>mdi-file-pdf</v-icon>
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
                    </v-card-title>
                    <dataTable
						:headers="headers"
						:data="datos"
                        :filters="filters"
                        :n_paginas="n_paginas"
                        :itemsPerPage="itemsPerPage"
                        @change_page="listarAsistencia"
                        @editItem="editItem"
                        @viewItem="viewItem"
                        @deleteItem="deleteItem"
			      	/>
                </v-card>
			</v-card>
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
				submenu: 0,
				modulo: 'asistencia',
				drawer:true,
	            asistenciaID: '',
                pagina_actual:1,
                n_paginas:1,
                search:'',
				headers:[
	               	{text: 'Fecha', value: 'fecha'},
	              	{text: 'Ingreso', value: 'hora_inicio'},
	              	{text: 'Inicio Refrigerio', value: 'refrigerio_inicio'},
	              	{text: 'Retorno Refrigerio', value: 'refrigerio_fin'},
                    {text: 'Salida', value: 'hora_fin'},
                    {text: 'Remoto Compl. Ingreso', value: 'hora_inicio2' },
                    {text: 'Remoto Compl. Salida ', value: 'hora_fin2' },
                    {text: 'Tipo', value: 'tipo', type: 'array', array:'1=Trabajo Presencial,2=Trabajo Remoto'},
                    {text: 'DNI', value: 'user_id'},
	              	{text: 'Apellidos y nombre', value: 'persona'},
	              	{text: 'Cargo', value: 'nombre_plaza'},
                       
	            ],
                datos:[],
                itemsPerPage:15,
                loading:true,
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
                nombre_archivo: this.parseDate(new Date().toLocaleDateString())+'.xls',
                filters: {
                },   
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
	    	}
        },
        computed: {
        },
        watch: {
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
            this.listarAsistencia();
	    },
	    methods: {
	        listarAsistencia(pag = 1, perpage = 15){

                this.showLoading('primary', 'Cargando datos', true);
	            var url = 'asistenciaApi?listarAllDay=true&page='+pag + '&date='+this.date
                this.itemsPerPage = perpage;

                this.exporta = false;

                if(perpage == 'all'){
                    this.search = '';
                    this.exporta = true;
                    url = 'asistenciaApi?listarAllDay=true&page=' + pag + '&date='+this.date + '&perPage=all';
                    this.itemsPerPage = 5000;
                } 

                if(this.search != '' && this.search != null){
	                url = 'asistenciaApi?listarAllDay=true&page=' + pag + '&date='+this.date + '&search='+this.search
                } 

                this.loading=true
	            axios.get(url)
	                .then(response =>{
                        this.datos = response.data.asistencias['data']
                        this.n_paginas = response.data.asistencias.last_page;
                        this.loading=false
                        if(perpage == 'all'){
                            this.json_data = this.datos;
                            this.json_fields = {
                                'Fecha': 'fecha',
                                'Ingreso': 'hora_inicio',
                                'Ini. Refrigerio': 'refrigerio_inicio',
                                'Ret. Refrigerio': 'refrigerio_fin',
                                'Salida': 'hora_fin',
                                'Remoto C. Ingreso': 'hora_inicio2',
                                'Remoto C. Salida': 'hora_fin2',
                                'DNI': 'user_id',
                                'Apellidos y nombres': 'persona',
                                'Cargo': 'nombre_plaza',
                                'Modalidad de trabajo': 'tipo_trabajo',
                            };
                        }

                        this.showLoading('primary', '', false);


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
                        this.showLoading('primary', '', false);

                    });

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
            printSection() {
                this.$htmlToPaper("printSection");
            },
            newItem(){},
            editItem(item){},
            viewItem(item){},
            deleteItem(item){},
            save(e){
            },
            close(){
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

            showLoading(color, text, show = true){
				this.dialogLoad.color = color
				this.dialogLoad.text = text
				this.dialogLoad.show = show
            },
            
	    }
     }
	
</script>