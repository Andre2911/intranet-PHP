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
            <v-row class="mt-1">
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
                            <template  v-if="activeItem.COD_VISUALIZACION_ESCRITO != 'N'">
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
                                        <v-list-item-subtitle>Usuario Registro de Mesa de Partes</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.NOM_USUARIO_ASOC_DOC_DIG_ESC}}</v-list-item-title>
                                        <v-list-item-subtitle>Usuario Digitalización de Mesa de Partes</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item @click="(activeItem.RUTA_FTP_ARCHIVO_ESCRITO != null && activeItem.NOM_ARCH_DOC_DIG_ESCRITO != null)? getFile(activeItem.NOM_ARCH_DOC_DIG_ESCRITO, activeItem.RUTA_FTP_ARCHIVO_ESCRITO, activeItem.USUARIO_FTP_ARCHIVO_ESCRITO, activeItem.CLAVE_FTP_ARCHIVO_ESCRITO, activeItem.IP_FTP_ARCHIVO_ESCRITO):''">
                                    <v-list-item-content>
                                        <v-btn color="error" outlined small v-if="activeItem.RUTA_FTP_ARCHIVO_ESCRITO != null && activeItem.NOM_ARCH_DOC_DIG_ESCRITO != null">
                                            <v-icon>mdi-file-pdf</v-icon>
                                            VISUALIZAR ESCRITO
                                        </v-btn>
                                        <v-list-item-subtitle>Archivo Escrito</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </template>
                            <template v-else>
                                <v-alert
                                    color="warning"
                                    border="left"
                                    elevation="2"
                                    colored-border
                                    icon="mdi-alert"
                                    >
                                    {{activeItem.DEN_VISUALIZACION_ESCRITO}}
                                </v-alert>
                            </template>
                        </v-list-item-group>
                    </v-list>
                </v-col>
                <v-col cols="12" md="4">
                    <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>ESTADO DEL ESCRITO</v-subheader>
                            <template  v-if="activeItem.COD_VISUALIZACION_ESCRITO != 'N'">

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
                                <v-list-item @click="(activeItem.RUTA_FTP_ARCHIVO_RESOLUCION != null && activeItem.NOM_ARCHIVO_PDF_RESOLUCION != null)? getFile(activeItem.NOM_ARCHIVO_PDF_RESOLUCION, activeItem.RUTA_FTP_ARCHIVO_RESOLUCION, activeItem.USUARIO_FTP_ARCHIVO_RESOLUC, activeItem.CLAVE_FTP_ARCHIVO_RESOLUCION, activeItem.IP_FTP_ARCHIVO_RESOLUCION):''">
                                    <v-list-item-content>
                                        <v-btn color="error" outlined small v-if="activeItem.RUTA_FTP_ARCHIVO_RESOLUCION != null && activeItem.NOM_ARCHIVO_PDF_RESOLUCION != null">
                                            <v-icon>mdi-file-pdf</v-icon>
                                            VISUALIZAR RESOLUCIÓN
                                        </v-btn>
                                        <v-list-item-subtitle>Resolución PDF</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </template>
                            <template v-else>
                                <v-alert
                                    color="warning"
                                    border="left"
                                    elevation="2"
                                    colored-border
                                    icon="mdi-alert"
                                    >
                                    {{activeItem.DEN_VISUALIZACION_ESCRITO}}
                                </v-alert>
                            </template>
                           <!-- <v-divider class="mt-0 mb-0"></v-divider>
                           <v-list-item @click="(activeItem.RUTA_FTP_ARCHIVO_RESOLUCION != null)? getFile(activeItem.NOM_ARCHIVO_DOC_RESOLUCION, activeItem.RUTA_FTP_ARCHIVO_RESOLUCION, activeItem.USUARIO_FTP_ARCHIVO_RESOLUC, activeItem.CLAVE_FTP_ARCHIVO_RESOLUCION, activeItem.IP_FTP_ARCHIVO_RESOLUCION):''">
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.RUTA_FTP_ARCHIVO_RESOLUCION}}{{activeItem.NOM_ARCHIVO_DOC_RESOLUCION}}</v-list-item-title>
                                    <v-list-item-subtitle>Resolución DOC</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            -->
                        </v-list-item-group>
                    </v-list>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
<script>
export default {
    props:['activeItem', 'dataUser'],
    methods: {
        getFile(filename, ruta, username, password, ipFTP){
            this.$emit('showLoad', 'primary', 'Obteniendo archivo')


            let url = '../consultasSIJ/getAudActa?ruta=' + ruta + 
                                                                                    '&filename=' + filename + 
                                                                                    '&destino=\\172.17.176.17\resoluciones$' + 
                                                                                    '&ftpUsername=' + username +
                                                                                    '&ftpPassword=' + password + 
                                                                                    '&ftpIP=' + ipFTP +
                                                                                    '&filename_destino=' + this.dataUser.usuario.numero_documento
                                                                                    ;

            axios.get(url)
                .then(response =>{
                    console.log(response.data)

                    if(response.data['status'] != 0 && response.data['data']['Status'] != 0){
                        window.open(    
                            "http://172.17.176.17/intranet/storage/app/resoluciones/tmp/"+response.data['data']['FilenameD'], "_blank");
                    } else{
                        this.$emit('showSnack', 'warning', 'No se pudo realizar la descarga, comuniquese con el administrador')
                    }
                    this.$emit('showLoad', '', '', false)

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                    }
                    if (errors.message === "Network Error"){}
                    if (errors.response.status === 500){}
                });
        }
    },
}
</script>