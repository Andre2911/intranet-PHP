<template>
   
   <v-card>
      <v-text-field
         v-model="search"
         append-icon="mdi-magnify"
         label="Buscar"
         :autofocus=true
         single-line
         hide-details
         ref="search"
      ></v-text-field>
      
      <v-data-table
         :headers="headers"
         :items="datos"
         :search="search"
         :options="pagination_l"
         :loading="loading"
      >
         <template slot="items" slot-scope="props">
            <td v-for="header in headers" @click="select === true ? itemSelected(props.item) :''">
               <template v-if="header.text=='Opciones'">
                  <v-icon 
                     v-for="opcion in splitOpciones(header.value)" 
                     small
                     class="mr-2"
                     @click="opciones(props.item, opcion)"
                     v-bind:key="opcion.value"
                  >
                           {{opcion}}
                  </v-icon>
               </template>
               <template v-else-if="header.type=='boolean'">
                  <v-checkbox :input-value="props.item[header.value]" readonly color="teal"></v-checkbox>
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
         <v-alert slot="no-results" :value="true" color="error" icon="warning">
            La busqueda para "{{ search }}" no obtuvo resultados.
         </v-alert>

         <template slot="pageText" slot-scope="props">
            Resultados {{ props.pageStart }} - {{ props.pageStop }} de {{ props.itemsLength }}
         </template>
      </v-data-table>
   </v-card>

</template>

<script >
  export default {
      props: ['datos', 'headers', 'pagination', 'usuario_id', 'select'],
      data() {
         return {
            search: '',
            pagination_l:{},
            loading: true,
         }
      },
      computed: {
         
      },
      watch: {
      },
      created () {
         this.pagination_l = this.pagination
         this.getData()
      },
      methods: {
         getData(){
            this.loading = false
         },
            
            
         getColor(estado){
            let color = 'warning';
            switch(estado){
               case 0: color='info'; break;
               case 1: color='success'; break;
               case 2: color='warning'; break;
               case 3: color='error'; break;
            }
            return color;
         },
         getEstado(estado){
            let estadomsg = 'warning';
            switch(estado){
               case 0: estadomsg = 'Registrado'; break;
               case 1: estadomsg = 'Programado'; break;
               case 2: estadomsg = 'Pendiente'; break;
               case 3: estadomsg = 'Rechazado'; break;
            }
            return estadomsg;
         }, 
         splitOpciones (splitOpciones) {
            if (!splitOpciones) return null
            const lista_opciones = splitOpciones.split(',')
            return lista_opciones;
         },
         opciones(item, opcion){
            switch(opcion){
               case 'visibility': this.$emit('viewItem', item); break;
               case 'edit': this.$emit('editItem', item); break;
               case 'delete': this.$emit('deleteItem', item) ; break;
            }
         },
         itemSelected(item){
            this.$emit('seleccion', item);
         }
      }
   };

</script>