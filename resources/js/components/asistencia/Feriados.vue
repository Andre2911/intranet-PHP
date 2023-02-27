<template>
	<v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
			<v-card>
				<v-app-bar dark color="grey">
                    <v-toolbar-title>Feriados</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="info" @click="listarData()">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                    <v-btn @click="d_feriado = true" color="red darken-3">
                        <v-icon>mdi-plus</v-icon>
                        Agregar Feriado
                    </v-btn>
                </v-app-bar>
                <div id="printSection">
                    <v-card>
                        <dataTableFull
                            :data="filteredData"
                            :headers="headers"
                            :filters="filters"
                            :n_paginas="n_paginas"
                            :itemsPerPage="itemsPerPage"
                            :tipo=1
                            @change_page="listarData"
                            @editItem="editItem"
                            @viewItem="viewItem"
                            @deleteItem="deleteItem"
                        />
                    </v-card>
                </div>
            </v-card>
        </v-main>
        <v-dialog 
            v-model="d_feriado"
            scrollable  
            :overlay="false"
            max-width="800px"
            transition="dialog-transition"
        >
            <v-card>
                <v-toolbar color="red darken-1" dark>
                    <v-toolbar-title>Agregar feriado</v-toolbar-title>
                </v-toolbar>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="3" xs="12" sm="4">
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
                                    v-model="activeItem.f_inicio"
                                    label="Fecha"
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
                        </v-col>
                        <v-col cols="12" md="5" xs="12" sm="4">
                            <v-text-field
                                v-model="activeItem.descripcion"
                                label="Descripción"
                            />

                        </v-col>
                        <v-col cols="12" md="2" xs="12" sm="4">
                            <v-switch
                                v-model="activeItem.asistencia_pre"
                                label="Asistencia presencial"
                            />
                        </v-col>  
                        <v-col cols="12" md="2" xs="12" sm="4">
                            <v-switch
                                v-model="activeItem.asistencia_rem"
                                label="Asistencia remota"
                            />
                        </v-col>  
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                        <v-btn color="success" @click="save()"><v-icon>mdi-content-save</v-icon> {{btn_registrar}}</v-btn>
                        <v-btn color="red" dark @click="d_feriado = false"><v-icon>mdi-close</v-icon> Cancelar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <snackbar :snack="snack" ></snackbar>
        <footer-component/>
    </v-app>
</template>

<script>
	export default {
   		props:['dataUser'],
      	data() {
         	return {
	            menu: this.hasRole('Asistencia.administrador')? 3: 5,
                submenu: -1,
                modulo: 'asistencia',
                drawer:true,
                snack: {
                    snackShow:false,
                    snackText: '',
                    snackColor: '',
                },
                pagina_actual:1,
                n_paginas:1,
                search:'',
                headers: [
                    { text: 'Fecha', value:'fecha'},
                    { text: 'Descripción', value:'descripcion'},
                    { text: 'Asistencia presencial', value:'asistencia_pre', type:'boolean_t'},
                    { text: 'Asistencia remota', value:'asistencia_rem', type:'boolean_t'},
                    { text: 'Opciones', value: 'delete', acciones: 'registrar', type:'opciones' }
                ],
                itemsPerPage:15,
                data: [
                ],
                activeItem:{
                },
                datetime_ini: false,
                date_inicio: this.parseDate(new Date().toLocaleDateString()),
                filters: {
                },
                d_feriado:false
	    	}
     	},
        computed: {
            filteredData() {
                if(this.data.length > 0){
                    return this.data.filter(d => {
                        return Object.keys(this.filters).every(f => {
                            return this.filters[f].length < 1 || this.filters[f].includes(d[f])
                        })
                    })
                }
            }, 
            btn_registrar(){
                return this.activeItem.id > 0 ? 'Actualizar Datos': 'Registrar'
            },
        },
        watch:{
            date_inicio (val) {
                this.activeItem.f_inicio = this.formatDate(this.date_inicio)
            },
            d_feriado(val){
                this.activeItem = {}
                this.activeItem.f_inicio = this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10)))
                this.activeItem.asistencia_pre = false
                this.activeItem.asistencia_rem = false
                this.date_inicio = this.parseDate(new Date().toLocaleDateString());
            }
        },
        created() {
            this.listarData();
        },
        methods: {
            listarData(pag=1, search=null, perpage=15){
                this.itemsPerPage = perpage;
                var url = 'getFeriados';
                this.pagina_actual = pag;

                axios.get(url)
                    .then(response =>{
                        this.data = response.data.asistencias.data;
                        this.n_paginas = response.data.asistencias.last_page;
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
                            //this.listarData();
                        }
                    });
            },
            save(){
                if(this.activeItem.descripcion != undefined && this.activeItem.descripcion != null && this.activeItem.descripcion != ''){
                    axios.post('setFeriado', this.activeItem)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.listarData();
                                    this.showSnack('success', response.data['messageDB'])
                                    this.d_feriado = false;
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
                } else{
                    this.showSnack('error', 'Debe indicar la descripción del día feriado')
                }
                
            },
            newItem(){},
            editItem(item){},
            viewItem(item){},
            deleteItem(item){
                if (item.id!= null){
                axios.put('setFeriado/'+item.id)
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.listarData();
                            this.showSnack('success', response.data['messageDB'])
                        } else{
                            this.showSnack('error', response.data['messageDB'])
                        }
                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (rrors.response != undefined && errors.response.status === 401) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
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
            hasRole(role){
                var roles = role.split('|');
                for (let index = 0; index < roles.length; index++) {
                    if (this.dataUser.modulos.includes(roles[index])) {
                        return this.dataUser.modulos.includes(roles[index]);
                    }
                } 
                return false;
            },
        }
    }
</script>