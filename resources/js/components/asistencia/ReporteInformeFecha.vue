<template>
   	<v-app id="inspire">
				<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
         	<v-card>
            	<v-toolbar color="grey" dark>
            	   <v-toolbar-title @click="counterClick+=1">Informe de Labor efectiva del Personal</v-toolbar-title>
				</v-toolbar>
				<v-container id="contentApp" grid-list-md>
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
                        
                        <v-flex xs12 md2>
                            <v-btn color="success" dark :href="'reporteinformedia2/'+date" target="_blank">
                                <v-icon>mdi-file-pdf</v-icon>
                                Reporte PDF
                            </v-btn>
                        </v-flex>
					</v-layout>
                </v-container>
    
			</v-card>
            <snackbar :snack="snack" />
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
				modulo: 'asistencia',
				drawer:true,
	            snack: false,
	            snackText: '',
	            search:'',
	            snackColor: '',
                date: this.parseDate(new Date().toLocaleDateString()),
                fecha_consulta: false,
                exporta: false,
	    	}
        },
        computed: {
            
        },
        watch: {
            date(val){
                //this.listarAsistencia(1)
            },
        },
     	created(){
	    },
	    methods: {
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