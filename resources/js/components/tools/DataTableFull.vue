<template>
    <v-row v-resize="onResize">
        <v-layout wrap class="ma-5">
            <template v-if="tipo != null">
                <v-text-field v-model="search" label="Buscar"></v-text-field>
            </template>
            <v-col cols="12" md="12">
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
                        <template v-if="!isMobile" >
                            <tbody>
                                <tr v-for="item in items" :key="item.id">
                                    <td v-for="header in headers" :key="header.value">
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
                                        <template v-else-if="header.type=='boolean'">
                                            <v-checkbox :input-value="item[header.value]*1" readonly color="teal"></v-checkbox>
                                        </template>
                                        <template v-else-if="header.type=='boolean_t'">
                                            <v-chip small :color="(item[header.value]*1)? 'success':'warning'">{{(item[header.value]*1)? 'SI':'NO'}}</v-chip>
                                        </template>
                                        <template v-else-if="header.type=='subvalue' && item[header.value] != undefined">
                                                {{item[header.value][header.subvalue]}}
                                        </template>
                                        <template v-else-if="header.type=='array' && item[header.value] != undefined">
                                            <template v-for="array in header.array.split(',')">
                                                <template v-for="opcion in array.split('=')">
                                                    <template v-if="opcion*1 == item[header.value]*1 ">
                                                       {{ array.split('=')[1]}}
                                                    </template>
                                                </template>
                                            </template>
                                        </template>
                                        <template v-else-if="header.type=='array_chip' && item[header.value] != undefined">
                                            <template v-for="array in header.array.split(',')">
                                                <template v-for="opcion in array.split('=')">
                                                    <v-chip v-if="opcion*1 == item[header.value]*1" :color="array.split('/')[1]" dark>
                                                       {{ array.split('/')[0].split('=')[1]}}
                                                    </v-chip>
                                                </template>
                                            </template>
                                        </template>
                                        <template v-else-if="header.type=='subvalues' && item[header.value] != undefined">
                                            <template v-for="dato in header.subvalue_concat.split(',')">
                                                {{item[header.value][dato]}}
                                            </template>
                                        </template>
                                        <template v-else-if="header.type=='subvalue_array' && item[header.value] != undefined">
                                                <v-chip v-for="grupo in item[header.value]" :key="grupo.id" color="info" small>
                                                    {{grupo[header.subvalue]}}
                                                </v-chip>
                                        </template>
                                        <template v-else-if="header.type=='subvalue_array_concat' && item[header.value] != undefined">
                                                <template v-for="grupo in item[header.value]" >
                                                    <template v-for="dato in header.subvalue_concat.split(',')">
                                                        {{grupo[dato]}}
                                                    </template>
                                                </template>
                                        </template>
                                        <template v-else>
                                            {{ item[header.value] }}
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                        <template v-else>
                            <tr v-for="item in items" :key="item.id" class="v-data-table__mobile-table-row">
                                <td v-for="header in headers" :key="header.value" class="v-data-table__mobile-row">
                                    <div class="v-data-table__mobile-row__header">
                                        {{header.text}}
                                    </div>
                                    <div class="v-data-table__mobile-row__cell"> 
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
                                        <template v-else-if="header.type=='boolean'">
                                            <v-checkbox :input-value="item[header.value]*1" readonly color="teal"></v-checkbox>
                                        </template>
                                        <template v-else-if="header.type=='subvalue' && item[header.value] != undefined">
                                                {{item[header.value][header.subvalue]}}
                                        </template>
                                        <template v-else>
                                            {{ item[header.value] }}
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
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
                <v-col cols="12" md="12">
                    <v-pagination v-model="pagina_actual" :length="n_paginas"></v-pagination>
                </v-col>
            </template>

            </v-layout>
    </v-row>
</template>
<script>
export default {
    props:['headers', 'data', 'filters', 'n_paginas', 'orden', 'itemsPerPage', 'tipo', 'dense'],
    data() {
        return {
            isMobile:false,
            pagina_actual:1,
            search:''
        }
    },
    watch:{
        pagina_actual(val){
            this.$emit('change_page', val)
        }, 
        pagina_selected(val){
            this.pagina_actual = val
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
               case 'delete': return {color: 'danger', icon: 'mdi-delete'};
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
        toggleAll () {
            if (this.selected.length) this.selected = []
            else this.selected = this.data.slice()
        },
        columnValueList(val) {
            return this.data.map(d => d[val])
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