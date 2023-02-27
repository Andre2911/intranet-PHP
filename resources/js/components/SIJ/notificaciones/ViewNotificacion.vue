<template>
    <v-card>
        <v-toolbar color="red darken-4" dark>
            <v-toolbar-title>
                <b>Expediente: {{activeItem.NUM_EXPEDIENTE}}</b>
            </v-toolbar-title>
            <v-spacer/>
            <v-btn  icon  dark @click="$emit('close')">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-toolbar>
        <v-card-text>
            <v-row>
                <v-col cols="12" md="4">
                    <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>DATOS GENERALES</v-subheader>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.NUM_EXPEDIENTE}}</v-list-item-title>
                                    <v-list-item-subtitle>Expediente</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_ESPECIALIDAD}}</v-list-item-title>
                                    <v-list-item-subtitle>Especialidad</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_INCIDENTE}}</v-list-item-title>
                                    <v-list-item-subtitle>Cuaderno</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.NOM_USUARIO_SECRETARIO}}</v-list-item-title>
                                    <v-list-item-subtitle>Secretario(a)</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_INSTANCIA_SIJ}}</v-list-item-title>
                                    <v-list-item-subtitle>Instancia</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_DEPENDENCIA}}</v-list-item-title>
                                    <v-list-item-subtitle>Dependencia</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_ORG_JURISDICCIONAL}}</v-list-item-title>
                                    <v-list-item-subtitle>Organo Jurisdiccional</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_SEDE_JUDICIAL}}</v-list-item-title>
                                    <v-list-item-subtitle>Sede</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list-item-group>
                        
                        
                    </v-list>
                </v-col>
                <v-col cols="12" md="4">
                     <v-list subheader dense >
                        <v-list-item-group>
                            <v-subheader>DATOS DEL ESCRITO</v-subheader>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_TIPO_ESCRITO}}</v-list-item-title>
                                    <v-list-item-subtitle>Tipo de escrito</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-tooltip right>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-list-item-title v-bind="attrs" v-on="on">{{activeItem.SUMILLA_ESCRITO}}</v-list-item-title>
                                        </template>
                                        <span>{{activeItem.SUMILLA_ESCRITO}}</span>
                                    </v-tooltip>
                                    <v-list-item-subtitle>Sumilla</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.NUM_FOJAS_ESCRITO}}</v-list-item-title>
                                    <v-list-item-subtitle>Número de Fojas</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.ANIO_ESCRITO}} - {{activeItem.NUM_SECUENCIA_ESCRITO}}</v-list-item-title>
                                    <v-list-item-subtitle>Nro. de Escrito</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.FECHA_INGRESO_ESCRITO}}</v-list-item-title>
                                    <v-list-item-subtitle>Fecha de ingreso</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            
                            <v-list-item>
                                <v-list-item-content>
                                    <v-tooltip right>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-list-item-title v-bind="attrs" v-on="on">{{activeItem.DEN_TIPO_PARTE}}: {{activeItem.APE_NOM_DEN_PARTE}}</v-list-item-title>
                                            </template>
                                        <span>{{activeItem.DEN_TIPO_PARTE}}: {{activeItem.APE_NOM_DEN_PARTE}}</span>
                                    </v-tooltip>
                                    <v-list-item-subtitle>Presentado por:</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.OBS_ESCRITO}}</v-list-item-title>
                                    <v-list-item-subtitle>Observación</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.NOM_USUARIO_REG_ESCRITO}}</v-list-item-title>
                                    <v-list-item-subtitle>Usuario de Mesa de Partes</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.RUTA_FTP_ARCHIVO_ESCRITO}}{{activeItem.NOM_ARCH_DOC_DIG_ESCRITO}}</v-list-item-title>
                                    <v-list-item-subtitle>Archivo Escrito</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list-item-group>
                    </v-list>
                </v-col>
                <v-col cols="12" md="4">
                    <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>ESTADO DEL ESCRITO</v-subheader>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title><b>{{activeItem.DEN_ESTADO_ESCRITO}}</b></v-list-item-title>
                                    <v-list-item-subtitle>Estado</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_ACTO_PROC_PROYECTO}} {{activeItem.DEN_RESOL_PROYECTO}}</v-list-item-title>
                                    <v-list-item-subtitle>Resolución Proyecto</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                             <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.SUMILLA_RESPUESTA}}</v-list-item-title>
                                    <v-list-item-subtitle>Sumilla Resolución</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider><v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.FECHA_RESOL_RESPUESTA}}</v-list-item-title>
                                    <v-list-item-subtitle>Fecha de Resolución</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider><v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.NOM_USUARIO_ESP_RESPUESTA}}</v-list-item-title>
                                    <v-list-item-subtitle>Usuario respuesta</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.RUTA_FTP_ARCHIVO_RESOLUCION}}{{activeItem.NOM_ARCHIVO_PDF_RESOLUCION}}</v-list-item-title>
                                    <v-list-item-subtitle>Resolución PDF</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.RUTA_FTP_ARCHIVO_RESOLUCION}}{{activeItem.NOM_ARCHIVO_DOC_RESOLUCION}}</v-list-item-title>
                                    <v-list-item-subtitle>Resolución DOC</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            
                        </v-list-item-group>
                    </v-list>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
<script>
export default {
    props:['activeItem'],
    
}
</script>