<template>
   <v-card class="mx-auto">
      <v-app-bar color="red darken-4" dark>
         <v-toolbar-title>Personal de la Corte Superior de Justicia de Puno</v-toolbar-title>
      </v-app-bar>
         <v-card>
            <v-card-title>
               <v-text-field
                     v-model="search"
                     label="Buscar"
                     single-line
                     hide-details
                     @keydown.enter="getData(1, search)"
               ></v-text-field>
               <v-btn color="info" @click="getData(1, search)">
                     <v-icon>mdi-magnify</v-icon>
                     Buscar
               </v-btn>
            </v-card-title>
            <dataTable
               :data="datos"
               :headers="headers"
               :filters="filters"
               :n_paginas="n_paginas"
               :pagina_selected="pagina_actual"
               :dense="true"
               @change_page="getData"
               @selectItem="selectItem"
            />
         </v-card>
         <v-dialog v-model="dialogLoad" persistent :width="290">
            <v-card dark>
               <v-card-text>
                  Cargando datos...
                  <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
               </v-card-text>
            </v-card>
      </v-dialog>
   </v-card>
</template>
<script>
   export default {
      data(){
         return {
            pagination: {
               rowsPerPage: 5,
            },
            search:'',
            headers:[
               {text: 'DNI', value: 'numero_documento'},
               {text: 'Apellido Paterno', value: 'nombrecompleto'},
               { text: 'Opciones', value: 'select', acciones: 'registrar', type:'opciones' }

            ],
            pagina_actual:1,
            n_paginas:1,
            dialogLoad: true,
            datos: [],
            activeItem:{},
            defaultItem:{
                tipo_documento_id:null,
                numero_documento:null,
            },
            editedItem:-1,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            filters:{}
         }
      },
      computed: {
         
      },
      created() {
         this.getData()
      },
      methods: {
         getData(pag=1, search=null){
            var url = 'personaApi?listar=true&page='+pag;
            this.pagina_actual = pag;

            if(search != null || this.search != ''){
                url = 'personaApi?listar=true&page='+pag+'&search='+this.search;
            }
            axios.get(url)
                .then(response =>{
                    this.datos = response.data.personas.data
                    var n_resultados = response.data.personas.total;
                    this.n_paginas = response.data.personas.last_page;

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                     }
                     this.getDocumentos();
                });
         },
         selectItem(item){
            this.$emit('selectedItem',item);
        },
      }
   };
</script>