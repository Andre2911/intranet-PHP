<template>
   
   <v-card v-resize="onResize">
      <v-text-field
         v-model="search"
         append-icon="mdi-magnify"
         label="Buscar"
         :autofocus=true
         single-line
         hide-details
         color="cyan"
      ></v-text-field>
      
      <v-data-table
         :headers="headers"
         :items="datos"
         :search="search"
         :options="pagination_l"
         :loading="loading"
      >
         <template v-slot:top>
            <v-container grid-list-md>
                  <v-layout wrap>
                     <div
                        v-for="header in headers"
                        :key="header.value"
                     >
                        <v-flex xs12 v-if="filters.hasOwnProperty(header.value)">
                              <v-select prepend-icon="mdi-filter" :label="header.text" hide-details x-small dense multiple clearable :items="columnValueList(header.value)" v-model="filters[header.value]">
                              </v-select>
                        </v-flex>
                        
                     </div>
                  </v-layout>
            </v-container>
         </template>

         <template slot="items" slot-scope="props">
            <tr v-if="!isMobile">
               <td v-for="header in headers" @click="select === true ? itemSelected(props.item) :''">
                  <template v-if="header.text=='Opciones'">
                     <template v-for="opcion in splitOpciones(header.value)" >
                        <v-icon 
                           v-if="props.item.estado_inicio == 0 && opcion == 'delete' && props.item.autorizacion_ji == 0 && props.item.idusuario == usuario_id"
                           class="mr-2"
                           @click="opciones(props.item, opcion)"
                           v-bind:key="opcion.value"
                        >
                                 {{opcion}}
                        </v-icon>
                        <v-icon 
                           v-else-if="opcion != 'delete'"
                           color="success"
                           class="mr-2"
                           @click="opciones(props.item, opcion)"
                           v-bind:key="opcion.value">
                                 {{opcion}}
                        </v-icon>
                     </template>
                  </template>
                  <template v-else-if="header.type=='archivo'">
                     <v-icon>attach_file</v-icon>
                  </template>
                  <template v-else-if="header.value == 'estado'">
                     <v-chip :color="getColor(props.item.estado)" dark>
                        {{ getEstado(props.item.estado) }}
                     </v-chip>
                  </template>
                  <template v-else-if="header.type == 'date'">
                        {{ formatDate(props.item[header.value]) }}
                  </template>
                  <template v-else-if="header.type=='check'">
                     <v-checkbox color="deep-purple" v-model="props.item[header.value]" />
                  </template>
                  <template v-else-if="header.type=='boolean'">
                     <v-chip :color="getColor(props.item[header.value])" dark>
                        {{ getEstado(props.item[header.value]) }}
                     </v-chip>
                  </template>
                  <template v-else-if="header.type=='chip'">
                     <v-chip color="primary" dark>
                        {{props.item[header.value]}}
                     </v-chip>
                  </template>
                  <template v-else-if="header.type=='time'">
                     <template v-if="props.item[header.value] == null "> 
                        Esperando...
                     </template>
                     <template v-else>
                        {{formatTime(props.item[header.value])}}   
                     </template>
                  </template>
                  <template v-else>
                     {{props.item[header.value]}}
                  </template>
               </td>
               <td class="justify-center px-0">
                  
               </td>
            </tr>
            <tr v-else>
               <td v-for="header in headers" @click="select === true ? itemSelected(props.item) :''">
                  <template v-if="header.text=='Opciones'">
                  </template>
                  <template v-else>
                     <b>{{header.text}}</b> : <br/> 
                  </template>
                  <template v-if="header.type=='boolean'">
                     <v-chip :color="getColor(props.item[header.value])" dark>
                        {{ getEstado(props.item[header.value]) }}
                     </v-chip>
                  </template>
                  <template v-else-if="header.type=='chip'">
                     <v-chip color="primary" dark>
                        {{props.item[header.value]}}
                     </v-chip>
                  </template>
                  
                  <template v-else-if="header.type=='time'">
                     <template v-if="props.item[header.value] == null "> 
                        Esperando...
                     </template>
                     <template v-else>
                        {{formatTime(props.item[header.value])}}   
                     </template>
                  </template>
                  <template v-else-if="header.type=='archivo'">
                     <v-icon>file</v-icon>
                  </template>
                  <template v-else-if="header.type=='autorizar'">
                     <template v-if="props.item.idusuario != props.item.id_jefe && props.item.autorizacion_ji == '0'">
                        <v-btn small color="primary" dark @click="autorizar(props.item, 1)">
                           Autorizar
                        </v-btn>
                        <br/>
                        <v-btn small color="warning" dark @click="autorizar(props.item, 2)">
                           Rechazar
                        </v-btn>   
                     </template>
                  </template>
                  <template v-else-if="header.type=='extra'">
                     <v-chip color="info" dark>
                        Asignar local
                     </v-chip>
                  </template>
                  <template v-else>
                     {{props.item[header.value]}}
                  </template>
               </td>
               <td class="justify-center px-0">
                  
               </td>
            </tr>
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
      props: ['datos', 'headers', 'pagination', 'usuario_id', 'select', 'loading', 'allData', 'filters'],
      data() {
         return {
            search: '',
            pagination_l:{},
            isMobile: false,
         }
      },
      computed: {
         
      },
      watch: {

      },
      created () {
         this.pagination_l = this.pagination
         if (window.screen.width < 769){
            this.isMobile = true;
         }
         else{
            this.isMobile = false;
         }
      },
      methods: {   
         formatTime(hora){
            return hora.substr(0,8)
         },
         formatDate(datetime){
            if (datetime != null) {
               return datetime.substr(0,10);   
            }
         },
         columnValueList(val) {
            return this.allData.map(d => d[val])
         },
         onResize() {
            if (window.screen.width < 769)
               this.isMobile = true;
            else
               this.isMobile = false;
         },
      },
      

   };

</script>