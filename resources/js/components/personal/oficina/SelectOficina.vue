<template>
   <v-card>
      <v-toolbar color="teal" dark>
         <v-toolbar-title>Oficinas de la Corte Superior de Justicia de Puno</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
         <dataTable
            :headers="headers"
            :data="datos"    
            :filters="filters"
            :n_paginas="n_paginas"
            :pagina_selected="pagina_actual"
            :dense="true"
            @change_page="getData"
            @selectItem="selectItem"
            :tipo="2"
         />
      </v-card-text>
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
            headers:[
               {text: 'ID', value: 'id'},
               {text: 'Descripción', value: 'nombre_oficina'},
               {text: 'Distrito', value: 'distrito'},
               { text: 'Opciones', value: 'select', acciones: 'registrar', type:'opciones' }

            ],
            dialogLoad: true,
            datos: [],
            filters:{},
            pagina_actual:1,
            n_paginas:1,
         }
      },
      computed: {
         
      },
      created() {
         this.getData()
      },
      methods: {
         getData(url = 'oficinasApi?listarfull=true'){
            axios.get(url)
                  .then(response =>{
                     this.datos = response.data['oficinas'];
                     this.loading = false;
                     this.dialogLoad = false;
                  })
                  .catch(errors =>{
                     console.log(errors);
                     this.getData();
                  });
         },
         selectItem(item){
            this.$emit('selectOficina', item)
         }
      }
   };
</script>