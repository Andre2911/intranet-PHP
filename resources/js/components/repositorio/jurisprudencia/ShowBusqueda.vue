<template>
   <v-card>
      <v-flex xs12 md12>
         <v-container>
            <template v-for="resolucion in datos" >
               <v-flex xs12 sm12 md12 lg12 v-bind:key="resolucion.id">
                  <v-card >
                     <v-card-title class="pb-0 pt-0">
                        <v-list two-line :dense=false>
                           <v-list-item-group
                              multiple
                              active-class="red--text"
                           >
                              <v-list-item>
                                 <v-list-item-content>
                                    <v-list-item-title>
                                       <div class="row">
                                          <div class="col-md-3 text-justify">
                                             <b>Expediente {{ resolucion.x_formato }} : 
                                                   {{ resolucion.x_desc_acto_procesal }} {{ resolucion.asunto }}</b>
                                          </div>
                                       </div>
                                    </v-list-item-title>
                                    <v-list-item-subtitle class="text--primary">Materia: <b> {{resolucion.x_desc_materia}} </b>
                                    </v-list-item-subtitle>
                                    <v-list-item-subtitle>
                                          Demandante: {{partes(resolucion.txt_demandante)}} <br/>
                                    </v-list-item-subtitle>
                                    <v-list-item-subtitle class="text-justify">
                                          <span class="text-justify">
                                             Demandado: {{partes(resolucion.txt_demandado)}}
                                          </span>
                                    </v-list-item-subtitle>
                                 </v-list-item-content>
                              </v-list-item>
                           </v-list-item-group>
                        </v-list>
                     </v-card-title>
                     <v-divider></v-divider>
                           <v-card-text>
                              <b> Fecha de Publicación : {{ nombremes(resolucion.mes_res)}} / {{resolucion.anio_res}} </b>
                              <div class="row">
                                 <div class="col-md-3">
                                    <template v-if="resolucion.r_archivopdf != null" >
                                       <a :href="'../'+resolucion.r_archivopdf" target="_blank"><img :src="'../public/image/pic09.png'" width="70px"  style="border-radius: 10px;" class="ml-4" /></a>   
                                    </template>
                                    <a :href="'../'+resolucion.r_archivoword" target="_blank"><img :src="'../public/image/microsoft-word.png'" width="70px"  style="border-radius: 10px;" class="ml-4" /></a>
                                 </div>
                                 <div class="col-md-9 text-justify">
                                    {{ resumen(resolucion.crawlerpdf) }}
                                 </div>     
                              </div>
                           </v-card-text>
                  </v-card>
                  <v-divider></v-divider>
               </v-flex>
            </template>
            <div class="text-xs-center pt-2">
               <v-pagination v-model="pagina_actual" :length="n_paginas"></v-pagination>
             </div>
         </v-container>
      </v-flex>
   </v-card>

</template>

<script >
  export default {
      props: ['datos', 'headers', 'pagination', 'usuario_id', 'select', 'loading','totalDatos','options', 'palabras','modo_busq', 'n_resultados', 'n_paginas'],
      data() {
         return {
            search: '',
            pagina_actual: 1,
            
         }
      },
      computed: {
         
      },
      watch: {
         pagina_actual(val){
            this.$emit('buscarRes', val);
         }
      },
      created () {
     
      },
      methods: {
         resumen(contenido){
            var inicio = 99999999;
            if (this.modo_busq != 3) {
               var preposiciones = ["a", "ante", "bajo",'con', 'contra', 'de', 'desde', 'en','entre', 'hacia', 'hasta', 'para', 'por', 'según', 'sin', 'so', 'sobre', 'tras', 'durante', 'mediante', 'versus', 'via', 'y', "A", "ANTE", "BAJO",'CON', 'CONTRA', 'DE', 'DESDE', 'EN','ENTRE', 'HACIA', 'HASTA', 'PARA', 'POR', 'SEGÚN', 'SIN', 'SO', 'SOBRE', 'TRAS', 'DURANTE', 'MEDIANTE', 'VERSUS', 'VIA', 'Y'];
               for (var i = 0; i < this.palabras.length; i++) {
                  var found = preposiciones.find(prepo => prepo === this.palabras[i]);
                  if (found === undefined) {
                     var pal_m = this.palabras[i].toUpperCase();
                     var cont_ma = contenido.toUpperCase();
                     var index_ma = cont_ma.indexOf(pal_m);
                     if (index_ma < inicio && index_ma > 0){
                        
                        inicio = index_ma;
                      }
                   }
               }


                  var nuevo_contenido = contenido.substr(inicio -5, 500);

                  /**/   
            } else{
               var pal_m = this.palabras+'';
               var cont_ma = contenido.toUpperCase();
               var index_ma = cont_ma.indexOf(pal_m.toUpperCase());
               console.log(index_ma);
               var nuevo_contenido = contenido.substr(index_ma -5, 500);
            }
            
            return '...' + nuevo_contenido + '...';
         },

         nombremes(mes){
            var n_mes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            var num_mes = mes*1;

            return n_mes[num_mes-1];
         },
         partes(txt_demandado){
            if (txt_demandado != null) {
               var n_partes = txt_demandado.substr(0,txt_demandado.length-1);
               if (n_partes.length > 100) {
                  return n_partes.substr(0, 120) + '...';
               } else{
                  return n_partes;
               }
            }
            
         },
         buscarpage(e){
            console.log(e);
         }
      }, 



   };

</script>