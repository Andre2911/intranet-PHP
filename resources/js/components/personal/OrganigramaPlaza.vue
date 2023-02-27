<template>
    <v-card>
        <v-app-bar dark color="red darken-4">
            <v-toolbar-title>Plazas disponibles</v-toolbar-title>
            <v-spacer></v-spacer>
        </v-app-bar>
        <v-card-text>
            <dataTable
                :data="filteredData"
                :headers="headers"
                :filters="filters"
                :tipo="2"
                :n_paginas="n_paginas"
                @change_page="getPlazas"
                @selectItem="seleccion"
            />
            
        </v-card-text>
    </v-card>
</template>
<script>
export default {
    props:['data-user'],
        data(){
            return {
                pagina_actual:1,
                n_paginas:1,
                headers:[
                    {text: 'Código', value: 'c_plaza'},
                    {text: 'Detalle', value: 'nombre_plaza'},
                    {text: 'Ubicación Física', value: 'nombre_oficina'},
                    {text: 'Régimen', value: 'regimen_completo'},
                    {text: 'Opciones', value: 'select', type:'opciones' }
                ],
                dialogLoad: true,
                data: [],
                filters: {},  
                snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            }
        },
        computed: {
            filteredData() {
                return this.data.filter(d => {
                    return Object.keys(this.filters).every(f => {
                        return this.filters[f].length < 1 || this.filters[f].includes(d[f])
                    })
                })
            }
        },
        created(){
            this.getPlazas()               
        },
        methods: {
            getPlazas(url='plazasApi?fisicasDisp=true'){
                axios.get(url)
                  .then(response =>{
                     this.data = response.data['plazas'];
                  })
                  .catch(errors =>{
                     this.getData();
                     console.log(errors);
                  });
            },
            seleccion(item){
                this.$emit('selectPlaza', item)
            }
        }
}
</script>