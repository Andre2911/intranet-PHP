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
                            <v-subheader>DATOS DE LA RESOLUCIÓN</v-subheader>
                            <template v-if="activeItem.COD_CONDICION_PROYECTO != 'X'">
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DEN_TIPO_ACTO_PROCESAL}}</v-list-item-title>
                                        <v-list-item-subtitle>Acto Procesal</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DEN_RESOL_PROYECTO}} - {{activeItem.NUM_RESOL_PROYECTO}}</v-list-item-title>
                                        <v-list-item-subtitle>Número de Resolución</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.FECHA_RESOL_PROYECTO}}</v-list-item-title>
                                        <v-list-item-subtitle>Fecha de Resolución Proyecto</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.NOM_USUARIO_PROYECTO}}</v-list-item-title>
                                        <v-list-item-subtitle>Usuario que creo Proyecto</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-list-item  @click="(activeItem.RUTA_FTP_ARCHIVO_RESOLUCION != null && activeItem.NOM_ARCHIVO_DOC_RESOLUCION != null)? getFile(activeItem.NOM_ARCHIVO_DOC_RESOLUCION):''">
                                    <v-list-item-content>
                                        <v-btn color="info" outlined small v-if="activeItem.RUTA_FTP_ARCHIVO_RESOLUCION != null && activeItem.NOM_ARCHIVO_DOC_RESOLUCION != null">
                                            <v-icon>mdi-file-word</v-icon>
                                            DESCARGAR PROYECTO
                                        </v-btn>
                                        <v-list-item-subtitle>Resolución DOC</v-list-item-subtitle>
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
                                    {{activeItem.DEN_CONDICION_PROYECTO}}
                                </v-alert>
                            </template>
                        </v-list-item-group>
                    </v-list>
                </v-col>
                <v-col cols="12" md="4">
                    <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>ESTADO DE LA DESCARGA</v-subheader>
                            <template v-if="activeItem.COD_CONDICION_PROYECTO != 'X' && activeItem.COD_VISUALIZACION_DESCARGO != 'N'">
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title><b>{{activeItem.DEN_ESTADO_ACTO_PROCESAL}}</b></v-list-item-title>
                                        <v-list-item-subtitle>Estado del Acto Procesal</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.NOM_USUARIO_DESCARGO}}</v-list-item-title>
                                        <v-list-item-subtitle>Usuario Descargo Resolución</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider><v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.FECHA_RESOL_DESCARGO}}</v-list-item-title>
                                        <v-list-item-subtitle>Fecha de Descargo Resolución</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider><v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.NOM_USUARIO_JUEZ}}</v-list-item-title>
                                        <v-list-item-subtitle>Usuario Juez</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider><v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DEN_ACTO_PROCESAL_HITO}}</v-list-item-title>
                                        <v-list-item-subtitle><b>Hito</b> Acto Procesal</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item  @click="(activeItem.RUTA_FTP_ARCHIVO_RESOLUCION != null && activeItem.NOM_ARCHIVO_PDF_RESOLUCION != null)? getFile(activeItem.NOM_ARCHIVO_PDF_RESOLUCION):''">
                                    <v-list-item-content>
                                        <v-btn color="error" outlined small v-if="activeItem.RUTA_FTP_ARCHIVO_RESOLUCION != null && activeItem.NOM_ARCHIVO_PDF_RESOLUCION != null">
                                            <v-icon>mdi-file-pdf</v-icon>
                                            VISUALIZAR RESOLUCIÓN
                                        </v-btn>
                                        <v-list-item-subtitle>Resolución PDF</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                
                            </template>
                            <template v-else>
                                <v-alert
                                    color="warning"
                                    border="left"
                                    elevation="2"
                                    colored-border
                                    icon="mdi-alert"
                                    >
                                        <template v-if="activeItem.COD_CONDICION_PROYECTO == 'X'">
                                            {{activeItem.DEN_CONDICION_PROYECTO}}
                                        </template>
                                        <template v-else-if="activeItem.COD_VISUALIZACION_DESCARGO == 'N'">
                                            {{activeItem.DEN_VISUALIZACION_DESCARGO}}
                                        </template>
                                    </v-alert>
                            </template>
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
        getFile(filename){
            this.$emit('showLoad', 'primary', 'Obteniendo archivo')


            let url = '../consultasSIJ/getAudActa?ruta=' + this.activeItem.RUTA_FTP_ARCHIVO_RESOLUCION + 
                                                                                    '&filename=' + filename + 
                                                                                    '&destino=\\172.17.176.17\resoluciones$' + 
                                                                                    '&ftpUsername=' + this.activeItem.USUARIO_FTP_ARCHIVO_RESOLUC +
                                                                                    '&ftpPassword=' + this.activeItem.CLAVE_FTP_ARCHIVO_RESOLUCION + 
                                                                                    '&ftpIP=' + this.activeItem.IP_FTP_ARCHIVO_RESOLUCION +
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