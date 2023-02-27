<template>
    <v-row >
        <v-layout wrap class="ma-2">
            <v-col cols="12" md="10" class="pt-3 pr-2 pl-2 pb-0">
                <template v-if="tipo != null && dosearch!='false'">
                    <v-text-field v-model="search" label="Buscar" append-icon="mdi-magnify" :dense="dense" class="pb-0 mb-0"></v-text-field>
                </template>
            </v-col>
            <v-col cols="12" md="2" class="pt-3 pr-2 pl-2 pb-0">
                <download-excel
                    :data   = "json_data"
                    :fields = "json_fields"
                    :name    = "nombre_archivo"
                    >
                    <v-btn color="success" block :disabled="data.length ==0">
                        <v-icon>mdi-file-excel</v-icon>
                        Exportar data
                    </v-btn>
                 </download-excel>
            </v-col>
            <v-col cols="12" md="12" class="pa-0">
                <v-data-table
                    :headers="headers"
                    :items="data"
                    :search="search"
                    :items-per-page="tipo != null? 10: itemsPerPage"
                    :hide-default-footer="tipo == null"
                    class="elevation-1"
                    :sort-by="[orden]"
                    :sort-desc="[false]"
                    :dense="dense"
                >
                    <template v-slot:top v-if="Object.keys(filters)[0]!= null">
                        <v-container grid-list-md>
                            <v-row>
                                <v-col cols="12" md="2"
                                    v-for="header in headers"
                                    :key="header.value"
                                    v-if="filters.hasOwnProperty(header.value)"
                                >
                                    <v-flex xs12 >
                                        <v-select prepend-icon="mdi-filter" :label="(header.text != null)? header.text:''" flat x-small dense hide-details multiple clearable :items="columnValueList(header.value)" v-model="filters[header.value]">
                                            <template v-slot:item="{item, attrs}">
                                                <v-list-item
                                                    dense
                                                    class="pa-0 ma-0"
                                                    >
                                                    <v-list-item-action>
                                                        <v-checkbox
                                                        class="pa-0 ma-0"
                                                            :input-value="attrs.inputValue"
                                                        ></v-checkbox>
                                                    </v-list-item-action>
                                                    <v-list-item-content>
                                                        <v-list-item-title>
                                                        {{item}}
                                                        </v-list-item-title>
                                                    </v-list-item-content>
                                                    <v-list-item-action v-if="checkFilteredData(header.value, item)">
                                                            <v-icon color="info" small>mdi-information-outline</v-icon>
                                                    </v-list-item-action>
                                                </v-list-item>
                                                <v-divider class="mt-2"></v-divider>
                                            </template>
                                        </v-select>
                                    </v-flex>
                                    
                                </v-col>
                            </v-row>
                        </v-container>
                    </template>
                    <template v-slot:body="{ items, headers }">
                        <template v-if="items.length==0">
                            <tbody>
                                <tr>
                                    <td>
                                    No existen datos
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                            <tbody>
                                <tr v-for="item in items" :key="item.id">
                                    <td v-for="header in headers" :key="header.value">
                                        <template v-if="item['COD_VISUALIZA_EXPEDIENTE'] != 'N'">
                                            <template v-if="header.type == 'chips'" >
                                                <v-chip :color="getColor(item[header.value])" dark>
                                                    {{ getEstado(item[header.value]) }}
                                                </v-chip>
                                            </template>
                                            <template v-else-if="header.type=='opciones'">
                                                <v-icon 
                                                    v-for="opcion in splitOpciones(header.value)" 
                                                    class="mr-2"
                                                    :color="icon(opcion).color"
                                                    @click="opciones(item, opcion)"
                                                    v-bind:key="opcion.value"
                                                >
                                                        {{icon(opcion).icon}}
                                                </v-icon>
                                            </template>
                                            <template v-else-if="header.type=='datetime' && item[header.value] != undefined">
                                                {{formatDateTime(item[header.value])}}   
                                            </template>
                                            <template v-else>
                                                <template v-if="item[header.value] != undefined && item[header.value] != null">
                                                    {{ item[header.value] }}
                                                </template>
                                            </template>

                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                    </template>
                    <template v-slot:no-data>
                        No existen datos
                    </template>
                    <template v-slot:no-results>
                        No se encontró ningun resultado
                    </template>
                </v-data-table>
            </v-col>
        
            <template v-if="tipo == null">
                <v-col cols="12" md="10" offset-md="1">
                    <v-pagination v-model="pagina_actual" :length="n_paginas"></v-pagination>
                </v-col>
            </template>

            </v-layout>
    </v-row>
</template>
<script>
export default {
    props:['headers', 'data', 'filters', 'search', 'n_paginas', 'orden', 'itemsPerPage', 'tipo', 'dense','pagina_selected', 'dosearch', 'allData', 'json_data', 'json_fields', 'nombre_archivo'],
    data() {
        return {
            isMobile:false,
            pagina_actual:1,
            selected: [],
        }
    },
    watch:{
        pagina_actual(val){
            this.$emit('change_page', val)
        }, 
        pagina_selected(val){
            this.pagina_actual = val
        },
        n_paginas(val){
            if(val < this.pagina_actual){
                this.pagina_actual = 1;
            }
        }
    },
    created(){
    },
    methods:{
        splitOpciones (splitOpciones) {
            if (!splitOpciones) return null
            const lista_opciones = splitOpciones.split(',')
            return lista_opciones;
        },
        icon(opcion){
            switch(opcion){
               case 'view': return {color: 'info', icon: 'mdi-file-eye'};
               case 'edit': return {color: 'primary', icon: 'mdi-file-edit'};
               case 'delete': return {color: 'error', icon: 'mdi-delete'};
               case 'select': return {color: 'success', icon: 'mdi-check-bold'};
            }
        },
        opciones(item, opcion){
            switch(opcion){
               case 'view': this.$emit('viewItem', item); break;
               case 'edit': this.$emit('editItem', item); break;
               case 'delete': this.$emit('deleteItem', item) ; break;
               case 'select': this.$emit('selectItem', item) ; break;
            }
        },
        getColor(estado){
            let color = 'warning';
            switch(estado){
               case 0: color='info'; break;
               case 1: color='success'; break;
               case 2: color='error'; break;
               case 3: color='primary'; break;
               case '0': color='info'; break;
               case '1': color='success'; break;
               case '2': color='error'; break;
               case '3': color='primary'; break;
            }
            return color;
        },
        getEstado(estado){
            let estadomsg = 'Actualizando';
            switch(estado){
               case 0: estadomsg = 'En revisión'; break;
               case 1: estadomsg = 'Registrado'; break;
               case 2: estadomsg = 'Observado'; break;
               case 3: estadomsg = 'Entregado'; break;
               case '0': estadomsg = 'En revisión'; break;
               case '1': estadomsg = 'Registrado'; break;
               case '2': estadomsg = 'Observado'; break;
               case '3': estadomsg = 'Entregado'; break;
            }
            return estadomsg;
        },
        formatDateTime(hora){
            return hora.substr(0,19)
        },
        toggleAll () {
            if (this.selected.length) this.selected = []
            else this.selected = this.data.slice()
        },
        columnValueList(val) {
            if(this.allData != undefined && this.allData != null){
                return this.allData.map(d => d[val])
            } else{
                return this.data.map(d => d[val])
            }
        },
        checkFilteredData(filter, item){
            var newFilter = this.data.map(d => d[filter])
            var flag = newFilter.find(element => {
                return element == item;
            });
            return flag;
        },
        onResize() {
            if (window.innerWidth < 769)
                this.isMobile = true;
            else
                this.isMobile = false;
        },
    }
}
</script>