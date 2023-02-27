<template>
   	<v-app id="inspire">
				<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
         	<v-card>
                 <v-app-bar dark color="grey">
                    <v-toolbar-title>Asistencia de Personal</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="red accent-1" v-if="hasRole('Asistencia.administrador|Administrador')" @click="openNew()">
                        <v-icon>mdi-plus</v-icon>
                                Registrar nueva Asistencia
                    </v-btn>
                     &nbsp; 
                    <v-btn color="red accent-2" @click="listarAsistencia()">
                        <v-icon>mdi-refresh</v-icon>
                                Actualizar
                    </v-btn>
                </v-app-bar>
            	<v-card>
                    <v-card-title>
                        <v-layout wrap>
                            <v-flex xs12 md2>
                                <v-menu
                                    v-model="fecha_consulta_ini"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="290px"
                                >
                                    <template v-slot:activator="{ on }">
                                    <v-text-field
                                        v-model="date_ini"
                                        label="Fecha inicial"
                                        prepend-icon="mdi-calendar"
                                        readonly
                                        v-on="on"
                                    ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="date_ini" @input="fecha_consulta_ini = false"></v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs12 md2>
                                <v-menu
                                    v-model="fecha_consulta_fin"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="290px"
                                >
                                    <template v-slot:activator="{ on }">
                                    <v-text-field
                                        v-model="date_fin"
                                        label="Fecha final"
                                        prepend-icon="mdi-calendar"
                                        readonly
                                        v-on="on"
                                    ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="date_fin" @input="fecha_consulta_fin = false"></v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs12 md5 offset-md1>
                                <v-text-field v-model="search" label="Buscar [DNI o nombres]" clearable @keydown.enter="listarAsistencia(1)">
                                </v-text-field>
                            </v-flex>
                            <v-flex xs12 md2>
                                <v-btn color="primary" dark @click="listarAsistencia(1)">
                                    <v-icon>mdi-magnify</v-icon>
                                    Buscar
                                </v-btn>
                            </v-flex>
                            <v-flex xs12 md2>
                                <v-btn color="info" @click="listarAsistencia(1,'all')">Mostrar todo</v-btn>
                            </v-flex>
                            <v-flex xs12 md2>
                                <download-excel
                                    :data   = "json_data"
                                    :fields = "json_fields"
                                    :name    = "nombre_archivo"
                                    >
                                        <v-btn 
                                        color="success"                                       
                                        :disabled="!exporta"
                                        >
                                        <i class="fa fa-file-excel-o"></i>
                                            &nbsp; Descargar XLS
                                        </v-btn>
                            </download-excel>
                            </v-flex>
                        </v-layout>
                    </v-card-title>
                    <dataTable
						:headers="headers"
						:data="datos"
                        :filters="filters"
                        :n_paginas="n_paginas"
                        :itemsPerPage="itemsPerPage"
                        @change_page="listarAsistencia"
                        @editItem="editItem"
                        @viewItem="viewItem"
                        @deleteItem="deleteItem"
			      	/>
                </v-card>
			</v-card>
            
            <v-dialog 
                v-model="d_view"
                scrollable  
                :overlay="false"
                max-width="1200px"
                transition="dialog-transition"
                v-if="d_view"
            >
                <v-card v-if="activeItem != null">
                    <v-toolbar color="red darken-1" dark>
                        <v-toolbar-title>Asistencia {{activeItem.fecha}}
                        </v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-icon @click="d_view = false">mdi-close</v-icon>
                    </v-toolbar>
                    <v-card-text  class="pa-0">
                        <v-row class="ma-1">
                            <v-col cols="12"
                              md="4">
                                <v-row>
                                    <v-col>
                                        <v-list two-line subheader dense>
                                            <v-subheader>Persona</v-subheader>
                                            <v-list-item>
                                                <v-list-item-content>
                                                    <v-list-item-title>{{activeItem.user_id}}</v-list-item-title>
                                                    <v-list-item-subtitle>Documento</v-list-item-subtitle>
                                                </v-list-item-content>
                                            </v-list-item>
                                            <v-list-item>
                                                <v-list-item-content>
                                                    <v-list-item-title>{{activeItem.persona}}</v-list-item-title>
                                                    <v-list-item-subtitle>Apellidos y nombres</v-list-item-subtitle>
                                                </v-list-item-content>
                                            </v-list-item>
                                            <v-subheader>Datos Laborales</v-subheader>
                                            <v-list-item>
                                                <v-list-item-content>
                                                    <v-list-item-title>{{activeItem.nombre_plaza}}</v-list-item-title>
                                                    <v-list-item-subtitle>Plaza</v-list-item-subtitle>
                                                </v-list-item-content>
                                            </v-list-item>
                                            <v-list-item>
                                                <v-list-item-content>
                                                    <v-list-item-title>{{activeItem.nombre_oficina}} - {{activeItem.distrito}}</v-list-item-title>
                                                    <v-list-item-subtitle>Dependencia</v-list-item-subtitle>
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-list>
                                    </v-col>
                                    
                                    <v-divider vertical/>
                                </v-row>
                            </v-col>
                            <v-col cols="12"
                              md="8">
                                <v-list two-line subheader dense>
                                    <v-subheader>Asistencia</v-subheader>
                                    <template v-if="hasRole('Asistencia.administrador|Administrador')">
                                        <v-btn color="info" @click="d_edit = true">
                                            <v-icon>mdi-clipboard-edit-outline</v-icon>
                                            Editar
                                        </v-btn>
                                    </template>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{activeItem.hora_inicio}}</v-list-item-title>
                                            <v-list-item-subtitle>Ingreso</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title v-if="activeItem.refrigerio_inicio != null">{{activeItem.refrigerio_inicio}}</v-list-item-title>
                                            <v-list-item-title v-else>-</v-list-item-title>
                                            <v-list-item-subtitle>Ini. refrigerio</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title v-if="activeItem.refrigerio_fin != null">{{activeItem.refrigerio_fin}}</v-list-item-title>
                                            <v-list-item-title v-else>-</v-list-item-title>
                                            <v-list-item-subtitle>Ret. refrigerio</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title v-if="activeItem.hora_fin != null">{{activeItem.hora_fin}}</v-list-item-title>
                                            <v-list-item-title v-else>-</v-list-item-title>
                                            <v-list-item-subtitle>Salida</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-list-item v-if="activeItem.hora_inicio2 != null">
                                            <v-list-item-content>
                                            <v-list-item-title>{{activeItem.hora_inicio2}}</v-list-item-title>
                                            <v-list-item-subtitle>Inicio complementario</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title>{{activeItem.hora_fin2}}</v-list-item-title>
                                            <v-list-item-subtitle>Fin complementario</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{activeItem.ip_registro}}</v-list-item-title>
                                            <v-list-item-subtitle>IP registro Ingreso</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title v-if="activeItem.tipo*1 == 1">PRESENCIAL</v-list-item-title>
                                            <v-list-item-title v-else>REMOTO</v-list-item-title>
                                            <v-list-item-subtitle>Tipo Asistencia</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content v-if="activeItem.observacion != null">
                                            <v-tooltip bottom>
                                                <template v-slot:activator="{ on }">
                                                    <v-list-item-title v-on="on">{{activeItem.observacion}}</v-list-item-title>
                                                    <v-list-item-subtitle v-on="on">Observación</v-list-item-subtitle>
                                                </template>
                                                <v-card max-width="300px">
                                                    <v-card-text>
                                                        <span>{{activeItem.observacion}}</span>
                                                    </v-card-text>
                                                </v-card>
                                            </v-tooltip>
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list>
                                <template v-if="boletas.length > 0">
                                    <v-subheader>Boletas de Permiso</v-subheader>
                                        <show-boleta-component
                                            :headers="headers_b"
                                            :datos="boletas"       
                                            :pagination="pagination_b"  
                                            :loading="loading"
                                            @viewItem="viewItem"
                                            @editItem="editItem"
                                            @deleteItem="deleteItem"
                                        />
                                </template>
                                <template v-if="actividades.length > 0">
                                    <v-subheader>Actividades</v-subheader>
                                    <v-list dense>
                                        <v-list-item-group
                                            color="primary"
                                        >
                                            <v-list-item v-for="(actividad, index) in actividades" v-bind:key="index" dense>
                                                <v-list-item-content>
                                                    {{actividad.meta_cantidad}} {{actividad.descripcion}}
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-list-item-group>
                                    </v-list>
                                </template>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-dialog>
            <v-dialog 
                v-model="d_edit"
                scrollable  
                :overlay="true"
                max-width="800px"
                transition="dialog-transition"
                v-if="d_edit && activeItem != null"
            >
                <v-card>

                    <v-toolbar color="red darken-1" dark>
                        <v-toolbar-title>Editar asistencia
                        </v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-icon @click="d_edit = false">mdi-close</v-icon>
                    </v-toolbar>
                    <v-card-text>
                        <v-autocomplete
                                v-model="activeItem.tipo"
                                label="Tipo de Asistencia"
                                :items="tipoAsistencia"
                                item-text="descripcion"
                                item-value="id"
                            />
                        <v-row>
                            <v-col>
                                <v-dialog
                                        ref="dialog_h_ini"
                                        v-model="h_inicio"
                                        :return-value.sync="time_inicio"
                                        persistent
                                        width="290px"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field
                                                v-model="activeItem.hora_inicio"
                                                label="H. Inicio"
                                                slot="activator"
                                                :rules="[rules.required]"
                                                v-on="on"
                                            ></v-text-field>
                                    </template>
                                    <v-time-picker
                                        v-if="h_inicio"
                                        v-model="time_inicio"
                                        full-width
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn text color="primary" @click="h_inicio = false">Cancel</v-btn>
                                        <v-btn text color="primary" @click="$refs.dialog_h_ini.save(time_inicio)">OK</v-btn>
                                    </v-time-picker>
                                </v-dialog>
                            </v-col>
                            <v-col>
                                <v-dialog
                                        ref="dialog_h_ref_ini"
                                        v-model="h_ref_ini"
                                        :return-value.sync="tref_inicio"
                                        persistent
                                        width="290px"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field
                                                v-model="activeItem.refrigerio_inicio"
                                                label="Ref. inicio"
                                                slot="activator"
                                                :rules="[rules.required]"
                                                v-on="on"
                                            ></v-text-field>
                                    </template>
                                    <v-time-picker
                                        v-if="h_ref_ini"
                                        v-model="tref_inicio"
                                        full-width
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn text color="primary" @click="h_ref_ini = false">Cancel</v-btn>
                                        <v-btn text color="primary" @click="$refs.dialog_h_ref_ini.save(tref_inicio)">OK</v-btn>
                                    </v-time-picker>
                                </v-dialog>
                            </v-col>
                            <v-col>
                                <v-dialog
                                        ref="dialog_h_ref_fin"
                                        v-model="h_ref_fin"
                                        :return-value.sync="tref_fin"
                                        persistent
                                        width="290px"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field
                                                v-model="activeItem.refrigerio_fin"
                                                label="Ref. retorno"
                                                slot="activator"
                                                :rules="[rules.required]"
                                                v-on="on"
                                            ></v-text-field>
                                    </template>
                                    <v-time-picker
                                        v-if="h_ref_fin"
                                        v-model="tref_fin"
                                        full-width
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn text color="primary" @click="h_ref_fin = false">Cancel</v-btn>
                                        <v-btn text color="primary" @click="$refs.dialog_h_ref_fin.save(tref_fin)">OK</v-btn>
                                    </v-time-picker>
                                </v-dialog>
                            </v-col>
                            <v-col>
                                <v-dialog
                                        ref="dialog_h_fin"
                                        v-model="h_fin"
                                        :return-value.sync="time_fin"
                                        persistent
                                        width="290px"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field
                                                v-model="activeItem.hora_fin"
                                                label="H. Salida"
                                                slot="activator"
                                                :rules="[rules.required]"
                                                v-on="on"
                                            ></v-text-field>
                                    </template>
                                    <v-time-picker
                                        v-if="h_fin"
                                        v-model="time_fin"
                                        full-width
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn text color="primary" @click="h_fin = false">Cancel</v-btn>
                                        <v-btn text color="primary" @click="$refs.dialog_h_fin.save(time_fin)">OK</v-btn>
                                    </v-time-picker>
                                </v-dialog>
                            </v-col>
                        </v-row>
                        <v-text-field
                            v-model="activeItem.observacion"
                            label="Observación"
                            ref="observacion"
                            counter
                            maxlength="250"
                            :rules="[rules.notNUll, rules.length_250]"
                        />                            
                    </v-card-text>
                    <v-card-actions>
                        <v-btn color="red" text @click="d_edit = false">
                            Cancelar
                            <v-icon
                                class="mr-2"
                                color="red"
                            >
                                mdi-cancel
                            </v-icon> 
                            </v-btn>
                            <template>
                                <v-btn color="blue darken-1" text @click="updateAsistencia()">Actualizar Datos
                                    <v-icon
                                        class="mr-2"
                                        color="blue darken-1"
                                    >
                                        mdi-content-save
                                    </v-icon> 
                                </v-btn>
                            </template>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            <v-dialog
                v-model="d_new"
                scrollable  
                :overlay="true"
                max-width="1000px"
                transition="dialog-transition"
                v-if="d_new && activeItem != null"
            >
                <v-card v-if="activeItem != null">
                    <v-toolbar color="red darken-1" dark>
                        <v-toolbar-title>Regularización de nueva Asistencia
                        </v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-icon @click="d_new = false">mdi-close</v-icon>
                    </v-toolbar>
                    <v-card-text >
                        <v-row>
                            <v-col cols="12" md="4">
                                <v-subheader>Persona</v-subheader>
                                <v-btn @click="changePersona()">
                                    <v-icon v-if="activeItem.persona_id != undefined" color="primary">mdi-refresh</v-icon>
                                    <v-icon v-else >mdi-magnify</v-icon>
                                    <v-icon>mdi-account</v-icon>
                                    <template v-if="activeItem.persona_id != undefined">
                                        [{{activeItem.persona.numero_documento}}]
                                    </template>
                                </v-btn>
                                
                                <template v-if="activeItem.persona_id != undefined">
                                    <h5>
                                        Apellidos y Nombres: 
                                    </h5>
                                    <h4>
                                        {{activeItem.persona.ap_paterno}}
                                        {{activeItem.persona.ap_materno}}
                                        {{activeItem.persona.nombres}}
                                    </h4>
                                </template>
                            </v-col>
                            <v-col cols="12" md="8">
                                <v-row>
                                    <v-col cols="12" md="4" class="mb-0 mt-0">
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
                                    <v-col  cols="12" md="8" class="mb-0 mt-0">
                                        <v-autocomplete
                                            v-model="activeItem.tipo"
                                            label="Tipo de Asistencia"
                                            :items="tipoAsistencia"
                                            item-text="descripcion"
                                            item-value="id"
                                        />
                                    </v-col>
                                </v-row>
                                
                                <v-row>
                                    <v-col>
                                        <v-dialog
                                                ref="dialog_h_ini"
                                                v-model="h_inicio"
                                                :return-value.sync="time_inicio"
                                                persistent
                                                width="290px"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field
                                                        v-model="activeItem.hora_inicio"
                                                        label="H. Inicio"
                                                        slot="activator"
                                                        :rules="[rules.required]"
                                                        v-on="on"
                                                    ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_inicio"
                                                v-model="time_inicio"
                                                full-width
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_inicio = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.dialog_h_ini.save(time_inicio)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col>
                                        <v-dialog
                                                ref="dialog_h_ref_ini"
                                                v-model="h_ref_ini"
                                                :return-value.sync="tref_inicio"
                                                persistent
                                                width="290px"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field
                                                        v-model="activeItem.refrigerio_inicio"
                                                        label="Ref. inicio"
                                                        slot="activator"
                                                        :rules="[rules.required]"
                                                        v-on="on"
                                                    ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_ref_ini"
                                                v-model="tref_inicio"
                                                full-width
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_ref_ini = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.dialog_h_ref_ini.save(tref_inicio)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col>
                                        <v-dialog
                                                ref="dialog_h_ref_fin"
                                                v-model="h_ref_fin"
                                                :return-value.sync="tref_fin"
                                                persistent
                                                width="290px"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field
                                                        v-model="activeItem.refrigerio_fin"
                                                        label="Ref. retorno"
                                                        slot="activator"
                                                        :rules="[rules.required]"
                                                        v-on="on"
                                                    ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_ref_fin"
                                                v-model="tref_fin"
                                                full-width
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_ref_fin = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.dialog_h_ref_fin.save(tref_fin)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col>
                                        <v-dialog
                                                ref="dialog_h_fin"
                                                v-model="h_fin"
                                                :return-value.sync="time_fin"
                                                persistent
                                                width="290px"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field
                                                        v-model="activeItem.hora_fin"
                                                        label="H. Salida"
                                                        slot="activator"
                                                        :rules="[rules.required]"
                                                        v-on="on"
                                                    ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_fin"
                                                v-model="time_fin"
                                                full-width
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_fin = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.dialog_h_fin.save(time_fin)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                </v-row>
                                <v-text-field
                                    v-model="activeItem.observacion"
                                    label="Observación"
                                    ref="observacion"
                                    counter
                                    maxlength="250"
                                    :rules="[rules.notNUll, rules.length_250]"
                                />                            
                            

                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer/>
                        <v-btn color="red" text @click="d_new = false">
                            Cancelar
                            <v-icon
                                class="mr-2"
                                color="red"
                            >
                                mdi-cancel
                            </v-icon> 
                        </v-btn>
                        <template>
                            <v-btn color="blue darken-1"
                                :disabled="activeItem.persona_id == undefined || activeItem.observacion == null || activeItem.hora_inicio == undefined"
                                text @click="createAsistencia()">Registrar Asistencia
                                <v-icon
                                    class="mr-2"
                                    color="blue darken-1"
                                >
                                    mdi-content-save
                                </v-icon> 
                            </v-btn>
                        </template>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            <v-dialog v-model="d_persona"
                scrollable
                max-width="1200PX"
                transition="dialog-transition">
                <select-persona-component @selectedItem="selectedPersona"/>
            </v-dialog>
            <dialogLoader :dialogLoad="dialogLoad"/>
            <snackbar :snack="snack" ></snackbar>
        </v-main>
    </v-app>
</template>

<script>
	export default {
        props:['dataUser'],
      	data() {
         	return {
                menu: 2,
				submenu: 3,
				modulo: 'asistencia',
				drawer:true,
	            asistenciaID: '',
                pagina_actual:1,
                n_paginas:1,
                search:'',
				headers:[
	               	{text: 'Fecha', value: 'fecha'},
	              	{text: 'Ingreso', value: 'hora_inicio'},
	              	{text: 'Inicio Refrigerio', value: 'refrigerio_inicio'},
	              	{text: 'Retorno Refrigerio', value: 'refrigerio_fin'},
                    {text: 'Salida', value: 'hora_fin'},
                    {text: 'Remoto Compl. Ingreso', value: 'hora_inicio2' },
                    {text: 'Remoto Compl. Salida ', value: 'hora_fin2' },
                    {text: 'Tipo', value: 'tipo', type: 'array', array:'1=Trabajo Presencial,2=Trabajo Remoto'},
                    {text: 'DNI', value: 'user_id'},
	              	{text: 'Apellidos y nombre', value: 'persona'},
	              	{text: 'Cargo', value: 'nombre_plaza'},
	              	{text: 'Opciones', value: 'view', type:'opciones'},
                       
	            ],
                datos:[],
                itemsPerPage:15,
                loading:true,
                date_ini: this.parseDate(new Date().toLocaleDateString()),
                date_fin: this.parseDate(new Date().toLocaleDateString()),
                fecha_consulta_ini: false,
                fecha_consulta_fin: false,
                exporta: false,
                activeItem:{},
                json_fields: {},
                json_data: [],
                json_meta: [
                [
                    {
                        'key': 'charset',
                        'value': 'utf-8'
                    }
                ]
                ],
                d_view:false,
                d_edit:false,
                d_new:false,
                d_persona:false,
                nombre_archivo: '',
                filters: {
                },   
                snack: {snackShow:false, snackText: '', snackColor: '',},  
                dialogLoad: {show:false, text: '', color: '',},

                headers_b: [
                    {text: 'Fecha', value: 'fecha_permiso'},
                    {text: 'Hora Inicio', value: 'hora_ini', type: 'time'},
                    {text: 'Hora Final', value: 'hora_fin', type: 'time'},
                    {text: 'Motivo', value: 'descripcion'},
                    {text: 'Detalle Motivo', value: 'detalle_motivo'},
                    {text: 'Aut. Jefe Inmediato', value: 'autorizacion_ji', type: 'boolean'},
                    {text: 'Aut. Seguridad', value: 'autorizacion_seg', type: 'boolean'},
                ],
                boletas:[],
                pagination_b: {
                    rowsPerPage: 25,
                },
                loading:false,
                rules: {
                    entero: v => v > 0 || 'El numero debe ser mayor a 0',
                    required: v => !!v || 'Descripcion Requerido',
                    notNUll: v => v != null || 'Indicar la observación de la modificación y/o regularización',
                    length_250: v => (v != null && v.length <= 250) || 'Max 250 caracteres'
                },
                tipoAsistencia:[
                    {id: 1, descripcion: 'PRESENCIAL'},
                    {id: 2, descripcion: 'REMOTO'},
                ],
                h_inicio: false,
                h_fin: false,
                h_ref_ini: false,
                h_ref_fin: false,
                time_inicio: null,
                time_fin:null,
                tref_inicio: null,
                tref_fin: null,
                datetime_ini: false,
                date_inicio: this.parseDate(new Date().toLocaleDateString()),
                actividades:[]
	    	}
        },
        computed: {
        },
        watch: {
            date_inicio (val) {
                this.activeItem.f_inicio = this.formatDate(this.date_inicio)
            },
            date_ini(val){
                this.listarAsistencia(1)
            },
            date_fin(val){
                this.listarAsistencia(1)
            },
            time_inicio (val) {
                if(this.activeItem != null){
                    this.activeItem.hora_inicio = this.time_inicio
                }
            },
            time_fin (val) {
                if(this.activeItem != null){
                    this.activeItem.hora_fin = this.time_fin
                }
            },
            tref_inicio (val) {
                if(this.activeItem != null){
                    this.activeItem.refrigerio_inicio = this.tref_inicio
                }
            },
            tref_fin (val) {
                if(this.activeItem != null){
                    this.activeItem.refrigerio_fin = this.tref_fin
                }
            },
            
            search(val){
                if(val == null){
                    this.listarAsistencia();
                }
            },
            d_edit(val) {
                if(!val){
                    this.viewItem(this.activeItem)
                }
            }, 
            d_new(val) {
                if(!val){
                    this.activeItem = {}
                    this.time_inicio = null
                    this.time_fin = null
                }
            }
        },
     	created(){
            this.listarAsistencia();
	    },
	    methods: {
	        listarAsistencia(pag = 1, perpage = 15){

                this.showLoading('primary', 'Cargando datos', true);
                if(this.hasRole('Asistencia.JSupervisor')){
    	            var url = 'asistenciaApi?listarFechasSup=true&page='+pag + '&dateini='+this.date_ini+'&datefin='+this.date_fin
                } else{
    	            var url = 'asistenciaApi?listarFechas=true&page='+pag + '&dateini='+this.date_ini+'&datefin='+this.date_fin
                }

                this.itemsPerPage = perpage;

                this.exporta = false;

                if(perpage == 'all'){
                    //this.search = '';
                    this.exporta = true;
                    if(this.search != '' && this.search != null){
                        if(this.hasRole('Asistencia.JSupervisor')){
                            url = 'asistenciaApi?listarFechasSup=true&page=' + pag + '&dateini='+this.date_ini +'&datefin='+this.date_fin + '&perPage=all&search2='+this.search
                        } else{
                            url = 'asistenciaApi?listarFechas=true&page=' + pag + '&dateini='+this.date_ini +'&datefin='+this.date_fin + '&perPage=all&search2='+this.search
                        }
                    } else{
                        if(this.hasRole('Asistencia.JSupervisor')){
                            url = 'asistenciaApi?listarFechasSup=true&page=' + pag + '&dateini='+this.date_ini + '&datefin='+this.date_fin + '&perPage=all';
                        } else{
                            url = 'asistenciaApi?listarFechas=true&page=' + pag + '&dateini='+this.date_ini + '&datefin='+this.date_fin + '&perPage=all';
                        }

                    }
                    this.itemsPerPage = 5000;
                } else if(this.search != '' && this.search != null){
                    if(this.hasRole('Asistencia.JSupervisor')){
    	                url = 'asistenciaApi?listarFechasSup=true&page=' + pag + '&dateini='+this.date_ini +'&datefin='+this.date_fin + '&search='+this.search
                    } else{
	                    url = 'asistenciaApi?listarFechas=true&page=' + pag + '&dateini='+this.date_ini +'&datefin='+this.date_fin + '&search='+this.search
                    }
                } 

                this.loading=true
	            axios.get(url)
	                .then(response =>{
                        this.datos = response.data.asistencias['data']
                        this.n_paginas = response.data.asistencias.last_page;
                        this.loading=false
                        if(perpage == 'all'){
                            this.nombre_archivo = this.date_ini+' - '+this.date_fin+'.xls'
                            this.json_data = this.datos;
                            this.json_fields = {
                                'Fecha': 'fecha',
                                'Ingreso': 'hora_inicio',
                                'Ini. Refrigerio': 'refrigerio_inicio',
                                'Ret. Refrigerio': 'refrigerio_fin',
                                'Salida': 'hora_fin',
                                'Remoto C. Ingreso': 'hora_inicio2',
                                'Remoto C. Salida': 'hora_fin2',
                                'DNI':'user_id',
                                'Apellidos y nombres': 'persona',
                                'Regimen': 'regimen_base',
                                'Cargo': 'nombre_plaza',
                                'Modalidad de trabajo': 'tipo_trabajo',
                            };
                        }

                        this.showLoading('primary', '', false);


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
                        }
                        this.showLoading('primary', '', false);

                    });

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
            printSection() {
                this.$htmlToPaper("printSection");
            },
            newItem(){},
            editItem(item){},
            viewItem(item){
                this.activeItem = null;
                this.time_inicio = null;
                this.time_fin = null;
                this.tref_inicio = null;
                this.tref_fin = null;
                this.showLoading('primary', 'Cargando datos', true);
                axios.post('getAsistenciaUser', {asistencia : item})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.activeItem = response.data['data'];
                            this.activeItem.tipo = this.activeItem.tipo*1
                            this.boletas = response.data['boletas']          
                            this.actividades = response.data['actividades']          
                            this.d_view = true

                            this.time_inicio = this.activeItem.hora_inicio
                            this.time_fin = this.activeItem.hora_fin
                            this.tref_inicio = this.activeItem.refrigerio_inicio
                            this.tref_fin = this.activeItem.refrigerio_fin


                            this.showSnack('success', response.data['messageBD'])
                        } else{
                            this.showSnack('error', response.data['messageBD'])
                        }
                        this.showLoading('', '', false);

                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
            },
            deleteItem(item){},
            save(e){
            },
            updateAsistencia(){
                this.showLoading('primary', 'Registrando Datos', true);

                if(this.activeItem.observacion == null || this.activeItem.observacion.length < 3){
                    this.showSnack('warning', 'Debe de ingresar una observación valida');
                    this.$refs.observacion.focus();
                    return;
                }
                if(this.activeItem.refrigerio_inicio != null  && this.activeItem.refrigerio_inicio < this.activeItem.hora_inicio){
                    this.showSnack('warning', 'La hora de refrigerio no puede ser inferior a la hora de ingreso');
                    return;
                }
                if(this.activeItem.refrigerio_fin != null  && this.activeItem.refrigerio_fin < this.activeItem.refrigerio_inicio){
                    this.showSnack('warning', 'La hora de retorno de refrigerio no puede ser inferior a la hora de ingreso u hora de ingreso');
                    return;
                }
                if(this.activeItem.hora_fin != null  && this.activeItem.hora_fin < this.activeItem.hora_inicio){
                    this.showSnack('warning', 'La hora de salida no puede ser inferior a la hora retorno de refrigerio o de ingreso');
                    return;
                } 

                axios.post('updateAsistenciaUser', {asistencia : this.activeItem})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.showSnack('success', response.data['messageBD'])
                            this.d_edit = false;
                        } else{
                            this.showSnack('error', response.data['messageBD'])
                        }
                        this.showLoading('', '', false);

                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
            },
            openNew(){
                this.activeItem = {}
                this.activeItem.f_inicio = this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10)));
                this.d_new = true;
            },
            createAsistencia(){
                this.showLoading('primary', 'Registrando Datos', true);

                if(this.activeItem.observacion == null || this.activeItem.observacion.length < 3){
                    this.showSnack('warning', 'Debe de ingresar una observación valida');
                    this.$refs.observacion.focus();
                    this.showLoading('', '', false);
                    return;
                }
                if(this.activeItem.hora_inicio == null ||  this.activeItem.hora_inicio == undefined){
                    this.showSnack('warning', 'Debe de indicar la hora de ingreso');
                    this.showLoading('', '', false);
                    return;
                }
                if(this.activeItem.refrigerio_inicio != null  && this.activeItem.refrigerio_inicio < this.activeItem.hora_inicio){
                    this.showSnack('warning', 'La hora de refrigerio no puede ser inferior a la hora de ingreso');
                    this.showLoading('', '', false);
                    return;
                }
                if(this.activeItem.refrigerio_fin != null  && this.activeItem.refrigerio_fin < this.activeItem.refrigerio_inicio){
                    this.showSnack('warning', 'La hora de retorno de refrigerio no puede ser inferior a la hora de ingreso u hora de ingreso');
                    this.showLoading('', '', false);
                    return;
                }
                if(this.activeItem.hora_fin != null  && this.activeItem.hora_fin < this.activeItem.hora_inicio){
                    this.showSnack('warning', 'La hora de salida no puede ser inferior a la hora retorno de refrigerio o de ingreso');
                    this.showLoading('', '', false);
                    return;
                } 

                axios.post('createAsistenciaUser', {asistencia : this.activeItem})
                    .then(response =>{
                        if (response.data['statusBD']) {
                            this.showSnack('success', response.data['messageBD'])
                            this.d_new = false;
                            this.listarAsistencia(1);
                        } else{
                            this.showSnack('error', response.data['messageBD'])
                        }
                        this.showLoading('', '', false);

                    })
                    .catch(errors =>{
                        console.log(errors);
                        if (errors.response != undefined && (errors.response.status === 401|| errors.response.status === 419)) {
                            console.log(errors.response)
                            if(errors.response.data.error == 'Unauthenticated'){
                                window.location = "inicio";
                            }
                        }
                    });
            },
            changePersona(){
                this.d_persona = true;
            },
            selectedPersona(item){
                this.activeItem.persona = {}
                this.activeItem.persona_id = item.numero_documento
                this.activeItem.persona.id = item.id
                this.activeItem.persona.ap_paterno = item.ap_paterno
                this.activeItem.persona.ap_materno = item.ap_materno
                this.activeItem.persona.nombres = item.nombres
                this.activeItem.persona.numero_documento = item.numero_documento
                this.d_persona = false;
                
            },
            close(){
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

            showLoading(color, text, show = true){
				this.dialogLoad.color = color
				this.dialogLoad.text = text
				this.dialogLoad.show = show
            },
            
	    }
     }
	
</script>